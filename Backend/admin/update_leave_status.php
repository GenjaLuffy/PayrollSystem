<?php
include './includes/connect.php';

// Get POST JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$id = (int)$data['id'];
$status = $data['status'];

// Validate status
if (!in_array($status, ['Pending','Approved','Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Fetch leave request
$stmt = $con->prepare("SELECT * FROM leave_requests WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$leave = $result->fetch_assoc();
$stmt->close();

if (!$leave) {
    echo json_encode(['success' => false, 'message' => 'Leave request not found']);
    exit;
}

// Update leave_requests status
$stmt = $con->prepare("UPDATE leave_requests SET status=? WHERE id=?");
$stmt->bind_param("si",$status,$id);
$stmt->execute();
$stmt->close();

// Only deduct leave if approved
if($status === 'Approved' && in_array($leave['leave_type'], ['Sick Leave','Annual Leave','Paid Leave'])) {
    $start = new DateTime($leave['start_date']);
    $end = new DateTime($leave['end_date']);
    $days = $start->diff($end)->days + 1;

    $stmt = $con->prepare("UPDATE leave_status SET used = used + ? WHERE employee_id=? AND leave_type=?");
    $stmt->bind_param("iss",$days,$leave['employee_id'],$leave['leave_type']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true]);
$con->close();
?>
