<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบค่าที่ส่งมาจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $mem_id = $_POST['mem_id'];
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname'];
    $mem_user = $_POST['mem_user'];
    $mem_mail = $_POST['mem_mail'];
    $mem_phone = $_POST['mem_phone'];
    $mem_address = $_POST['mem_address'];

    // ตรวจสอบว่ามีค่าข้อมูลทั้งหมดหรือไม่
    if (empty($mem_id) || empty($mem_fname) || empty($mem_lname) || empty($mem_user) || empty($mem_mail)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
        exit;
    }

    // ใช้ prepared statement เพื่อลดความเสี่ยงจาก SQL injection
    $stmt = $conn->prepare("UPDATE `member` SET mem_fname = ?, mem_lname = ?, mem_user = ?, mem_mail = ?, mem_phone = ?, mem_address = ? WHERE mem_id = ?");
    $stmt->bind_param("sssssss", $mem_fname, $mem_lname, $mem_user, $mem_mail, $mem_phone, $mem_address, $mem_id);

    // ตรวจสอบผลลัพธ์ของการอัพเดต
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัพเดตข้อมูล: ' . $stmt->error]);
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
}
?>
