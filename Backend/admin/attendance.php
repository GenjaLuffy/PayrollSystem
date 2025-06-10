<?php 
include './includes/header.php'; 
include './includes/connect.php'; 

// Query to get attendance records with employee names
$sql = "SELECT a.date, e.fullName, a.check_in_time, a.check_out_time
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        ORDER BY a.date DESC, e.fullName";

$result = $con->query($sql);

function getAttendanceStatus($checkIn, $checkOut) {
    if (!$checkIn && !$checkOut) {
        return ['Absent', 'bg-danger']; 
    }
    if ($checkIn && !$checkOut) {
        return ['Late', 'bg-warning text-dark']; 
    }
    // Check if check-in time is after 10:15 AM means Late, etc.
    $checkInTime = strtotime($checkIn);
    $lateThreshold = strtotime('10:15:00');
    if ($checkInTime > $lateThreshold) {
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
                      list($statusText, $badgeClass) = getAttendanceStatus($row['check_in_time'], $row['check_out_time']);
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
