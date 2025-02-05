<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');
include('../includes/navbar_admin.php');

// ตรวจสอบว่าได้รับ mem_id ผ่าน URL หรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ไม่มี ID ของสมาชิกที่ระบุ");
}

// ดึง mem_id จาก URL
$mem_id = intval($_GET['id']);

// ดึงข้อมูลสมาชิกจากฐานข้อมูลตาม mem_id
$query = "SELECT mem_fname, mem_lname, mem_user, mem_mail FROM `member` WHERE mem_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mem_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบข้อมูลหรือไม่
if ($result->num_rows == 0) {
    die("ไม่พบข้อมูลสมาชิก");
}

$member = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>รายละเอียดสมาชิก</title>
    <style>
        .card {
            margin-top: 20px;
        }
        .card-header {
            font-size: 20px;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .card-body p {
            margin: 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">รายละเอียดสมาชิก</h2>

        <div class="card">
            <div class="card-header">
                ข้อมูลสมาชิก: <?php echo htmlspecialchars($member['mem_fname'] . ' ' . $member['mem_lname']); ?>
            </div>
            <div class="card-body">
                <p><strong>ชื่อผู้ใช้:</strong> <?php echo htmlspecialchars($member['mem_user']); ?></p>
                <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($member['mem_mail']); ?></p>
            </div>
        </div>

        <a href="manage_member.php" class="btn btn-secondary mt-3">กลับไปหน้าจัดการสมาชิก</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
