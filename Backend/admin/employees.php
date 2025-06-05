<?php include './includes/header.php'; ?>
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
                        <tr>
                            <td>101</td>
                            <td>John Doe</td>
                            <td>HR</td>
                            <td>Manager</td>
                            <td>
                                <button class="btn btn-sm btn-info">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <!-- More rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>