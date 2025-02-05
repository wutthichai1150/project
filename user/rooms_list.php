<?php
// ตรวจสอบการเชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบว่า session ได้เริ่มต้นแล้วหรือยัง
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['mem_user'])) {
    echo "ยังไม่ได้ล็อกอิน";
    exit();
}

$username = $_SESSION['mem_user'];

// คำสั่ง SQL เพื่อดึงข้อมูลห้องที่เชื่อมโยงกับผู้ใช้
$query = "SELECT * FROM room WHERE member_id = (SELECT mem_id FROM `member` WHERE mem_user = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $rooms = [];
    echo "ไม่พบข้อมูลห้องที่เชื่อมโยงกับผู้ใช้<br>";
}
?>

<!-- แสดงข้อมูลห้อง -->
<h3 class="mt-4">ห้องของคุณ</h3>
<div class="card">
    <div class="card-header bg-primary text-white">
        รายละเอียดห้อง
    </div>
    <div class="card-body">
        <?php if (!empty($rooms)): ?>
            <ul class="list-group">
                <?php foreach ($rooms as $room): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>ห้องที่:</strong> <?php echo $room['room_number']; ?><br>
                            <strong>ประเภท:</strong> <?php echo $room['room_type']; ?><br>
                            <strong>ราคา:</strong> <?php echo $room['room_price']; ?> บาท<br>
                            <strong>สถานะ:</strong> <?php echo $room['room_status']; ?><br>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>ยังไม่มีห้องที่เชื่อมโยงกับคุณ</p>
        <?php endif; ?>
    </div>
</div>
