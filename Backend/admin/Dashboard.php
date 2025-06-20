<?php 
include './includes/header.php'; 
include './includes/connect.php'; 
include './algorithm/PayrollCalculator.php'; // Assuming the PayrollCalculator class is in this file

// Get current month and year
$currentMonth = date('n'); // Numeric month (1-12)
$currentYear = date('Y'); // Full year

// 1. Total Employees
$totalEmployees = 0;
$result = $con->query("SELECT COUNT(*) AS total FROM employees");
if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployees = $row['total'];
}

// 2. Remaining to Pay (sum of net salaries for unpaid payslips)
$remainingToPay = 0;
$employeeQuery = $con->query("SELECT employee_id FROM employees");
if ($employeeQuery) {
    while ($row = $employeeQuery->fetch_assoc()) {
        $employeeId = $row['employee_id'];
        // Check if payslip exists and is unpaid for the current month
        $sql = "SELECT status FROM payslips WHERE employee_id = ? AND month = ?";
        $stmt = $con->prepare($sql);
        $monthYear = sprintf("%d-%02d", $currentYear, $currentMonth);
        $stmt->bind_param("ss", $employeeId, $monthYear);
        $stmt->execute();
        $payslipResult = $stmt->get_result();
        if ($payslipRow = $payslipResult->fetch_assoc()) {
            if ($payslipRow['status'] === 'Unpaid') {
                try {
                    $calculator = new PayrollCalculator($con, $employeeId, $currentMonth, $currentYear);
                    $remainingToPay += $calculator->calculateNetSalary();
                } catch (Exception $e) {
                    // Log error if needed, skip this employee
                    continue;
                }
            }
        }
        $stmt->close();
    }
}

// 3. Pending Leaves (count of leave requests with status 'Pending')
$pendingLeaves = 0;
$result = $con->query("SELECT COUNT(*) AS pending FROM leave_requests WHERE status = 'Pending'");
if ($result) {
    $row = $result->fetch_assoc();
    $pendingLeaves = $row['pending'];
}

$con->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-md-10 p-4">
                <h2>Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Employees</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($totalEmployees) ?></p>
                            </div>
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
                            <div class="card-body">
                                <h5 class="card-title">Pending Leaves</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($pendingLeaves) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>