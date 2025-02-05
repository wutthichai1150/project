<?php
session_start(); // เริ่มต้น session

// เช็คว่าผู้ใช้ยังไม่ได้ออกจากระบบ
if (isset($_SESSION['user_id']) || isset($_SESSION['ad_id'])) {
    // ทำลาย session และออกจากระบบ
    session_unset();
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        // ใช้ SweetAlert2 เพื่อแสดงการแจ้งเตือน
        Swal.fire({
            title: 'ออกจากระบบสำเร็จ!',
            text: 'คุณได้ออกจากระบบแล้ว',
            icon: 'success',
            confirmButtonText: 'ตกลง'
        }).then(function() {
            window.location.href = 'login.php'; // รีไดเร็กต์ไปที่หน้า login.php หลังจากคลิก "ตกลง"
        });
    </script>
</body>
</html>
