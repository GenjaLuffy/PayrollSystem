<?php
include './includes/auth.php';
include './includes/connect.php';

$user_id = $_SESSION['user_id'];

$stmt = $con->prepare("SELECT full_name, profile_image FROM admins WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$full_name = $admin['full_name'] ?? 'Admin';
$profile_image = !empty($admin['profile_image']) ? $admin['profile_image'] : 'profile.png';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="./dashboard.php">Payroll System</a>
    <div class="d-flex align-items-center">
      <span class="navbar-text me-2 text-white">Welcome, <?= htmlspecialchars($full_name) ?></span>
      <img src="assets/images/<?= htmlspecialchars($profile_image) ?>" alt="Admin Avatar" class="rounded-circle me-3" width="32" height="32" style="object-fit: cover;" />
    </div>
  </div>
</nav>
