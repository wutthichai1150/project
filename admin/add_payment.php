<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($_POST['room_id']) || empty($_POST['pay_name']) || empty($_POST['pay_room_charge']) || empty($_POST['pay_room_type']) || empty($_POST['pay_electricity']) || empty($_POST['pay_water']) || empty($_POST['pay_total']) || empty($_POST['pay_date']) || empty($_POST['pay_method'])) {
        header("Location: payment_list.php?error=1");
        exit();
    }

    // ดึงข้อมูลจากฟอร์ม
    $room_id = $_POST['room_id'];
    $pay_name = $_POST['pay_name'];
    $pay_room_charge = $_POST['pay_room_charge'];
    $pay_room_type = $_POST['pay_room_type'];
    $pay_electricity = $_POST['pay_electricity'];
    $pay_water = $_POST['pay_water'];
    $pay_total = $_POST['pay_total'];
    
    // แปลงวันที่เป็นรูปแบบ Y-m-d
    $pay_date = DateTime::createFromFormat('d/m/Y', $_POST['pay_date'])->format('Y-m-d');
    
    $pay_method = $_POST['pay_method'];

    // อัปโหลดรูปสลิป (ถ้ามี)
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            header("Location: payment_list.php?error=1");
            exit();
        }

        // ตรวจสอบขนาดไฟล์ (ไม่เกิน 2MB)
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            header("Location: payment_list.php?error=1");
            exit();
        }

        // สร้างโฟลเดอร์หากยังไม่มี
        $upload_dir = '../uploads/payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // สร้างชื่อไฟล์ใหม่เพื่อป้องกันการทับซ้อน
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        // ย้ายไฟล์ไปยังโฟลเดอร์เป้าหมาย
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image_path = $file_path;
        } else {
            header("Location: payment_list.php?error=1");
            exit();
        }
    }

    // บันทึกข้อมูลลงฐานข้อมูล
    $query = "INSERT INTO payments (room_id, pay_name, pay_room_charge, pay_room_type, pay_electricity, pay_water, pay_total, pay_date, pay_method, image) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("เตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }
    // ปรับเปลี่ยน bind_param ตามประเภทของข้อมูล
    $stmt->bind_param("isssssssss", $room_id, $pay_name, $pay_room_charge, $pay_room_type, $pay_electricity, $pay_water, $pay_total, $pay_date, $pay_method, $image_path);

    if ($stmt->execute()) {
        header("Location: payment_list.php?success=1");
    } else {
        header("Location: payment_list.php?error=1");
    }
    exit();
}
?>
