<?php
include './includes/connect.php';


// Fetch all employees for admin to select
$employees = [];
$sql = "SELECT employee_id, fullName FROM employees";
$result = $con->query($sql);
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll Processing</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>
<body>
    <?php include './includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-md-10 p-4">
                <h2>Payroll Processing</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php elseif (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="post" action="process_salary.php">
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
                               max="2025-06" value="2025-06">
                    </div>

                    <button type="submit" class="btn btn-success">Process Salary</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$con->close();
?>