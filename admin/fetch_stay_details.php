<?php
// เริ่ม session (ถ้ามี)
session_start();

// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบว่ามีการส่ง room_id มาใน URL หรือไม่
if (!isset($_GET['room_id'])) {
    echo json_encode(['error' => 'room_id is required']);
    exit();
}

// รับค่า room_id จาก URL
$room_id = $_GET['room_id'];

// เตรียมคำสั่ง SQL
$sql = "SELECT s.stay_id, s.mem_id, s.room_id, s.stay_start_date, 
               IF(s.stay_end_date = '0000-00-00', NULL, s.stay_end_date) AS stay_end_date, 
               s.stay_deposit, 
               r.room_number, r.room_price, rt.electricity_rate, rt.water_rate, r.room_type, 
               CONCAT(m.mem_fname, ' ', m.mem_lname) AS mem_name 
        FROM stay s
        JOIN room r ON s.room_id = r.room_id
        JOIN `member` m ON s.mem_id = m.mem_id
        CROSS JOIN rate rt -- ดึงข้อมูลจากตาราง rate โดยไม่ต้องเชื่อมโยง
        WHERE s.room_id = ? 
        AND (s.stay_end_date IS NULL OR s.stay_end_date = '0000-00-00')
        AND rt.id = 1";

// เตรียมคำสั่ง SQL และตรวจสอบข้อผิดพลาด
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare SQL statement: ' . $conn->error]);
    exit();
}

// ผูกพารามิเตอร์และตรวจสอบข้อผิดพลาด
$stmt->bind_param("i", $room_id);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to execute SQL statement: ' . $stmt->error]);
    exit();
}

// ดึงผลลัพธ์
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data); // ส่งข้อมูลกลับเป็น JSON
} else {
    echo json_encode(['error' => 'No data found']); // ถ้าไม่พบข้อมูล
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>