<?php
session_start();
include './includes/connect.php';

// Redirect if not logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$employee_id = $_SESSION['employee_id'] ?? $_SESSION['user_id'];
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$currentYear = (int)date('Y');
$monthYear = sprintf("%d-%02d", $currentYear, $currentMonth);

// Fetch employee name
$sql_emp = "SELECT fullName FROM employees WHERE employee_id = ?";
$stmt_emp = $con->prepare($sql_emp);
$stmt_emp->bind_param("s", $employee_id);
$stmt_emp->execute();
$emp_result = $stmt_emp->get_result();
$employee = $emp_result->fetch_assoc();
$stmt_emp->close();

if (!$employee) {
    $_SESSION['error'] = "Employee not found.";
    header("Location: login.php");
    exit;
}
$fullName = $employee['fullName'];

// Fetch payslip
$sql_payslip = "SELECT total_present_days, total_leave_days, total_absent_days, gross_salary, net_salary,
                ssf_employee, ssf_employer, pf_employee, pf_employer, tax_deduction, festival_bonus,
                overtime_pay, overtime_hours, status
                FROM payslips
                WHERE employee_id = ? AND month = ?";
$stmt_payslip = $con->prepare($sql_payslip);
$stmt_payslip->bind_param("ss", $employee_id, $monthYear);
$stmt_payslip->execute();
$payslip_result = $stmt_payslip->get_result();
$payslip_data = $payslip_result->fetch_assoc();
$stmt_payslip->close();

function getMonthName($m) {
    return date("F", mktime(0, 0, 0, $m, 10));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Payslip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>
    <?php include './includes/sidebar.php'; ?>

    <div class="content">
        <?php include './includes/header.php'; ?>
        <div class="container my-5">
            <h2>My Payslip - <?= htmlspecialchars($fullName) ?></h2>

            <div class="my-4">
                <h5>View Payslip by Month - <?= $currentYear ?></h5>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <a href="?month=<?= $m ?>" class="btn btn-outline-primary mb-2 me-2 <?= $m == $currentMonth ? 'active' : '' ?>">
                        <?= getMonthName($m) ?>
                    </a>
                <?php endfor; ?>
            </div>

            <div class="card p-4 shadow-sm">
                <h5 class="mb-3">Payslip for <?= getMonthName($currentMonth) ?> <?= $currentYear ?></h5>

                <?php if ($payslip_data): ?>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Present Days</th>
                                <td><?= htmlspecialchars($payslip_data['total_present_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Leave Days</th>
                                <td><?= htmlspecialchars($payslip_data['total_leave_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Absent Days</th>
                                <td><?= htmlspecialchars($payslip_data['total_absent_days']) ?></td>
                            </tr>
                            <tr>
                                <th>Total Overtime Hours</th>
                                <td><?= htmlspecialchars($payslip_data['overtime_hours']) ?> hrs</td>
                            </tr>
                            <tr>
                                <th>Basic Salary</th>
                                <td>Rs. <?= number_format($payslip_data['gross_salary'] - $payslip_data['festival_bonus'] - $payslip_data['overtime_pay'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Festival Bonus</th>
                                <td>Rs. <?= number_format($payslip_data['festival_bonus'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Overtime Pay</th>
                                <td>Rs. <?= number_format($payslip_data['overtime_pay'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Gross Salary</th>
                                <td><strong>Rs. <?= number_format($payslip_data['gross_salary'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>SSF Deduction (Employee)</th>
                                <td>Rs. <?= number_format($payslip_data['ssf_employee'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>PF Deduction (Employee)</th>
                                <td>Rs. <?= number_format($payslip_data['pf_employee'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Tax Deduction</th>
                                <td>Rs. <?= number_format($payslip_data['tax_deduction'], 2) ?></td>
                            </tr>
                            <tr class="table-info">
                                <th>Net Salary</th>
                                <td><strong>Rs. <?= number_format($payslip_data['net_salary'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge <?= $payslip_data['status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= htmlspecialchars($payslip_data['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No payslip found for <?= getMonthName($currentMonth) ?> <?= $currentYear ?>.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?>
