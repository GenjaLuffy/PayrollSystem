<?php
session_start();
include './includes/header.php';
include './includes/connect.php';
include './algorithm/PayrollCalculator.php';

// Current month/year
$currentMonth = date('n');
$currentYear = date('Y');
$monthYearStr = sprintf("%d-%02d", $currentYear, $currentMonth);

// ===== KPI 1: Total Employees =====
$totalEmployees = 0;
$result = $con->query("SELECT COUNT(*) AS total FROM employees");
if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployees = intval($row['total']);
}

// ===== KPI 2: Remaining to Pay =====
$remainingToPay = 0;
$res = $con->query("SELECT net_salary FROM payslips WHERE month = '$monthYearStr' AND status <> 'Paid'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $remainingToPay += floatval($row['net_salary']);
    }
}

// ===== KPI 3: Pending Leaves =====
$pendingLeaves = 0;
$res = $con->query("SELECT COUNT(*) AS pending FROM leave_requests WHERE status = 'Pending'");
if ($res) {
    $row = $res->fetch_assoc();
    $pendingLeaves = intval($row['pending']);
}

// ===== Monthly Payroll Chart =====
$monthlyPayroll = [];
for ($m = 1; $m <= 12; $m++) {
    $monthStr = sprintf("%d-%02d", $currentYear, $m);
    $res = $con->query("SELECT SUM(net_salary) AS total FROM payslips WHERE month = '$monthStr' AND status = 'Paid'");
    $row = $res->fetch_assoc();
    $monthlyPayroll[] = floatval($row['total'] ?? 0);
}

// ===== Recent Payslips =====
$recentPayslips = [];
$res = $con->query("
   SELECT p.employee_id, p.month, p.status, p.gross_salary, p.net_salary, p.overtime_pay,
          e.fullName AS name
   FROM payslips p
   JOIN employees e ON p.employee_id = e.employee_id
   ORDER BY p.month DESC
   LIMIT 10
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $gross = floatval($row['gross_salary']);
        $net = floatval($row['net_salary']);
        $overtime = floatval($row['overtime_pay']);

        // Calculate deductions and bonus
        $deductions = $gross - $net - $overtime;
        $bonus = $overtime;

        $recentPayslips[] = [
            'name' => $row['name'],
            'month' => $row['month'],
            'gross' => $gross,
            'deductions' => $deductions,
            'bonus' => $bonus,
            'net' => $net,
            'status' => $row['status']
        ];
    }
}

$con->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll Dashboard</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include './includes/sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Dashboard</h2>

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <a href="./employees.php" style="text-decoration: none; color: inherit;">
                            <div class="card-body">
                                <h5 class="card-title text-white">Total Employees</h5>
                                <p class="card-text fs-4 text-white"><?= htmlspecialchars($totalEmployees) ?></p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Remaining to Pay</h5>
                            <p class="card-text fs-4">Rs. <?= number_format($remainingToPay, 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <a href="./leave.php" style="text-decoration: none; color: inherit;">
                            <div class="card-body">
                                <h5 class="card-title text-white">Pending Leaves</h5>
                                <p class="card-text fs-4 text-white"><?= htmlspecialchars($pendingLeaves) ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Payslips -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Recent Payslips</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Month</th>
                                    <th>Gross Salary (Rs)</th>
                                    <th>Deductions (Rs)</th>
                                    <th>Bonus (Rs)</th>
                                    <th>Net Pay (Rs)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentPayslips)): ?>
                                    <?php foreach ($recentPayslips as $payslip): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($payslip['name']) ?></td>
                                            <td><?= htmlspecialchars($payslip['month']) ?></td>
                                            <td><?= number_format($payslip['gross'], 2) ?></td>
                                            <td><?= number_format($payslip['deductions'], 2) ?></td>
                                            <td><?= number_format($payslip['bonus'], 2) ?></td>
                                            <td><?= number_format($payslip['net'], 2) ?></td>
                                            <td>
                                                <span class="badge <?= $payslip['status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= htmlspecialchars($payslip['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No payslips found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Monthly Payroll Chart -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Monthly Payroll (<?= $currentYear ?>)</h5>
                    <canvas id="monthlyPayrollChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('monthlyPayrollChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Payroll (Rs)',
            data: <?= json_encode($monthlyPayroll) ?>,
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
    