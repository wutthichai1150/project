<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

if ($conn === false) {
    die("Error: Could not connect to the database.");
}

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$query = "
    SELECT 
        pay_id, room_id, pay_name, pay_room_charge, pay_room_type, pay_electricity, pay_water, pay_total, pay_date, image
    FROM 
        payments
    WHERE 
        MONTH(pay_date) = ? AND YEAR(pay_date) = ?
    ORDER BY 
        pay_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error: Could not execute query. " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/manage_payment.css"> 
    <title>หน้าจัดการข้อมูลการชำระเงิน</title>
</head>
<body>
<div class="container mt-5">
    <h2>จัดการข้อมูลการชำระเงิน</h2>
    

    <form method="get" action="manage_payment.php" class="form-inline" onchange="this.submit()">
        <div class="form-group">
            <label for="month">เลือกเดือน:</label>
            <select name="month" id="month" class="form-control ml-2">
                <option value="1" <?php echo ($selected_month == 1) ? 'selected' : ''; ?>>มกราคม</option>
                <option value="2" <?php echo ($selected_month == 2) ? 'selected' : ''; ?>>กุมภาพันธ์</option>
                <option value="3" <?php echo ($selected_month == 3) ? 'selected' : ''; ?>>มีนาคม</option>
                <option value="4" <?php echo ($selected_month == 4) ? 'selected' : ''; ?>>เมษายน</option>
                <option value="5" <?php echo ($selected_month == 5) ? 'selected' : ''; ?>>พฤษภาคม</option>
                <option value="6" <?php echo ($selected_month == 6) ? 'selected' : ''; ?>>มิถุนายน</option>
                <option value="7" <?php echo ($selected_month == 7) ? 'selected' : ''; ?>>กรกฎาคม</option>
                <option value="8" <?php echo ($selected_month == 8) ? 'selected' : ''; ?>>สิงหาคม</option>
                <option value="9" <?php echo ($selected_month == 9) ? 'selected' : ''; ?>>กันยายน</option>
                <option value="10" <?php echo ($selected_month == 10) ? 'selected' : ''; ?>>ตุลาคม</option>
                <option value="11" <?php echo ($selected_month == 11) ? 'selected' : ''; ?>>พฤศจิกายน</option>
                <option value="12" <?php echo ($selected_month == 12) ? 'selected' : ''; ?>>ธันวาคม</option>
            </select>
        </div>
        <div class="form-group ml-2">
            <label for="year">เลือกปี:</label>
            <select name="year" id="year" class="form-control ml-2">
                <?php
                for ($i = date('Y'); $i >= date('Y') - 5; $i--) {
                    echo "<option value='$i' " . ($selected_year == $i ? 'selected' : '') . ">$i</option>";
                }
                ?>
            </select>
        </div>
    </form>

    <table class="table table-bordered table-hover mt-3">
    <thead>
        <tr>
            <th class="text-center" style="width: 120px;">หมายเลขห้อง</th>
            <th class="text-center" style="width: 150px;">ชื่อ</th>
            <th class="text-center" style="width: 150px;">ค่าห้อง</th>
            <th class="text-center" style="width: 150px;">ประเภทห้อง</th>
            <th class="text-center" style="width: 150px;">ค่าไฟ</th>
            <th class="text-center" style="width: 150px;">ค่าน้ำ</th>
            <th class="text-center" style="width: 150px;">ยอดรวม</th>
            <th class="text-center" style="width: 180px;">วันที่ชำระเงิน</th>
            <th class="text-center" style="width: 100px;">ใบเสร็จ</th>
            <th class="text-center" style="width: 180px;">การดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $calculated_total = $row['pay_room_charge'] + $row['pay_electricity'] + $row['pay_water'];
            $pay_total = $row['pay_total'];  

            echo "<tr>";
            echo "<td class='text-center'>" . htmlspecialchars($row['room_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['pay_name']) . "</td>";
            echo "<td class='text-right'>" . number_format($row['pay_room_charge'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . htmlspecialchars($row['pay_room_type']) . "</td>";
            echo "<td class='text-right'>" . number_format($row['pay_electricity'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . number_format($row['pay_water'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . number_format($pay_total, 2) . " บาท</td>";
            echo "<td class='text-center'>" . htmlspecialchars($row['pay_date']) . "</td>";
            echo "<td class='text-center'><a href='../uploads/" . htmlspecialchars($row['image']) . "' target='_blank'>ดูใบเสร็จ</a></td>";
            echo "<td class='text-center'>";  // Assume this column holds actions like editing or deleting
            echo "<a href='edit_payment.php?pay_id=" . htmlspecialchars($row['pay_id']) . "' class='btn btn-warning btn-sm'>แก้ไข</a>";
            echo "<a href='delete_payment.php?pay_id=" . htmlspecialchars($row['pay_id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณแน่ใจว่าจะลบข้อมูลนี้?\")'>ลบ</a>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10' class='text-center'>ไม่มีข้อมูลการชำระเงิน</td></tr>";
    }
    ?>
    </tbody>
</table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
