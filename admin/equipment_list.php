<?php
session_start();

include('../includes/db.php'); // เชื่อมต่อฐานข้อมูล

// ฟังก์ชันเพิ่มครุภัณฑ์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $eqm_type = $_POST['eqm_type'];
    $eqm_name = $_POST['eqm_name'];

    $sql = "INSERT INTO equipment_detail (eqm_type, eqm_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }
    $stmt->bind_param("ss", $eqm_type, $eqm_name);

    if ($stmt->execute()) {
        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'สำเร็จ',
            'text' => 'เพิ่มครุภัณฑ์เรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด',
            'text' => 'ไม่สามารถเพิ่มครุภัณฑ์ได้: ' . $stmt->error
        ];
    }
    header("Location: equipment_list.php"); // Redirect กลับไปที่หน้าเดิม
    exit();
}

// ฟังก์ชันลบครุภัณฑ์
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $eqm_id = $_GET['delete_id'];

    $sql = "DELETE FROM equipment_detail WHERE eqm_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }
    $stmt->bind_param("i", $eqm_id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'สำเร็จ',
            'text' => 'ลบครุภัณฑ์เรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'เกิดข้อผิดพลาด',
            'text' => 'ไม่สามารถลบครุภัณฑ์ได้: ' . $stmt->error
        ];
    }
    header("Location: equipment_list.php"); // Redirect กลับไปที่หน้าเดิม
    exit();
}

// ดึงข้อมูลครุภัณฑ์
$sql = "SELECT * FROM equipment_detail";
$result = $conn->query($sql);

if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
}

// จัดกลุ่มข้อมูลตามประเภทครุภัณฑ์
$equipmentByType = [];
while ($row = $result->fetch_assoc()) {
    $type = $row['eqm_type'];
    if (!isset($equipmentByType[$type])) {
        $equipmentByType[$type] = [];
    }
    $equipmentByType[$type][] = $row;
}
?>

<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการครุภัณฑ์</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <div class="flex-1 p-8">
            <h2 class="text-2xl font-bold mb-6 dark:text-white">รายการครุภัณฑ์</h2>

            <!-- ปุ่มเพิ่มครุภัณฑ์ -->
            <div class="mb-6">
                <button onclick="openAddModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">
                    เพิ่มครุภัณฑ์
                </button>
            </div>

            <!-- แสดงข้อมูลครุภัณฑ์ -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($equipmentByType as $type => $equipmentList): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow" onclick="openModal('<?php echo $type; ?>')">
                        <h3 class="text-xl font-bold mb-2 dark:text-white"><?php echo $type; ?></h3>
                        <p class="text-gray-600 dark:text-gray-300">จำนวนครุภัณฑ์: <?php echo count($equipmentList); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงรายการครุภัณฑ์ -->
    <?php foreach ($equipmentByType as $type => $equipmentList): ?>
        <div id="modal-<?php echo $type; ?>" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden">
            <div class="relative top-20 mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold dark:text-white">รายการครุภัณฑ์: <?php echo $type; ?></h3>
                    <button onclick="closeModal('<?php echo $type; ?>')" class="text-gray-500 hover:text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <?php foreach ($equipmentList as $equipment): ?>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold dark:text-white"><?php echo $equipment['eqm_name']; ?></h4>
                            <div class="mt-2">
                                <button onclick="confirmDelete(<?php echo $equipment['eqm_id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700">ลบ</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Modal สำหรับเพิ่มครุภัณฑ์ -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden">
        <div class="relative top-20 mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold dark:text-white">เพิ่มครุภัณฑ์</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="add_equipment" value="1">
                <div class="mb-4">
                    <label for="eqm_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทครุภัณฑ์</label>
                    <select id="eqm_type" name="eqm_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="เครื่องใช้ไฟฟ้า">เครื่องใช้ไฟฟ้า</option>
                        <option value="ระบบสาธารณูปโภค">ระบบสาธารณูปโภค</option>
                        <option value="เฟอร์นิเจอร์">เฟอร์นิเจอร์</option>
                        <option value="อุปกรณ์สุขภัณฑ์">อุปกรณ์สุขภัณฑ์</option>
                        <option value="โครงสร้างและส่วนประกอบอาคาร">โครงสร้างและส่วนประกอบอาคาร</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="eqm_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อครุภัณฑ์</label>
                    <input type="text" id="eqm_name" name="eqm_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">บันทึก</button>
                    <button type="button" onclick="closeAddModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ฟังก์ชันเปิด/ปิด Modal
        function openModal(type) {
            document.getElementById(`modal-${type}`).classList.remove('hidden');
        }

        function closeModal(type) {
            document.getElementById(`modal-${type}`).classList.add('hidden');
        }

        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        // ฟังก์ชันยืนยันการลบ
        function confirmDelete(eqmId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณต้องการลบครุภัณฑ์นี้หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `equipment_list.php?delete_id=${eqmId}`;
                }
            });
        }

        // แสดง SweetAlert2 จาก session
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?php echo $_SESSION['alert']['icon']; ?>',
                title: '<?php echo $_SESSION['alert']['title']; ?>',
                text: '<?php echo $_SESSION['alert']['text']; ?>',
            });
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
    </script>
</body>
</html>