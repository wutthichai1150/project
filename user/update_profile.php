<?php
session_start();
include('../includes/db.php');

// ตรวจสอบว่ามีการส่งข้อมูลมา
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก AJAX
    $mem_fname = mysqli_real_escape_string($conn, $_POST['mem_fname']);
    $mem_lname = mysqli_real_escape_string($conn, $_POST['mem_lname']);
    $mem_email = mysqli_real_escape_string($conn, $_POST['mem_email']);
    $mem_phone = mysqli_real_escape_string($conn, $_POST['mem_phone']);

    // ตรวจสอบว่าผู้ใช้ล็อกอินแล้ว
    if (isset($_SESSION['mem_user'])) {
        $mem_user = $_SESSION['mem_user'];

        // สร้างคำสั่ง SQL สำหรับอัปเดตข้อมูลในฐานข้อมูล
        $sql = "UPDATE `member` SET mem_fname = '$mem_fname', mem_lname = '$mem_lname', mem_mail = '$mem_email', mem_phone = '$mem_phone' WHERE mem_user = '$mem_user'";

        // ดำเนินการอัปเดตข้อมูล
        if (mysqli_query($conn, $sql)) {
            echo 'success';  // ส่งค่ากลับไปที่ AJAX
        } else {
            echo 'error';    // ถ้ามีข้อผิดพลาดในการอัปเดต
        }
    } else {
        echo 'no_session'; // ถ้าไม่มี session ของผู้ใช้
    }
}
?>
