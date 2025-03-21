<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $rec_id = $_POST['rec_id'];
    $pay_name = $_POST['pay_name'];
    $pay_room_charge = $_POST['pay_room_charge'];
    $pay_electricity = $_POST['pay_electricity'];
    $pay_water = $_POST['pay_water'];
    $pay_total = $_POST['pay_total'];
    $pay_room_type = $_POST['pay_room_type'];
    $pay_date = $_POST['pay_date'];
    
    // ตรวจสอบและอัพโหลดไฟล์สลิป
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $upload_dir = '../uploads/payments/';
        $image_path = $upload_dir . $image_name;
        
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // บันทึกข้อมูลการชำระเงิน
            $query = "INSERT INTO payments (room_id, pay_name, pay_room_charge, pay_electricity, pay_water, pay_total, pay_room_type, pay_date, image) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issddssss", $room_id, $pay_name, $pay_room_charge, $pay_electricity, $pay_water, $pay_total, $pay_room_type, $pay_date, $image_path);
            
            if ($stmt->execute()) {
                // อัปเดตสถานะใบแจ้งหนี้เป็น "รอดำเนินการ"
                $update_query = "UPDATE invoice_receipt SET rec_status = 'รอดำเนินการ' WHERE rec_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("i", $rec_id);
                $update_stmt->execute();

                // แสดง SweetAlert2 สำหรับการบันทึกสำเร็จ
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกการชำระเงินเรียบร้อยแล้ว',
                            text: 'ขอบคุณที่ชำระเงิน!',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            window.location.href = 'user_dashboard.php'; // เปลี่ยนเป็นหน้าเป้าหมาย
                        });
                    });
                </script>";
            } else {
                // แสดง SweetAlert2 สำหรับเกิดข้อผิดพลาด
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง!',
                            confirmButtonText: 'ตกลง'
                        });
                    });
                </script>";
            }
        } else {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์',
                        text: 'กรุณาลองใหม่อีกครั้ง!',
                        confirmButtonText: 'ตกลง'
                    });
                });
            </script>";
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกไฟล์สลิปการชำระเงิน',
                    confirmButtonText: 'ตกลง'
                });
            });
        </script>";
    }
}
?>
