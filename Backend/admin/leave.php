<?php
include './includes/header.php'
?>
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
