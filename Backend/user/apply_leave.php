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

// ===============================
// === Function: Update Monthly Leave ===
function updateMonthlyLeave($con, $employee_id)
{
    $today = new DateTime();
    $currentMonth = $today->format('Y-m-01');

    $leave_accrual = [
        'Sick Leave' => 2,
        'Annual Leave' => 1,
        'Paid Leave' => 1
    ];

    foreach ($leave_accrual as $type => $days) {
        // Check if record exists
        $stmt = $con->prepare("SELECT id, last_updated FROM leave_status WHERE employee_id=? AND leave_type=?");
        $stmt->bind_param("ss", $employee_id, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            if ($row['last_updated'] < $currentMonth) {
                $stmt = $con->prepare("UPDATE leave_status SET total_allocated = total_allocated + ?, last_updated=? WHERE id=?");
                $stmt->bind_param("isi", $days, $currentMonth, $row['id']);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $con->prepare("INSERT INTO leave_status (employee_id, leave_type, total_allocated, used, last_updated) VALUES (?, ?, ?, 0, ?)");
            $stmt->bind_param("ssis", $employee_id, $type, $days, $currentMonth);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Update leave balances at the start
updateMonthlyLeave($con, $employee_id);

// ================================
// === Handle Form Submission ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = trim($_POST['leave_type'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Validation
    if (empty($leave_type)) $errors[] = "Please select a leave type.";
    if (empty($start_date)) $errors[] = "Please select a start date.";
    if (empty($end_date)) $errors[] = "Please select an end date.";
    if ($start_date && $end_date && $end_date < $start_date) $errors[] = "End date cannot be before start date.";

    if (empty($errors)) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end)->days + 1;

        // Leave duration limits
        $leave_limits = [
            'Paternity Leave' => ['max' => 20],
            'Bereavement Leave' => ['max' => 50],
            'Sick Leave' => ['max' => 4],
            'Annual Leave' => ['max' => 3],
            'Paid Leave' => ['max' => 3],
        ];

        if (isset($leave_limits[$leave_type])) {
            $max_days = $leave_limits[$leave_type]['max'];
            if ($interval > $max_days) {
                $errors[] = "$leave_type cannot exceed $max_days days.";
            }
        }

        // Check leave balance (optional at application time)
        if (in_array($leave_type, ['Sick Leave', 'Annual Leave', 'Paid Leave'])) {
            $stmt = $con->prepare("SELECT total_allocated, used FROM leave_status WHERE employee_id=? AND leave_type=?");
            $stmt->bind_param("ss", $employee_id, $leave_type);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $available = ($row['total_allocated'] ?? 0) - ($row['used'] ?? 0);
            if ($interval > $available) {
                $errors[] = "You only have $available days remaining for $leave_type.";
            }
        }
    }

    // Insert leave request only (do not deduct yet)
    if (empty($errors)) {
        $stmt = $con->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $employee_id, $leave_type, $start_date, $end_date, $reason);
        if ($stmt->execute()) {
            $success = true;
            $_POST = [];
        } else {
            $errors[] = "Database error: " . $con->error;
        }
        $stmt->close();
    }
}

// Fetch leave status for display
$stmt = $con->prepare("SELECT leave_type, total_allocated, used FROM leave_status WHERE employee_id=?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$leave_status_result = $stmt->get_result();
$stmt->close();

// Fetch all leave requests for this employee
$stmt = $con->prepare("SELECT * FROM leave_requests WHERE employee_id=? ORDER BY applied_on DESC");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$leave_requests = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Apply Leave</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="content">
<?php include 'includes/header.php'; ?>

<div class="container my-5" style="max-width: 800px;">
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

    <form method="post" action="apply_leave.php">
        <div class="mb-3">
            <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
            <select class="form-select" id="leave_type" name="leave_type" required>
                <option value="">Select leave type</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Annual Leave">Annual Leave</option>
                <option value="Paternity Leave">Paternity Leave</option>
                <option value="Bereavement Leave">Bereavement Leave</option>
                <option value="Paid Leave">Paid Leave</option>
            </select>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
