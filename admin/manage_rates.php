<?php
include('../includes/db.php'); // รวมไฟล์เชื่อมต่อฐานข้อมูล
include('../includes/navbar_admin.php');

// ดึงข้อมูลเรทจากฐานข้อมูล
$query = "SELECT * FROM `rate` WHERE id = 1";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('การเตรียมคำสั่ง SQL ล้มเหลว: ' . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

// ถ้ามีข้อมูลในฐานข้อมูล
$rate = $result->fetch_assoc();

// อัปเดตข้อมูลเรท
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['water_rate']) && isset($_POST['electricity_rate'])) {
        // แปลงค่าเป็นจำนวนเต็ม
        $water_rate = (int)round($_POST['water_rate']);
        $electricity_rate = (int)round($_POST['electricity_rate']);

        // อัปเดตข้อมูลในฐานข้อมูล
        $update_query = "UPDATE rate SET water_rate = ?, electricity_rate = ? WHERE id = 1";
        $stmt = $conn->prepare($update_query);
        if ($stmt === false) {
            die('การเตรียมคำสั่ง SQL ล้มเหลว: ' . $conn->error);
        }

        $stmt->bind_param("ii", $water_rate, $electricity_rate);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error; // แสดงข้อผิดพลาดจากฐานข้อมูล
        } else {
            echo "<p>อัปเดตเรทค่าน้ำและค่าไฟเรียบร้อยแล้ว</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเรทค่าน้ำค่าไฟ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>ข้อมูลเรทค่าน้ำและค่าไฟ</h2>

        <!-- การ์ดแสดงข้อมูล -->
        <div class="card" style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title">เรทค่าน้ำ</h5>
                <p class="card-text"><?php echo isset($rate['water_rate']) ? $rate['water_rate'] . " บาท/หน่วย" : "ไม่มีข้อมูล"; ?></p>
                <h5 class="card-title">เรทค่าไฟ</h5>
                <p class="card-text"><?php echo isset($rate['electricity_rate']) ? $rate['electricity_rate'] . " บาท/หน่วย" : "ไม่มีข้อมูล"; ?></p>
                <!-- ปุ่มเปิด Modal แก้ไข -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
                    แก้ไขข้อมูล
                </button>
            </div>
        </div>

        <!-- Modal แก้ไขข้อมูล -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">แก้ไขเรทค่าน้ำค่าไฟ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="water_rate" class="form-label">เรทค่าน้ำ (บาท/หน่วย)</label>
                                <input type="number" class="form-control" id="water_rate" name="water_rate" value="<?php echo isset($rate['water_rate']) ? $rate['water_rate'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="electricity_rate" class="form-label">เรทค่าไฟ (บาท/หน่วย)</label>
                                <input type="number" class="form-control" id="electricity_rate" name="electricity_rate" value="<?php echo isset($rate['electricity_rate']) ? $rate['electricity_rate'] : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS และ Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
