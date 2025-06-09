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
if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Update status query
$stmt = $con->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$con->close();
?>
