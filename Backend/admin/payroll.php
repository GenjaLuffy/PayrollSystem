<?php include './includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Payroll</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

</head>
<body>
<div class="container-fluid">
  <div class="row">

    <?php include './includes/sidebar.php'; ?>

    <div class="col-md-10 p-4">
      <h2>Payroll Processing</h2>
      <form>
        <div class="mb-3">
          <label>Select Employee</label>
          <select class="form-select">
            <option>John Doe</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Month</label>
          <input type="month" class="form-control">
        </div>
        <div class="mb-3">
          <label>Amount</label>
          <input type="number" class="form-control">
        </div>
        <button class="btn btn-success">Process Salary</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
