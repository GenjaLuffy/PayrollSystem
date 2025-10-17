<?php
session_start();
include './includes/auth.php';
include './algorithm/filter_employee.php';//filter algorithm
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees by Department | Payroll System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
     <?php include './includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include './includes/sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Employees by Department</h2>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" class="mb-4 d-flex align-items-center">
                        <label for="dept" class="form-label me-2 fw-bold">Select Department:</label>
                        <select name="dept" id="dept" class="form-select me-2" style="width:300px;">
                            <option value="">-- All Departments --</option>
                            <?php while ($dept = $dept_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($dept['department']) ?>" 
                                    <?= ($selectedDept == $dept['department']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['department']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>

                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($emp_result && $emp_result->num_rows > 0): ?>
                                <?php while ($row = $emp_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                        <td><?= htmlspecialchars($row['fullName']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars($row['designation']) ?></td>
                                        <td>Rs. <?= number_format($row['salary'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No employees found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?>
