<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/db.php');  
include('includes/navbar.php');  

$registration_status = false;
$duplicate_error = false;

if (isset($_POST['register'])) {
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname']; 
    $mem_user = $_POST['mem_user'];  
    $mem_password = $_POST['mem_password']; 
    $mem_mail = $_POST['mem_mail'];  
    $mem_phone = $_POST['mem_phone'];  
    $mem_address = $_POST['mem_address'];  
    $mem_id_card = $_FILES['mem_id_card']['name']; 
    $target_dir = "uploads/member/"; 
    $target_file = $target_dir . basename($_FILES["mem_id_card"]["name"]);

    // ตรวจสอบข้อมูลซ้ำ
    $user_check_query = $conn->prepare("SELECT * FROM `member` WHERE `mem_user` = ? OR `mem_mail` = ?");
    $user_check_query->bind_param("ss", $mem_user, $mem_mail);
    $user_check_query->execute();
    $result = $user_check_query->get_result();

    if ($result->num_rows > 0) {
        // ถ้ามีชื่อผู้ใช้หรืออีเมลซ้ำ
        $duplicate_error = true;
    } else {
        // อัพโหลดไฟล์บัตรประชาชน
        if (!move_uploaded_file($_FILES["mem_id_card"]["tmp_name"], $target_file)) {
            die("<div class='alert alert-danger'>ล้มเหลวในการอัปโหลดรูปภาพบัตรประชาชน</div>");
        }

        // ทำการลงทะเบียน
        $query = $conn->prepare("INSERT INTO `member` (mem_fname, mem_lname, mem_user, mem_password, mem_mail, mem_phone, mem_address, mem_id_card) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if ($query === false) {
            die("เกิดข้อผิดพลาด SQL: " . $conn->error);
        }

        $query->bind_param("ssssssss", $mem_fname, $mem_lname, $mem_user, $mem_password, $mem_mail, $mem_phone, $mem_address, $mem_id_card);

        if ($query->execute()) {
            $registration_status = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
        }
        .form-label i {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i> ลงทะเบียน</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="mem_fname" class="form-label"><i class="fas fa-user me-2"></i>ชื่อ</label>
                <input type="text" name="mem_fname" class="form-control form-control-sm" placeholder="กรอกชื่อของคุณ" required>
            </div>
            <div class="mb-3">
                <label for="mem_lname" class="form-label"><i class="fas fa-user me-2"></i>นามสกุล</label>
                <input type="text" name="mem_lname" class="form-control form-control-sm" placeholder="กรอกนามสกุลของคุณ" required>
            </div>
            <div class="mb-3">
                <label for="mem_user" class="form-label"><i class="fas fa-user-circle me-2"></i>ชื่อผู้ใช้</label>
                <input type="text" name="mem_user" class="form-control form-control-sm" placeholder="เลือกชื่อผู้ใช้" required>
            </div>
            <div class="mb-3">
                <label for="mem_password" class="form-label"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                <input type="password" name="mem_password" class="form-control form-control-sm" placeholder="กรอกรหัสผ่าน" required>
            </div>
            <div class="mb-3">
                <label for="mem_mail" class="form-label"><i class="fas fa-envelope me-2"></i>อีเมล</label>
                <input type="email" name="mem_mail" class="form-control form-control-sm" placeholder="กรอกอีเมลของคุณ" required>
            </div>
            <div class="mb-3">
                <label for="mem_phone" class="form-label"><i class="fas fa-phone me-2"></i>หมายเลขโทรศัพท์</label>
                <input type="text" name="mem_phone" class="form-control form-control-sm" placeholder="กรอกหมายเลขโทรศัพท์ของคุณ" required>
            </div>
            <div class="mb-3">
                <label for="mem_address" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>ที่อยู่</label>
                <textarea name="mem_address" class="form-control form-control-sm" placeholder="กรอกที่อยู่ของคุณ" required></textarea>
            </div>
            <div class="mb-3">
                <label for="mem_id_card" class="form-label"><i class="fas fa-id-card me-2"></i>รูปบัตรประชาชน</label>
                <input type="file" name="mem_id_card" class="form-control form-control-sm" accept="image/*" required>
            </div>
            <button type="submit" name="register" class="btn btn-sm w-100" style="background-color: #008080; color: white;">
                <i class="fas fa-check-circle me-2"></i> ลงทะเบียน
            </button>
        </form>
    </div>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php
        if ($duplicate_error) {
            echo "Swal.fire({
                    icon: 'error',
                    title: 'ข้อมูลซ้ำ!',
                    text: 'ชื่อผู้ใช้หรืออีเมลที่ท่านกรอกมีในระบบแล้ว',
                    confirmButtonText: 'ตกลง'
                });";
        } elseif (isset($registration_status) && $registration_status) {
            echo "Swal.fire({
                    icon: 'success',
                    title: 'ลงทะเบียนสำเร็จ!',
                    text: 'คลิกที่นี่เพื่อเข้าสู่ระบบ',
                    confirmButtonText: 'ตกลง'
                }).then(function() {
                    window.location.href = 'login.php';
                });";
        } 
        ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
