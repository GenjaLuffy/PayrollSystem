<?php
include './includes/header.php';
include './includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Just insert posted data into attendance table (no validation)
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $check_in_time = $_POST['start_time'] ?? null;
    $check_out_time = $_POST['end_time'] ?? null;

    $sql = "INSERT INTO attendance (employee_id, date, status, check_in_time, check_out_time)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssss", $employee_id, $date, $status, $check_in_time, $check_out_time);

    if ($stmt->execute()) {
        // Redirect or show success message
        header("Location: attendance.php?msg=Attendance added successfully");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch employees for dropdown
$sql = "SELECT employee_id, fullName FROM employees ORDER BY fullName";
$result = $con->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Attendance</title>
  <link rel="stylesheet" href="./assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include './includes/sidebar.php'; ?>

      <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 mt-4">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark mb-0"><i class="bi bi-calendar-plus me-2"></i>Add Attendance</h2>
            <a href="attendance.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Attendance
            </a>
          </div>

          <form method="POST" action="">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="attendanceDate" class="form-label">Date</label>
                <input type="date" name="date" id="attendanceDate" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label for="employeeName" class="form-label">Employee</label>
                <select name="employee_id" id="employeeName" class="form-select" required>
                  <option selected disabled>Select employee</option>
                  <?php
                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo '<option value="' . htmlspecialchars($row['employee_id']) . '">' . htmlspecialchars($row['fullName']) . '</option>';
                    }
                  } else {
                    echo '<option disabled>No employees found</option>';
                  }
                  ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="status" class="form-label">Attendance Status</label>
                <select name="status" id="status" class="form-select" required>
                  <option selected disabled>Select status</option>
                  <option value="Present">Present</option>
                  <option value="Absent">Absent</option>
                  <option value="Late">Late</option>
                  <option value="Half Day">Half Day</option>
                  <option value="Leave">Leave</option>
                </select>
              </div>

              <div class="col-md-3 time-input" style="display: none;">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" name="start_time" id="start_time" class="form-control">
              </div>

              <div class="col-md-3 time-input" style="display: none;">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" name="end_time" id="end_time" class="form-control">
              </div>
            </div>

            <div class="mt-4 text-end">
              <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-1"></i> Save Attendance
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script src="./assets/script/script.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $con->close(); ?>
