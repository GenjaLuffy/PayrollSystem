<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: #fff;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 sidebar">
            <div class="pt-3">
                <h5 class="text-white text-center">Admin</h5>
                <a href="dashboard.html">Dashboard</a>
                    <a href="employees.html">Employees</a>
                    <a href="add_employee.html">Add Employee</a>
                    <a href="payroll.html">Payroll</a>
                    <a href="attendance.html">Attendance</a>
                    <a href="leave.html">Leave Requests</a>
                    <a href="settings.html">Settings</a>
                    <a href="#">Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 content">
            <h2 class="mb-4">Attendance Records</h2>

            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025-05-20</td>
                        <td>Jane Smith</td>
                        <td><span class="badge bg-success">Present</span></td>
                    </tr>
                    <!-- Add more rows dynamically if needed -->
                </tbody>
            </table>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
