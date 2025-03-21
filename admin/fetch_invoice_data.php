<?php
session_start();
include('../includes/db.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

// รับค่า room_id จากคำขอ
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

if (!$room_id) {
    echo json_encode([]);
    exit();
}

// ดึงข้อมูลใบแจ้งหนี้ล่าสุดจากตาราง invoice_receipt
$query = "SELECT * FROM invoice_receipt WHERE room_id = '$room_id' ORDER BY rec_date DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $invoice_data = $result->fetch_assoc();
    echo json_encode($invoice_data);
} else {
    echo json_encode([]);
}
?>