<?php
include './includes/connect.php';

// Configuration constants
define('MINIMUM_WAGE', 17300); // NPR 17,300/month (2025)
define('SSF_EMPLOYEE_RATE', 0.11); // 11% employee contribution
define('SSF_EMPLOYER_RATE', 0.20); // 20% employer contribution
define('PF_EMPLOYEE_RATE', 0.05); // 5% employee contribution
define('PF_EMPLOYER_RATE', 0.05); // 5% employer contribution
define('ANNUAL_LEAVE_DAYS', 15); // 15 days paid leave per year
define('WORKING_DAYS_PER_MONTH', 26); // Average working days per month
define('FESTIVAL_BONUS_RATE', 1/12); // 1/12th of basic salary as monthly festival bonus

// Tax slabs for single individual (2025, NPR)
$taxSlabs = [
    ['limit' => 500000, 'rate' => 0.01], // 1% on first 500,000
    ['limit' => 700000, 'rate' => 0.10], // 10% on next 200,000
    ['limit' => 1000000, 'rate' => 0.20], // 20% on next 300,000
    ['limit' => 2000000, 'rate' => 0.30], // 30% on next 1,000,000
    ['limit' => PHP_INT_MAX, 'rate' => 0.36] // 36% on above 2,000,000
];

class PayrollCalculator {
    private $conn;
    private $employeeId;
    private $month;
    private $year;
    private $basicSalary;
    private $allowances;
    private $festivalBonus;
    private $leaveDaysTaken;
    private $presentDays;
    private $absentDays;
    private $overtimeHours;

    public function __construct($conn, $employeeId, $month, $year) {
        $this->conn = $conn;
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;
        $this->loadEmployeeData();
        $this->loadAttendanceData();
    }

    private function loadEmployeeData() {
        $sql = "SELECT salary FROM employees WHERE employee_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $totalSalary = max($row['salary'], MINIMUM_WAGE);
            $this->basicSalary = $totalSalary * 0.7; // 70% as basic salary
            $this->allowances = $totalSalary * 0.3; // 30% as allowances
            $this->festivalBonus = $this->basicSalary * FESTIVAL_BONUS_RATE; // Monthly festival bonus
        } else {
            throw new Exception("Employee not found");
        }
        $stmt->close();
    }

    private function loadAttendanceData() {
        // Approved leave days
        $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as leave_days
                FROM leave_requests
                WHERE employee_id = ? AND status = 'Approved'
                AND MONTH(start_date) = ? AND YEAR(start_date) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->leaveDaysTaken = $result->fetch_assoc()['leave_days'] ?? 0;
        $stmt->close();

        // Present days
        $sql = "SELECT COUNT(*) as present_days
                FROM attendance
                WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
                AND status = 'Present'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->presentDays = $result->fetch_assoc()['present_days'] ?? 0;
        $stmt->close();

        // Overtime hours
        $sql = "SELECT SUM(overtime_hours) as overtime_hours
                FROM attendance
                WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
                AND status IN ('Present', 'Overtime')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->overtimeHours = $result->fetch_assoc()['overtime_hours'] ?? 0;
        $stmt->close();

        // Absent days
        $totalPossibleDays = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $weekends = floor($totalPossibleDays / 7) * 2 + ($totalPossibleDays % 7 > 5 ? 2 : ($totalPossibleDays % 7 > 0 ? 1 : 0));
        $workingDays = $totalPossibleDays - $weekends;
        $this->absentDays = max(0, $workingDays - ($this->presentDays + $this->leaveDaysTaken));
    }

    private function calculateLeavePay() {
        $dailyRate = $this->basicSalary / WORKING_DAYS_PER_MONTH;
        return min($this->leaveDaysTaken, ANNUAL_LEAVE_DAYS / 12) * $dailyRate;
    }

    private function calculateOvertimePay() {
        $hourlyRate = $this->basicSalary / (WORKING_DAYS_PER_MONTH * 8); // 8-hour workday
        return $this->overtimeHours * ($hourlyRate * 1.5); // 1.5x overtime rate
    }

    public function calculateGrossSalary() {
        $leavePay = $this->calculateLeavePay();
        $overtimePay = $this->calculateOvertimePay();
        return $this->basicSalary + $this->allowances + $this->festivalBonus + $leavePay + $overtimePay;
    }

    public function calculateSSF() {
        $employeeSSF = $this->basicSalary * SSF_EMPLOYEE_RATE;
        $employerSSF = $this->basicSalary * SSF_EMPLOYER_RATE;
        return ['employee' => $employeeSSF, 'employer' => $employerSSF];
    }

    public function calculatePF() {
        $employeePF = $this->basicSalary * PF_EMPLOYEE_RATE;
        $employerPF = $this->basicSalary * PF_EMPLOYER_RATE;
        return ['employee' => $employeePF, 'employer' => $employerPF];
    }

    public function calculateTax() {
        $grossSalary = $this->calculateGrossSalary();
        $monthlyTaxableIncome = $grossSalary - $this->calculateSSF()['employee'] - $this->calculatePF()['employee'];
        $annualTaxableIncome = $monthlyTaxableIncome * 12 * (($this->presentDays + $this->leaveDaysTaken) / WORKING_DAYS_PER_MONTH);
        $tax = 0;
        $previousLimit = 0;

        foreach ($GLOBALS['taxSlabs'] as $slab) {
            if ($annualTaxableIncome > $previousLimit) {
                $taxableInSlab = min($annualTaxableIncome, $slab['limit']) - $previousLimit;
                $tax += $taxableInSlab * $slab['rate'];
                $previousLimit = $slab['limit'];
            } else {
                break;
            }
        }

        return $tax / 12; // Monthly tax
    }

    public function calculateNetSalary() {
        $grossSalary = $this->calculateGrossSalary();
        $ssfEmployee = $this->calculateSSF()['employee'];
        $pfEmployee = $this->calculatePF()['employee'];
        $tax = $this->calculateTax();
        return $grossSalary - ($ssfEmployee + $pfEmployee + $tax);
    }

    public function generateAndStorePayslip() {
        $grossSalary = $this->calculateGrossSalary();
        $netSalary = $this->calculateNetSalary();
        $monthYear = sprintf("%d-%02d", $this->year, $this->month);
        $ssf = $this->calculateSSF();
        $pf = $this->calculatePF();
        $tax = $this->calculateTax();
        $overtimePay = $this->calculateOvertimePay();

        // Insert into payslips with status 'Paid'
        $sql = "INSERT INTO payslips (employee_id, month, total_present_days, total_leave_days, total_absent_days, 
                gross_salary, net_salary, ssf_employee, ssf_employer, pf_employee, pf_employer, tax_deduction, 
                festival_bonus, overtime_pay, overtime_hours, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiiiddddddddds", $this->employeeId, $monthYear, $this->presentDays, $this->leaveDaysTaken, 
                          $this->absentDays, $grossSalary, $netSalary, $ssf['employee'], $ssf['employer'], 
                          $pf['employee'], $pf['employer'], $tax, $this->festivalBonus, $overtimePay, $this->overtimeHours);
        $stmt->execute();
        $stmt->close();

        // Insert into salaries with paid_status 'Paid'
        $sql = "INSERT INTO salaries (employee_id, month, year, basic_salary, paid_status)
                VALUES (?, ?, ?, ?, 'Paid')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siid", $this->employeeId, $this->month, $this->year, $this->basicSalary);
        $stmt->execute();
        $stmt->close();

        return $this->generatePayslip();
    }

    public function generatePayslip() {
        $ssf = $this->calculateSSF();
        $pf = $this->calculatePF();
        return [
            'employee_id' => $this->employeeId,
            'month' => sprintf("%d-%02d", $this->year, $this->month),
            'basic_salary' => $this->basicSalary,
            'allowances' => $this->allowances,
            'festival_bonus' => $this->festivalBonus,
            'leave_pay' => $this->calculateLeavePay(),
            'overtime_pay' => $this->calculateOvertimePay(),
            'gross_salary' => $this->calculateGrossSalary(),
            'ssf_employee' => $ssf['employee'],
            'ssf_employer' => $ssf['employer'],
            'pf_employee' => $pf['employee'],
            'pf_employer' => $pf['employer'],
            'tax_deduction' => $this->calculateTax(),
            'net_salary' => $this->calculateNetSalary(),
            'present_days' => $this->presentDays,
            'leave_days' => $this->leaveDaysTaken,
            'absent_days' => $this->absentDays,
            'overtime_hours' => $this->overtimeHours,
            'status' => 'Paid'
        ];
    }
}
?>