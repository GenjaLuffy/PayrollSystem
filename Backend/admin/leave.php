<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Leave Requests | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

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
                    <a href="payroll.html">Payroll</a>
                    <a href="attendance.html">Attendance</a>
                    <a href="leave.html">Leave Requests</a>
                    <a href="settings.html">Settings</a>
                    <a href="#">Logout</a>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 content">
                <h2>Leave Requests</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason (summary)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Michael</td>
                            <td>2025-05-22</td>
                            <td>2025-05-25</td>
                            <td>Medical Leave</td>
                            <td>Pending</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reasonModal1">View Reason</button>
                                <button class="btn btn-sm btn-success">Approve</button>
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sarah</td>
                            <td>2025-06-01</td>
                            <td>2025-06-03</td>
                            <td>Family Emergency</td>
                            <td>Approved</td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reasonModal2">View Reason</button>
                                <button class="btn btn-sm btn-success" disabled>Approve</button>
                                <button class="btn btn-sm btn-danger" disabled>Reject</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Reason Modal 1 -->
    <div class="modal fade" id="reasonModal1" tabindex="-1" aria-labelledby="reasonModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Reason for Michael</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Michael has requested leave due to medical reasons following a surgery that requires recovery time.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reason Modal 2 -->
    <div class="modal fade" id="reasonModal2" tabindex="-1" aria-labelledby="reasonModalLabel2" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Reason for Sarah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Sarah has requested leave to attend to a family emergency requiring her presence out of town.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
