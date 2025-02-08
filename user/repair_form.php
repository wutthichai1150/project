<?php
session_start();
include('../includes/db.php');
include('../includes/navbar_user.php');

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลจากตาราง equipment_detail
$sql = "SELECT eqm_id, eqm_type, eqm_name FROM equipment_detail ORDER BY eqm_type, eqm_name";
$result = mysqli_query($conn, $sql);
$equipment_details = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $equipment_details[] = $row;
    }
}

// สร้างอาร์เรย์เพื่อจัดกลุ่มข้อมูลตามประเภท
$grouped_equipment = [];
foreach ($equipment_details as $equipment) {
    $grouped_equipment[$equipment['eqm_type']][] = $equipment;
}

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $room_id = $_POST['room_id'];
    $repair_name = $_POST['repair_name'];
    $repair_type = $_POST['repair_type'];
    $repair_eqm_name = $_POST['repair_eqm_name'];
    $repair_detail = $_POST['repair_detail'];
    $repair_date = $_POST['repair_date'];

    // อัปโหลดรูปภาพ
    if (isset($_FILES['repair_image']) && $_FILES['repair_image']['error'] == 0) {
        $image_name = $_FILES['repair_image']['name'];
        $image_tmp_name = $_FILES['repair_image']['tmp_name'];
        $image_path = '../uploads/repair/' . $image_name;
        move_uploaded_file($image_tmp_name, $image_path);
    } else {
        $image_name = NULL; 
    }

    $sql = "INSERT INTO repair_requests (room_id, repair_name, repair_type, repair_eqm_name, repair_detail, repair_image, repair_date, repair_state) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'กำลังดำเนินการ')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $room_id, $repair_name, $repair_type, $repair_eqm_name, $repair_detail, $image_name, $repair_date);

    if ($stmt->execute()) {
        echo "<script>alert('แจ้งซ่อมเรียบร้อยแล้ว'); window.location.href='user_dashboard.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อมครุภัณฑ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script>
        // ฟังก์ชันสำหรับกรองชื่อครุภัณฑ์ตามประเภทที่เลือก
        function filterEquipment() {
            var typeSelect = document.getElementById('repair_type');
            var nameSelect = document.getElementById('repair_eqm_name');
            var selectedType = typeSelect.value;

            // ลบตัวเลือกเก่าออกก่อน
            nameSelect.innerHTML = '<option value="">เลือกชื่อครุภัณฑ์</option>';

            if (selectedType !== '') {
                // กรองตามประเภท
                var equipment = <?php echo json_encode($grouped_equipment); ?>;
                var options = equipment[selectedType] || [];

                // เพิ่มตัวเลือกใหม่ใน select ของชื่อครุภัณฑ์
                options.forEach(function(item) {
                    var option = document.createElement('option');
                    option.value = item.eqm_name;
                    option.textContent = item.eqm_name;
                    nameSelect.appendChild(option);
                });
            }
        }
    </script>
</head>
<body>
<div class="container mt-4">
    <?php
    // ดึง room_id จาก URL
    $room_id_from_url = $_GET['room_id'] ?? null;
    
    if ($room_id_from_url) {
        // ดึงหมายเลขห้องจากฐานข้อมูล
        $sql = "SELECT room_number FROM room WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id_from_url);
        $stmt->execute();
        $stmt->bind_result($room_number_to_display);
        $stmt->fetch();
        $stmt->close();
    }
    ?>

    <h3 class="mb-4">
        <i class="fas fa-tools"></i> แจ้งซ่อมครุภัณฑ์ สำหรับห้องหมายเลข: 
        <?php echo isset($room_number_to_display) ? $room_number_to_display : 'ไม่พบข้อมูลห้อง'; ?>
    </h3>

    <form action="repair_form.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <input type="hidden" name="room_id" value="<?php echo $room_id_from_url; ?>" required>
        </div>

        <div class="mb-3">
            <label for="repair_name" class="form-label">
                <i class="fas fa-user"></i> ชื่อผู้แจ้ง
            </label>
            <!-- ดึงชื่อผู้แจ้งจาก session -->
            <input type="text" name="repair_name" id="repair_name" class="form-control" value="<?php echo $_SESSION['mem_fname'] . ' ' . $_SESSION['mem_lname']; ?>" required readonly>
        </div>

        <div class="mb-3">
            <label for="repair_type" class="form-label">
                <i class="fas fa-cogs"></i> ประเภทครุภัณฑ์
            </label>
            <select name="repair_type" id="repair_type" class="form-select" required onchange="filterEquipment()">
                <option value="">เลือกประเภทครุภัณฑ์</option>
                <?php
                foreach (array_keys($grouped_equipment) as $type) {
                    echo "<option value='" . $type . "'>" . $type . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="repair_eqm_name" class="form-label">
                <i class="fas fa-box"></i> ชื่อครุภัณฑ์
            </label>
            <select name="repair_eqm_name" id="repair_eqm_name" class="form-select" required>
                <option value="">เลือกชื่อครุภัณฑ์</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="repair_detail" class="form-label">
                <i class="fas fa-comment-dots"></i> รายละเอียดปัญหา
            </label>
            <textarea name="repair_detail" id="repair_detail" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="repair_date" class="form-label">
                <i class="fas fa-calendar-day"></i> วันที่แจ้งซ่อม
            </label>
            <input type="date" name="repair_date" id="repair_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="repair_image" class="form-label">
                <i class="fas fa-image"></i> รูปภาพ
            </label>
            <input type="file" name="repair_image" id="repair_image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> แจ้งซ่อม
        </button>
   
        <a href="repair_history.php?room_id=<?php echo $_GET['room_id']; ?>" class="btn btn-secondary">
            <i class="fas fa-history"></i> ดูรายการซ่อม
        </a>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
