<?php
include './includes/header.php';
include './includes/connect.php';

// Fetch admin info using session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM admins WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    echo "Admin not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="fw-bold mb-4">My Profile</h2>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card profile-card shadow-sm">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img src="./assets/images/<?= htmlspecialchars($admin['profile_image'] ?? 'default.png') ?>" 
                                         alt="Profile Picture" 
                                         class="profile-img rounded-circle" 
                                         style="width:100px; height:100px; object-fit: cover;">
                                    <h4 class="mt-2 fw-semibold"><?= htmlspecialchars($admin['full_name']) ?></h4>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($admin['user_type']) ?></p>
                                    <span class="badge bg-success">Active</span>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-person-fill me-2"></i>Full Name:</label>
                                    <p><?= htmlspecialchars($admin['full_name']) ?></p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Username:</label>
                                    <p><?= htmlspecialchars($admin['username']) ?></p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-envelope-fill me-2"></i>Email:</label>
                                    <p><?= htmlspecialchars($admin['email']) ?></p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-telephone-fill me-2"></i>Phone:</label>
                                    <p><?= htmlspecialchars($admin['phone']) ?></p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><i class="bi bi-person-bounding-box me-2"></i>Role:</label>
                                    <p><?= htmlspecialchars($admin['user_type']) ?></p>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="edit-profile.php" class="btn btn-edit-profile">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
