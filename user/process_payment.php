<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบว่าได้ส่งข้อมูลจากฟอร์มมาแล้ว
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบค่าจาก POST
    if (isset($_POST['pay_id'], $_POST['pay_name'], $_POST['pay_room_type'], $_POST['pay_room_charge'], $_POST['pay_electricity'], $_POST['pay_water'], $_POST['pay_total'], $_POST['payment_date'], $_POST['room_id']) && isset($_FILES['payment_slip'])) {

        // รับค่าจากฟอร์ม
        $pay_id = $_POST['pay_id'];
        $pay_name = $_POST['pay_name'];
        $pay_room_type = $_POST['pay_room_type'];
        $pay_room_charge = $_POST['pay_room_charge'];
        $pay_electricity = $_POST['pay_electricity'];
        $pay_water = $_POST['pay_water'];
        $pay_total = $_POST['pay_total'];
        $payment_date = $_POST['payment_date'];
        $room_id = $_POST['room_id'];  // รับค่า room_id จากฟอร์ม

        // การอัปโหลดไฟล์
        $upload_dir = '../uploads/payments/';
        $image_name = uniqid() . basename($_FILES['payment_slip']['name']);
        $upload_file = $upload_dir . $image_name;
        $image_type = pathinfo($upload_file, PATHINFO_EXTENSION);

        // ตรวจสอบว่าเป็นไฟล์ภาพหรือไม่
        if (in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_file)) {
                $image_path = $upload_file; // เก็บเส้นทางไฟล์ที่อัปโหลด
            } else {
                echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
                exit;
            }
        } else {
            echo "ไฟล์ไม่รองรับ กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น";
            exit;
        }

        // เตรียมคำสั่ง SQL
        $query = "INSERT INTO payments (pay_id, pay_name, pay_room_type, pay_room_charge, pay_electricity, pay_water, pay_total, pay_date, image, room_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("SQL Error: " . $conn->error);
        }

        // bind parameters
        $stmt->bind_param("issddddssi", $pay_id, $pay_name, $pay_room_type, $pay_room_charge, $pay_electricity, $pay_water, $pay_total, $payment_date, $image_path, $room_id);

        // execute statement
        if ($stmt->execute()) {
            // เมื่อชำระเงินสำเร็จ รีไดเร็กต์ไปที่หน้า user_dashboard.php พร้อมกับการแสดง SweetAlert2
            header('Location: user_dashboard.php?payment_success=true');
            exit; // หยุดการทำงานของสคริปต์หลังจากที่รีไดเร็กต์
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลการชำระเงิน!";
        }

        $stmt->close();
    } else {
        echo "ข้อมูลที่ส่งไม่ครบถ้วน!";
    }
}
?>
