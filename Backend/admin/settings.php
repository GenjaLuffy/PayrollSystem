<?php include './includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Settings</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

</head>
<body>
<div class="container-fluid">
  <div class="row">
<?php include './includes/sidebar.php'; ?>
    <div class="col-md-10 p-4">
      <h2>System Settings</h2>
      <form>
        <div class="mb-3">
          <label>Company Name</label>
          <input type="text" class="form-control" value="Venture Four Technology">
        </div>
        <div class="mb-3">
          <label>Admin Email</label>
          <input type="email" class="form-control" value="admin@example.com">
        </div>
        <button class="btn btn-primary">Save Settings</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
