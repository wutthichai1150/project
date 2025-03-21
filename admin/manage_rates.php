<?php
session_start();

include('../includes/db.php'); // รวมไฟล์เชื่อมต่อฐานข้อมูล

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
        } 
    }
}
?>

<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเรทค่าน้ำค่าไฟ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h2 class="text-2xl font-bold mb-4 dark:text-white">ข้อมูลหน่วยค่าน้ำและค่าไฟ</h2>

            <!-- การ์ดแสดงข้อมูล -->
            <div class="bg-white p-6 rounded-lg shadow-md w-64 dark:bg-gray-800">
                <h5 class="text-lg font-semibold dark:text-white">เรทค่าน้ำ</h5>
                <p class="text-gray-700 dark:text-gray-300"><?php echo isset($rate['water_rate']) ? $rate['water_rate'] . " บาท/หน่วย" : "ไม่มีข้อมูล"; ?></p>
                <h5 class="text-lg font-semibold mt-4 dark:text-white">เรทค่าไฟ</h5>
                <p class="text-gray-700 dark:text-gray-300"><?php echo isset($rate['electricity_rate']) ? $rate['electricity_rate'] . " บาท/หน่วย" : "ไม่มีข้อมูล"; ?></p>
                <!-- ปุ่มเปิด Modal แก้ไข -->
                <button type="button" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700" onclick="openModal()">
                    แก้ไขข้อมูล
                </button>
            </div>

            <!-- Modal แก้ไขข้อมูล -->
            <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">แก้ไขหน่วยค่าน้ำค่าไฟ</h3>
                        <div class="mt-2 px-7 py-3">
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="water_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">หน่วยค่าน้ำ (บาท/หน่วย)</label>
                                    <input type="number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" id="water_rate" name="water_rate" value="<?php echo isset($rate['water_rate']) ? $rate['water_rate'] : ''; ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label for="electricity_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">หน่วยค่าไฟ (บาท/หน่วย)</label>
                                    <input type="number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" id="electricity_rate" name="electricity_rate" value="<?php echo isset($rate['electricity_rate']) ? $rate['electricity_rate'] : ''; ?>" required>
                                </div>
                                <div class="items-center px-4 py-3">
                                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">บันทึกการเปลี่ยนแปลง</button>
                                    <button type="button" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700" onclick="closeModal()">ยกเลิก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ฟังก์ชันเปิดปิด Modal
        function openModal() {
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // ฟังก์ชันสลับ Dark/Light Mode
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeText = document.getElementById('theme-text');

        themeToggleBtn.addEventListener('click', () => {
            const isDarkMode = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
            themeText.innerText = isDarkMode ? 'โหมดกลางวัน' : 'โหมดกลางคืน';
        });
    </script>
</body>
</html>