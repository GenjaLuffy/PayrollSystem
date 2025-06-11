<?php
session_start();
include 'includes/connect.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = trim($_POST['leave_type'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Validation
    if (empty($leave_type)) {
        $errors[] = "Please select a leave type.";
    }
    if (empty($start_date)) {
        $errors[] = "Please select a start date.";
    }
    if (empty($end_date)) {
        $errors[] = "Please select an end date.";
    }
    if ($start_date && $end_date && $end_date < $start_date) {
        $errors[] = "End date cannot be before start date.";
    }

    if (empty($errors)) {
        // Insert into DB
        $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssss", $employee_id, $leave_type, $start_date, $end_date, $reason);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Database error: " . $con->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Apply Leave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <?php include 'includes/header.php'; ?>

        <div class="container my-5" style="max-width: 700px;">
            <h2 class="mb-4 text-center">Apply for Leave</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">Leave application submitted successfully.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="apply_leave.php" novalidate>
                <div class="mb-3">
                    <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="leave_type" name="leave_type" required>
                        <option value="" <?= (isset($_POST['leave_type']) && $_POST['leave_type'] == '') ? 'selected' : '' ?>>Select leave type</option>
                        <option value="Sick Leave" <?= (isset($_POST['leave_type']) && $_POST['leave_type'] == 'Sick Leave') ? 'selected' : '' ?>>Sick Leave</option>
                        <option value="Annual Leave" <?= (isset($_POST['leave_type']) && $_POST['leave_type'] == 'Annual Leave') ? 'selected' : '' ?>>Annual Leave</option>
                        <option value="Casual Leave" <?= (isset($_POST['leave_type']) && $_POST['leave_type'] == 'Casual Leave') ? 'selected' : '' ?>>Casual Leave</option>
                        <!-- Add more leave types if needed -->
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required
                               value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required
                               value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Optional"><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Submit Leave Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
