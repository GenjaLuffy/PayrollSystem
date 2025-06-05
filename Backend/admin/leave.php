<?php include './includes/header.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Leave Requests</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-md-10 p-4">
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
            </div>
        </div>
    </div>
</body>

</html>