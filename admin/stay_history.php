<?php
include('../includes/navbar_admin.php');
include('../includes/db.php');

// ตรวจสอบการเริ่ม session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// รับค่า room_id จาก URL
$room_id = $_GET['room_id']; 

$sql = "
    SELECT s.stay_id, s.room_id, s.mem_id, s.stay_start_date, s.stay_end_date, 
           m.mem_fname, m.mem_lname, r.room_number
    FROM stay s
    LEFT JOIN `member` m ON s.mem_id = m.mem_id
    LEFT JOIN room r ON s.room_id = r.room_id
    WHERE s.room_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการค่าเช่า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .manage-column button {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<div class="container mt-4">
        <h2 class="mb-4 text-center">การเข้าพัก</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-light">
                <?php if ($result->num_rows > 0): ?>
                    <thead>
                        <tr>
                            <th>ลำดับ</th>  <!-- เปลี่ยนหัวข้อจาก "รหัสการเข้าพัก" เป็น "ลำดับ" -->
                            <th>เลขห้อง</th>
                            <th>ชื่อสมาชิก</th>
                            <th>วันที่เริ่มเข้าพัก</th>
                            <th>วันที่สิ้นสุดการเข้าพัก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;  // เริ่มต้นลำดับที่ 1
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo sprintf("%03d", $index); ?></td> <!-- เติม 0 หน้าเลขลำดับ -->
                                <td><?php echo $row['room_number']; ?></td>
                                <td><?php echo $row['mem_fname'] . " " . $row['mem_lname']; ?></td>
                                <td><?php echo $row['stay_start_date']; ?></td>
                                <td><?php echo $row['stay_end_date']; ?></td>
                            </tr>
                        <?php 
                        $index++;  // เพิ่มลำดับทีละ 1
                        endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>ไม่มีประวัติการเข้าพักสำหรับห้องนี้</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
