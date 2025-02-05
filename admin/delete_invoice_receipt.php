<?php
include('../includes/db.php');

// รับค่า rec_id จากการส่งผ่าน AJAX
if (isset($_POST['rec_id'])) {
    $rec_id = $_POST['rec_id'];

    // เตรียมคำสั่ง SQL เพื่อลบข้อมูลใบเสร็จ
    $query = "DELETE FROM invoice_receipt WHERE rec_id = ?";
    $stmt = $conn->prepare($query);

    // ตรวจสอบการเตรียมคำสั่ง
    if ($stmt === false) {
        die('Error in preparing query: ' . $conn->error);
    }

    $stmt->bind_param("i", $rec_id); // bind ค่า rec_id

    // ลบข้อมูลในฐานข้อมูล
    if ($stmt->execute()) {
        echo "ลบใบเสร็จสำเร็จ";
    } else {
        echo "ไม่สามารถลบใบเสร็จได้";
    }

    $stmt->close();
}
?>
