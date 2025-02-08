<?php
session_start();
include('../includes/db.php');
include('../includes/navbar_user.php');

// รับค่า room_number จาก URL
$room_number = isset($_GET['room_number']) ? $_GET['room_number'] : '';

// ตรวจสอบว่าได้รับ room_number หรือไม่
if (!$room_number) {
    echo 'ไม่พบข้อมูลหมายเลขห้อง';
    exit;
}

// ตรวจสอบว่าได้เข้าสู่ระบบและมีชื่อผู้ใช้ใน session หรือไม่
if (!isset($_SESSION['mem_fname']) || !isset($_SESSION['mem_lname'])) {
    echo 'กรุณาเข้าสู่ระบบ';
    exit;
}

$mem_fname = $_SESSION['mem_fname'];  // ชื่อจาก session
$mem_lname = $_SESSION['mem_lname'];  // นามสกุลจาก session

// คำสั่ง SQL เพื่อดึงข้อมูลการชำระเงินของผู้ใช้คนนี้
$sql = "SELECT p.pay_id, p.pay_name, p.pay_date, p.pay_room_charge, p.pay_electricity, p.pay_water, p.pay_total, p.image, r.room_number, r.room_type
        FROM payments p
        JOIN room r ON p.room_id = r.room_id
        WHERE r.room_number = '$room_number' 
        AND p.pay_name LIKE '$mem_fname%' 
        AND p.pay_name LIKE '%$mem_lname'  
        ORDER BY p.pay_date DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Error in query: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="container mt-4">
<h2 class="mb-4 text-center">
    <i class="fas fa-credit-card"></i>
    ประวัติการชำระเงินห้องหมายเลข <?php echo $room_number; ?>
</h2>    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-start"><i class="fas fa-door-open"></i> หมายเลขห้อง</th>
                        <th class="text-start"><i class="fas fa-user"></i> ชื่อ</th>
                        <th class="text-start"><i class="fas fa-calendar-day"></i> วันที่ชำระเงิน</th>
                        <th class="text-start"><i class="fas fa-bed"></i> ค่าห้อง</th>
                        <th class="text-start"><i class="fas fa-bolt"></i> ค่าไฟ</th>
                        <th class="text-start"><i class="fas fa-tint"></i> ค่าน้ำ</th>
                        <th class="text-start"><i class="fas fa-cogs"></i> ประเภทห้อง</th>
                        <th class="text-start"><i class="fas fa-money-bill-wave"></i> ยอดรวม</th>
                        <th class="text-start"><i class="fas fa-file-invoice"></i> ใบเสร็จ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['room_number']; ?></td>
                            <td><?php echo $row['pay_name']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['pay_date'])); ?></td>
                            <td><?php echo number_format($row['pay_room_charge'], 2); ?> บาท</td>
                            <td><?php echo number_format($row['pay_electricity'], 2); ?> บาท</td>
                            <td><?php echo number_format($row['pay_water'], 2); ?> บาท</td>
                            <td><?php echo $row['room_type']; ?></td>
                            <td><?php echo number_format($row['pay_total'], 2); ?> บาท</td>
                            <td><a href="<?php echo $row['image']; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-eye"></i> ดูใบเสร็จ</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center">ไม่มีข้อมูลการชำระเงินสำหรับห้องหมายเลขนี้ หรือคุณไม่ได้ชำระเงินในห้องนี้.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
