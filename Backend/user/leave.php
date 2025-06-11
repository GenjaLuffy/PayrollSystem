<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/connect.php';

if (!isset($_SESSION['employee_id'])) {
    die("Not logged in or session expired.");
}

$employee_id = $_SESSION['employee_id'];
$leave_requests = []; // Initialize an empty array to hold the data

$sql = "SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY applied_on DESC";
$stmt = $con->prepare($sql);

if (!$stmt) {
    // This catches errors in the SQL syntax itself
    die("Query Prepare Error: " . $con->error);
}

// Bind the parameter
$stmt->bind_param("s", $employee_id);

// Execute the statement and check for errors
if (!$stmt->execute()) {
    die("Query Execute Error: " . $stmt->error);
}

// Get the result set
$result = $stmt->get_result();

if ($result === false) {
    die("Get Result Error: " . $stmt->error);
}

// Fetch all results into an array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
}

$stmt->close();
$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Leave Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <style>
    
  </style>
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <div class="content">
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
      <h2 class="mb-4">My Leave Details</h2>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3 filters">
            <select id="leaveTypeFilter" class="form-select me-3" aria-label="Filter by Leave Type">
              <option value="">All Leave Types</option>
              <option value="Sick Leave">Sick Leave</option>
              <option value="Annual Leave">Annual Leave</option>
              <!-- Add other leave types you might have -->
            </select>

            <select id="statusFilter" class="form-select" aria-label="Filter by Status">
              <option value="">All Statuses</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
              <option value="Pending">Pending</option>
            </select>
          </div>

          <div class="table-responsive">
            <table id="leaveTable" class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Leave Type</th>
                  <th>Remarks</th>
                  <th>Leave Duration</th>
                  <th>Leave Period</th>
                  <th>Applied At</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($leave_requests)): ?>
                  <?php foreach ($leave_requests as $row): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['leave_type']) ?></td>
                      <td><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                      <td>
                        <?php
                          try {
                              $start = new DateTime($row['start_date']);
                              $end = new DateTime($row['end_date']);
                              $diff = $start->diff($end)->days + 1;
                              echo $diff . ' day' . ($diff > 1 ? 's' : '');
                          } catch (Exception $e) {
                              echo "Invalid date";
                          }
                        ?>
                      </td>
                      <td><?= htmlspecialchars(date("M d, Y", strtotime($row['start_date']))) ?> to <?= htmlspecialchars(date("M d, Y", strtotime($row['end_date']))) ?></td>
                      <td><?= date("M d, Y, h:i A", strtotime($row['applied_on'])) ?></td>
                      <td>
                        <?php
                          $status = strtolower($row['status']);
                          $statusClass = 'status-' . $status;
                        ?>
                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="6" class="text-center p-4">No leave records found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const leaveTypeFilter = document.getElementById('leaveTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const leaveTableBody = document.querySelector('#leaveTable tbody');

    function filterTable() {
      const leaveType = leaveTypeFilter.value.toLowerCase();
      const status = statusFilter.value.toLowerCase();
      const noResultsRow = leaveTableBody.querySelector('.no-results');
      if (noResultsRow) noResultsRow.remove();

      let visibleRows = 0;
      Array.from(leaveTableBody.rows).forEach(row => {
        // Skip the potential 'no records' row in this loop
        if (row.cells.length < 6) return; 

        const rowLeaveType = row.cells[0].innerText.toLowerCase();
        const rowStatus = row.cells[5].innerText.toLowerCase();

        const matchesLeaveType = leaveType === "" || rowLeaveType.includes(leaveType);
        const matchesStatus = status === "" || rowStatus.includes(status);

        if (matchesLeaveType && matchesStatus) {
            row.style.display = "";
            visibleRows++;
        } else {
            row.style.display = "none";
        }
      });
      
      // Optional: Show a message if filters result in no matches
      if (visibleRows === 0 && leaveTableBody.rows.length > 1) {
          const newRow = leaveTableBody.insertRow();
          newRow.className = 'no-results';
          const cell = newRow.insertCell();
          cell.colSpan = 6;
          cell.className = 'text-center p-4';
          cell.textContent = 'No records match your filter criteria.';
      }
    }

    leaveTypeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>