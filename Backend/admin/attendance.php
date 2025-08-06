<?php 
include './includes/header.php'; 
include './includes/connect.php'; 

$today = date('Y-m-d');

$filterEmployee = $_GET['employee'] ?? '';
$filterMonth = $_GET['month'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Fetch today's attendance
$sqlToday = "SELECT a.date, e.fullName, a.check_in, a.check_out, e.startTime
             FROM attendance a
             JOIN employees e ON a.employee_id = e.employee_id
             WHERE a.date = '$today'
             ORDER BY e.fullName";
$resultToday = $con->query($sqlToday);

// Fetch all attendance records
$sql = "SELECT a.date, e.fullName, a.check_in, a.check_out, e.startTime
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        ORDER BY a.date DESC, e.fullName";
$result = $con->query($sql);

// Employees list for filter
$employeeQuery = $con->query("SELECT DISTINCT fullName FROM employees ORDER BY fullName ASC");

// Function to get status
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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2><i class="bi bi-calendar-check me-2"></i>Attendance Records</h2>
          <a href="add_attendance.php" class="btn btn-outline-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Attendance
          </a>
        </div>

        <!-- Today's Attendance -->
        <h4><i class="bi bi-clock-history me-2"></i>Today's Attendance (<?= $today ?>)</h4>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-striped">
            <thead>
              <tr><th>Employee</th><th>Status</th></tr>
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
                <tr><td colspan="2" class="text-center">No records today.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Filters -->
        <h4><i class="bi bi-funnel me-2"></i>Filter Attendance</h4>
        <form method="GET" class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Employee</label>
            <select name="employee" class="form-select">
              <option value="">All Employees</option>
              <?php while ($emp = $employeeQuery->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($emp['fullName']) ?>" <?= $filterEmployee === $emp['fullName'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($emp['fullName']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Month</label>
            <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($filterMonth) ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">All Status</option>
              <option value="Present" <?= $filterStatus === 'Present' ? 'selected' : '' ?>>Present</option>
              <option value="Late" <?= $filterStatus === 'Late' ? 'selected' : '' ?>>Late</option>
              <option value="Absent" <?= $filterStatus === 'Absent' ? 'selected' : '' ?>>Absent</option>
            </select>
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
          </div>
        </form>

        <?php 
        // Process and group records
        $attendanceByMonth = [];
        $filteredMonths = [];

        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            list($statusText, $badgeClass) = getAttendanceStatus($row['check_in'], $row['check_out'], $row['startTime']);

            if ($filterEmployee && strcasecmp(trim($row['fullName']), trim($filterEmployee)) !== 0) continue;
            if ($filterMonth && strpos($row['date'], $filterMonth) !== 0) continue;
            if ($filterStatus && $statusText !== $filterStatus) continue;

            $monthKey = date('Y-m', strtotime($row['date']));
            $row['statusText'] = $statusText;
            $row['badgeClass'] = $badgeClass;

            $attendanceByMonth[$monthKey][] = $row;
            if (!in_array($monthKey, $filteredMonths)) $filteredMonths[] = $monthKey;
          }
        }

        // Create sorted list of months
        sort($filteredMonths);
        $allMonths = $filteredMonths;
        ?>

        <!-- Month Selection Buttons -->
        <?php if (!empty($allMonths)): ?>
          <h4><i class="bi bi-calendar3 me-2"></i>Select a Month</h4>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($allMonths as $monthKey): 
              $monthDisplay = DateTime::createFromFormat('Y-m', $monthKey)->format('F Y');
            ?>
              <button class="btn btn-outline-primary month-btn" data-month="<?= $monthKey ?>">
                <?= $monthDisplay ?>
              </button>
            <?php endforeach; ?>
          </div>

          <!-- Display container -->
          <div id="monthAttendanceContainer" class="mt-4"></div>

          <script>
            const attendanceData = <?= json_encode($attendanceByMonth) ?>;
            const buttons = document.querySelectorAll('.month-btn');
            const container = document.getElementById('monthAttendanceContainer');

            buttons.forEach(btn => {
              btn.addEventListener('click', () => {
                const month = btn.dataset.month;
                const displayMonth = btn.textContent;
                const records = attendanceData[month] || [];

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                let html = `<h5 class="mb-3">Attendance for <strong>${displayMonth}</strong></h5>`;

                if (records.length > 0) {
                  html += `<div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                      <thead class="table-light">
                        <tr><th>Date</th><th>Employee</th><th>Status</th></tr>
                      </thead>
                      <tbody>`;

                  records.forEach(record => {
                    html += `<tr>
                      <td>${record.date}</td>
                      <td>${record.fullName}</td>
                      <td><span class="badge ${record.badgeClass} px-3 py-2">${record.statusText}</span></td>
                    </tr>`;
                  });

                  html += `</tbody></table></div>`;
                } else {
                  html += `<p class="text-muted">No attendance records for this month.</p>`;
                }

                container.innerHTML = html;
              });
            });
          </script>
        <?php else: ?>
          <p class="text-muted">No matching attendance records.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?>
