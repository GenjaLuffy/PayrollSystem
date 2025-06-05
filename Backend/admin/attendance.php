<?php include './includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendance</title>
  <link rel="stylesheet" href="./assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include './includes/sidebar.php'; ?>

      <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 mt-4">
        <div class="attendance-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-Dark mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Records</h2>
            <button class="btn btn-outline-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Attendance
            </button>
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
                <tr>
                  <td>2025-05-20</td>
                  <td>Jane Smith</td>
                  <td><span class="badge bg-success px-3 py-2">Present</span></td>
                </tr>
                <tr>
                  <td>2025-05-21</td>
                  <td>John Doe</td>
                  <td><span class="badge bg-danger px-3 py-2">Absent</span></td>
                </tr>
                <tr>
                  <td>2025-05-22</td>
                  <td>Emily Johnson</td>
                  <td><span class="badge bg-warning text-dark px-3 py-2">Late</span></td>
                </tr>
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
