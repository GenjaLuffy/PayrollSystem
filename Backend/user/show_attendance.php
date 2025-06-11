<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['employee_id'])) {
    http_response_code(401);
    die("Unauthorized access.");
}

$employee_id = $_SESSION['employee_id'];
date_default_timezone_set('Asia/Kathmandu');

$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$today = date('Y-m-d');

$firstDayOfMonth = date("Y-m-01", strtotime("$selectedYear-$selectedMonth-01"));
$daysInMonth = date('t', strtotime($firstDayOfMonth));
$lastDayOfMonth = date("Y-m-t", strtotime($firstDayOfMonth));

// Fetch all attendance for the month and map it by date
$attendance_map = [];
$stmt_month = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ?");
$stmt_month->bind_param("sss", $employee_id, $firstDayOfMonth, $lastDayOfMonth);
$stmt_month->execute();
$result = $stmt_month->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendance_map[$row['date']] = $row;
    }
}
$stmt_month->close();
$con->close();
?>
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($d = 1; $d <= $daysInMonth; $d++):
                $current_gregorian_str = date('Y-m-d', strtotime("$selectedYear-$selectedMonth-$d"));
                $current_date_obj = new DateTime($current_gregorian_str);
                $day_of_week = (int)$current_date_obj->format('w'); // 0=Sun, 6=Sat

                // Default values
                $check_in = '-';
                $check_out = '-';
                $status = '-';
                $badge_class = '';
                
                // Check if a record exists
                if (isset($attendance_map[$current_gregorian_str])) {
                    $record = $attendance_map[$current_gregorian_str];
                    $check_in = $record['check_in'] ? htmlspecialchars(date("h:i:s A", strtotime($record['check_in']))) : 'N/A';
                    $check_out = $record['check_out'] ? htmlspecialchars(date("h:i:s A", strtotime($record['check_out']))) : '-';
                    $status = htmlspecialchars($record['status']);
                } else {
                    if ($day_of_week === 6) { // Saturday
                        $status = 'Weekend';
                    } elseif ($current_date_obj < new DateTime($today)) {
                        $status = 'Absent';
                    }
                }

                $badge_class = match(strtolower($status)) {
                    'present' => 'bg-success',
                    'absent' => 'bg-danger',
                    'leave' => 'bg-warning text-dark',
                    'weekend' => 'bg-secondary',
                    default => ''
                };
            ?>
                <tr class="<?= ($current_gregorian_str == $today) ? 'table-info' : '' ?>">
                    <td><?= $current_date_obj->format('M d, Y') ?></td>
                    <td><?= $current_date_obj->format('l') ?></td>
                    <td><?= $check_in ?></td>
                    <td><?= $check_out ?></td>
                    <td>
                        <?php if ($status !== '-'): ?>
                          <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                        <?php else: echo '-'; endif; ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>