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

// Check today's attendance
$stmt_today = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
$stmt_today->bind_param("ss", $employee_id, $today);
$stmt_today->execute();
$todays_attendance = $stmt_today->get_result()->fetch_assoc();
$stmt_today->close();

// Handle Check-In / Check-Out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_in']) && !$todays_attendance) {
        $insert = $con->prepare("INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, 'Present')");
        $insert->bind_param("sss", $employee_id, $today, $current_time);
        $insert->execute();
        $insert->close();
    }
    if (isset($_POST['check_out']) && $todays_attendance && empty($todays_attendance['check_out'])) {
        $update = $con->prepare("UPDATE attendance SET check_out = ? WHERE id = ?");
        $update->bind_param("si", $current_time, $todays_attendance['id']);
        $update->execute();
        $update->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get current week's Monday and Sunday
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

// Fetch current week attendance and map it for a full 7-day display
$week_map = [];
$stmt = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ?");
$stmt->bind_param("sss", $employee_id, $monday, $sunday);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $week_map[$row['date']] = $row;
}
$stmt->close();
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
    <form method="POST">
      <button type="submit" name="check_in" class="btn btn-success me-2" <?= $todays_attendance ? 'disabled' : '' ?>>Check In</button>
      <button type="submit" name="check_out" class="btn btn-danger" <?= !$todays_attendance || !empty($todays_attendance['check_out']) ? 'disabled' : '' ?>>Check Out</button>
    </form>
  </div>

  <!-- Months Row -->
  <div class="mb-4 d-flex flex-wrap gap-2">
    <?php for($i=1; $i<=12; $i++): ?>
      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#monthModal" data-month="<?= $i ?>" data-year="<?= date('Y') ?>">
        <?= date("F", mktime(0, 0, 0, $i, 10)) ?>
      </button>
    <?php endfor; ?>
  </div>

  <!-- Current Week Attendance Table -->
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
              $badge_class = '';

              if(isset($week_map[$day_str])) {
                  $record = $week_map[$day_str];
                  $check_in = $record['check_in'] ? date("h:i A", strtotime($record['check_in'])) : 'N/A';
                  $check_out = $record['check_out'] ? date("h:i A", strtotime($record['check_out'])) : '-';
                  $status = $record['status'];
              } else {
                  if ($day_obj->format('w') == 6) { // Saturday
                      $status = 'Weekend';
                  } elseif ($day_obj < new DateTime($today)) {
                      $status = 'Absent';
                  }
              }

              $badge_class = match(strtolower($status)) {
                  'present' => 'bg-success', 'absent' => 'bg-danger',
                  'leave' => 'bg-warning text-dark', 'weekend' => 'bg-secondary', default => ''
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

<!-- Modal for Month View -->
<div class="modal fade" id="monthModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Monthly Attendance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalAttendanceBody">
        <!-- Content will be loaded here by JavaScript -->
      </div>
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
      
      modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
      
      fetch(`show_attendance.php?month=${month}&year=${year}`)
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.text();
        })
        .then(data => {
          modalBody.innerHTML = data;
        })
        .catch(error => {
          modalBody.innerHTML = `<div class="alert alert-danger">Failed to load attendance data. Please try again.</div>`;
          console.error('Error fetching attendance:', error);
        });
    });
  });
});
</script>
</body>
</html>