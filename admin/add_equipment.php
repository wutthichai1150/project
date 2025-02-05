<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn === false) {
    die("Error: Could not connect to the database. " . mysqli_connect_error());
}

$resultMessage = ''; // ตัวแปรสำหรับแสดงข้อความหลังจากเพิ่มข้อมูล

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงข้อมูลจากฟอร์ม
    $eqm_type = $_POST['eqm_type'];
    $eqm_name = $_POST['eqm_name'];

    // คำสั่ง SQL สำหรับการเพิ่มข้อมูล
    $query = "INSERT INTO equipment_detail (eqm_type, eqm_name) VALUES (?, ?)";

    // เตรียมคำสั่ง SQL
    if ($stmt = $conn->prepare($query)) {
        // กำหนดค่าตัวแปรให้กับคำสั่ง SQL
        $stmt->bind_param("ss", $eqm_type, $eqm_name);

        // ตรวจสอบการ execute คำสั่ง SQL
        if ($stmt->execute()) {
            $resultMessage = 'success'; // เพิ่มข้อมูลสำเร็จ
        } else {
            $resultMessage = 'error'; // เกิดข้อผิดพลาดในการ execute
            $resultMessageDetails = $stmt->error; // ข้อความแสดงข้อผิดพลาดจาก SQL
        }
    } else {
        $resultMessage = 'error'; // เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL
        $resultMessageDetails = $conn->error; // ข้อความแสดงข้อผิดพลาดจากการเตรียมคำสั่ง
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลครุภัณฑ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            width: 400px;
            max-width: 100%;
            margin-top: 50px;
        }

        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>เพิ่มข้อมูลครุภัณฑ์</h2>

        <!-- แสดงข้อความหลังจากการบันทึก -->
        <div class="message">
            <?php if ($resultMessage == 'success') : ?>
                <p>ข้อมูลถูกบันทึกสำเร็จ!</p>
            <?php elseif ($resultMessage == 'error') : ?>
                <p>เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง</p>
                <!-- แสดงข้อผิดพลาดจากฐานข้อมูล -->
                <p>รายละเอียดข้อผิดพลาด: <?php echo isset($resultMessageDetails) ? $resultMessageDetails : 'ไม่สามารถดึงข้อมูลข้อผิดพลาดได้'; ?></p>
            <?php endif; ?>
        </div>

        <!-- ฟอร์มเพิ่มข้อมูลครุภัณฑ์ -->
        <form method="POST" action="">
            <label for="eqm_type">ประเภทครุภัณฑ์:</label>
            <input type="text" name="eqm_type" class="form-control" required><br>

            <label for="eqm_name">ชื่อครุภัณฑ์:</label>
            <input type="text" name="eqm_name" class="form-control" required><br>

            <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
        </form>
    </div>
</body>
</html>
