<?php
// At the top of header.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Do NOT call session_start() again here
include 'connect.php';

if (!isset($_SESSION['employee_id'])) {
    die("Session not found.");
}

$employee_id = $_SESSION['employee_id'];

$sql = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Header Query prepare error: " . $con->error);
}

$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $full_name = $row['fullName'];
    $profile_image = $row['profile_image'];
} else {
    $full_name = "Employee";
    $profile_image = "default.png"; 
}
?>

<nav class="navbar navbar-expand navbar-light bg-white mb-4 shadow-sm rounded">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1">Employee Dashboard</span>
    <div class="d-flex align-items-center">
      <span class="navbar-text me-2 text-dark">Welcome, <?= htmlspecialchars($full_name) ?></span>
      <img src="./assets/images/<?= htmlspecialchars($profile_image) ?>" alt="Employee Avatar" class="rounded-circle me-3" width="32" height="32" style="object-fit: cover;" />
    </div>
  </div>
</nav>
