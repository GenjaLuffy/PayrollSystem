<?php 
include './includes/header.php'; 

// Database connection
$servername = "localhost";
$username = "root";      // your DB username
$password = "";          // your DB password
$dbname = "payroll_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Total Employees
$totalEmployees = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM employees");
if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployees = $row['total'];
}

// 2. Monthly Salary sum for employees with unpaid payslips
$monthlySalary = 0;
$query = "
    SELECT SUM(e.salary) AS total_salary 
    FROM employees e
    INNER JOIN payslips p ON e.employee_id = p.employee_id
    WHERE p.status = 'Unpaid'
";
$result = $con->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    $monthlySalary = $row['total_salary'] ?? 0;
}


// 3. Pending Leaves (count of leave requests with status 'Pending')
$pendingLeaves = 0;
$result = $conn->query("SELECT COUNT(*) AS pending FROM leave_requests WHERE status = 'Pending'");
if ($result) {
    $row = $result->fetch_assoc();
    $pendingLeaves = $row['pending'];
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <div class="col-md-10 p-4">
                <h2>Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Employees</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($totalEmployees) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Salary</h5>
                                <p class="card-text fs-4">Rs. <?= number_format($monthlySalary, 2) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pending Leaves</h5>
                                <p class="card-text fs-4"><?= htmlspecialchars($pendingLeaves) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
