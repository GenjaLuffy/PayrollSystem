<?php
session_start();
include './includes/connect.php';
include __DIR__ . '/../admin/algorithm/aes_helper.php'; // AES helper

// Redirect if not logged in
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$employee_id = $_SESSION['employee_id'] ?? $_SESSION['user_id'];
$monthYear = $_GET['month'] ?? date('Y-m');
[$currentYear, $currentMonth] = explode('-', $monthYear);
$currentYear = (int)$currentYear;
$currentMonth = (int)$currentMonth;

// --- Fetch employee info ---
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

// --- Fetch payslip ---
$sql_payslip = "SELECT total_present_days, full_paid_leave_days, half_paid_leave_days, total_absent_days,
                basic_salary, allowances, gross_salary, net_salary,
                ssf_employee, ssf_employer, pf_employee, pf_employer,
                tax_deduction, festival_bonus, overtime_pay, overtime_hours, status
                FROM payslips
                WHERE employee_id = ? AND month = ?";
$stmt_payslip = $con->prepare($sql_payslip);
$stmt_payslip->bind_param("ss", $employee_id, $monthYear);
$stmt_payslip->execute();
$payslip_result = $stmt_payslip->get_result();
$payslip_data = $payslip_result->fetch_assoc();
$stmt_payslip->close();

// --- Decrypt numeric fields ---
if ($payslip_data) {
    $fields = [
        'full_paid_leave_days', 'half_paid_leave_days', 'overtime_hours',
        'basic_salary', 'allowances', 'gross_salary', 'net_salary',
        'ssf_employee', 'ssf_employer', 'pf_employee', 'pf_employer',
        'tax_deduction', 'festival_bonus', 'overtime_pay'
    ];

    foreach ($fields as $f) {
        $decrypted = decryptAES_GCM($payslip_data[$f]); // Use your actual decrypt function
        $payslip_data[$f] = is_numeric($decrypted) ? (float)$decrypted : 0;
    }
}

// --- Calculate total earnings and deductions ---
if ($payslip_data) {
    $totalEarnings = $payslip_data['basic_salary'] + $payslip_data['allowances'] + $payslip_data['overtime_pay'] + $payslip_data['festival_bonus'];
    $totalDeductions = $payslip_data['ssf_employee'] + $payslip_data['pf_employee'] + $payslip_data['tax_deduction'];
}

// --- Helper ---
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .deduction { background-color: #ffe5e5; }
        .earning { background-color: #e5ffe5; }
        .highlight-net { background-color: #fff3cd; }
    </style>
</head>
<body>
<?php include './includes/sidebar.php'; ?>
<div class="content">
    <?php include './includes/header.php'; ?>
    <div class="container my-5">
        <h2>My Payslip - <?= htmlspecialchars($fullName) ?></h2>

        <div class="my-4">
            <h5>Select Month</h5>
            <form method="get" class="d-flex flex-wrap mb-3">
                <input type="month" name="month" class="form-control w-auto me-2" value="<?= htmlspecialchars($monthYear) ?>" max="<?= date('Y-m') ?>" />
                <button type="submit" class="btn btn-primary">View</button>
                <?php if ($payslip_data): ?>
                    <button type="button" class="btn btn-success ms-2" onclick="window.print()">Print Payslip</button>
                <?php endif; ?>
            </form>
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
                            <th>Full Paid Leave</th>
                            <td><?= number_format($payslip_data['full_paid_leave_days'], 1) ?></td>
                        </tr>
                        <tr>
                            <th>Half Paid Leave</th>
                            <td><?= number_format($payslip_data['half_paid_leave_days'], 1) ?></td>
                        </tr>
                        <tr>
                            <th>Absent Days</th>
                            <td><?= htmlspecialchars($payslip_data['total_absent_days']) ?></td>
                        </tr>

                        <tr class="earning">
                            <th>Basic Salary</th>
                            <td>Rs. <?= number_format($payslip_data['basic_salary'], 2) ?></td>
                        </tr>
                        <tr class="earning">
                            <th>Allowances</th>
                            <td>Rs. <?= number_format($payslip_data['allowances'], 2) ?></td>
                        </tr>
                        <tr class="earning">
                            <th>Overtime Pay (<?= number_format($payslip_data['overtime_hours'], 1) ?> hrs)</th>
                            <td>Rs. <?= number_format($payslip_data['overtime_pay'], 2) ?></td>
                        </tr>
                        <tr class="earning">
                            <th>Festival Bonus</th>
                            <td>Rs. <?= number_format($payslip_data['festival_bonus'], 2) ?></td>
                        </tr>
                        <tr class="table-info">
                            <th>Total Earnings</th>
                            <td><strong>Rs. <?= number_format($totalEarnings, 2) ?></strong></td>
                        </tr>

                        <tr class="deduction" title="SSF Employee Contribution">
                            <th>SSF Deduction</th>
                            <td>Rs. <?= number_format($payslip_data['ssf_employee'], 2) ?></td>
                        </tr>
                        <tr class="deduction" title="PF Employee Contribution">
                            <th>PF Deduction</th>
                            <td>Rs. <?= number_format($payslip_data['pf_employee'], 2) ?></td>
                        </tr>
                        <tr class="deduction" title="Tax Deduction">
                            <th>Tax Deduction</th>
                            <td>Rs. <?= number_format($payslip_data['tax_deduction'], 2) ?></td>
                        </tr>
                        <tr class="table-danger">
                            <th>Total Deductions</th>
                            <td><strong>Rs. <?= number_format($totalDeductions, 2) ?></strong></td>
                        </tr>

                        <tr class="<?= $payslip_data['net_salary'] < $payslip_data['gross_salary'] ? 'highlight-net' : '' ?>">
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

                <?php if ($payslip_data['status'] === 'Paid'): ?>
                    <a href="download_payslip.php?month=<?= $monthYear ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-download"></i> Download PDF
                    </a>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    No payslip found for <?= getMonthName($currentMonth) ?> <?= $currentYear ?>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?>
