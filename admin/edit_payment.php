<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

if ($conn === false) {
    die("Error: Could not connect to the database.");
}

// ตรวจสอบว่าได้ส่ง pay_id มาใน URL หรือไม่
if (!isset($_GET['pay_id']) || empty($_GET['pay_id'])) {
    die("Error: Invalid Pay ID.");
}

$pay_id = $_GET['pay_id'];  // รับค่าจาก URL

// Query ดึงข้อมูลสถานะการชำระเงินจากฐานข้อมูล
$query = "SELECT pay_state FROM pay_detail WHERE pay_id = '$pay_id'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pay_state = $row['pay_state'];
} else {
    die("Error: No data found for Pay ID.");
}

// เมื่อได้รับการโพสต์ข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $pay_state = $_POST['pay_state'];

    // อัปเดตข้อมูลสถานะ
    $update_query = "UPDATE pay_detail SET pay_state = '$pay_state' WHERE pay_id = '$pay_id'";

    if ($conn->query($update_query) === TRUE) {
        echo "สถานะใบเสร็จได้รับการอัปเดตสำเร็จ!";
        header("Location: manage_payment.php"); // หลังการอัปเดต
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>แก้ไขสถานะใบเสร็จ</title>
</head>
<body>
<div class="container mt-5">
    <h2>แก้ไขสถานะใบเสร็จ</h2>
    <form method="post">
        <div class="mb-3">
            <label for="pay_state" class="form-label">สถานะการชำระเงิน</label>
            <select class="form-control" id="pay_state" name="pay_state" required>
                <option value="รอดำเนินการ" <?php echo ($pay_state == 'รอดำเนินการ') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                <option value="ชำระเงินแล้ว" <?php echo ($pay_state == 'ชำระเงินแล้ว') ? 'selected' : ''; ?>>ชำระเงินแล้ว</option>
                <option value="ยังไม่ชำระ" <?php echo ($pay_state == 'ยังไม่ชำระ') ? 'selected' : ''; ?>>ยังไม่ชำระ</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
