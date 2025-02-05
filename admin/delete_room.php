<?php
include('../includes/db.php');

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['room_id'])) {
    $room_id = $data['room_id'];
    $deleteQuery = "DELETE FROM room WHERE room_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $room_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>
