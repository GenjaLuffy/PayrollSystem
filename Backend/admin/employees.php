<?php 
include './includes/header.php'; 
include './includes/connect.php'; 

$deleteMessage = '';
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Prepare and execute delete query
    $stmt = $con->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $deleteId);

    if ($stmt->execute()) {
        $deleteMessage = "<div class='alert alert-success'>Employee deleted successfully.</div>";
    } else {
        $deleteMessage = "<div class='alert alert-danger'>Error deleting employee: " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
    header("Location: employees.php");
    exit();
}

// Fetch employees from the database
$sql = "SELECT * FROM employees";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Employees</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-md-10 p-4">
                <h2>Employees</h2>
                
                <?= $deleteMessage ?>

                <a href="add_employee.php" class="btn btn-primary mb-3">Add New Employee</a>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                    <td><?= htmlspecialchars($row['fullName']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td>
                                        <a href="employees.php?delete_id=<?= urlencode($row['employee_id']) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No employees found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>

<?php $con->close(); ?>
