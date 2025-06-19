<?php
session_start();
include './includes/connect.php';
include './includes/auth.php';
include './algorithm/PayrollCalculator.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? '';
    $monthYear = $_POST['month_year'] ?? '';

    if (!$employeeId || !$monthYear) {
        $_SESSION['error'] = "Employee and Month-Year are required.";
        header("Location: payroll.php");
        exit;
    }

    [$year, $month] = explode('-', $monthYear);
    $month = (int)$month;
    $year = (int)$year;

    try {
        $payroll = new PayrollCalculator($con, $employeeId, $month, $year);
        $payslip = $payroll->generateAndStorePayslip();
        $_SESSION['success'] = "Salary processed for $employeeId ($monthYear).";

        // Log the action in audit_logs
        $action = "Processed Salary";
        $details = "Salary processed for employee $employeeId for $monthYear";
        $sql = "INSERT INTO audit_logs (employee_id, action, details) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sss", $employeeId, $action, $details);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing salary: " . $e->getMessage();
    }

    header("Location: payroll.php");
    exit;
}

header("Location: payroll.php");
exit;
?>