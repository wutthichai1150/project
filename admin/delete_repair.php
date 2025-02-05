<?php
include('../includes/db.php');

// ตรวจสอบว่าได้รับ repair_id หรือไม่
if (isset($_POST['repair_id'])) {
    $repair_id = $_POST['repair_id'];

    // เตรียมคำสั่ง SQL สำหรับลบรายการ
    $query = "DELETE FROM repair_requests WHERE repair_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $repair_id);

        if ($stmt->execute()) {
            // ถ้าลบสำเร็จ, เปลี่ยนเส้นทางไปยังหน้าจัดการรายการแจ้งซ่อม
            header('Location: manage_repair.php?status=deleted');
            exit();
        } else {
            die('Error: ไม่สามารถลบข้อมูลได้');
        }
    } else {
        die('Error: ไม่สามารถเตรียมคำสั่ง SQL ได้');
    }
} else {
    die('Error: ข้อมูลไม่ครบถ้วน');
}
?>
