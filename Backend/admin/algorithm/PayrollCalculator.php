<?php
include './includes/connect.php';

// ================================
// === Constants & Configurations ===
// ================================
define('MINIMUM_WAGE', 17300);
define('SSF_EMPLOYEE_RATE', 0.11);
define('SSF_EMPLOYER_RATE', 0.20);
define('PF_EMPLOYEE_RATE', 0.05);
define('PF_EMPLOYER_RATE', 0.05);
define('WORKING_DAYS_PER_MONTH', 26);

// Tax Slabs (Annual)
$taxSlabs = [
    ['limit' => 500000, 'rate' => 0.01],
    ['limit' => 700000, 'rate' => 0.10],
    ['limit' => 1000000, 'rate' => 0.20],
    ['limit' => 2000000, 'rate' => 0.30],
    ['limit' => PHP_INT_MAX, 'rate' => 0.36],
];

// ================================
// === Payroll Calculator Class ===
// ================================
class PayrollCalculator {
    private $conn;
    private $employeeId;
    private $month;
    private $year;

    private $monthlySalary;
    private $dailySalary;
    private $basicSalaryPerDay;
    private $allowancePerDay;
    private $employeeName;

    private $presentDays = 0;
    private $leaveDaysTaken = 0;
    private $halfPaidLeaveDays = 0;
    private $absentDays = 0;
    private $overtimeHours = 0;

    public function __construct($conn, $employeeId, $month, $year) {
        $this->conn = $conn;
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;

        $this->loadEmployeeData();
        $this->loadAttendanceData();
    }

    // ======================
    // === Load Employee Data ===
    // ======================
    private function loadEmployeeData() {
        $sql = "SELECT salary, fullName FROM employees WHERE employee_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $this->employeeId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->monthlySalary = max($row['salary'], MINIMUM_WAGE);
            $this->dailySalary = $this->monthlySalary / WORKING_DAYS_PER_MONTH;
            $this->basicSalaryPerDay = $this->dailySalary * 0.7;
            $this->allowancePerDay = $this->dailySalary * 0.3;
            $this->employeeName = $row['fullName'];
        } else {
            throw new Exception("Employee not found.");
        }

        $stmt->close();
    }

    // ======================
    // === Load Attendance & Leave Data ===
    // ======================
    private function loadAttendanceData() {
        // --- Full Paid Leave (Sick, Annual) ---
        $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) AS full_paid_leave
                FROM leave_requests
                WHERE employee_id = ? AND status = 'Approved'
                AND leave_type IN ('Sick', 'Annual')
                AND MONTH(start_date) = ? AND YEAR(start_date) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $fullPaidLeave = (int)($result->fetch_assoc()['full_paid_leave'] ?? 0);
        $stmt->close();

        // --- Half Paid Leave (Paternity, Bereavement) ---
        $sql = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) AS half_paid_leave
                FROM leave_requests
                WHERE employee_id = ? AND status = 'Approved'
                AND leave_type IN ('Paternity', 'Bereavement')
                AND MONTH(start_date) = ? AND YEAR(start_date) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $halfPaidLeave = (int)($result->fetch_assoc()['half_paid_leave'] ?? 0);
        $stmt->close();

        // --- Present Days ---
        $sql = "SELECT COUNT(*) AS present_days
                FROM attendance
                WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND status = 'Present'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->presentDays = (int)($result->fetch_assoc()['present_days'] ?? 0);
        $stmt->close();

        // --- Overtime Hours ---
        $sql = "SELECT SUM(overtime_hours) AS overtime_hours
                FROM attendance
                WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
                AND status IN ('Present', 'Overtime')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $this->employeeId, $this->month, $this->year);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->overtimeHours = (float)($result->fetch_assoc()['overtime_hours'] ?? 0);
        $stmt->close();

        // --- Store leave counts ---
        $this->halfPaidLeaveDays = $halfPaidLeave;
        $this->leaveDaysTaken = $fullPaidLeave + $halfPaidLeave;

        // --- Absences ---
        $totalWorkDays = WORKING_DAYS_PER_MONTH;
        $this->absentDays = max(0, $totalWorkDays - ($this->presentDays + $this->leaveDaysTaken));
    }

    // ======================
    // === Salary Calculations ===
    // ======================
    private function calculateBasicSalary() {
        $fullPaid = $this->presentDays + ($this->leaveDaysTaken - $this->halfPaidLeaveDays);
        $halfPaid = $this->halfPaidLeaveDays;
        $salary = ($fullPaid * $this->basicSalaryPerDay) + ($halfPaid * $this->basicSalaryPerDay * 0.5);
        return round($salary, 2);
    }

    private function calculateAllowances() {
        $fullPaid = $this->presentDays + ($this->leaveDaysTaken - $this->halfPaidLeaveDays);
        $halfPaid = $this->halfPaidLeaveDays;
        $allowance = ($fullPaid * $this->allowancePerDay) + ($halfPaid * $this->allowancePerDay * 0.5);
        return round($allowance, 2);
    }

    private function calculateOvertimePay() {
        $hourlyRate = $this->basicSalaryPerDay / 8;
        return round($this->overtimeHours * ($hourlyRate * 1.5), 2);
    }

    public function calculateGrossSalary() {
        return round(
            $this->calculateBasicSalary() +
            $this->calculateAllowances() +
            $this->calculateOvertimePay(), 2
        );
    }

    // ======================
    // === Deductions ===
    // ======================
    public function calculateSSF() {
        $basic = $this->calculateBasicSalary();
        return [
            'employee' => round($basic * SSF_EMPLOYEE_RATE, 2),
            'employer' => round($basic * SSF_EMPLOYER_RATE, 2)
        ];
    }

    public function calculatePF() {
        $basic = $this->calculateBasicSalary();
        return [
            'employee' => round($basic * PF_EMPLOYEE_RATE, 2),
            'employer' => round($basic * PF_EMPLOYER_RATE, 2)
        ];
    }

    public function calculateTax() {
        global $taxSlabs;

        $gross = $this->calculateGrossSalary();
        $ssf = $this->calculateSSF()['employee'];
        $pf = $this->calculatePF()['employee'];

        $monthlyTaxable = $gross - $ssf - $pf;
        $annualTaxable = $monthlyTaxable * 12;

        $tax = 0;
        $prevLimit = 0;

        foreach ($taxSlabs as $slab) {
            if ($annualTaxable > $prevLimit) {
                $portion = min($annualTaxable, $slab['limit']) - $prevLimit;
                $tax += $portion * $slab['rate'];
                $prevLimit = $slab['limit'];
            } else {
                break;
            }
        }

        return round($tax / 12, 2); // monthly tax
    }

    // ======================
    // === Net Salary ===
    // ======================
    public function calculateNetSalary() {
        return round(
            $this->calculateGrossSalary()
            - $this->calculateSSF()['employee']
            - $this->calculatePF()['employee']
            - $this->calculateTax(), 2
        );
    }

    // ======================
    // === Payslip Generation ===
    // ======================
    public function generateAndStorePayslip() {
        if ($this->presentDays <= 0 && $this->leaveDaysTaken <= 0) {
            throw new Exception("No attendance or leave data. Cannot generate payslip.");
        }

        $gross = $this->calculateGrossSalary();
        $net = $this->calculateNetSalary();
        $ssf = $this->calculateSSF();
        $pf = $this->calculatePF();
        $tax = $this->calculateTax();
        $overtimePay = $this->calculateOvertimePay();
        $basicSalary = $this->calculateBasicSalary();
        $monthYear = sprintf("%d-%02d", $this->year, $this->month);

        // Insert into payslips table
        $sql = "INSERT INTO payslips (
                    employee_id, month, total_present_days, total_leave_days, total_absent_days,
                    gross_salary, net_salary, ssf_employee, ssf_employer, pf_employee, pf_employer,
                    tax_deduction, overtime_pay, overtime_hours, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiiidddddddds",
            $this->employeeId, $monthYear, $this->presentDays, $this->leaveDaysTaken, $this->absentDays,
            $gross, $net, $ssf['employee'], $ssf['employer'], $pf['employee'], $pf['employer'],
            $tax, $overtimePay, $this->overtimeHours
        );
        $stmt->execute();
        $stmt->close();

        // Insert into salaries table
        $sql = "INSERT INTO salaries (employee_id, month, year, basic_salary, paid_status)
                VALUES (?, ?, ?, ?, 'Paid')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siid", $this->employeeId, $this->month, $this->year, $basicSalary);
        $stmt->execute();
        $stmt->close();

        return $this->generatePayslip();
    }

    // ======================
    // === Generate Payslip Data (Array) ===
    // ======================
    public function generatePayslip() {
        $ssf = $this->calculateSSF();
        $pf = $this->calculatePF();

        return [
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'month' => sprintf("%d-%02d", $this->year, $this->month),
            'present_days' => $this->presentDays,
            'full_paid_leave_days' => $this->leaveDaysTaken - $this->halfPaidLeaveDays,
            'half_paid_leave_days' => $this->halfPaidLeaveDays,
            'absent_days' => $this->absentDays,
            'basic_salary' => $this->calculateBasicSalary(),
            'allowances' => $this->calculateAllowances(),
            'overtime_hours' => $this->overtimeHours,
            'overtime_pay' => $this->calculateOvertimePay(),
            'gross_salary' => $this->calculateGrossSalary(),
            'ssf_employee' => $ssf['employee'],
            'ssf_employer' => $ssf['employer'],
            'pf_employee' => $pf['employee'],
            'pf_employer' => $pf['employer'],
            'tax_deduction' => $this->calculateTax(),
            'net_salary' => $this->calculateNetSalary(),
        ];
    }
}
?>
