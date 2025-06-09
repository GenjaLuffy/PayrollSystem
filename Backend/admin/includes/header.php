<?php
session_start();
include './includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $con->prepare("SELECT full_name, profile_image FROM admins WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Fallbacks
$full_name = $admin['full_name'] ?? 'Admin';
$profile_image = !empty($admin['profile_image']) ? $admin['profile_image'] : 'profile.png';
?>




<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="./dashboard.php">Payroll System</a>
    <div class="d-flex align-items-center">
      <span class="navbar-text me-2 text-white">Welcome, <?= htmlspecialchars($full_name) ?></span>
      <img src="assets/images/<?= htmlspecialchars($profile_image) ?>" alt="Admin Avatar" class="rounded-circle me-3" width="32" height="32" style="object-fit: cover;" />
      <a class="btn btn-outline-light" href="../admin/logout.php">Logout</a>
    </div>
  </div>
</nav>
