<?php
include './includes/connect.php';
include './algorithm/PayrollCalculator.php';
include './includes/auth.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? '';
    $monthYear = $_POST['month_year'] ?? '';

    if (!$employeeId || !$monthYear) {
        $_SESSION['error'] = "Employee and Month-Year are required.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    [$year, $month] = explode('-', $monthYear);
    $month = (int)$month;
    $year = (int)$year;
    $monthYearStr = sprintf("%d-%02d", $year, $month);

    // Check if payslip already exists
    $check_sql = "SELECT * FROM payslips WHERE employee_id = ? AND month = ?";
    $stmt = $con->prepare($check_sql);
    $stmt->bind_param("ss", $employeeId, $monthYearStr);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Payslip already processed for $employeeId ($monthYear).";
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    $stmt->close();

    try {
        $payroll = new PayrollCalculator($con, $employeeId, $month, $year);
        $payslip = $payroll->generateAndStorePayslip();
        $_SESSION['success'] = "Payslip processed and marked as Paid for $employeeId ($monthYear).";

        // Log the action in audit_logs
        $action = "Processed Payslip";
        $details = "Payslip processed and marked as Paid for employee $employeeId for $monthYear";
        $sql = "INSERT INTO audit_logs (employee_id, action, details) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sss", $employeeId, $action, $details);
        $stmt->execute();
        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error processing payslip: " . $e->getMessage();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch employees for dropdown
$employees = [];
$sql = "SELECT employee_id, fullName FROM employees";
$result = $con->query($sql);
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payroll Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/style.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
    <?php include './includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <main class="col-md-10 p-4">
                <h2>Payroll Processing</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= htmlspecialchars($emp['employee_id']) ?>">
                                    <?= htmlspecialchars($emp['fullName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="month_year" class="form-label">Select Month</label>
                        <input type="month" name="month_year" id="month_year" class="form-control" required
                               max="<?= date('Y-m') ?>" value="<?= date('Y-m') ?>">
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cash-stack"></i> Process Salary
                    </button>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$con->close();
?>
