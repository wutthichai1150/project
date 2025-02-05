<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบว่ามีการส่งค่ามาจากฟอร์มหรือไม่
if (isset($_POST['mem_id'])) {
    $mem_id = $_POST['mem_id'];

    // คำสั่ง SQL สำหรับลบสมาชิก
    $query = "DELETE FROM `member` WHERE mem_id = '$mem_id'";

    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        // ถ้าเกิดข้อผิดพลาดในการลบ ให้แสดงข้อความข้อผิดพลาด
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ ID ของสมาชิก']);
}
?>
