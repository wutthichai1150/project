<?php
session_start();
include('../includes/db.php');

// รับค่าจากฟอร์ม
$invoice_id = $_POST['invoice_id'];
$room_id = $_POST['room_id'];  // รับ room_id ที่ส่งมาจากฟอร์ม

// การอัพเดตข้อมูลในฐานข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rec_room_charge = $_POST['rec_room_charge'];
    $rec_water = $_POST['rec_water'];
    $rec_electricity = $_POST['rec_electricity'];
    $rec_total = $rec_room_charge + $rec_water + $rec_electricity; // คำนวณยอดรวม
    $rec_date = $_POST['rec_date'];
    $rec_status = $_POST['rec_status'];

    // คิวรี SQL อัพเดตข้อมูลใบเสร็จ
    $query = "UPDATE invoice_receipt SET rec_room_charge = ?, rec_water = ?, rec_electricity = ?, rec_total = ?, rec_date = ?, rec_status = ? WHERE rec_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ddddssi", $rec_room_charge, $rec_water, $rec_electricity, $rec_total, $rec_date, $rec_status, $invoice_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'แก้ไขใบเสร็จสำเร็จ';
    } else {
        $_SESSION['error_message'] = 'เกิดข้อผิดพลาดในการแก้ไขใบเสร็จ';
    }

    // รีไดเร็กต์กลับไปยังหน้า edit_room.php พร้อม room_id
    header("Location: manage_room.php?room_id=" . $room_id);
    exit();
}
?>
