<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['employee_id'])) {
    die("Unauthorized access.");
}

$employee_id = $_SESSION['employee_id'];
date_default_timezone_set('Asia/Kathmandu');

$today = date('Y-m-d');
$current_time = date('H:i:s');

// --- Fetch employee info including endTime ---
$stmt_emp = $con->prepare("SELECT startTime, endTime FROM employees WHERE employee_id = ?");
$stmt_emp->bind_param("s", $employee_id);
$stmt_emp->execute();
$employee_data = $stmt_emp->get_result()->fetch_assoc();
$stmt_emp->close();

$employee_end_time = $employee_data['endTime'] ?? '19:00:00';
$employee_start_time = $employee_data['startTime'] ?? '09:00:00';

// --- Fetch today's attendance ---
$stmt_today = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND DATE(date) = ?");
$stmt_today->bind_param("ss", $employee_id, $today);
$stmt_today->execute();
$todays_attendance = $stmt_today->get_result()->fetch_assoc();
$stmt_today->close();

// --- Determine if Check-In is allowed ---
$can_check_in = true;
if ($todays_attendance || strtotime($current_time) >= strtotime($employee_end_time)) {
    $can_check_in = false;
}

// --- Determine if Check-Out button should show ---
$show_checkout = false;
if ($todays_attendance && empty($todays_attendance['check_out'])) {
    if (strtotime($current_time) < strtotime($employee_end_time)) {
        $show_checkout = true;
    }
}

// --- Handle Check-In ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_in'])) {
    if (!$can_check_in) {
        echo "<script>alert('Cannot check in after shift end or already checked in.'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }

    $insert = $con->prepare("INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, 'Present')");
    $insert->bind_param("sss", $employee_id, $today, $current_time);
    $insert->execute();
    $insert->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- Handle Manual Check-Out ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_out'])) {
    if ($todays_attendance && empty($todays_attendance['check_out'])) {
        $checkout_time = date('H:i:s');
        $update_checkout = $con->prepare("UPDATE attendance SET check_out = ? WHERE id = ?");
        $update_checkout->bind_param("si", $checkout_time, $todays_attendance['id']);
        $update_checkout->execute();
        $update_checkout->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// --- Weekly Attendance ---
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

$week_map = [];
$stmt_week = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND DATE(date) BETWEEN ? AND ?");
$stmt_week->bind_param("sss", $employee_id, $monday, $sunday);
$stmt_week->execute();
$result = $stmt_week->get_result();
while ($row = $result->fetch_assoc()) {
    $week_map[date('Y-m-d', strtotime($row['date']))] = $row;
}
$stmt_week->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="content">
<?php include 'includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between mb-3 align-items-center flex-wrap gap-2">
        <h2 class="mb-0">My Attendance</h2>
        <form method="POST" class="d-flex gap-2">
            <button type="submit" name="check_in" class="btn btn-success" <?= $can_check_in ? '' : 'disabled' ?>>Check In</button>
            <?php if ($show_checkout): ?>
                <button type="submit" name="check_out" class="btn btn-danger">Check Out</button>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$can_check_in && !$todays_attendance): ?>
        <small class="text-danger">Cannot check in after shift end (<?= $employee_end_time ?>)</small>
    <?php endif; ?>

    <!-- Month Buttons -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <?php for($i=1; $i<=12; $i++): ?>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#monthModal" data-month="<?= $i ?>" data-year="<?= date('Y') ?>">
                <?= date("F", mktime(0,0,0,$i,10)) ?>
            </button>
        <?php endfor; ?>
    </div>

    <!-- Weekly Attendance Table -->
    <div class="card">
        <div class="card-header bg-light fw-bold">
            This Week: <?= date('d M', strtotime($monday)) ?> - <?= date('d M, Y', strtotime($sunday)) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered m-0">
                <thead class="table-light">
                    <tr><th>Day</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php for ($i=0; $i<7; $i++): 
                    $day_str = date('Y-m-d', strtotime("$monday +$i days"));
                    $day_obj = new DateTime($day_str);
                    $check_in = '-';
                    $check_out = '-';
                    $status = '-';

                    if(isset($week_map[$day_str])) {
                        $record = $week_map[$day_str];
                        $check_in = $record['check_in'] ? date("h:i A", strtotime($record['check_in'])) : 'N/A';
                        $check_out = $record['check_out'] ? date("h:i A", strtotime($record['check_out'])) : '-';
                        $status = $record['status'];
                    } else {
                        if ($day_obj->format('w') == 6) { $status = 'Weekend'; }
                        elseif ($day_obj < new DateTime($today)) { $status = 'Absent'; }
                    }

                    $badge_class = match(strtolower($status)) {
                        'present' => 'bg-success',
                        'absent' => 'bg-danger',
                        'leave' => 'bg-warning text-dark',
                        'weekend' => 'bg-secondary',
                        default => ''
                    };
                ?>
                    <tr class="<?= ($day_str == $today) ? 'table-info' : '' ?>">
                        <td><?= $day_obj->format('l') ?></td>
                        <td><?= $day_obj->format('M d, Y') ?></td>
                        <td><?= $check_in ?></td>
                        <td><?= $check_out ?></td>
                        <td>
                            <?php if ($status !== '-'): ?>
                                <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                            <?php else: echo '-'; endif; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Month Modal -->
<div class="modal fade" id="monthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Monthly Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAttendanceBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthModal = new bootstrap.Modal(document.getElementById('monthModal'));
    document.querySelectorAll('button[data-bs-target="#monthModal"]').forEach(button => {
        button.addEventListener('click', () => {
            const month = button.dataset.month;
            const year = button.dataset.year;
            const modalBody = document.getElementById('modalAttendanceBody');
            modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>';
            fetch(`show_attendance.php?month=${month}&year=${year}`)
                .then(r => r.text())
                .then(data => modalBody.innerHTML = data)
                .catch(e => modalBody.innerHTML = `<div class="alert alert-danger">Failed to load attendance data.</div>`);
        });
    });
});
</script>
</body>
</html>
