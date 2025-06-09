<?php
include './includes/connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("SELECT reason FROM leave_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($reason);
    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'reason' => $reason]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Reason not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
}
?>
