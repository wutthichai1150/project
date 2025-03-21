<?php
include('../includes/db.php');
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาไหม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];
    $selected_equipment = $_POST['equipment'] ?? []; // อุปกรณ์ที่เลือก

    // ตรวจสอบเลขห้องซ้ำในฐานข้อมูล
    $sql_check = "SELECT * FROM room WHERE room_number = '$room_number'";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        // ถ้ามีเลขห้องซ้ำ
        echo json_encode(['status' => 'error', 'message' => 'เลขห้องนี้มีอยู่แล้ว กรุณากรอกใหม่']);
        exit;
    }

    // ตรวจสอบค่าก่อนบันทึก
    if (empty($room_number) || empty($room_type) || empty($room_price) || empty($room_status)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }

    // สร้างคำสั่ง SQL สำหรับบันทึกห้องพัก
    $sql_room = "INSERT INTO room (room_number, room_type, room_price, room_status) 
                 VALUES ('$room_number', '$room_type', '$room_price', '$room_status')";

    // ตรวจสอบการดำเนินการ SQL
    if ($conn->query($sql_room)) {
        $room_id = $conn->insert_id; // รับ ID ของห้องที่เพิ่งเพิ่ม

        // บันทึกอุปกรณ์ที่เลือก
        if (!empty($selected_equipment)) {
            foreach ($selected_equipment as $eqm_id) {
                $sql_equipment = "INSERT INTO room_equipment (room_id, eqm_id) 
                                  VALUES ('$room_id', '$eqm_id')";
                $conn->query($sql_equipment);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลสำเร็จ']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
    }
    exit; // หยุดการทำงานหลังจากส่ง JSON กลับ
}

// ดึงข้อมูลอุปกรณ์ทั้งหมด
$sql_equipment = "SELECT * FROM equipment_detail";
$equipment_result = $conn->query($sql_equipment);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มห้องพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- เนื้อหาหลัก -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="max-w-2xl mx-auto bg-gray-100 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-semibold mb-6 dark:text-gray-100">เพิ่มห้องพัก</h1>
                <form id="roomForm" method="POST">
                    <!-- หมายเลขห้อง -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">หมายเลขห้อง</label>
                        <input type="text" name="room_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600" required placeholder="กรุณากรอกหมายเลขห้อง">
                    </div>

                    <!-- ประเภทห้อง -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทห้อง</label>
                        <select name="room_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600" required>
                            <option value="แอร์">แอร์</option>
                            <option value="พัดลม">พัดลม</option>
                        </select>
                    </div>

                    <!-- ราคา -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ราคา</label>
                        <input type="number" name="room_price" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600" required placeholder="กรุณากรอกราคา" min="0">
                        <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">กรอกตัวเลขเท่านั้น</p>
                    </div>

                    <!-- สถานะ -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">สถานะ</label>
                        <select name="room_status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600" required>
                            <option value="ว่าง">ว่าง</option>
                            <option value="มีผู้เช่า">มีผู้เช่า</option>
                        </select>
                    </div>

                    <!-- อุปกรณ์ -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">อุปกรณ์</label>
                        <div class="mt-1 grid grid-cols-2 gap-4">
                            <?php while ($row = $equipment_result->fetch_assoc()) : ?>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="equipment[]" value="<?= $row['eqm_id'] ?>" class="form-checkbox h-5 w-5 text-blue-600">
                                    <span class="ml-2 dark:text-gray-100"><?= $row['eqm_name'] ?></span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- ปุ่มบันทึกและยกเลิก -->
                    <div class="flex justify-between">
                        <button type="button" id="submitButton" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">บันทึก</button>
                        <a href="admin_dashboard.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('submitButton').addEventListener('click', function (e) {
            e.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

            // ส่งข้อมูลแบบ AJAX
            fetch('add_rooms.php', {
                method: 'POST',
                body: new FormData(document.getElementById('roomForm')) // ส่งข้อมูลฟอร์ม
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        // กลับไปยังหน้าจัดการห้องพักหลังจากกดตกลง
                        window.location.href = 'admin_dashboard.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                    confirmButtonText: 'ตกลง'
                });
            });
        });
    </script>
</body>
</html>