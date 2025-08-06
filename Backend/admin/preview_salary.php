<?php
include './includes/connect.php';
include './algorithm/PayrollCalculator.php';

$employeeId = $_POST['employee_id'] ?? '';
$monthYear = $_POST['month_year'] ?? '';

if (!$employeeId || !$monthYear) {
    echo "<p class='text-danger'>Missing employee or month.</p>";
    exit;
}

[$year, $month] = explode('-', $monthYear);

try {
    $payroll = new PayrollCalculator($con, $employeeId, (int)$month, (int)$year);
    $preview = $payroll->generatePayslip();// You need to implement this if not available

    echo "<ul class='list-group'>";
    foreach ($preview as $key => $value) {
        echo "<li class='list-group-item d-flex justify-content-between'>
                <strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . "</strong>
                <span>" . htmlspecialchars($value) . "</span>
              </li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='text-danger'>Error: " . $e->getMessage() . "</p>";
}
?>
