<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

// รับค่า room_id จาก URL
$room_id = $_GET['room_id'];

// ดึงข้อมูลห้องจากฐานข้อมูล
$query = "SELECT room.room_id, room.room_number, room.room_type, room.room_price, 
                 `member`.mem_id, `member`.mem_fname, `member`.mem_lname
          FROM room
          LEFT JOIN `member` ON room.member_id = `member`.mem_id
          WHERE room.room_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลห้องหรือไม่
if ($result->num_rows > 0) {
    $room = $result->fetch_assoc();
} else {
    echo "<p>ไม่พบข้อมูลห้องนี้</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>ดูรายละเอียดห้อง</title>
</head>
<body>
    <div class="container mt-4">
        <h2>รายละเอียดห้อง</h2>
        <div class="card">
            <div class="card-header">
                ห้อง <?php echo $room['room_number']; ?>
            </div>
            <div class="card-body">
                <p><strong>ประเภทห้อง:</strong> <?php echo $room['room_type']; ?></p>
                <p><strong>ราคา:</strong> <?php echo number_format($room['room_price'], 2); ?> บาท</p>
                <p><strong>ชื่อผู้เช่า:</strong> <?php echo $room['mem_fname'] . " " . $room['mem_lname']; ?></p>
            </div>
        </div>
        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">กลับไปที่หน้าห้องทั้งหมด</a>
    </div>
</body>
</html>
