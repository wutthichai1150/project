<?php
include('../includes/db.php'); // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $room_id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];

    // อัปเดตข้อมูลห้องในฐานข้อมูล
    $sql = "UPDATE room SET room_number = ?, room_type = ?, room_price = ?, room_status = ? WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $room_number, $room_type, $room_price, $room_status, $room_id);

    if ($stmt->execute()) {
        // ส่งผลลัพธ์กลับไปยัง JavaScript
        echo json_encode([
            'success' => true,
            'room_number' => $room_number,
            'room_type' => $room_type,
            'room_status' => $room_status,
            'room_price' => $room_price
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>