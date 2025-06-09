<?php

include './includes/connect.php';
include './includes/header.php';

// Check login
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = $_POST['full_name'] ?? '';
  $username = $_POST['username'] ?? '';
  $email = $_POST['email'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $role = $_POST['role'] ?? '';
  $password = $_POST['password'] ?? '';

  // Handle profile image upload
  $profile_image = "";
  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $img_name = time() . '_' . basename($_FILES['profile_picture']['name']);
    $target_dir = "./assets/images/";
    $target_file = $target_dir . $img_name;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
      $profile_image = $img_name;
    }
  }

  // Build update SQL dynamically
  $sql = "UPDATE admins SET full_name=?, username=?, email=?, phone=?, user_type=?";
  $params = [$full_name, $username, $email, $phone, $role];

  if (!empty($profile_image)) {
    $sql .= ", profile_image=?";
    $params[] = $profile_image;
  }

  if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password=?";
    $params[] = $hashed_password;
  }

  $sql .= " WHERE id=?";
  $params[] = $user_id;

  // Prepare types string for bind_param
  // All strings except last param is int for id
  $types = str_repeat('s', count($params) - 1) . 'i';

  $stmt = $con->prepare($sql);
  $stmt->bind_param($types, ...$params);

  if ($stmt->execute()) {
    $message = "<div class='alert alert-success'>Profile updated successfully.</div>";
    // Refresh the page to show updated info or redirect
    header("Location: ./edit-profile.php");
    exit();
  } else {
    $message = "<div class='alert alert-danger'>Error updating profile: " . $stmt->error . "</div>";
  }
}

// Fetch current user data
$stmt = $con->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
  die("User not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
  <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
      <div class="card-body">
        <h2 class="mb-4 text-center">Edit Profile</h2>
        <?= $message ?>

        <form action="" method="POST" enctype="multipart/form-data">
          <div class="text-center mb-4">
            <label for="profilePicInput">
              <img src="./assets/images/<?= htmlspecialchars($admin['profile_image'] ?? 'default.png') ?>"
                alt="Profile Picture" class="profile-pic" id="profilePicPreview" />
            </label>
            <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" hidden />
          </div>

          <div class="mb-3">
            <label for="fullName" class="form-label fw-bold">Full Name</label>
            <input type="text" class="form-control" id="fullName" name="full_name"
              value="<?= htmlspecialchars($admin['full_name']) ?>" required />
          </div>

          <div class="mb-3">
            <label for="username" class="form-label fw-bold">Username</label>
            <input type="text" class="form-control" id="username" name="username"
              value="<?= htmlspecialchars($admin['username']) ?>" required />
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-bold">Email</label>
            <input type="email" class="form-control" id="email" name="email"
              value="<?= htmlspecialchars($admin['email']) ?>" required />
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label fw-bold">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone"
              value="<?= htmlspecialchars($admin['phone']) ?>" />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label fw-bold">New Password</label>
            <input type="password" class="form-control" id="password" name="password"
              placeholder="Leave blank to keep existing password" />
          </div>

          <div class="mb-4">
            <label for="role" class="form-label fw-bold">Role</label>
            <select class="form-select" id="role" name="role" required>
              <option value="Admin" <?= ($admin['user_type'] === 'Admin') ? 'selected' : '' ?>>Admin
              </option>
              <option value="Manager" <?= ($admin['user_type'] === 'Manager') ? 'selected' : '' ?>>Manager
              </option>
              <option value="Super Admin"
                <?= ($admin['user_type'] === 'Super Admin') ? 'selected' : '' ?>>Super Admin
              </option>
              <option value="Super Admin"
                <?= ($admin['user_type'] === 'Super Admin') ? 'selected' : '' ?>>User
              </option>
            </select>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="./assets/script/script.js"></script>
</body>

</html>