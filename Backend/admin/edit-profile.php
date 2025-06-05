<?php include './includes/header.php'; ?>
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
<div class="container">
  <div class="edit-card">
    <h2 class="mb-4 text-center">Edit Profile</h2>
    <form action="update-profile.php" method="POST" enctype="multipart/form-data">

      <!-- Centered clickable profile picture -->
      <div class="text-center mb-4">
        <label for="profilePicInput" style="cursor:pointer;">
          <img src="./assets/images/profile.png" alt="Profile Picture" class="profile-pic" id="profilePicPreview" />
        </label>
        <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" />
      </div>

      <div class="mb-3">
        <label for="fullName" class="form-label fw-bold">Full Name</label>
        <input type="text" class="form-control" id="fullName" name="full_name" placeholder="Johnathan Doe" required />
      </div>
      <div class="mb-3">
        <label for="username" class="form-label fw-bold">Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="johndoe" required />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label fw-bold">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="johndoe@example.com" required />
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label fw-bold">Phone</label>
        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+977-9800000000" />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-bold">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" />
      </div>
      <div class="mb-4">
        <label for="role" class="form-label fw-bold">Role</label>
        <select class="form-select" id="role" name="role" required>
          <option value="Admin">Admin</option>
          <option value="User">Employee</option>
        </select>
      </div>
      <div class="text-center">
        <button type="submit" class="btn btn-primary btn-save">
          <i class="bi bi-save me-2"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  const input = document.getElementById('profilePicInput');
  const img = document.getElementById('profilePicPreview');

  input.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      img.src = URL.createObjectURL(file);
    }
  });
</script>

</body>
</html>
