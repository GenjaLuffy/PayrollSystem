<?php
include 'includes/connect.php';
date_default_timezone_set('Asia/Kathmandu');

$current_time = date('H:i:s');
$today = date('Y-m-d');

$employees = $con->query("SELECT employee_id, endTime FROM employees");

while ($emp = $employees->fetch_assoc()) {
    $emp_id = $emp['employee_id'];
    $end_time = $emp['endTime'] ?? '19:00:00';

    $stmt = $con->prepare("SELECT id, check_out FROM attendance WHERE employee_id = ? AND DATE(date) = ?");
    $stmt->bind_param("ss", $emp_id, $today);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($attendance && empty($attendance['check_out']) && strtotime($current_time) >= strtotime($end_time)) {
        $checkout_time = $end_time;
        $update = $con->prepare("UPDATE attendance SET check_out = ? WHERE id = ?");
        $update->bind_param("si", $checkout_time, $attendance['id']);
        $update->execute();
        $update->close();
    }
}
