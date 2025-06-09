<?php include 'includes/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Leave Details</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    

    <div class="main">
      <div class="header">
        <h1>My Leave Details</h1>
      </div>

      <section class="filters">
        <select name="" id="">
          <option value="">All leave Type</option>
          <option value="">Sick leave</option>
        </select>

        <select name="" id="">
          <option value="">All Status</option>
          <option value="">Approved</option>
          <option value="">Decline</option>
        </select>
      </section>

      <section class="leave-table">
        <table>
          <thead>
            <tr>
              <th>Leave Type</th>
              <th>Remarks</th>
              <th>Leave Duration</th>
              <th>Leave Period</th>
              <th>Applied at</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tr>
              <td>Annual Leave - Outsource</td>
              <td>Sick Leave</td>
              <td>2082-2-03</td>
              <td>Full Day</td>
              <td>4 days ago</td>
              <td><span class="badge">Approved</span></td>
              <td>Edit Remove</td>
          </tr>
        </table>
      </section>
    </div>
</body>
</html>
