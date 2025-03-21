<?php
session_start();
include('../includes/db.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

// รับข้อมูลจากฟอร์ม
$pay_id = $_POST['pay_id'];
$pay_name = $_POST['pay_name'];
$pay_room_charge = $_POST['pay_room_charge'];
$pay_room_type = $_POST['pay_room_type'];
$pay_electricity = $_POST['pay_electricity'];
$pay_water = $_POST['pay_water'];
$pay_total = $_POST['pay_total'];
$pay_date = $_POST['pay_date'];
$pay_method = $_POST['pay_method'];

// อัปโหลดรูปสลิป (ถ้ามี)
$image = null;
if ($pay_method === 'โอนเงิน' && isset($_FILES['image'])) {
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($_FILES['image']['name']);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image = $target_file;
    }
}

// อัปเดตข้อมูลในฐานข้อมูล
$query = "
    UPDATE payments 
    SET pay_name = '$pay_name', 
        pay_room_charge = '$pay_room_charge', 
        pay_room_type = '$pay_room_type', 
        pay_electricity = '$pay_electricity', 
        pay_water = '$pay_water', 
        pay_total = '$pay_total', 
        pay_date = '$pay_date', 
        pay_method = '$pay_method', 
        image = '$image'
    WHERE pay_id = '$pay_id'
";
if ($conn->query($query)) {
    header("Location: payment_list.php");
} else {
    header("Location: payment_list.php");
}
?>