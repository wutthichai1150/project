<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');
include('../includes/navbar_admin.php');

// ตรวจสอบว่ามีการส่งค่า id เข้ามาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ไม่พบ ID สมาชิก");
}

$member_id = $_GET['id'];

// ดึงข้อมูลสมาชิกที่ต้องการแก้ไขจากฐานข้อมูล
$query = "SELECT * FROM `member` WHERE mem_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลสมาชิก");
}

$member = $result->fetch_assoc();

// เมื่อผู้ใช้ส่งแบบฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    // อัปเดตข้อมูลในฐานข้อมูล
    $update_query = "UPDATE `member` SET mem_fname = ?, mem_lname = ?, mem_user = ?, mem_mail = ? WHERE mem_id = ?";
    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        die("SQL Error: " . $conn->error);
    }

    $update_stmt->bind_param("ssssi", $fname, $lname, $username, $email, $member_id);

    if ($update_stmt->execute()) {
        echo "<script>
                alert('แก้ไขข้อมูลสำเร็จ!');
                window.location.href = 'manage_member.php';
              </script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $conn->error . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>แก้ไขข้อมูลสมาชิก</title>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">แก้ไขข้อมูลสมาชิก</h2>
        <form action="" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="fname" class="form-label">ชื่อ:</label>
                <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($member['mem_fname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="lname" class="form-label">นามสกุล:</label>
                <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($member['mem_lname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้:</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($member['mem_user']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">อีเมล:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($member['mem_mail']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="manage_member.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>