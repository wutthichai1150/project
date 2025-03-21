<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// ตัวกรองเดือนและปี
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m'); // ค่าเริ่มต้นเป็นเดือนปัจจุบัน
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // ค่าเริ่มต้นเป็นปีปัจจุบัน

// ดึงข้อมูลรายการชำระเงินพร้อมเลขห้องและกรองเดือน
$query_payments = "
    SELECT p.*, r.room_number 
    FROM payments p
    JOIN room r ON p.room_id = r.room_id
    WHERE YEAR(p.pay_date) = '$selected_year' AND MONTH(p.pay_date) = '$selected_month'
";
$result_payments = $conn->query($query_payments);

$query_monthly_payments = "
SELECT DATE_FORMAT(pay_date, '%Y-%m') AS month, SUM(pay_total) AS total 
FROM payments 
WHERE YEAR(pay_date) = '$selected_year' AND MONTH(pay_date) = '$selected_month'
GROUP BY DATE_FORMAT(pay_date, '%Y-%m')
ORDER BY month
";
$result_monthly_payments = $conn->query($query_monthly_payments);

// สร้างข้อมูลสำหรับ Chart
$monthly_payments_data = [];
if ($result_monthly_payments && $result_monthly_payments->num_rows > 0) {
while ($row = $result_monthly_payments->fetch_assoc()) {
    $monthly_payments_data[] = $row;
}
}
// ดึงข้อมูลการชำระเงินรวมทั้งหมด
$query_total_payments_all_months = "SELECT SUM(pay_total) AS total FROM payments";
$result_total_payments_all_months = $conn->query($query_total_payments_all_months);
$total_payments_all_months = $result_total_payments_all_months->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>รายการชำระเงิน</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="./assets/js/focus-trap.js"></script>
    <script src="./assets/js/init-alpine.js"></script>
</head>
<body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
        <?php include 'includes/sidebar.php'; ?>

        <main class="h-full overflow-y-auto">
            <div class="container px-6 mx-auto grid">
                <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">รายการชำระเงิน</h2>

                <!-- ตัวกรองเดือนและปี -->
                <form method="GET" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เลือกเดือนและปี:</label>
                    <div class="flex space-x-2">
                        <!-- เลือกเดือน -->
                        <select name="month" class="p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" onchange="this.form.submit()">
                            <?php
                            $months = [
                                "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน",
                                "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม",
                                "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($num == $selected_month) ? "selected" : "";
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>

                        <!-- เลือกปี -->
                        <select name="year" class="p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" onchange="this.form.submit()">
                            <?php
                            $current_year = date('Y');
                            for ($year = $current_year - 10; $year <= $current_year + 10; $year++) {
                                $selected = ($year == $selected_year) ? "selected" : "";
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>

                <!-- แสดงการชำระเงินรวมทั้งหมดของทุกเดือน -->
                <div class="mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">การชำระเงินรวมทั้งหมด</h3>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo number_format($total_payments_all_months, 2); ?> บาท</p>
                    </div>
                </div>

                <!-- ปุ่มเพิ่มการชำระเงิน -->
                <div class="mb-6">
                    <button onclick="openAddPaymentModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-plus"></i> เพิ่มการชำระเงิน
                    </button>
                </div>

                <!-- จัดวางตารางและแท็บชาร์ต -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- ตารางแสดงรายการชำระเงิน -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">รหัสชำระเงิน</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ห้อง</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ชื่อผู้ชำระ</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">รวมทั้งหมด</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">วันที่ชำระ</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
    <?php
    if ($result_payments && $result_payments->num_rows > 0) {
        while ($payment = $result_payments->fetch_assoc()) {
            $pay_id = $payment['pay_id'];
            $room_number = $payment['room_number'];
            $pay_name = $payment['pay_name'];
            $pay_total = $payment['pay_total'];
            $pay_date = $payment['pay_date'];
            $pay_room_charge = $payment['pay_room_charge'];
            $pay_room_type = $payment['pay_room_type'];
            $pay_electricity = $payment['pay_electricity'];
            $pay_water = $payment['pay_water'];
            $pay_image = $payment['image'];
            ?>
            <tr>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo $pay_id; ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo $room_number; ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo $pay_name; ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo $pay_total; ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo $pay_date; ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                    <!-- ปุ่มดู -->
                    <button onclick="openModal(
                        '<?php echo $pay_id; ?>', 
                        '<?php echo $room_number; ?>', 
                        '<?php echo $pay_name; ?>', 
                        '<?php echo $pay_room_charge; ?>', 
                        '<?php echo $pay_room_type; ?>', 
                        '<?php echo $pay_electricity; ?>', 
                        '<?php echo $pay_water; ?>', 
                        '<?php echo $pay_total; ?>', 
                        '<?php echo $pay_date; ?>', 
                        '<?php echo !empty($payment['image']) ? $payment['image'] : ''; ?>',
                        '<?php echo $payment['pay_method']; ?>'
                    )" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-eye"></i> ดู
                    </button>

                    <!-- ปุ่มแก้ไข -->
                    <button onclick="openEditModal(
                        '<?php echo $pay_id; ?>', 
                        '<?php echo $room_number; ?>', 
                        '<?php echo $pay_name; ?>', 
                        '<?php echo $pay_room_charge; ?>', 
                        '<?php echo $pay_room_type; ?>', 
                        '<?php echo $pay_electricity; ?>', 
                        '<?php echo $pay_water; ?>', 
                        '<?php echo $pay_total; ?>', 
                        '<?php echo $pay_date; ?>', 
                        '<?php echo !empty($payment['image']) ? $payment['image'] : ''; ?>',
                        '<?php echo $payment['pay_method']; ?>'
                    )" class="text-yellow-500 hover:text-yellow-700 ml-2">
                        <i class="fas fa-edit"></i> แก้ไข
                    </button>

                    <!-- ปุ่มพิมพ์ใบเสร็จ -->
                    <a href="generate_payment_pdf.php?pay_id=<?php echo $pay_id; ?>" class="text-blue-500 hover:text-blue-700 ml-2">
                        <i class="fas fa-print"></i> พิมพ์ใบเสร็จ
                    </a>
                </td>
            </tr>
            <?php
        }
    } else {
        echo "<tr><td colspan='6' class='px-4 py-4 text-center text-gray-600 dark:text-gray-400'>ไม่พบข้อมูลรายการชำระเงิน</td></tr>";
    }
    ?>
</tbody>
                        </table>
                    </div>
                    <?php
                    function getThaiMonth($month) {
                        $thai_months = [
                            "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม",
                            "04" => "เมษายน", "05" => "พฤษภาคม", "06" => "มิถุนายน",
                            "07" => "กรกฎาคม", "08" => "สิงหาคม", "09" => "กันยายน",
                            "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
                        ];
                        return $thai_months[$month];
                    }

$thai_month = getThaiMonth($selected_month);
?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">การชำระเงินของเดือน <?php echo $thai_month . ' ' . $selected_year; ?></h3>
                        <canvas id="monthlyPaymentsChart" class="mt-4"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
<!-- Modal สำหรับแก้ไขใบเสร็จ -->
<div id="editPaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- พื้นหลังมืด -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-75"></div>
        </div>

        <!-- Modal Content -->
        <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full mx-4 sm:mx-0">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 text-center">แก้ไขใบเสร็จ</h3>
            </div>

            <!-- Body -->
            <div class="px-4 pb-4">
                <form id="editPaymentForm" method="POST" action="edit_payment.php" enctype="multipart/form-data" class="space-y-3">
                    <!-- ซ่อนรหัสชำระเงิน -->
                    <input type="hidden" name="pay_id" id="editPayId">

                    <!-- ชื่อผู้ชำระ -->
                    <div>
                        <label for="editPayName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อผู้ชำระ</label>
                        <input type="text" name="pay_name" id="editPayName" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- ค่าห้อง -->
                    <div>
                        <label for="editPayRoomCharge" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าห้อง</label>
                        <input type="number" name="pay_room_charge" id="editPayRoomCharge" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- ประเภทห้อง -->
                    <div>
                        <label for="editPayRoomType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทห้อง</label>
                        <input type="text" name="pay_room_type" id="editPayRoomType" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- ค่าไฟฟ้า -->
                    <div>
                        <label for="editPayElectricity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าไฟฟ้า</label>
                        <input type="number" name="pay_electricity" id="editPayElectricity" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- ค่าน้ำ -->
                    <div>
                        <label for="editPayWater" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าน้ำ</label>
                        <input type="number" name="pay_water" id="editPayWater" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- รวมทั้งหมด -->
                    <div>
                        <label for="editPayTotal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">รวมทั้งหมด</label>
                        <input type="number" name="pay_total" id="editPayTotal" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- วันที่ชำระ -->
                    <div>
                        <label for="editPayDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วันที่ชำระ</label>
                        <input type="date" name="pay_date" id="editPayDate" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                    </div>

                    <!-- วิธีการชำระเงิน -->
                    <div>
                        <label for="editPayMethod" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วิธีการชำระเงิน</label>
                        <select name="pay_method" id="editPayMethod" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required onchange="toggleEditSlipUpload(this.value)">
                            <option value="ชำระเงินสด">ชำระเงินสด</option>
                            <option value="โอนเงิน">โอนเงิน</option>
                        </select>
                    </div>

                    <!-- อัปโหลดรูปสลิป (แสดงเฉพาะเมื่อเลือกโอนเงิน) -->
                    <div id="editSlipUploadSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รูปสลิป (ถ้ามี)</label>
                        <input type="file" name="image" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md">
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse">
                <button type="submit" form="editPaymentForm" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:text-sm">
                    บันทึก
                </button>
                <button type="button" onclick="closeEditPaymentModal()" class="mt-3 w-full sm:w-auto inline-flex justify-center rounded-md border border-red-500 shadow-sm px-4 py-2 bg-red-500 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:text-sm dark:bg-red-600 dark:text-white dark:border-red-600 dark:hover:bg-red-700">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>
  <!-- Modal สำหรับดูรายละเอียดการชำระเงิน -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal content -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">รหัสชำระเงิน <span class="font-bold text-gray-900 dark:text-gray-100" id="modalPayId"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">วันที่ชำระ <span class="font-bold text-gray-900 dark:text-gray-100" id="modalPayDate"></span></p>
                    </div>
                    <div>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex flex-col md:flex-row mb-6">
                    <div class="flex-1">
                        <h5 class="font-bold text-lg text-gray-900 dark:text-gray-100" id="modalRoomNumber"></h5>
                        <p class="text-gray-500 dark:text-gray-400">ชื่อผู้ชำระ: <span class="text-gray-900 dark:text-gray-100" id="modalPayName"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">ประเภทห้อง: <span class="text-gray-900 dark:text-gray-100" id="modalPayRoomType"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">ค่าห้อง: <span class="text-gray-900 dark:text-gray-100" id="modalPayRoomCharge"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">ค่าไฟฟ้า: <span class="text-gray-900 dark:text-gray-100" id="modalPayElectricity"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">ค่าน้ำ: <span class="text-gray-900 dark:text-gray-100" id="modalPayWater"></span></p>
                        <p class="text-gray-500 dark:text-gray-400">วิธีการชำระเงิน: <span class="text-gray-900 dark:text-gray-100" id="modalPayMethod"></span></p>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-4">รวมทั้งหมด: <span id="modalPayTotal"></span></h4>
                    </div>
                    <div class="mt-4 md:mt-0 md:ml-6">
                        <img id="modalPayImage" src="" alt="รูปสลิป" class="w-48 h-48 object-cover rounded-lg">
                        <p id="noImageMessage" class="text-sm text-gray-500 dark:text-gray-400 mt-2 hidden">ชำระเงินสด</p>
                    </div>
                </div>

                <!-- Progress bar -->
                <ul class="flex justify-between items-center mb-6">
                    <li class="flex-1 text-center">
                        <span class="block text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-check text-green-500"></i> ชำระแล้ว
                        </span>
                        <div class="h-1 bg-green-500 dark:bg-green-700 mt-2"></div>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>

                <!-- Modal สำหรับเพิ่มการชำระเงิน -->
                <div id="addPaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- พื้นหลังมืด -->
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900 dark:opacity-75"></div>
                        </div>

                        <!-- Modal Content -->
                        <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full mx-4 sm:mx-0">
                            <!-- Header -->
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 text-center">เพิ่มการชำระเงิน</h3>
                            </div>

                            <!-- Body -->
                            <div class="px-4 pb-4">
                                <form id="addPaymentForm" method="POST" action="add_payment.php" enctype="multipart/form-data" class="space-y-3">
                                    <!-- เลือกห้อง -->
                                    <div>
                                        <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ห้อง</label>
                                        <select name="room_id" id="room_id" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required onchange="fetchInvoiceData(this.value)">
                                            <option value="">เลือกห้อง</option>
                                            <?php
                                            $query_rooms = "SELECT room_id, room_number FROM room";
                                            $result_rooms = $conn->query($query_rooms);
                                            while ($room = $result_rooms->fetch_assoc()) {
                                                echo "<option value='{$room['room_id']}'>{$room['room_number']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- ชื่อผู้ชำระ -->
                                    <div>
                                        <label for="pay_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อผู้ชำระ</label>
                                        <input type="text" name="pay_name" id="pay_name" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- วันที่ออกใบแจ้งหนี้ -->
                                    <div>
                                        <label for="rec_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วันที่ออกใบแจ้งหนี้</label>
                                        <input type="text" name="rec_date" id="rec_date" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100 bg-gray-100" readonly>
                                    </div>

                                    <!-- ค่าห้อง -->
                                    <div>
                                        <label for="pay_room_charge" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าห้อง</label>
                                        <input type="number" name="pay_room_charge" id="pay_room_charge" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- ประเภทห้อง -->
                                    <div>
                                        <label for="pay_room_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทห้อง</label>
                                        <input type="text" name="pay_room_type" id="pay_room_type" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- ค่าไฟฟ้า -->
                                    <div>
                                        <label for="pay_electricity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าไฟฟ้า</label>
                                        <input type="number" name="pay_electricity" id="pay_electricity" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- ค่าน้ำ -->
                                    <div>
                                        <label for="pay_water" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าน้ำ</label>
                                        <input type="number" name="pay_water" id="pay_water" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- รวมทั้งหมด -->
                                    <div>
                                        <label for="pay_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300">รวมทั้งหมด</label>
                                        <input type="number" name="pay_total" id="pay_total" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                        <!-- วันที่ชำระ -->
                                    <div>
                                        <label for="pay_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วันที่ชำระ</label>
                                        <input type="text" name="pay_date" id="pay_date" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required>
                                    </div>

                                    <!-- ใส่ CDN ของ Flatpickr -->
                                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            flatpickr("#pay_date", {
                                                dateFormat: "d/m/Y", // แสดงวันที่ในรูปแบบ DD/MM/YYYY
                                                defaultDate: "today", // วันที่เริ่มต้นคือวันนี้
                                            });
                                        });
                                    </script>


                                    <!-- วิธีการชำระเงิน -->
                                    <div>
                                        <label for="pay_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วิธีการชำระเงิน</label>
                                        <select name="pay_method" id="pay_method" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-100" required onchange="toggleSlipUpload(this.value)">
                                            <option value="ชำระเงินสด">ชำระเงินสด</option>
                                            <option value="โอนเงิน">โอนเงิน</option>
                                        </select>
                                    </div>

                                    <!-- อัปโหลดรูปสลิป (แสดงเฉพาะเมื่อเลือกโอนเงิน) -->
                                    <div id="slipUploadSection" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รูปสลิป (ถ้ามี)</label>
                                        <input type="file" name="image" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md">
                                    </div>
                                </form>
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse">
                                <button type="submit" form="addPaymentForm" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:text-sm">
                                    บันทึก
                                </button>
                                <button type="button" onclick="closeAddPaymentModal()" class="mt-3 w-full sm:w-auto inline-flex justify-center rounded-md border border-red-500 shadow-sm px-4 py-2 bg-red-500 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:text-sm dark:bg-red-600 dark:text-white dark:border-red-600 dark:hover:bg-red-700">
                                    ยกเลิก
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
    <!-- Script สำหรับเปิด/ปิด Modal -->
    <script>
    // ฟังก์ชันเปิด Modal ดูรายละเอียด
    function openModal(payId, roomNumber, payName, payRoomCharge, payRoomType, payElectricity, payWater, payTotal, payDate, payImage, payMethod) {
        // กำหนดค่าข้อมูลใน Modal
        document.getElementById('modalPayId').innerText = payId;
        document.getElementById('modalRoomNumber').innerText = roomNumber;
        document.getElementById('modalPayName').innerText = payName;
        document.getElementById('modalPayRoomCharge').innerText = payRoomCharge;
        document.getElementById('modalPayRoomType').innerText = payRoomType;
        document.getElementById('modalPayElectricity').innerText = payElectricity;
        document.getElementById('modalPayWater').innerText = payWater;
        document.getElementById('modalPayTotal').innerText = payTotal;
        document.getElementById('modalPayDate').innerText = payDate;
        document.getElementById('modalPayMethod').innerText = payMethod; // แสดงวิธีการชำระเงิน

        // แสดงรูปภาพถ้ามี
        const modalPayImage = document.getElementById('modalPayImage');
        const noImageMessage = document.getElementById('noImageMessage');
        if (payImage) {
            modalPayImage.src = payImage;
            modalPayImage.classList.remove('hidden');
            noImageMessage.classList.add('hidden');
        } else {
            modalPayImage.classList.add('hidden');
            noImageMessage.classList.remove('hidden');
        }

        // แสดง Modal
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    // ฟังก์ชันปิด Modal
    function closeModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }
</script>
<script>
    // ฟังก์ชันเปิด Modal เพิ่มการชำระเงิน
    function openAddPaymentModal() {
        document.getElementById('addPaymentModal').classList.remove('hidden');
    }

    // ฟังก์ชันปิด Modal เพิ่มการชำระเงิน
    function closeAddPaymentModal() {
        document.getElementById('addPaymentModal').classList.add('hidden');
    }
</script>
<script>
    // ฟังก์ชันดึงข้อมูลใบแจ้งหนี้
    function fetchInvoiceData(roomId) {
        if (!roomId) return;

        // ส่งคำขอ AJAX เพื่อดึงข้อมูลใบแจ้งหนี้ล่าสุด
        fetch(`fetch_invoice_data.php?room_id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
                    const recDate = data.rec_date ? formatDate(data.rec_date) : '';
                    
                    // เติมข้อมูลลงในฟอร์ม
                    document.getElementById('rec_date').value = recDate;
                    document.getElementById('pay_name').value = data.rec_name || '';
                    document.getElementById('pay_room_charge').value = data.rec_room_charge || '';
                    document.getElementById('pay_room_type').value = data.rec_room_type || '';
                    document.getElementById('pay_electricity').value = data.rec_electricity || '';
                    document.getElementById('pay_water').value = data.rec_water || '';
                    document.getElementById('pay_total').value = data.rec_total || '';
                }
            })
            .catch(error => console.error('Error fetching invoice data:', error));
    }

    // ฟังก์ชันแปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // เดือนเริ่มจาก 0
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // ฟังก์ชันแสดง/ซ่อนฟอร์มอัปโหลดรูปสลิป
    function toggleSlipUpload(payMethod) {
        const slipUploadSection = document.getElementById('slipUploadSection');
        if (payMethod === 'โอนเงิน') {
            slipUploadSection.classList.remove('hidden');
        } else {
            slipUploadSection.classList.add('hidden');
        }
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ข้อมูลการชำระเงินของเดือนที่เลือกจาก PHP
        const monthlyPaymentsData = <?php echo json_encode($monthly_payments_data); ?>;

        // เตรียมข้อมูลสำหรับ Chart
        const labels = monthlyPaymentsData.map(item => item.month);
        const chartData = monthlyPaymentsData.map(item => item.total);

        // ตรวจสอบว่ามีกราฟเก่าอยู่หรือไม่ และทำลายกราฟเก่าก่อนสร้างกราฟใหม่
        if (window.monthlyPaymentsChart && typeof window.monthlyPaymentsChart.destroy === 'function') {
            window.monthlyPaymentsChart.destroy(); // ทำลายกราฟเก่า
        }

        // สร้างกราฟใหม่
        const ctx = document.getElementById('monthlyPaymentsChart').getContext('2d');
        window.monthlyPaymentsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'การชำระเงินรวม (บาท)',
                    data: chartData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
<script>
    // ฟังก์ชันเปิด Modal แก้ไขใบเสร็จ
    function openEditModal(payId, roomNumber, payName, payRoomCharge, payRoomType, payElectricity, payWater, payTotal, payDate, payImage, payMethod) {
        // เติมข้อมูลเดิมลงในฟอร์ม
        document.getElementById('editPayId').value = payId;
        document.getElementById('editPayName').value = payName;
        document.getElementById('editPayRoomCharge').value = payRoomCharge;
        document.getElementById('editPayRoomType').value = payRoomType;
        document.getElementById('editPayElectricity').value = payElectricity;
        document.getElementById('editPayWater').value = payWater;
        document.getElementById('editPayTotal').value = payTotal;
        document.getElementById('editPayDate').value = payDate;
        document.getElementById('editPayMethod').value = payMethod;

        // แสดงหรือซ่อนฟอร์มอัปโหลดรูปสลิป
        toggleEditSlipUpload(payMethod);

        // แสดง Modal
        document.getElementById('editPaymentModal').classList.remove('hidden');
    }

    // ฟังก์ชันปิด Modal แก้ไขใบเสร็จ
    function closeEditPaymentModal() {
        document.getElementById('editPaymentModal').classList.add('hidden');
    }

    // ฟังก์ชันแสดง/ซ่อนฟอร์มอัปโหลดรูปสลิปใน Modal แก้ไข
    function toggleEditSlipUpload(payMethod) {
        const editSlipUploadSection = document.getElementById('editSlipUploadSection');
        if (payMethod === 'โอนเงิน') {
            editSlipUploadSection.classList.remove('hidden');
        } else {
            editSlipUploadSection.classList.add('hidden');
        }
    }
</script>

</body>
</html>