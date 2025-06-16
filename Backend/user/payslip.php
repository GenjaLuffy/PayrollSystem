<?php
session_start();
$_SESSION['user_id'] = 1; 

include './includes/header.php';
include './includes/connect.php';

// Ensure user is logged in
$employee_id = $_SESSION['user_id'] ?? 0;

if (!$employee_id) {
    // It's better to redirect to a login page or show a more formal error
    header('Location: login.php'); 
    exit;
}

$currentMonth = date('m');
$currentYear = date('Y');

// Fetch all payslips for the logged-in employee using a prepared statement to prevent SQLi
$sql = "SELECT * FROM salaries WHERE employee_id = ? ORDER BY year DESC, month DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

$payslips = [];
while ($row = $result->fetch_assoc()) {
    $payslips[] = $row;
}

function getMonthName($monthNum) {
    return date("F", mktime(0, 0, 0, (int)$monthNum, 10));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Payslip</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2><i class="bi bi-wallet2 me-2"></i>My Payslips</h2>

    <div class="card p-4 mt-3 shadow">
      <h5 class="mb-3">Current Month (<?= htmlspecialchars(getMonthName($currentMonth)) ?> <?= htmlspecialchars($currentYear) ?>)</h5>
      <?php
        $currentPayslipData = null;
        // Find the current month's payslip
        foreach ($payslips as $p) {
            if ($p['month'] == $currentMonth && $p['year'] == $currentYear) {
                $currentPayslipData = $p;
                break;
            }
        }

        if ($currentPayslipData):
      ?>
        <!-- VULNERABILITY FIX: Use htmlspecialchars() to prevent Stored XSS -->
        <p><strong>Basic Salary:</strong> Rs. <?= htmlspecialchars($currentPayslipData['basic_salary']) ?></p>
        <p><strong>Status:</strong>
          <span class="badge <?= $currentPayslipData['paid'] ? 'bg-success' : 'bg-warning text-dark' ?>">
            <?= $currentPayslipData['paid'] ? 'Paid' : 'Unpaid' ?>
          </span>
        </p>
      <?php else: ?>
        <p>No salary record found for this month.</p>
      <?php endif; ?>
    </div>

    <div class="card p-4 mt-4 shadow">
      <h5 class="mb-3">Payslip History</h5>
      <div class="row">
        <?php
          // LOGIC FIX: Filter out the current month's payslip to avoid redundancy in the history list.
          $previousPayslips = array_filter($payslips, function($p) use ($currentMonth, $currentYear) {
              return !($p['month'] == $currentMonth && $p['year'] == $currentYear);
          });
        ?>
        <?php if (empty($previousPayslips)): ?>
            <p>No previous payslip history found.</p>
        <?php else: ?>
          <?php foreach ($previousPayslips as $payslip): ?>
            <div class="col-md-3 mb-3">
              <!-- VULNERABILITY FIX: Sanitize the ID in case it contains malicious characters -->
              <button class="btn btn-outline-primary w-100"
                data-bs-toggle="modal"
                data-bs-target="#modal<?= htmlspecialchars($payslip['id']) ?>">
                <!-- VULNERABILITY FIX: Use htmlspecialchars() to prevent Stored XSS -->
                <?= htmlspecialchars(getMonthName($payslip['month'])) ?> <?= htmlspecialchars($payslip['year']) ?>
              </button>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal<?= htmlspecialchars($payslip['id']) ?>" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <!-- VULNERABILITY FIX: Use htmlspecialchars() to prevent Stored XSS -->
                    <h5 class="modal-title">Payslip - <?= htmlspecialchars(getMonthName($payslip['month'])) ?> <?= htmlspecialchars($payslip['year']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <!-- VULNERABILITY FIX: Use htmlspecialchars() to prevent Stored XSS -->
                    <p><strong>Basic Salary:</strong> Rs. <?= htmlspecialchars($payslip['basic_salary']) ?></p>
                    <p><strong>Status:</strong>
                      <span class="badge <?= $payslip['paid'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $payslip['paid'] ? 'Paid' : 'Unpaid' ?>
                      </span>
                    </p>
                    <p><strong>Payment Date:</strong>
                      <!-- The original code was already correct here, but we keep it for consistency -->
                      <?= $payslip['paid'] ? htmlspecialchars($payslip['payment_date']) : 'N/A' ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $con->close(); ?> 