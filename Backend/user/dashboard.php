<?php
session_start();
include './includes/connect.php';

// --- AES Decrypt Function ---
function decryptAES($data) {
    $key = '12345678901234567890123456789012'; // 32 chars
    $iv  = '1234567890123456'; // 16 chars
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return $decrypted ?: 0; // return 0 if decryption fails
}

// --- Get logged-in employee ID ---
$employee_id = $_SESSION['employee_id'] ?? null;
if (!$employee_id) {
    header('Location: login.php');
    exit;
}

// --- Fetch employee details ---
$sql_emp = "SELECT * FROM employees WHERE employee_id = ?";
$stmt_emp = $con->prepare($sql_emp);
$stmt_emp->bind_param("s", $employee_id);
$stmt_emp->execute();
$result_emp = $stmt_emp->get_result();
$employee = $result_emp->fetch_assoc();

if (!$employee) {
    echo "Employee not found.";
    exit;
}

// --- Attendance summary for current month ---
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$sql_att = "SELECT 
    SUM(status = 'Present') AS days_present,
    SUM(status = 'Absent') AS days_absent,
    SUM(status = 'Leave') AS leaves_taken
FROM attendance 
WHERE employee_id = ? AND date BETWEEN ? AND ?";
$stmt_att = $con->prepare($sql_att);
$stmt_att->bind_param("sss", $employee_id, $month_start, $month_end);
$stmt_att->execute();
$result_att = $stmt_att->get_result();
$attendance = $result_att->fetch_assoc();

// --- Recent Payslips (last 3 months) ---
$sql_payslips = "SELECT month, net_salary FROM payslips WHERE employee_id = ? ORDER BY month DESC LIMIT 3";
$stmt_payslips = $con->prepare($sql_payslips);
$stmt_payslips->bind_param("s", $employee_id);
$stmt_payslips->execute();
$result_payslips = $stmt_payslips->get_result();
$payslips = $result_payslips->fetch_all(MYSQLI_ASSOC);

// --- Calculate allowances and deductions for dashboard summary ---
$basic_pay_encrypted = $employee['salary'] ?? 0;
$basic_pay = (float)decryptAES($basic_pay_encrypted);
$allowances = $basic_pay * 0.15; 
$deductions = $basic_pay * 0.05; 
$net_salary = $basic_pay + $allowances - $deductions;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Dashboard - Payroll System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="./assets/css/style.css" rel="stylesheet" />
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
  <?php include 'includes/header.php'; ?>

  <div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Profile</h5>
          <p><strong>Name:</strong> <?= htmlspecialchars($employee['fullName']) ?></p>
          <p><strong>Employee ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></p>
          <p><strong>Department:</strong> <?= htmlspecialchars($employee['department']) ?></p>
          <p><strong>Position:</strong> <?= htmlspecialchars($employee['designation']) ?></p>
          <a href="#" class="btn btn-primary btn-sm mt-2">View Profile</a>
        </div>
      </div>
    </div>

    <!-- Attendance Card -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Attendance Summary (This Month)</h5>
          <p><strong>Days Present:</strong> <?= $attendance['days_present'] ?? 0 ?></p>
          <p><strong>Days Absent:</strong> <?= $attendance['days_absent'] ?? 0 ?></p>
          <p><strong>Leaves Taken:</strong> <?= $attendance['leaves_taken'] ?? 0 ?></p>
          <a href="#" class="btn btn-primary btn-sm mt-2">View Attendance</a>
        </div>
      </div>
    </div>

    <!-- Salary Summary Card -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Salary Summary</h5>
          <p><strong>Basic Pay:</strong> Rs. <?= number_format($basic_pay, 2) ?></p>
          <p><strong>Allowances:</strong> Rs. <?= number_format($allowances, 2) ?></p>
          <p><strong>Deductions:</strong> Rs. <?= number_format($deductions, 2) ?></p>
          <p><strong>Net Salary:</strong> Rs. <?= number_format($net_salary, 2) ?></p>
        </div>
      </div>
    </div>

    <!-- Recent Payslips Card -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Recent Payslips</h5>
          <ul class="list-group list-group-flush mb-3">
            <?php
            $hasValidPayslip = false;

            if ($payslips) {
                foreach ($payslips as $payslip) {
                    $month = htmlspecialchars($payslip['month']);
                    $encrypted_salary = $payslip['net_salary'] ?? 0;

                    // Decrypt salary
                    $net_salary = (float)decryptAES($encrypted_salary);

                    if ($net_salary > 0) {
                        echo '<li class="list-group-item payslip-item" data-month="' . $month . '">' 
                             . $month . ' - Rs.' . number_format($net_salary, 2) . '</li>';
                        $hasValidPayslip = true;
                    }
                }
            }

            if (!$hasValidPayslip) {
                echo '<li class="list-group-item">No valid payslips found.</li>';
            }
            ?>
          </ul>
          <a href="#" class="btn btn-primary btn-sm">View All</a>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
