<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

include('../includes/db.php');

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$room_id_from_url = $_GET['room_id'] ?? null;

if ($room_id_from_url) {
    // ดึงหมายเลขห้องจากฐานข้อมูล
    $sql = "SELECT room_number FROM room WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('SQL Error: ' . $conn->error);
    }
    $stmt->bind_param("i", $room_id_from_url);
    $stmt->execute();
    $stmt->bind_result($room_number_to_display);
    $stmt->fetch();
    $stmt->close();
}

// ตรวจสอบว่ามีหมายเลขห้องหรือไม่
if (!isset($room_number_to_display)) {
    $room_number_to_display = 'ไม่พบข้อมูลห้อง'; // กำหนดค่าเริ่มต้นหากไม่พบข้อมูล
}

// ดึงข้อมูลอุปกรณ์ที่อยู่ในห้องพักนี้
$sql = "SELECT ed.eqm_id, ed.eqm_type, ed.eqm_name 
        FROM room_equipment re
        JOIN equipment_detail ed ON re.eqm_id = ed.eqm_id
        WHERE re.room_id = $room_id_from_url
        ORDER BY ed.eqm_type, ed.eqm_name";
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
    $repair_detail = $_POST['repair_detail'];
    $repair_date = $_POST['repair_date']; // รับค่าจากฟอร์มวันที่
    $selected_equipment = $_POST['selected_equipment']; // รับค่าครุภัณฑ์ที่เลือก

    // อัปโหลดรูปภาพ
    if (isset($_FILES['repair_image']) && $_FILES['repair_image']['error'] == 0) {
        $image_name = $_FILES['repair_image']['name'];
        $image_tmp_name = $_FILES['repair_image']['tmp_name'];
        $image_path = '../uploads/repair/' . $image_name;
        move_uploaded_file($image_tmp_name, $image_path);
    } else {
        $image_name = NULL; 
    }

    // บันทึกข้อมูลการแจ้งซ่อม
    $sql = "INSERT INTO repair_requests (room_id, repair_name, repair_type, repair_eqm_name, repair_detail, repair_image, repair_date, repair_state) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'รอรับเรื่อง')";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('SQL Error: ' . $conn->error);
    }

    // ดึงประเภทครุภัณฑ์จากชื่อครุภัณฑ์
    $repair_type = '';
    foreach ($grouped_equipment as $type => $equipments) {
        foreach ($equipments as $equipment) {
            if ($equipment['eqm_name'] === $selected_equipment) {
                $repair_type = $type;
                break;
            }
        }
    }

    $stmt->bind_param("issssss", $room_id, $repair_name, $repair_type, $selected_equipment, $repair_detail, $image_name, $repair_date);

    if (!$stmt->execute()) {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error . "'); window.history.back();</script>";
        exit();
    }

    echo "<script>alert('แจ้งซ่อมเรียบร้อยแล้ว'); window.location.href='user_dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>แจ้งซ่อมครุภัณฑ์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dropdown-content {
            display: none;
            transition: all 0.3s ease;
        }
        .dropdown-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans font-prompt">

<div class="container mx-auto mt-10 p-5 bg-white rounded-lg shadow-xl">
    <h3 class="text-2xl font-semibold text-teal-700 mb-4">
        <i class="fas fa-tools text-yellow-500"></i> แจ้งซ่อมครุภัณฑ์ ห้องหมายเลข: 
        <?php echo $room_number_to_display; ?>
    </h3>

    <form action="repair_form.php?room_id=<?php echo $room_id_from_url; ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="room_id" value="<?php echo $room_id_from_url; ?>" required>

    <!-- ชื่อผู้แจ้ง -->
    <div class="mb-5">
        <label for="repair_name" class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-user text-blue-600"></i> ชื่อผู้แจ้ง
        </label>
        <input type="text" name="repair_name" id="repair_name" class="mt-1 block w-full border-blue-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500" value="<?php echo $_SESSION['mem_fname'] . ' ' . $_SESSION['mem_lname']; ?>" required readonly>
    </div>

    <!-- เลือกครุภัณฑ์ที่ต้องการแจ้งซ่อม -->
    <div class="mb-5">
        <label class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-box text-red-500"></i> เลือกครุภัณฑ์ที่ต้องการแจ้งซ่อม
        </label>
        <?php foreach ($grouped_equipment as $type => $equipments): ?>
            <div class="mt-4 bg-gray-50 p-4 rounded-lg" id="category-<?php echo $type; ?>">
                <h4 class="text-lg text-gray-800 mb-3 cursor-pointer" onclick="toggleDropdown('<?php echo $type; ?>')">
                    <i class="fas fa-folder-open text-teal-500"></i> <?php echo $type; ?>
                    <i class="fas fa-chevron-down float-right text-gray-600"></i>
                </h4>
                <div id="dropdown-<?php echo $type; ?>" class="dropdown-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($equipments as $equipment): ?>
                            <label class="flex items-center p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <input type="radio" name="selected_equipment" value="<?php echo $equipment['eqm_name']; ?>" class="form-radio h-5 w-5 text-blue-600" onchange="handleSelection('<?php echo $type; ?>')">
                                <span class="ml-3 text-lg text-gray-700"><?php echo $equipment['eqm_name']; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- รายละเอียดปัญหา -->
    <div class="mb-5">
        <label for="repair_detail" class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-comment-dots text-purple-500"></i> รายละเอียดปัญหา
        </label>
        <textarea name="repair_detail" id="repair_detail" class="mt-1 block w-full border-purple-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500" rows="4" required></textarea>
    </div>

    <!-- วันที่แจ้งซ่อม -->
    <div class="mb-5">
        <label for="repair_date" class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-calendar-day text-orange-500"></i> วันที่แจ้งซ่อม
        </label>
        <input type="hidden" name="repair_date" id="repair_date" value="<?php echo date('Y-m-d'); ?>">
        <p class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 p-2">
            <?php echo date('d/m/Y'); ?>
        </p>
    </div>

    <!-- รูปภาพ -->
    <div class="mb-5">
        <label for="repair_image" class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-image text-indigo-500"></i> รูปภาพ
        </label>
        <input type="file" name="repair_image" id="repair_image" class="mt-1 block w-full border-indigo-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500" accept="image/*">
    </div>

    <!-- ปุ่มแจ้งซ่อม -->
    <div class="flex justify-between items-center">
        <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <i class="fas fa-save"></i> แจ้งซ่อม
        </button>

        <!-- ปุ่มดูรายการซ่อม -->
        <a href="repair_history.php?room_id=<?php echo $room_id_from_url; ?>" class="inline-block bg-gray-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
            <i class="fas fa-history"></i> ดูรายการซ่อม
        </a>
    </div>
</form>

</div>



</body>
</html>