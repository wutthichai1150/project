<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบการเริ่ม session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // ดึงข้อมูลห้องจากฐานข้อมูล
    $query = "SELECT * FROM room WHERE room_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $room_id);
    if (!$stmt->execute()) {
        echo "เกิดข้อผิดพลาดในการ execute คำสั่ง SQL: " . $stmt->error;
        exit();
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
    } else {
        echo "ไม่พบห้องที่ต้องการแก้ไข";
        exit();
    }
} else {
    echo "ห้องที่ต้องการแก้ไขไม่ถูกต้อง";
    exit();
}

// ตรวจสอบค่าที่ส่งจากฟอร์ม
if (isset($_POST['update_room'])) {
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];

    // ตรวจสอบว่าได้เลือกค่า room_status หรือไม่
    if (empty($room_number) || empty($room_type) || empty($room_price) || empty($room_status)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    } else {
        // อัพเดตข้อมูลห้องในฐานข้อมูล
        $update_query = "UPDATE room SET room_number = ?, room_type = ?, room_price = ?, room_status = ? WHERE room_id = ?";
        $stmt = $conn->prepare($update_query);
        if ($stmt) {
            $stmt->bind_param("ssssi", $room_number, $room_type, $room_price, $room_status, $room_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "ข้อมูลห้องถูกอัพเดตเรียบร้อยแล้ว!";
                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "เกิดข้อผิดพลาดในการอัพเดตข้อมูล: " . $stmt->error;
            }
        } else {
            echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        }
    }
}

if (isset($_POST['add_stay'])) {
    $mem_id = $_POST['mem_id'];
    $stay_start_date = $_POST['stay_start_date'];
    $stay_deposit = isset($_POST['stay_deposit']) && $_POST['stay_deposit'] !== "" ? $_POST['stay_deposit'] : NULL;

    // ตรวจสอบและแปลงวันที่จาก dd/mm/yyyy เป็น yyyy-mm-dd
    $date_parts = explode('/', $stay_start_date); // แยกวัน, เดือน, ปี
    if (count($date_parts) == 3) {
        $stay_start_date = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0]; // สลับเป็น yyyy-mm-dd
    } else {
        echo "<script>alert('รูปแบบวันที่ไม่ถูกต้อง');</script>";
        exit();
    }

    // ตรวจสอบให้แน่ใจว่าไม่มีข้อมูลที่จำเป็นหายไป
    if (empty($mem_id) || empty($stay_start_date)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    } else {
        // SQL สำหรับการบันทึกข้อมูล (ให้ stay_deposit รับค่า NULL ได้)
        $insert_query = "INSERT INTO stay (mem_id, room_id, stay_start_date, stay_deposit) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt) {
            $stmt->bind_param("iisd", $mem_id, $room_id, $stay_start_date, $stay_deposit);

            if ($stmt->execute()) {
                echo "<script>alert('บันทึกข้อมูลการเข้าพักเรียบร้อย!'); window.location.href='manage_room.php?room_id=$room_id';</script>";
            } else {
                echo "เกิดข้อผิดพลาด: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        }
    }
}


// ถ้าผู้ใช้ส่งข้อมูลฟอร์มสำหรับการอัพเดต
if (isset($_POST['update_stay'])) {
    $stay_id = $_POST['stay_id'];
    $mem_id = $_POST['mem_id'];
    $stay_start_date = $_POST['stay_start_date'];
    $stay_deposit = isset($_POST['stay_deposit']) && $_POST['stay_deposit'] !== "" ? $_POST['stay_deposit'] : NULL; // ถ้าไม่กรอก ให้เป็น NULL

    // แปลงวันที่เป็นรูปแบบที่ฐานข้อมูลรองรับ (YYYY-MM-DD)
    // ใช้ฟังก์ชัน DateTime เพื่อให้การแปลงวันที่ทำงานได้อย่างถูกต้อง
    $stay_start_date = DateTime::createFromFormat('d/m/Y', $stay_start_date)->format('Y-m-d');

    // อัปเดตข้อมูล (ให้ stay_deposit เป็น NULL ได้)
    $update_query = "UPDATE stay SET mem_id = ?, stay_start_date = ?, stay_deposit = ? WHERE stay_id = ?";

    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("isdi", $mem_id, $stay_start_date, $stay_deposit, $stay_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('อัพเดตข้อมูลการเข้าพักสำเร็จ'); window.location.href = 'manage_room.php?room_id=" . $room_id . "';</script>";
        } else {
            echo "<script>alert('ไม่มีการเปลี่ยนแปลงข้อมูล');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการอัพเดตข้อมูล');</script>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_invoice'])) {
    $room_id = $_POST['room_id'];
    $rec_room_charge = $_POST['rec_room_charge'];
    $rec_electricity = $_POST['rec_electricity'];
    $rec_water = $_POST['rec_water'];
    $rec_total = $_POST['rec_total'];
    $rec_status = $_POST['rec_status'];

    // คำสั่ง SQL สำหรับเพิ่มข้อมูล
    $sql = "INSERT INTO invoice_receipt (room_id, rec_room_charge, rec_electricity, rec_water, rec_total, rec_status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }
    $stmt->bind_param("idddds", $room_id, $rec_room_charge, $rec_electricity, $rec_water, $rec_total, $rec_status);

    if ($stmt->execute()) {
        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'เพิ่มใบแจ้งหนี้เรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['alert'] = [
            'status' => 'error',
            'message' => 'ไม่สามารถเพิ่มใบแจ้งหนี้ได้: ' . $stmt->error
        ];
    }
    header("Location: manage_invoice.php"); // Redirect กลับไปที่หน้าเดิม
    exit();
}

$query_rate = "SELECT electricity_rate, water_rate FROM rate WHERE id = 1";
$stmt_rate = $conn->prepare($query_rate);
if ($stmt_rate) {
    $stmt_rate->execute();
    $rate_result = $stmt_rate->get_result();
    $rate = $rate_result->fetch_assoc();
} else {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูลอัตราค่าไฟและค่าน้ำ";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการห้องพัก</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="h-full overflow-y-auto">
            <div class="container px-6 mx-auto grid">
                <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">จัดการห้องพัก: ห้อง <?= $room['room_number'] ?></h2>

                <!-- การ์ดแสดงข้อมูลห้องพัก -->
                <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
                    <!-- การ์ดเลขห้อง -->
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">เลขห้อง</p>
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= $room['room_number'] ?></p>
                        </div>
                    </div>

                    <!-- การ์ดประเภทห้อง -->
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                            <i class="fas fa-home"></i>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">ประเภทห้อง</p>
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= $room['room_type'] ?></p>
                        </div>
                    </div>

                    <!-- การ์ดราคา -->
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">ราคา</p>
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= number_format($room['room_price']) ?> บาท</p>
                        </div>
                    </div>

                    <!-- การ์ดสถานะห้อง -->
                    <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                        <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">สถานะห้อง</p>
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200"><?= $room['room_status'] ?></p>
                        </div>
                    </div>
                </div>
        <!-- ข้อมูลการเข้าพัก -->
<hr class="my-6 border-gray-300">
<div class="flex justify-center items-center mb-6">
    <h3 class="text-2xl font-semibold mb-6 dark:text-gray-100">ข้อมูลการเข้าพัก</h3>
    <button onclick="openAddStayModal()" class="ml-2 p-2 text-blue-500 hover:text-blue-600">
        <i class="fas fa-user-plus"></i>
    </button>
</div>
<!-- Modal สำหรับเพิ่มข้อมูลการเข้าพัก -->
<div id="addStayModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">เพิ่มข้อมูลการเข้าพัก</h3>
        <form method="POST" action="manage_room.php?room_id=<?php echo $room_id; ?>">
            <!-- ฟิลด์เลือกผู้เช่า -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">เลือกผู้เช่า</label>
                <select name="mem_id" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" required>
                    <?php
                    // ดึงข้อมูลสมาชิกทั้งหมดจากฐานข้อมูล
                    $members_query = "SELECT mem_id, mem_fname, mem_lname FROM `member`";
                    $members_result = $conn->query($members_query);
                    while ($member = $members_result->fetch_assoc()) {
                        echo "<option value='{$member['mem_id']}'>{$member['mem_fname']} {$member['mem_lname']}</option>";
                    }
                    ?>
                </select>
            </div>

           <!-- วันที่เข้า -->
                <div class="mb-4">
                    <label for="stay_start_date" class="block text-sm font-medium text-gray-700">วันที่เข้า</label>
                    <input type="text" name="stay_start_date" id="stay_start_date" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" required>
                </div>


                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        flatpickr("#stay_start_date", {
                            dateFormat: "d/m/Y", // แสดงวันที่ในรูปแบบ DD/MM/YYYY
                            defaultDate: "today", // วันที่เริ่มต้นคือวันที่ปัจจุบัน
                        });
                    });
                </script>


            <!-- ค่ามัดจำ (ไม่บังคับกรอก) -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">ค่ามัดจำ (ถ้ามี)</label>
                <input type="number" name="stay_deposit" step="0.01" min="0" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" placeholder="ระบุจำนวนเงิน (ถ้ามี)">
            </div>

            <!-- ปุ่ม Submit และ Cancel -->
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeAddStayModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">ยกเลิก</button>
                <button type="submit" name="add_stay" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
            </div>
        </form>
    </div>
</div>


<div class="container mx-auto bg-white rounded-lg shadow-md p-6 max-w-4xl">
<?php 
// รับค่า room_id จาก URL
$room_id = $_GET['room_id'];

// ดึงข้อมูลการเข้าพักล่าสุดของห้องนั้น
$stay_query = "
    SELECT s.stay_id, s.room_id, s.stay_start_date, s.stay_end_date, 
           s.stay_deposit, m.mem_fname, m.mem_lname, m.mem_phone, m.mem_address,
           r.room_number
    FROM stay s
    JOIN `member` m ON s.mem_id = m.mem_id
    JOIN room r ON s.room_id = r.room_id
    WHERE s.room_id = ?
    ORDER BY s.stay_start_date DESC
    LIMIT 1
";

$stmt = $conn->prepare($stay_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$stay_result = $stmt->get_result();

if ($stay_result->num_rows > 0) {
    $stay = $stay_result->fetch_assoc();
    

// เป็นเงื่อนไขใหม่ที่เช็คว่ามีวันที่ออก
if (!empty($stay['stay_end_date']) && $stay['stay_end_date'] != '0000-00-00') {
    echo "<p class='text-red-500 font-semibold'>ไม่มีการเข้าพักสำหรับห้องนี้</p>";
} else {
?>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">ห้องหมายเลข <?php echo $stay['room_number']; ?></h1>
   
    <h2 class="text-xl font-semibold text-gray-700 mb-4">ข้อมูลผู้เข้าพัก</h2>
        
    <div class="bg-gray-50 p-4 rounded-lg">
        <p class="text-gray-600">ชื่อผู้เข้าพัก:</p>
        <p class="text-gray-800 font-medium"><?php echo $stay['mem_fname'] . " " . $stay['mem_lname']; ?></p>
        
        <p class="text-gray-600 mt-2">เบอร์โทรศัพท์:</p>
        <p class="text-gray-800 font-medium"><?php echo $stay['mem_phone']; ?></p>
        
        <p class="text-gray-600 mt-2">ที่อยู่:</p>
        <p class="text-gray-800 font-medium"><?php echo $stay['mem_address']; ?></p>
        
        <p class="text-gray-600 mt-2">วันที่เข้า:</p>
        <p class="text-gray-800 font-medium">
            <?php 
                $stay_start_date = DateTime::createFromFormat('Y-m-d', $stay['stay_start_date']);
                echo $stay_start_date ? $stay_start_date->format('d/m/Y') : $stay['stay_start_date'];
            ?>
        </p>
        <!-- ค่ามัดจำ -->
        <p class="text-gray-600 mt-2">ค่ามัดจำ:</p>
        <p class="text-gray-800 font-medium">
            <?php echo (!empty($stay['stay_deposit'])) ? number_format($stay['stay_deposit'], 2) . " บาท" : "<span class='text-gray-500'>ไม่มีค่ามัดจำ</span>"; ?>
        </p>
    </div>

    <!-- ปุ่มดำเนินการ -->
    <div class="flex justify-left space-x-4 mt-4">
        <button onclick="openEditStayModal(<?php echo $stay['stay_id']; ?>)" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">
            <i class="fas fa-pencil-alt"></i> แก้ไข
        </button>
    </div>
<?php
    }
} else {
    // หากไม่พบการเข้าพักในห้องนี้เลย
    echo "<p class='text-red-500 font-semibold'>ไม่มีการเข้าพักสำหรับห้องนี้</p>";
}
?>
</div>

<!-- Modal สำหรับแก้ไขข้อมูลการเข้าพัก -->
<div id="editStayModal<?php echo $stay['stay_id']; ?>" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">แก้ไขข้อมูลการเข้าพัก</h3>
        <form method="POST" action="manage_room.php?room_id=<?php echo $room_id; ?>">
            <!-- เพิ่มฟิลด์ stay_id -->
            <input type="hidden" name="stay_id" value="<?php echo $stay['stay_id']; ?>">
            
            <!-- ฟิลด์เลือกผู้เช่า -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">เลือกผู้เช่า</label>
                <select name="mem_id" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" required>
                    <?php
                        $members_query = "SELECT mem_id, mem_fname, mem_lname FROM `member`";
                        $members_result = $conn->query($members_query);
                        while ($member = $members_result->fetch_assoc()) {
                            $selected = ($member['mem_id'] == $stay['mem_id']) ? 'selected' : '';
                            echo "<option value='{$member['mem_id']}' {$selected}>{$member['mem_fname']} {$member['mem_lname']}</option>";
                        }
                    ?>
                </select>
            </div>

           <!-- วันที่เข้า -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">วันที่เข้า</label>
                        <input type="text" name="stay_start_date" id="stay_start_date" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" value="<?php echo date('d/m/Y', strtotime($stay['stay_start_date'])); ?>" required>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            flatpickr("#stay_start_date", {
                                dateFormat: "d/m/Y", // แสดงวันที่ในรูปแบบ DD/MM/YYYY
                                defaultDate: "<?php echo date('d/m/Y', strtotime($stay['stay_start_date'])); ?>", // วันที่เริ่มต้นคือวันที่จากฐานข้อมูล
                            });
                        });
                    </script>

            <!-- ค่ามัดจำ -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">ค่ามัดจำ</label>
                <input type="number" step="0.01" name="stay_deposit" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md"
                    value="<?php echo isset($stay['stay_deposit']) ? $stay['stay_deposit'] : ''; ?>" placeholder="กรอกค่ามัดจำ (ถ้ามี)">
            </div>

            <!-- ปุ่ม Submit และ Cancel -->
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeEditStayModal(<?php echo $stay['stay_id']; ?>)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">ยกเลิก</button>
                <button type="submit" name="update_stay" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
            </div>
        </form>
    </div>
</div>

</form>
    </div>
</div>
<!-- JavaScript สำหรับควบคุม Modal -->
<script>
    function openRentModal() {
        document.getElementById('rentModal').classList.remove('hidden');
    }

    function closeRentModal() {
        document.getElementById('rentModal').classList.add('hidden');
    }

    function calculateTotal() {
        var roomPrice = parseFloat(document.getElementById('room_price').value) || 0;
        var electricityUnit = parseFloat(document.getElementById('rec_electricity').value) || 0;
        var waterUnit = parseFloat(document.getElementById('rec_water').value) || 0;

        // รับค่าค่าไฟฟ้าและค่าน้ำจาก PHP
        var electricityRate = <?php echo $rate['electricity_rate']; ?>; // อัตราค่าไฟฟ้าจากฐานข้อมูล
        var waterRate = <?php echo $rate['water_rate']; ?>; // อัตราค่าน้ำจากฐานข้อมูล

        // คำนวณค่าไฟฟ้าและค่าน้ำ
        var electricityCharge = electricityUnit * electricityRate;
        var waterCharge = waterUnit * waterRate;

        // คำนวณยอดรวม
        var total = roomPrice + electricityCharge + waterCharge;

        // แสดงผลในช่อง "รวมทั้งหมด"
        document.getElementById('rec_total').value = total.toFixed(2); // แสดงผลรวมทั้งหมดในฟอร์ม

        // เก็บค่าที่คำนวณใน input ที่ซ่อน
        document.getElementById('rec_electricity_charge').value = electricityCharge.toFixed(2);
        document.getElementById('rec_water_charge').value = waterCharge.toFixed(2);
    }

    function submitInvoice() {
        // คำนวณยอดรวมก่อน
        calculateTotal();

        // หา form
        var form = document.getElementById('invoiceForm');
        var formData = new FormData(form);

        // เพิ่ม rec_status ใน FormData (ค่าที่ส่งไปในฟอร์ม)
        formData.append('rec_status', 'รอชำระ'); // ส่งค่า 'รอชำระ' ไป

        // ส่งข้อมูลผ่าน AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "invoice_receipt_form.php", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText); // แปลงข้อมูล JSON ที่ได้รับจากเซิร์ฟเวอร์
                    if (response.success) {
                        Swal.fire({
                            title: 'สำเร็จ',
                            text: 'ข้อมูลถูกบันทึกสำเร็จ',
                            icon: 'success',
                            confirmButtonText: 'ตกลง',
                            width: '300px',  // ลดขนาดหน้าต่าง
                            padding: '1em', // ปรับ padding
                            fontSize: '14px',  // ปรับขนาดฟอนต์
                            buttonsStyling: false,  // ปิดการสไตล์ปุ่มที่มีค่าเริ่มต้น
                            customClass: {
                                confirmButton: 'btn btn-success btn-sm',  // ปรับขนาดของปุ่ม
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                closeRentModal();
                                window.location.reload(); // รีเฟรชหน้าเว็บหลังจากบันทึกข้อมูล
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + response.message,
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                            width: '300px',
                            padding: '1em',
                            fontSize: '14px',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-danger btn-sm',
                            }
                        });
                    }
                } catch (e) {
                    console.error('ไม่สามารถแปลงข้อมูลเป็น JSON ได้:', e);
                    console.error('ข้อมูลที่ได้รับจากเซิร์ฟเวอร์:', xhr.responseText);
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถแปลงข้อมูลเป็น JSON ได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                        width: '300px',
                        padding: '1em',
                        fontSize: '14px',
                        buttonsStyling: false,
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
                    width: '300px',
                    padding: '1em',
                    fontSize: '14px',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger btn-sm',
                    }
                });
            }
        };

        xhr.send(formData); // ส่งข้อมูลฟอร์ม
    }
    function openAddStayModal() {
    document.getElementById('addStayModal').classList.remove('hidden');
}

function closeAddStayModal() {
    document.getElementById('addStayModal').classList.add('hidden');
}
</script>
    </div>
    
    <!-- JavaScript สำหรับควบคุม Modal -->
    <script>
        function openModal() {
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }

        function submitBooking() {
            const memberName = document.getElementById('member_name').value;
            const checkInDate = document.getElementById('check_in_date').value;

            if (!memberName || !checkInDate) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                return;
            }

            alert(`บันทึกข้อมูลสำเร็จ!\nชื่อผู้เข้าพัก: ${memberName}\nวันที่เข้า: ${checkInDate}`);
            closeModal();
        }
        function openEditStayModal(stayId) {
    // เปิด Modal สำหรับแก้ไขข้อมูลการเข้าพัก
    document.getElementById('editStayModal' + stayId).classList.remove('hidden');
}

function closeEditStayModal(stayId) {
    // ปิด Modal สำหรับแก้ไขข้อมูลการเข้าพัก
    document.getElementById('editStayModal' + stayId).classList.add('hidden');
}
    </script>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</body>

</html>