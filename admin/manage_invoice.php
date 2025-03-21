<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// กำหนดเดือนและปีเริ่มต้น
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// ดึงข้อมูลใบแจ้งหนี้จากฐานข้อมูล พร้อมดึงเลขห้อง
$sql = "SELECT ir.*, r.room_number FROM `invoice_receipt` ir
        JOIN room r ON ir.room_id = r.room_id
        WHERE MONTH(ir.rec_date) = '$selected_month' AND YEAR(ir.rec_date) = '$selected_year'";
$result = $conn->query($sql);

// ตรวจสอบการลบข้อมูล
if (isset($_GET['delete_rec_id'])) {
    $rec_id = $_GET['delete_rec_id'];
    $delete_sql = "DELETE FROM `invoice_receipt` WHERE rec_id = $rec_id";
    if ($conn->query($delete_sql)) {
        $alert = ['status' => 'success', 'message' => 'ลบข้อมูลใบแจ้งหนี้เรียบร้อย'];
    } else {
        $alert = ['status' => 'error', 'message' => 'ไม่สามารถลบข้อมูลได้'];
    }
}

if (isset($_POST['update_receipt'])) {
    $rec_id = $_POST['rec_id'];
    $rec_room_charge = $_POST['rec_room_charge'];
    $rec_electricity = $_POST['rec_electricity'];
    $rec_water = $_POST['rec_water'];
    $rec_total = $_POST['rec_total'];
    $rec_status = $_POST['rec_status'];

    // ดึงวันที่เดิมจากฐานข้อมูล
    $query_old_date = "SELECT rec_date FROM invoice_receipt WHERE rec_id = ?";
    $stmt_old_date = $conn->prepare($query_old_date);
    $stmt_old_date->bind_param("i", $rec_id);
    $stmt_old_date->execute();
    $result_old_date = $stmt_old_date->get_result();
    $row = $result_old_date->fetch_assoc();
    $old_date = $row['rec_date']; // วันที่เดิมในฐานข้อมูล

    // ตรวจสอบค่าที่ได้รับจากฟอร์ม
    $rec_date = isset($_POST['rec_date']) && !empty($_POST['rec_date']) ? $_POST['rec_date'] : $old_date;

    // แปลงวันที่หากมีการส่งค่ามาจากฟอร์ม
    if (!empty($_POST['rec_date'])) {
        $date_obj = DateTime::createFromFormat('d/m/Y', $_POST['rec_date']);
        if ($date_obj) {
            $rec_date = $date_obj->format('Y-m-d');
        }
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $update_sql = "UPDATE `invoice_receipt` SET 
                   rec_room_charge = ?, 
                   rec_electricity = ?, 
                   rec_water = ?, 
                   rec_total = ?, 
                   rec_status = ?, 
                   rec_date = ?
                   WHERE rec_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ddddssi", $rec_room_charge, $rec_electricity, $rec_water, $rec_total, $rec_status, $rec_date, $rec_id);

    if ($stmt->execute()) {
        $alert = ['status' => 'success', 'message' => 'อัปเดตข้อมูลใบแจ้งหนี้เรียบร้อย'];
    } else {
        $alert = ['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้'];
    }
}


// ดึงข้อมูลอัตราค่าไฟฟ้าและค่าน้ำจากฐานข้อมูล
$query_rate = "SELECT electricity_rate, water_rate FROM rate WHERE id = 1";
$stmt_rate = $conn->prepare($query_rate);
if ($stmt_rate) {
    $stmt_rate->execute();
    $rate_result = $stmt_rate->get_result();
    $rate = $rate_result->fetch_assoc();
} else {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลอัตราค่าไฟและค่าน้ำ");
}

// ตรวจสอบว่าเป็น AJAX request หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_invoice'])) {
    header('Content-Type: application/json');

    // รับค่าจาก $_POST และตรวจสอบว่ามีค่าหรือไม่
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    $rec_room_charge = isset($_POST['rec_room_charge']) ? floatval($_POST['rec_room_charge']) : 0;
    $rec_electricity_units = isset($_POST['rec_electricity_units']) ? floatval($_POST['rec_electricity_units']) : 0;
    $rec_water_units = isset($_POST['rec_water_units']) ? floatval($_POST['rec_water_units']) : 0;
    $rec_date = isset($_POST['rec_date']) ? date("Y-m-d", strtotime($_POST['rec_date'])) : date("Y-m-d");
    $rec_status = isset($_POST['rec_status']) ? $_POST['rec_status'] : 'รอดำเนินการ';
    $rec_name = isset($_POST['rec_name']) ? $_POST['rec_name'] : '';
    $rec_room_type = isset($_POST['rec_room_type']) ? $_POST['rec_room_type'] : '';

    // ตรวจสอบอัตราค่าไฟฟ้าและค่าน้ำจากฐานข้อมูล
    $query_rate = "SELECT electricity_rate, water_rate FROM rate WHERE id = 1";
    $stmt_rate = $conn->prepare($query_rate);
    if ($stmt_rate) {
        $stmt_rate->execute();
        $rate_result = $stmt_rate->get_result();
        $rate = $rate_result->fetch_assoc();
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลอัตราค่าไฟและค่าน้ำ']);
        exit();
    }

    // คำนวณค่าไฟฟ้าและค่าน้ำ
    $rec_electricity = $rec_electricity_units * floatval($rate['electricity_rate']);
    $rec_water = $rec_water_units * floatval($rate['water_rate']);
    $rec_total = $rec_room_charge + $rec_electricity + $rec_water;

    // ตรวจสอบวันที่ที่ส่งจากฟอร์ม
        $rec_date = isset($_POST['rec_date']) ? $_POST['rec_date'] : date("d/m/Y");
        $date_obj = DateTime::createFromFormat('d/m/Y', $rec_date);

        if ($date_obj && $date_obj->format('d/m/Y') === $rec_date) {
            // ถ้าวันที่ถูกต้อง แปลงเป็นรูปแบบ Y-m-d
            $rec_date = $date_obj->format('Y-m-d');
        } else {
            // หากวันที่ไม่ถูกต้อง กำหนดให้เป็นวันที่ปัจจุบัน
            $rec_date = date('Y-m-d');
        }

        $stmt = $conn->prepare("INSERT INTO invoice_receipt (room_id, rec_room_charge, rec_electricity, rec_water, rec_total, rec_date, rec_status, rec_name, rec_room_type) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iddddssss", $room_id, $rec_room_charge, $rec_electricity, $rec_water, $rec_total, $rec_date, $rec_status, $rec_name, $rec_room_type);


    if (!$stmt->execute()) {
        $response = [
            'success' => false,
            'message' => 'ไม่สามารถเพิ่มใบแจ้งหนี้ได้',
            'error' => $stmt->error // แสดงข้อผิดพลาดของ MySQL
        ];
    } else {
        $response = ['success' => true, 'message' => 'เพิ่มใบแจ้งหนี้เรียบร้อย'];
    }
    
    // ล้าง output buffer เพื่อป้องกัน HTML หรือข้อความอื่นๆ แทรกเข้าไป
    ob_clean();
    echo json_encode($response);
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการใบแจ้งหนี้</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- เนื้อหาหลัก -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div id="alertMessage" 
                 data-status="<?php echo isset($alert['status']) ? $alert['status'] : ''; ?>" 
                 data-message="<?php echo isset($alert['message']) ? $alert['message'] : ''; ?>">
            </div>


            <!-- ตัวกรองเดือนและปีและปุ่ม -->
            <div class="flex justify-between items-end mb-6">
                <!-- ตัวกรองเดือนและปี -->
                <div class="flex space-x-4">
                    <!-- ตัวเลือกปี -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ปี</label>
                        <select name="year" id="year" onchange="filterData()" class="mt-1 block w-24 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            <?php
                            $current_year = date('Y');
                            for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
                                $selected = ($i == $selected_year) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- ตัวเลือกเดือน -->
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">เดือน</label>
                        <select name="month" id="month" onchange="filterData()" class="mt-1 block w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            <?php
                            $thai_months = [
                                '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
                                '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
                                '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
                                '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
                            ];
                            foreach ($thai_months as $key => $month) {
                                $selected = ($key == $selected_month) ? 'selected' : '';
                                echo "<option value='$key' $selected>$month</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>


            </div>

            <!-- Modal เพิ่มใบแจ้งหนี้ -->
            <div id="addInvoiceModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                <div class="bg-white dark:bg-gray-800 w-full max-w-2xl p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">เพิ่มใบแจ้งหนี้</h3>
                    <form id="invoiceForm" method="POST">
                        <input type="hidden" name="add_invoice" value="1">
                        <div class="space-y-4">
                            <!-- เลขห้อง -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เลขห้อง</label>
                                <select name="room_id" id="room_id" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required onchange="fetchStayDetails(this.value)">
                                    <option value="">เลือกห้อง</option>
                                    <?php
                                    $room_query = "SELECT room_id, room_number FROM room";
                                    $room_result = $conn->query($room_query);
                                    while ($room = $room_result->fetch_assoc()) {
                                        echo "<option value='{$room['room_id']}'>{$room['room_number']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- ชื่อผู้เช่า -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อผู้เช่า</label>
                                <input type="text" name="rec_name" id="tenant_name" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" readonly>
                            </div>

                            <!-- ประเภทห้อง -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทห้อง</label>
                                <input type="text" name="rec_room_type" id="room_type" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" readonly>
                            </div>

                            <!-- ค่าเช่าห้อง -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าเช่าห้อง</label>
                                <input type="number" name="rec_room_charge" id="room_charge" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" readonly>
                            </div>

                            <!-- ค่าไฟฟ้า -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าไฟฟ้า (หน่วย)</label>
                                <input type="number" name="rec_electricity_units" id="rec_electricity" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" placeholder="กรอกจำนวนหน่วยค่าไฟ" required oninput="calculateTotal()">
                                <small class="text-xs text-gray-500">ค่าหน่วยไฟฟ้าคิดจาก: <?php echo $rate['electricity_rate']; ?> บาท/หน่วย</small>
                            </div>

                            <!-- ค่าน้ำ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าน้ำ (หน่วย)</label>
                                <input type="number" name="rec_water_units" id="rec_water" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" placeholder="กรอกจำนวนหน่วยค่าน้ำ" required oninput="calculateTotal()">
                                <small class="text-xs text-gray-500">ค่าหน่วยน้ำคิดจาก: <?php echo $rate['water_rate']; ?> บาท/หน่วย</small>
                            </div>

                            <!-- วันที่คิดค่าเช่า -->
                            <div>
                                <label for="rec_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">วันที่คิดค่าเช่า</label>
                                <input type="text" name="rec_date" id="rec_date" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" placeholder="วว/ดด/ปปปป" required>
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    flatpickr("#rec_date", {
                                        dateFormat: "d/m/Y", 
                                    });
                                });
                            </script>

                            

                            <!-- รวมทั้งหมด -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รวมทั้งหมด</label>
                                <input type="text" name="rec_total" id="rec_total" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" value="0.00" readonly>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-4">
                            <button type="button" onclick="closeAddInvoiceModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">ยกเลิก</button>
                            <button type="button" onclick="submitInvoice()" class="px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-lg hover:bg-green-600">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex items-center mb-6">
    <h1 class="text-2xl font-semibold dark:text-gray-100">จัดการใบแจ้งหนี้</h1>
    <button onclick="openAddInvoiceModal()" class="flex items-center bg-transparent text-green-600 p-2 text-sm sm:text-base rounded-lg border border-green-500 hover:bg-green-500 hover:text-white dark:border-green-400 dark:hover:bg-green-600 dark:hover:text-white ml-4 transition-colors duration-300">
        <i class="fas fa-file-invoice mr-2"></i><i class="fas fa-plus"></i>
    </button>
</div>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full">
    <thead class="bg-gray-50 dark:bg-gray-700">
    <tr>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <i class="fas fa-file-invoice"></i> รหัสใบแจ้งหนี้
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <i class="fas fa-door-closed"></i> เลขห้อง
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
            <i class="fas fa-user"></i> ชื่อผู้เช่า
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
            <i class="fas fa-home"></i> ค่าเช่าห้อง
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
            <i class="fas fa-bolt"></i> ค่าไฟฟ้า
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
            <i class="fas fa-tint"></i> ค่าน้ำ
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <i class="fas fa-coins"></i> ยอดรวม
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <i class="fas fa-info-circle"></i> สถานะ
        </th>
        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <i class="fas fa-cog"></i> จัดการ
        </th>
    </tr>
</thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = ($row['rec_status'] === 'ชำระเงินแล้ว') ?
                        'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green' :
                        'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red';

                    echo "<tr class='text-sm text-gray-900 dark:text-gray-100'>";
                    echo "<td class='px-3 py-3 whitespace-nowrap'>{$row['rec_id']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap'>{$row['room_number']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap hidden sm:table-cell'>{$row['rec_name']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap hidden sm:table-cell'>{$row['rec_room_charge']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap hidden sm:table-cell'>{$row['rec_electricity']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap hidden sm:table-cell'>{$row['rec_water']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap'>{$row['rec_total']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap'>
                        <span class='$status_class rounded-full px-3 py-1 text-xs font-medium whitespace-nowrap'>
                            {$row['rec_status']}
                        </span>
                    </td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap'>
                        <div class='flex gap-2'>
                            <button onclick='openEditModal(" . json_encode($row) . ")' class='text-blue-500 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-500'>
                                <i class='fas fa-edit text-2xl'></i>
                            </button>
                            <button onclick='confirmDelete({$row['rec_id']})' class='text-red-500 hover:text-red-700 dark:text-red-300 dark:hover:text-red-500'>
                                <i class='fas fa-trash text-2xl'></i>
                            </button>
                            <a href='payment_detail.php?rec_id={$row['rec_id']}' class='text-purple-500 hover:text-purple-700 dark:text-purple-300 dark:hover:text-purple-500'>
                                <i class='fas fa-credit-card text-2xl'></i>
                            </a>
                            <a href='generate_invoice.php?rec_id={$row['rec_id']}' target='_blank' class='text-green-500 hover:text-green-700 dark:text-green-300 dark:hover:text-green-500'>
                                <i class='fas fa-file-invoice text-2xl'></i>
                            </a>
                        </div>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='px-4 py-4 text-center text-gray-500 dark:text-gray-300'>ไม่มีข้อมูลใบแจ้งหนี้</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
            <!-- Modal สำหรับแก้ไขข้อมูลใบแจ้งหนี้ -->
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 w-full max-w-2xl p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">แก้ไขข้อมูลใบแจ้งหนี้</h3>
        <form method="POST" action="">
            <input type="hidden" name="rec_id" id="editRecId">
            <div class="space-y-4">
                <!-- ค่าเช่าห้อง -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าเช่าห้อง</label>
                    <input type="text" name="rec_room_charge" id="editRecRoomCharge" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                </div>

                <!-- ค่าไฟฟ้า -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าไฟฟ้า</label>
                    <input type="text" name="rec_electricity" id="editRecElectricity" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                </div>

                <!-- ค่าน้ำ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ค่าน้ำ</label>
                    <input type="text" name="rec_water" id="editRecWater" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                </div>

                <!-- ยอดรวม -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ยอดรวม</label>
                    <input type="text" name="rec_total" id="editRecTotal" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                </div>

                <!-- วันที่ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">วันที่</label>
                        <input type="text" name="rec_date" id="editRecDate" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            flatpickr("#editRecDate", {
                                dateFormat: "d/m/Y", 
                                defaultDate: "today", 
                            });
                        });
                    </script>


                <!-- สถานะ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">สถานะ</label>
                    <select name="rec_status" id="editRecStatus" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                        <option value="ค้างชำระ">ค้างชำระ</option>
                        <option value="ชำระเงินแล้ว">ชำระเงินแล้ว</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">ยกเลิก</button>
                <button type="submit" name="update_receipt" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
            </div>
        </form>
    </div>
</div>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                function showAlert() {
                    const alertElement = document.getElementById('alertMessage');
                    const status = alertElement.getAttribute('data-status');
                    const message = alertElement.getAttribute('data-message');

                    if (status && message) {
                        Swal.fire({
                            title: status === 'success' ? 'สำเร็จ!' : 'เกิดข้อผิดพลาด!',
                            text: message,
                            icon: status,
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            if (status === 'success') {
                                window.location.href = 'manage_invoice.php';
                            }
                        });
                    }
                }

                document.addEventListener('DOMContentLoaded', showAlert);

                function openEditModal(receipt) {
                    // เตรียมข้อมูลจาก receipt object
                    document.getElementById('editRecId').value = receipt.rec_id;
                    document.getElementById('editRecRoomCharge').value = receipt.rec_room_charge;
                    document.getElementById('editRecElectricity').value = receipt.rec_electricity;
                    document.getElementById('editRecWater').value = receipt.rec_water;
                    document.getElementById('editRecTotal').value = receipt.rec_total;
                    document.getElementById('editRecStatus').value = receipt.rec_status;

                    // แปลงวันที่ให้อยู่ในรูปแบบ YYYY-MM-DD (รูปแบบที่ input type="date" ต้องการ)
                    var recDate = new Date(receipt.rec_date);
                    var formattedDate = recDate.toISOString().split('T')[0];
                    document.getElementById('editRecDate').value = formattedDate;

                    // แสดง Modal
                    document.getElementById('editModal').classList.remove('hidden');
                }

                function formatDateToDMY(dateString) {
                    var date = new Date(dateString);
                    var day = date.getDate();
                    var month = date.getMonth() + 1; // เดือนเริ่มจาก 0
                    var year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                }

                function formatDateToYMD(dateString) {
                    var parts = dateString.split('/');
                    var day = parts[0];
                    var month = parts[1];
                    var year = parts[2];
                    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}
                function closeEditModal() {
                    document.getElementById('editModal').classList.add('hidden');
                }

                function confirmDelete(recId) {
                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `manage_invoice.php?delete_rec_id=${recId}`;
                        }
                    });
                }

                // ฟังก์ชันกรองข้อมูลอัตโนมัติ
                function filterData() {
                    const year = document.getElementById('year').value;
                    const month = document.getElementById('month').value;
                    window.location.href = `manage_invoice.php?year=${year}&month=${month}`;
                }

                // ฟังก์ชันเปิด/ปิด Modal เพิ่มใบแจ้งหนี้
                function openAddInvoiceModal() {
                    document.getElementById('addInvoiceModal').classList.remove('hidden');
                }

                function closeAddInvoiceModal() {
                    document.getElementById('addInvoiceModal').classList.add('hidden');
                }

                // ฟังก์ชันดึงข้อมูลผู้เช่าและห้อง
                function fetchStayDetails(roomId) {
                    if (roomId) {
                        fetch(`fetch_stay_details.php?room_id=${roomId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    alert(data.error); // แสดงข้อความผิดพลาด
                                } else {
                                    // แสดงข้อมูลในฟอร์ม
                                    document.getElementById('tenant_name').value = data.mem_name || 'ไม่มีข้อมูล';
                                    document.getElementById('room_charge').value = data.room_price || 0;
                                    document.getElementById('room_type').value = data.room_type || 'ไม่มีข้อมูล';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
                            });
                    }
                }

    function calculateTotal() {
    var roomChargeElement = document.getElementById('room_charge');
    var electricityElement = document.getElementById('rec_electricity');
    var waterElement = document.getElementById('rec_water');
    var totalElement = document.getElementById('rec_total');

    if (!roomChargeElement || !electricityElement || !waterElement || !totalElement) {
        console.error('Element ไม่พบในหน้าเว็บ');
        return;
    }

    var roomPrice = parseFloat(roomChargeElement.value) || 0;
    var electricityUnit = parseFloat(electricityElement.value) || 0;
    var waterUnit = parseFloat(waterElement.value) || 0;

    var electricityRate = <?php echo $rate['electricity_rate']; ?>;
    var waterRate = <?php echo $rate['water_rate']; ?>;

    var electricityCharge = electricityUnit * electricityRate;
    var waterCharge = waterUnit * waterRate;
    var total = roomPrice + electricityCharge + waterCharge;

    console.log("ค่าไฟฟ้า:", electricityUnit, "x", electricityRate, "=", electricityCharge);
    console.log("ค่าน้ำ:", waterUnit, "x", waterRate, "=", waterCharge);
    console.log("รวมทั้งหมด:", total);

    totalElement.value = total.toFixed(2);
}


function submitInvoice() {
    calculateTotal();

    var form = document.getElementById('invoiceForm');
    var formData = new FormData(form);

    var electricityElement = document.getElementById('rec_electricity');
    var waterElement = document.getElementById('rec_water');

    if (!electricityElement.value || !waterElement.value) {
        Swal.fire({
            title: 'เกิดข้อผิดพลาด',
            text: 'กรุณากรอกค่าไฟและค่าน้ำ',
            icon: 'error',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#d33', 
            width: '300px',
            customClass: {
                confirmButton: 'btn btn-danger btn-sm',
            }
        });
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    Swal.fire({
                        title: 'สำเร็จ',
                        text: 'ข้อมูลถูกบันทึกสำเร็จ',
                        icon: 'success',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#3085d6', 
                        width: '300px',
                        customClass: {
                            confirmButton: 'btn btn-success btn-sm', 
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            closeAddInvoiceModal();
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + response.message,
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#d33',
                        width: '300px',
                        customClass: {
                            confirmButton: 'btn btn-danger btn-sm',
                        }
                    });
                }
            } catch (e) {
                console.error('ไม่สามารถแปลงข้อมูลเป็น JSON ได้:', e);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถแปลงข้อมูลเป็น JSON ได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#d33', 
                    width: '300px',
                    customClass: {
                        confirmButton: 'btn btn-danger btn-sm', 
                    }
                });
            }
        } else {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์',
                icon: 'error',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#d33', 
                width: '300px',
                customClass: {
                    confirmButton: 'btn btn-danger btn-sm',
                }
            });
        }
    };

    xhr.send(formData);
}

document.addEventListener('DOMContentLoaded', function () {
    calculateTotal(); 
});
            </script>
        </div>
    </div>
</body>
</html>