<?php 
include './includes/header.php'; 
include './includes/connect.php'; 

$today = date('Y-m-d');

// Get today's attendance with employee start time
$sqlToday = "SELECT a.date, e.fullName, a.check_in, a.check_out, e.startTime
             FROM attendance a
             JOIN employees e ON a.employee_id = e.employee_id
             WHERE a.date = '$today'
             ORDER BY e.fullName";

$resultToday = $con->query($sqlToday);

// Get all attendance records with employee start time
$sql = "SELECT a.date, e.fullName, a.check_in, a.check_out, e.startTime
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        ORDER BY a.date DESC, e.fullName";

$result = $con->query($sql);

// Function to get status based on check-in and check-out
function getAttendanceStatus($checkIn, $checkOut, $dutyStartTime) {
    if (!$checkIn && !$checkOut) return ['Absent', 'bg-danger'];
    if ($checkIn && !$checkOut) return ['Late', 'bg-warning text-dark'];

    $checkInTime = strtotime($checkIn);
    $graceTime = strtotime('+15 minutes', strtotime($dutyStartTime));

    if ($checkInTime > $graceTime) {
        return ['Late', 'bg-warning text-dark'];
    }
    return ['Present', 'bg-success'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendance</title>
  <link rel="stylesheet" href="./assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include './includes/sidebar.php'; ?>

      <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 mt-4">
        <div class="attendance-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Records</h2>
            <a href="add_attendance.php" class="btn btn-outline-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Attendance
            </a>
          </div>

          <!-- Today's Attendance -->
          <h4 class="text-dark mt-4 mb-3"><i class="bi bi-clock-history me-2"></i>Today's Attendance (<?= $today ?>)</h4>
          <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($resultToday && $resultToday->num_rows > 0): ?>
                  <?php while($row = $resultToday->fetch_assoc()): 
                        list($statusText, $badgeClass) = getAttendanceStatus($row['check_in'], $row['check_out'], $row['startTime']);
                  ?>
                    <tr>
                      <td><?= htmlspecialchars($row['fullName']) ?></td>
                      <td><span class="badge <?= $badgeClass ?> px-3 py-2"><?= $statusText ?></span></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="2" class="text-center">No attendance records for today.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- All Attendance Records -->
          <h4 class="text-dark mt-4 mb-3"><i class="bi bi-journal-text me-2"></i>All Records</h4>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Employee</th>
                  <th scope="col">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                  <?php while($row = $result->fetch_assoc()): 
                        list($statusText, $badgeClass) = getAttendanceStatus($row['check_in'], $row['check_out'], $row['startTime']);
                  ?>
                    <tr>
                      <td><?= htmlspecialchars($row['date']) ?></td>
                      <td><?= htmlspecialchars($row['fullName']) ?></td>
                      <td><span class="badge <?= $badgeClass ?> px-3 py-2"><?= $statusText ?></span></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="3" class="text-center">No attendance records found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?>
