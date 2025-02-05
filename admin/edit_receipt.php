<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

if (isset($_GET['receip_room_id'])) {
    $receip_room_id = $_GET['receip_room_id'];

    // ดึงข้อมูลที่เกี่ยวข้องจากฐานข้อมูล
    $query = "SELECT * FROM receip_detail WHERE receip_room_id = '$receip_room_id'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $receip_room_charge = $_POST['receip_room_charge'];
    $receip_electricity = $_POST['receip_electricity'];
    $receip_water = $_POST['receip_water'];
    $receip_type = $_POST['receip_type'];
    $receip_date = $_POST['receip_date'];

    // อัปเดตข้อมูลในฐานข้อมูล
    $update_query = "UPDATE receip_detail SET 
                        receip_room_charge = '$receip_room_charge', 
                        receip_electricity = '$receip_electricity', 
                        receip_water = '$receip_water', 
                        receip_type = '$receip_type', 
                        receip_date = '$receip_date' 
                    WHERE receip_room_id = '$receip_room_id'";

    if ($conn->query($update_query)) {
        echo "ข้อมูลได้รับการอัปเดตเรียบร้อยแล้ว";
        header("Location: receipt_detail.php");
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
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
    <title>แก้ไขใบเสร็จ</title>
</head>
<body>
    <div class="container mt-4">
        <h2>แก้ไขใบเสร็จ</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="receip_room_charge" class="form-label">ค่าเช่าห้อง</label>
                <input type="text" class="form-control" id="receip_room_charge" name="receip_room_charge" value="<?php echo $row['receip_room_charge']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="receip_electricity" class="form-label">ค่าไฟฟ้า</label>
                <input type="text" class="form-control" id="receip_electricity" name="receip_electricity" value="<?php echo $row['receip_electricity']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="receip_water" class="form-label">ค่าน้ำ</label>
                <input type="text" class="form-control" id="receip_water" name="receip_water" value="<?php echo $row['receip_water']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="receip_type" class="form-label">ชนิดห้อง</label>
                <input type="text" class="form-control" id="receip_type" name="receip_type" value="<?php echo $row['receip_type']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="receip_date" class="form-label">วันที่</label>
                <input type="date" class="form-control" id="receip_date" name="receip_date" value="<?php echo $row['receip_date']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
