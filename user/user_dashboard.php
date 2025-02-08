<?php
session_start();

if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');
include('../includes/navbar_user.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/room_user.css">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
// ถ้ามีการส่งค่า payment_success มา, แสดงข้อความแจ้งเตือน
if (isset($_GET['payment_success']) && $_GET['payment_success'] == 'true') {
    echo "<script>
            Swal.fire({
                title: 'ชำระเงินสำเร็จ',
                text: 'การชำระเงินของคุณได้ถูกบันทึกเรียบร้อยแล้ว!',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'user_dashboard.php'; // หรือหน้าที่ต้องการ
                }
            });
          </script>";
}
?>

<div class="container mt-4">
    <h2>ยินดีต้อนรับ, <?php echo $_SESSION['mem_fname']; ?> <i class="fas fa-smile"></i></h2> 
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <i class="fas fa-user"></i> ข้อมูลผู้ใช้ 
        </div>
        <div class="card-body">
            <p><strong>ชื่อ:</strong> <?php echo $_SESSION['mem_fname'] . ' ' . $_SESSION['mem_lname']; ?></p>
            <p><strong>ชื่อผู้ใช้:</strong> <?php echo $_SESSION['mem_user']; ?></p>
            <p><strong>อีเมล:</strong> <?php echo isset($_SESSION['mem_mail']) ? $_SESSION['mem_mail'] : 'ไม่พบข้อมูลอีเมล'; ?></p>
            <p><strong>ที่อยู่:</strong> <?php echo $_SESSION['mem_address']; ?></p>
        </div>
    </div>

    <?php include('room_user.php'); ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
