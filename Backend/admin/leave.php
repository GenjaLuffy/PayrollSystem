<?php 
include './includes/header.php'; 
include './includes/connect.php'; 

// Fetch leave requests with employee username
$sql = "SELECT 
            lr.id, 
            u.username AS employee, 
            lr.leave_type,
            lr.start_date, 
            lr.end_date, 
            lr.reason, 
            lr.status 
        FROM leave_requests lr
        JOIN users u ON lr.employee_id = u.employee_id
        ORDER BY lr.applied_on DESC";

$result = $con->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave Requests</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <?php include './includes/sidebar.php'; ?>
        <div class="col-md-10 p-4">
            <h2>Leave Requests</h2>
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?= $row['id'] ?>">
                                <td><?= htmlspecialchars($row['employee']) ?></td>
                                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm view-reason-btn" 
                                            data-reason="<?= htmlspecialchars($row['reason'], ENT_QUOTES) ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#reasonModal">
                                        View Reason
                                    </button>
                                </td>
                                <td id="status-<?= $row['id'] ?>"><?= htmlspecialchars($row['status']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <button class="btn btn-success btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'Approved')">Approve</button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'Rejected')">Reject</button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-sm" disabled>Approve</button>
                                        <button class="btn btn-danger btn-sm" disabled>Reject</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No leave requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable"> <!-- makes modal body scrollable if content is long -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reasonModalLabel">Leave Reason</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalReasonContent" style="white-space: pre-wrap;">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalReasonContent = document.getElementById('modalReasonContent');

    // Set modal content on button click
    document.querySelectorAll('.view-reason-btn').forEach(button => {
        button.addEventListener('click', function() {
            const reason = this.getAttribute('data-reason');
            modalReasonContent.textContent = reason;
        });
    });
});

function updateStatus(leaveId, status) {
    if (!confirm(`Are you sure you want to mark this leave as ${status}?`)) return;

    fetch('update_leave_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: leaveId, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            document.getElementById('status-' + leaveId).textContent = status;
            const row = document.getElementById('row-' + leaveId);
            row.querySelectorAll('button').forEach(btn => btn.disabled = true);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Failed to update status. Please try again.'));
}
</script>
</body>
</html>

<?php
$con->close();
?>
