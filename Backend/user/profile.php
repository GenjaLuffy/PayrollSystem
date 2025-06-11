<?php
session_start();
include './includes/connect.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];

// Fetch employee data
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile - Employee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
        <?php include 'includes/header.php'; ?>
        <div class="content container my-5">
            <h2 class="mb-4">My Profile</h2>
            <div class="card shadow-sm">
                <div class="card-body row">
                    <div class="col-md-4 text-center">
                        <img src="./assets/images/<?= htmlspecialchars($employee['profile_image']) ?>"
                            alt="Profile Image" class="profile-img mb-3" />
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th scope="row">Full Name:</th>
                                    <td><?= htmlspecialchars($employee['fullName']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Employee ID:</th>
                                    <td><?= htmlspecialchars($employee['employee_id']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Department:</th>
                                    <td><?= htmlspecialchars($employee['department']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Designation:</th>
                                    <td><?= htmlspecialchars($employee['designation']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Email:</th>
                                    <td><?= htmlspecialchars($employee['email']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Phone:</th>
                                    <td><?= htmlspecialchars($employee['phone']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Date of Birth:</th>
                                    <td><?= htmlspecialchars($employee['dob']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Gender:</th>
                                    <td><?= htmlspecialchars($employee['gender']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Address:</th>
                                    <td><?= htmlspecialchars($employee['addressStreet'] . ', ' . $employee['addressCity']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Bank Name:</th>
                                    <td><?= htmlspecialchars($employee['bankName']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Account Number:</th>
                                    <td><?= htmlspecialchars($employee['accountNumber']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">PAN Number:</th>
                                    <td><?= htmlspecialchars($employee['pan']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Work Type:</th>
                                    <td><?= htmlspecialchars($employee['workType']) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Working Hours:</th>
                                    <td><?= htmlspecialchars($employee['startTime'] . " - " . $employee['endTime']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="edit_profile.php" class="btn btn-primary mt-3">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>