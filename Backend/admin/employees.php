<?php
include './includes/header.php'
?>
            <!-- Main -->
            <main class="col-md-10 content">
                <h2>Employees</h2>
                <a href="add_employee.html" class="btn btn-primary mb-3">Add New Employee</a>
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
            </main>
        </div>
    </div>
</body>

</html>