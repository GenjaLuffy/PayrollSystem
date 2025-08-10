<?php
include './includes/header.php';
include './includes/connect.php';

$employeeId = $_GET['employee_id'] ?? '';
$successMessage = '';
$errorMessage = '';

if (!$employeeId) {
    echo "<p class='text-danger'>Invalid employee ID.</p>";
    exit;
}

// Fetch employee data
$stmt = $con->prepare("SELECT * FROM employees WHERE employee_id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $con->error);
    echo "<p class='text-danger'>Database error. Please try again later.</p>";
    exit;
}
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "<p class='text-danger'>Employee not found.</p>";
    exit;
}

// Update employee if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $emergencyContact = $_POST['emergencyContact'] ?? '';
    $addressStreet = $_POST['addressStreet'] ?? '';
    $addressCity = $_POST['addressCity'] ?? '';
    $designation = $_POST['designation'] ?? '';
    $department = $_POST['department'] ?? '';
    $salary = $_POST['salary'] ?? 0;
    $joiningDate = $_POST['joiningDate'] ?? '';
    $maritalStatus = $_POST['maritalStatus'] ?? '';
    $bankName = $_POST['bankName'] ?? '';
    $accountNumber = $_POST['accountNumber'] ?? '';
    $pan = $_POST['pan'] ?? '';
    $workType = $_POST['workType'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif ($salary < 0) {
        $errorMessage = "Salary cannot be negative.";
    } elseif (!preg_match('/^\d{10}$/', $phone) && !empty($phone)) {
        $errorMessage = "Phone number must be 10 digits.";
    } elseif (!preg_match('/^[A-Z0-9]{10}$/', $pan) && !empty($pan)) {
        $errorMessage = "PAN must be 10 alphanumeric characters.";
    } elseif (!in_array($maritalStatus, ['Single', 'Married']) && !empty($maritalStatus)) {
        $errorMessage = "Invalid marital status.";
    } else {
        $stmt = $con->prepare("UPDATE employees SET 
            fullName=?, username=?, email=?, phone=?, dob=?, gender=?, emergencyContact=?, 
            addressStreet=?, addressCity=?, designation=?, department=?, salary=?, joiningDate=?, marital_status=?, 
            bankName=?, accountNumber=?, pan=?, workType=?, startTime=?, endTime=?
            WHERE employee_id=?");
        if (!$stmt) {
            error_log("Prepare failed: " . $con->error);
            $errorMessage = "Database error. Please try again.";
        } else {
            // Debugging
            $typeString = "sssssssssssdsssssssss";
            error_log("Type string: $typeString, Length: " . strlen($typeString));
            error_log("Variables: " . json_encode([
                $fullName, $username, $email, $phone, $dob, $gender, $emergencyContact,
                $addressStreet, $addressCity, $designation, $department, $salary, $joiningDate,
                $maritalStatus, $bankName, $accountNumber, $pan, $workType, $startTime, $endTime,
                $employeeId
            ]));

            $stmt->bind_param(
                $typeString,
                $fullName,
                $username,
                $email,
                $phone,
                $dob,
                $gender,
                $emergencyContact,
                $addressStreet,
                $addressCity,
                $designation,
                $department,
                $salary,
                $joiningDate,
                $maritalStatus,
                $bankName,
                $accountNumber,
                $pan,
                $workType,
                $startTime,
                $endTime,
                $employeeId
            );

            if ($stmt->execute()) {
                $successMessage = "Employee updated successfully.";
                // Refresh employee data
                $stmt->close();
                $stmt = $con->prepare("SELECT * FROM employees WHERE employee_id = ?");
                $stmt->bind_param("s", $employeeId);
                $stmt->execute();
                $employee = $stmt->get_result()->fetch_assoc();
            } else {
                $errorMessage = "Error updating employee. Please try again.";
                error_log("MySQL Error: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Employee</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include './includes/sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 content">
                <div class="card p-4 mt-4">
                    <h2 class="mb-4 text-dark">Edit Employee</h2>

                    <?php if ($successMessage): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <!-- Basic Info -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="fullName" class="form-control" value="<?= htmlspecialchars($employee['fullName']) ?>" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($employee['username']) ?>" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($employee['phone']) ?>" pattern="[0-9]{10}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($employee['dob']) ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?= $employee['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $employee['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= $employee['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    <option value="Prefer not to say" <?= $employee['gender'] == 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Emergency Contact</label>
                                <input type="tel" name="emergencyContact" class="form-control" value="<?= htmlspecialchars($employee['emergencyContact']) ?>" pattern="[0-9]{10}" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-4">
                            <h5>Address</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Street</label>
                                    <input type="text" name="addressStreet" class="form-control" value="<?= htmlspecialchars($employee['addressStreet']) ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <input type="text" name="addressCity" class="form-control" value="<?= htmlspecialchars($employee['addressCity']) ?>" />
                                </div>
                            </div>
                        </div>

                        <!-- Job Info -->
                        <div class="mt-4">
                            <h5>Job Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($employee['designation']) ?>" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($employee['department']) ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salary (Rs)</label>
                                    <input type="number" name="salary" class="form-control" value="<?= htmlspecialchars($employee['salary']) ?>" required min="0" step="0.01" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Joining</label>
                                    <input type="date" name="joiningDate" class="form-control" value="<?= htmlspecialchars($employee['joiningDate']) ?>" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marital Status</label>
                                    <select name="maritalStatus" class="form-select">
                                        <option value="Single" <?= isset($employee['marital_status']) && $employee['marital_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                                        <option value="Married" <?= isset($employee['marital_status']) && $employee['marital_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Info -->
                        <div class="mt-4">
                            <h5>Bank Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bankName" class="form-control" value="<?= htmlspecialchars($employee['bankName']) ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="accountNumber" class="form-control" value="<?= htmlspecialchars($employee['accountNumber']) ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PAN Number</label>
                                    <input type="text" name="pan" class="form-control" value="<?= htmlspecialchars($employee['pan']) ?>" pattern="[A-Z0-9]{10}" />
                                </div>
                            </div>
                        </div>

                        <!-- Work Schedule -->
                        <div class="mt-4">
                            <h5>Work Schedule</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Work Type</label>
                                    <select name="workType" class="form-select" required>
                                        <option value="Full Time" <?= $employee['workType'] == 'Full Time' ? 'selected' : '' ?>>Full Time</option>
                                        <option value="Part Time" <?= $employee['workType'] == 'Part Time' ? 'selected' : '' ?>>Part Time</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="startTime" class="form-control" value="<?= htmlspecialchars($employee['startTime']) ?>" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="endTime" class="form-control" value="<?= htmlspecialchars($employee['endTime']) ?>" />
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Employee</button>
                            <a href="employees.php" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>

<?php $con->close(); ?>