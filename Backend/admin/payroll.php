<?php
include './includes/header.php'
?>
            <!-- Main Content -->
            <main class="col-md-10 content">
                <h2>Payroll Management</h2>
                <button class="btn btn-success mb-3">Generate Payroll</button>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Paid</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>May 2025</td>
                            <td>$95,000</td>
                            <td>Completed</td>
                            <td><button class="btn btn-sm btn-primary">View</button></td>
                        </tr>
                        <!-- Additional rows as needed -->
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
