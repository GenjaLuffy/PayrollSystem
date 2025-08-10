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
$monthYearStr = sprintf("%d-%02d", (int)$year, (int)$month);

// Check payment status
$status = 'Pending'; // default

$check_sql = "SELECT status FROM payslips WHERE employee_id = ? AND month = ?";
$stmt = $con->prepare($check_sql);
$stmt->bind_param("ss", $employeeId, $monthYearStr);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (strtolower($row['status']) === 'paid') {
        $status = 'Paid';
    }
}
$stmt->close();

try {
    $payroll = new PayrollCalculator($con, $employeeId, (int)$month, (int)$year);
    $preview = $payroll->generatePayslip(); // Make sure this returns an array of key-value pairs

    // Show status on top
    echo "<div class='mb-3'>";
    if ($status === 'Paid') {
        echo "<span class='badge bg-success'>Status: Paid</span>";
    } else {
        echo "<span class='badge bg-warning text-dark'>Status: Pending</span>";
    }
    echo "</div>";

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
