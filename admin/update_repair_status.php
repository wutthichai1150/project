<?php
include('../includes/db.php');

if ($conn === false) {
    die("Error: Could not connect to the database.");
}

// ตรวจสอบว่าได้ส่งข้อมูลมาหรือไม่
if (isset($_POST['repair_id']) && isset($_POST['repair_state'])) {
    $repair_id = $_POST['repair_id'];
    $repair_state = $_POST['repair_state'];

    // ตรวจสอบสถานะการซ่อมที่ได้รับ
    $valid_states = ['รอรับเรื่อง', 'กำลังดำเนินการ', 'ซ่อมบำรุงเรียบร้อย'];
    if (!in_array($repair_state, $valid_states)) {
        die('Error: สถานะการซ่อมไม่ถูกต้อง');
    }

    // เตรียมคำสั่ง SQL สำหรับอัพเดตสถานะการซ่อม
    $query = "UPDATE repair_requests SET repair_state = ? WHERE repair_id = ?";
    
    // เตรียมคำสั่ง SQL
    if ($stmt = $conn->prepare($query)) {
        // ผูกค่าตัวแปรกับคำสั่ง SQL
        $stmt->bind_param('si', $repair_state, $repair_id);

        // ดำเนินการคำสั่ง SQL
        if ($stmt->execute()) {
            // ถ้าอัพเดตสำเร็จ
            header('Location: manage_repair.php?status=success');
            exit();
        } else {
            // ถ้ามีข้อผิดพลาดในการอัพเดต
            die('Error: ไม่สามารถอัพเดตสถานะได้');
        }
    } else {
        die('Error: ไม่สามารถเตรียมคำสั่ง SQL ได้');
    }
} else {
    die('Error: ข้อมูลไม่ครบถ้วน');
}
?>
