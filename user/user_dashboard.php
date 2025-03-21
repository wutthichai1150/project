<?php
session_start();

if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');
include('../includes/navbar_user.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT water_rate, electricity_rate FROM rate WHERE id = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rate = $result->fetch_assoc();
} else {
    $rate = ['water_rate' => '0.00', 'electricity_rate' => '0.00'];
}

$username = $_SESSION['mem_user'];
$query = "
    SELECT r.*, s.stay_start_date 
    FROM room r
    JOIN stay s ON r.room_id = s.room_id 
    WHERE s.mem_id = (SELECT mem_id FROM `member` WHERE mem_user = ?) 
    AND (s.stay_end_date IS NULL OR s.stay_end_date = '0000-00-00')
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
if ($result->num_rows > 0) {
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="../assets/css/tailwind.css"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans font-prompt">

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg mt-8">
    <div class="flex flex-col items-center p-6 rounded-lg bg-gradient-to-r from-teal-600 to-teal-600 text-white">
        <div class="relative w-28 h-28">
            <img src="../assets/image/profile.png" alt="Avatar" class="w-full h-full rounded-full border-4 border-white shadow-lg">
            <span class="absolute bottom-1 right-1 bg-green-500 w-4 h-4 rounded-full border-2 border-white"></span>
        </div>
        
        <h5 class="text-lg font-semibold mt-3">
            ผู้เข้าพัก: 
            <span class="text-white">
                <?php 
                echo isset($_SESSION['mem_fname']) ? $_SESSION['mem_fname'] : "ไม่ทราบ";
                echo " ";
                echo isset($_SESSION['mem_lname']) ? $_SESSION['mem_lname'] : "ไม่ทราบ";
                ?>
            </span>
        </h5>
        <p class="text-sm mt-1">ยินดีต้อนรับสู่ระบบของเรา! 😊</p>
    </div>

    <h3 class="text-2xl font-bold mt-8 text-gray-800">ห้องของคุณ</h3>

    <?php if (!empty($rooms)): ?>
        <?php foreach ($rooms as $room): ?>
            <?php $room_id = $room['room_id']; ?>
            <div class="bg-gray-100 p-4 rounded-lg shadow-sm">
                <p class="text-2xl font-bold">ห้องที่: <?php echo htmlspecialchars($room['room_number']); ?></p>
                <h4 class="text-2x">ราคา: <?php echo number_format($room['room_price'], 2); ?> บาท</h4>
                <p class="text-2x">ประเภทห้อง: <?php echo htmlspecialchars($room['room_type']); ?></p>
                <p class="text-2x">วันที่เข้า: 
        <?php
        $stay_start_date = DateTime::createFromFormat('Y-m-d', $room['stay_start_date']);
        echo $stay_start_date ? $stay_start_date->format('d/m/Y') : $room['stay_start_date'];
        ?>
    </p>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-center sm:space-x-4 space-y-4 sm:space-y-0 mt-6 border-b pb-4">
    <!-- ปุ่ม "ค่าเช่าเดือนนี้" -->
    <button onclick="loadBill()" 
            class="px-4 py-2 sm:px-6 sm:py-2 rounded-lg bg-blue-500 text-white font-semibold transition duration-300 
                hover:bg-blue-600 hover:shadow-lg text-sm sm:text-base">
        <i class="fas fa-file-invoice-dollar mr-2"></i>ค่าเช่าเดือนนี้
    </button>

    <!-- ปุ่ม "ประวัติการชำระเงิน" -->
    <button onclick="loadPaymentHistory()" 
            class="px-4 py-2 sm:px-6 sm:py-2 rounded-lg bg-green-500 text-white font-semibold transition duration-300 
                hover:bg-green-600 hover:shadow-lg text-sm sm:text-base">
        <i class="fas fa-history mr-2"></i>ประวัติการชำระเงิน
    </button>

    <!-- ปุ่ม "การแจ้งซ่อม" -->
    <button onclick="loadRepairRequests('<?php echo $room_id; ?>')" 
            class="px-4 py-2 sm:px-6 sm:py-2 rounded-lg bg-yellow-500 text-white font-semibold transition duration-300 
                hover:bg-yellow-600 hover:shadow-lg text-sm sm:text-base">
        <i class="fas fa-tools mr-2"></i>การแจ้งซ่อม
    </button>
</div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-600 text-center mt-6">ยังไม่มีห้องที่เชื่อมโยงกับคุณ</p>
    <?php endif; ?>

    <!-- เพิ่ม div สำหรับแสดงผลต่าง ๆ ที่นี่ -->
    <div id="bill-container" class="mt-6"></div>
    <div id="payment_history-container" class="mt-6"></div>
    <div id="repair_form-container" class="mt-6"></div>
</div> <!-- ปิด div ของ max-w-4xl -->

<script>
    // ฟังก์ชันโหลดข้อมูลค่าเช่าเดือนนี้
    function loadBill() {
        document.getElementById('payment_history-container').innerHTML = ''; // ล้าง container ประวัติการชำระเงิน
        document.getElementById('repair_form-container').innerHTML = ''; // ล้าง container แบบฟอร์มแจ้งซ่อม
        fetch('bill.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('bill-container').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
    }

    // ฟังก์ชันโหลดประวัติการชำระเงิน
    function loadPaymentHistory() {
        document.getElementById('bill-container').innerHTML = ''; // ล้าง container ค่าเช่าเดือนนี้
        document.getElementById('repair_form-container').innerHTML = ''; // ล้าง container แบบฟอร์มแจ้งซ่อม
        fetch('payment_history.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('payment_history-container').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
    }

    // ฟังก์ชันโหลดแบบฟอร์มแจ้งซ่อม
    function loadRepairRequests(room_id) {
        document.getElementById('bill-container').innerHTML = ''; // ล้าง container ค่าเช่าเดือนนี้
        document.getElementById('payment_history-container').innerHTML = ''; // ล้าง container ประวัติการชำระเงิน
        fetch(`repair_form.php?room_id=${room_id}`) // ส่ง room_id ผ่าน URL
            .then(response => response.text())
            .then(data => {
                document.getElementById('repair_form-container').innerHTML = data; // แสดงผลแบบฟอร์มแจ้งซ่อม
            })
            .catch(error => console.error('Error:', error));
    }

    // โหลดค่าเช่าเดือนนี้โดยอัตโนมัติเมื่อหน้าเว็บโหลดเสร็จ
    window.onload = loadBill;

    function loadEquipmentNames() {
        const eqmType = document.getElementById('repair_type').value;
        if (eqmType === "") {
            document.getElementById('eqm_name').innerHTML = '<option value="">เลือกชื่อครุภัณฑ์</option>';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `get_equipment_names.php?eqm_type=${eqmType}`, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('repair_eqm_name').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    
    
</script>

<script>
    function toggleDropdown(type) {
        const dropdown = document.getElementById(`dropdown-${type}`);
        dropdown.classList.toggle('active');
    }

    function handleSelection(selectedType) {
        // ซ่อน dropdown ประเภทอื่นๆ
        const categories = document.querySelectorAll('[id^="category-"]');
        categories.forEach(category => {
            if (!category.id.includes(selectedType)) {
                category.style.display = 'none';
            }
        });

        // เพิ่มปุ่มยกเลิกการเลือก (ถ้ายังไม่มี)
        const selectedCategory = document.getElementById(`category-${selectedType}`);
        let cancelButton = selectedCategory.querySelector('button.bg-red-500');

        if (!cancelButton) {
            cancelButton = document.createElement('button');
            cancelButton.textContent = 'ยกเลิกการเลือก';
            cancelButton.className = 'mt-3 bg-red-500 text-white font-bold py-1 px-3 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500';
            cancelButton.onclick = function(event) {
                event.preventDefault(); // ป้องกันการส่งฟอร์ม
                resetSelection();
            };
            selectedCategory.appendChild(cancelButton);
        }
    }

    function resetSelection() {
        // ล้างการเลือกและแสดง dropdown ทั้งหมด
        const radioButtons = document.querySelectorAll('input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.checked = false;
        });

        const categories = document.querySelectorAll('[id^="category-"]');
        categories.forEach(category => {
            category.style.display = 'block';
        });

        // ลบปุ่มยกเลิกการเลือก
        const cancelButtons = document.querySelectorAll('button.bg-red-500');
        cancelButtons.forEach(button => {
            button.remove();
        });
    }
    
</script>

</body>
</html>