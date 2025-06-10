<?php include 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payroll Management System</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="./assets/css/style.css" rel="stylesheet">
  <!-- Optional: Custom CSS -->
  <style>
   
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->


  <!-- Main Content -->
  <div class="main-content flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Welcome, Pujan Tandukar Khusal</h2>
      <span class="text-muted">Employee ID: 123456</span>
    </div>

    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Current Payslip</h5>
            <p class="card-text mb-1">Net Pay: ₹75,000</p>
            <span class="badge bg-success">Released</span>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Attendance</h5>
            <p class="card-text mb-1">Present Days: 20</p>
            <p class="card-text">Late: 2</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Leave Balance</h5>
            <p class="card-text mb-1">Annual Leave: 10 days</p>
            <p class="card-text">Sick Leave: 4 days</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Profile</h5>
            <p class="card-text mb-1">Designation: Software Engineer</p>
            <p class="card-text">Department: IT</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
