<?php
session_start();
include './includes/connect.php';

// Redirect if not logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];

// Fetch current employee data
$sql = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Employee not found.";
    exit;
}

$employee = $result->fetch_assoc();
$profile_image = $employee['profile_image'];

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $addressStreet = $_POST['addressStreet'];
    $addressCity = $_POST['addressCity'];

    // Image upload
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            $error_message = "Only JPG, PNG, and GIF image types are allowed.";
        } else {
            $uploadDir = __DIR__ . '/assets/images';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('profile_', true) . '.' . $extension;
            $targetPath = $uploadDir . '/' . $imageName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $profile_image = $imageName;
            } else {
                $error_message = "Failed to move uploaded image.";
            }
        }
    }

    // Update query
    if (empty($error_message)) {
        $update = "UPDATE employees SET fullName=?, dob=?, phone=?, gender=?, addressStreet=?, addressCity=?, profile_image=? WHERE employee_id=?";
        $stmt = $con->prepare($update);
        $stmt->bind_param("ssssssss", $fullName, $dob, $phone, $gender, $addressStreet, $addressCity, $profile_image, $employee_id);

        if ($stmt->execute()) {
            $success_message = "Profile updated successfully.";
            // Update values for display
            $employee['fullName'] = $fullName;
            $employee['dob'] = $dob;
            $employee['phone'] = $phone;
            $employee['gender'] = $gender;
            $employee['addressStreet'] = $addressStreet;
            $employee['addressCity'] = $addressCity;
            $employee['profile_image'] = $profile_image;
        } else {
            $error_message = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Profile - Employee Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="./assets/css/style.css" rel="stylesheet" />
  <style>
    .profile-img-small {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      cursor: pointer;
    }
    .edit-overlay {
      position: absolute;
      bottom: 0;
      right: 0;
      background: #fff;
      border-radius: 50%;
      padding: 5px;
    }
    .image-container {
      position: relative;
      display: inline-block;
    }
  </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="container my-5">
  <h2 class="mb-4">Edit Profile</h2>

  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($success_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
    <div class="mb-4 text-center">
      <div class="image-container">
        <img id="profileImage" src="./assets/images/<?= htmlspecialchars($employee['profile_image']) ?>" alt="Profile" class="profile-img-small mb-2">
        <div class="edit-overlay">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000" class="bi bi-pencil" viewBox="0 0 16 16">
            <path d="M12.146.146a.5.5 0 0 1 .708 0l2.854 2.854a.5.5 0 0 1 0 .708L5.207 14.207a.5.5 0 0 1-.168.11l-4 1.5a.5.5 0 0 1-.65-.65l1.5-4a.5.5 0 0 1 .11-.168L12.146.146zM11.207 2L3 10.207V13h2.793L14 4.793 11.207 2z"/>
          </svg>
        </div>
      </div>
      <input type="file" name="profile_image" id="profileImageInput" class="form-control mt-2" style="display: none;" accept="image/*">
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullName" class="form-control" value="<?= htmlspecialchars($employee['fullName']) ?>" required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($employee['dob']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($employee['phone']) ?>" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Gender</label>
      <select name="gender" class="form-select" required>
        <option value="Male" <?= $employee['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $employee['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
        <option value="Other" <?= $employee['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
      </select>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Street Address</label>
        <input type="text" name="addressStreet" class="form-control" value="<?= htmlspecialchars($employee['addressStreet']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">City</label>
        <input type="text" name="addressCity" class="form-control" value="<?= htmlspecialchars($employee['addressCity']) ?>" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="./assets/script/script.js"></script>
</body>
</html>
