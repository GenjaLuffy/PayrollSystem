<?php
session_start();
include './includes/connect.php';

// Check if user is logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$employee_id = $_SESSION['employee_id'] ?? $_SESSION['user_id']; // Fallback to user_id
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m'); // Default to June 2025
$currentYear = (int)date('Y'); // 2025

// Fetch basic salary and full name from employees table
$sql_emp = "SELECT salary, fullName FROM employees WHERE employee_id = ?";
$stmt_emp = $con->prepare($sql_emp);
$stmt_emp->bind_param("s", $employee_id);
$stmt_emp->execute();
$res_emp = $stmt_emp->get_result();
$emp = $res_emp->fetch_assoc();

if (!$emp) {
    $_SESSION['error'] = "Employee not found.";
    header("Location: login.php");
    exit;
}

$fullName = $emp['fullName'];
$basic_salary = (float)$emp['salary']; // Use salary as basic_salary directly

// Calculate working days and attendance data for the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
$weekends = floor($daysInMonth / 7) * 2 + ($daysInMonth % 7 > 5 ? 2 : ($daysInMonth % 7 > 0 ? 1 : 0));
$totalWorkingDays = $daysInMonth - $weekends;

// Fetch attendance data
$sql_att = "SELECT COUNT(*) as present_days, SUM(overtime_hours) as total_overtime_hours 
            FROM attendance 
            WHERE employee_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND status = 'Present'";
$stmt_att = $con->prepare($sql_att);
$stmt_att->bind_param("sii", $employee_id, $currentMonth, $currentYear);
$stmt_att->execute();
$att_result = $stmt_att->get_result();
$att_data = $att_result->fetch_assoc();
$present_days = (int)$att_data['present_days'];
$total_overtime_hours = (float)($att_data['total_overtime_hours'] ?? 0);

// Fetch approved leave days
$sql_leave = "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as leave_days 
              FROM leave_requests 
              WHERE employee_id = ? AND status = 'Approved' 
              AND MONTH(start_date) = ? AND YEAR(start_date) = ?";
$stmt_leave = $con->prepare($sql_leave);
$stmt_leave->bind_param("sii", $employee_id, $currentMonth, $currentYear);
$stmt_leave->execute();
$leave_result = $stmt_leave->get_result();
$leave_days = (int)($leave_result->fetch_assoc()['leave_days'] ?? 0);

$absent_days = max(0, $totalWorkingDays - ($present_days + $leave_days));

// Calculate payslip data based on attendance using basic_salary as base
$daily_rate = $basic_salary / $totalWorkingDays;
$pro_rated_basic_salary = $daily_rate * $present_days; // Pro-rated based on present days
$allowances = ($basic_salary * 0.3) * ($present_days / $totalWorkingDays); // 30% of basic_salary, pro-rated
$overtime_rate = ($basic_salary / ($totalWorkingDays * 8)) * 1.5; // 1.5x hourly rate for 8-hour day
$overtime_pay = $total_overtime_hours * $overtime_rate;
$ssf_deduction = $pro_rated_basic_salary * 0.11; // 11% SSF on pro-rated basic salary
$pf_deduction = $pro_rated_basic_salary * 0.05; // 5% PF on pro-rated basic salary
$other_deduction = $pro_rated_basic_salary * 0.05; // 5% other deduction
$deductions = $ssf_deduction + $pf_deduction + $other_deduction;
$net_salary = $pro_rated_basic_salary + $allowances + $overtime_pay - $deductions;
$paid = 0; // Assume unpaid unless integrated with payment data
$payment_date = null;

$currentPayslipData = [
    'basic_salary' => $pro_rated_basic_salary,
    'allowances' => $allowances,
    'overtime_pay' => $overtime_pay,
    'ssf_deduction' => $ssf_deduction,
    'pf_deduction' => $pf_deduction,
    'deductions' => $deductions,
    'net_salary' => $net_salary,
    'paid' => $paid,
    'payment_date' => $payment_date,
    'month' => sprintf("%d-%02d", $currentYear, $currentMonth), // e.g., "2025-06"
    'present_days' => $present_days,
    'leave_days' => $leave_days,
    'absent_days' => $absent_days,
    'total_overtime_hours' => $total_overtime_hours,
];

$allMonths = range(1, 12);

// Define getMonthName function
function getMonthName($monthNum)
{
    return date("F", mktime(0, 0, 0, (int)$monthNum, 10));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Payslips</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <?php include './includes/header.php'; ?>
        <div class="container my-5">
            <h2>My Payslips for <?= htmlspecialchars($fullName) ?></h2>

            <div class="mt-4">
                <h5>View Payslip By Month (Year <?= htmlspecialchars($currentYear) ?>)</h5>
                <div class="mb-3">
                    <?php foreach ($allMonths as $monthNum): ?>
                        <?php $monthName = getMonthName($monthNum); ?>
                        <a href="?month=<?= $monthNum ?>" class="btn btn-outline-primary me-2 mb-2">
                            <?= htmlspecialchars($monthName) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm p-4 mt-3">
                <h5>Current Month (<?= htmlspecialchars(getMonthName($currentMonth)) ?> <?= htmlspecialchars($currentYear) ?>)</h5>
                <?php if ($currentPayslipData): ?>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Present Days</th>
                                <td><?= htmlspecialchars($currentPayslipData['present_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Leave Days</th>
                                <td><?= htmlspecialchars($currentPayslipData['leave_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Absent Days</th>
                                <td><?= htmlspecialchars($currentPayslipData['absent_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Total Overtime Hours</th>
                                <td><?= htmlspecialchars($currentPayslipData['total_overtime_hours']) ?></td>
                            </tr>
                            <tr>
                                <th>Basic Salary</th>
                                <td>Rs. <?= number_format($currentPayslipData['basic_salary'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Allowances</th>
                                <td>Rs. <?= number_format($currentPayslipData['allowances'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Overtime Pay</th>
                                <td>Rs. <?= number_format($currentPayslipData['overtime_pay'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>SSF Deduction</th>
                                <td>Rs. <?= number_format($currentPayslipData['ssf_deduction'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>PF Deduction</th>
                                <td>Rs. <?= number_format($currentPayslipData['pf_deduction'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Other Deductions</th>
                                <td>Rs. <?= number_format($currentPayslipData['deductions'] - $currentPayslipData['ssf_deduction'] - $currentPayslipData['pf_deduction'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Net Salary</th>
                                <td><strong>Rs. <?= number_format($currentPayslipData['net_salary'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge <?= $currentPayslipData['paid'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $currentPayslipData['paid'] ? 'Paid' : 'Need to Pay' ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No salary data calculated for this month.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt_emp->close();
$stmt_att->close();
$stmt_leave->close();
$con->close();
?>