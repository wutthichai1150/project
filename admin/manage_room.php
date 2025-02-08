<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบการเริ่ม session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../includes/navbar_admin.php');

// ดึงข้อมูลอัตราค่าไฟและค่าน้ำ
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

// ถ้าผู้ใช้ส่งข้อมูลฟอร์มสำหรับการเข้าพัก
if (isset($_POST['add_stay'])) {
    $mem_id = $_POST['mem_id'];
    $stay_start_date = $_POST['stay_start_date'];

    if (empty($mem_id) || empty($stay_start_date)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    } else {
        // SQL สำหรับการบันทึกข้อมูลการเข้าพัก
        $insert_query = "INSERT INTO stay (mem_id, room_id, stay_start_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt) {
            $stmt->bind_param("iis", $mem_id, $room_id, $stay_start_date);

            if ($stmt->execute()) {
                echo "<script>alert('บันทึกข้อมูลการเข้าพักเรียบร้อย!'); window.location.href='manage_room.php?room_id=$room_id';</script>";
            } else {
                echo "เกิดข้อผิดพลาด: " . $stmt->error;
            }
        } else {
            echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL สำหรับการบันทึกข้อมูล";
        }
    }
}
if (isset($_POST['update_stay'])) {
    // รับข้อมูลจากฟอร์ม
    $stay_id = $_POST['stay_id'];
    $mem_id = $_POST['mem_id'];
    $stay_start_date = $_POST['stay_start_date'];

    // เตรียมคำสั่ง SQL เพื่ออัพเดตข้อมูล
    $update_query = "UPDATE stay SET mem_id = ?, stay_start_date = ? WHERE stay_id = ?";

    // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("isi", $mem_id, $stay_start_date, $stay_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // การอัพเดตสำเร็จ
            echo "<script>alert('อัพเดตข้อมูลการเข้าพักสำเร็จ'); window.location.href = 'manage_room.php?room_id=" . $room_id . "';</script>";
        } else {
            // ถ้าไม่มีการเปลี่ยนแปลง
            echo "<script>alert('ไม่มีการเปลี่ยนแปลงข้อมูล');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการอัพเดตข้อมูล');</script>";
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Edit Room</title>
</head>
<body>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title mb-4">ข้อมูลห้องพัก</h2>

            <!-- แสดงข้อมูลห้อง -->
            <div class="mb-3 d-flex align-items-center">
                <i class="bi bi-door-open me-2"></i> 
                <strong>เลขห้อง:</strong>
                <p class="ms-3 mb-0"><?php echo $room['room_number']; ?></p>
            </div>
            <div class="mb-3 d-flex align-items-center">
                <i class="bi bi-house-door me-2"></i>
                <strong>ประเภทห้อง:</strong>
                <p class="ms-3 mb-0"><?php echo $room['room_type']; ?></p>
            </div>
            <div class="mb-3 d-flex align-items-center">
                <i class="bi bi-cash-coin me-2"></i> 
                <strong>ราคา:</strong>
                <p class="ms-3 mb-0"><?php echo $room['room_price']; ?></p>
            </div>
            <div class="mb-3 d-flex align-items-center">
                <i class="bi bi-circle-fill me-2"></i> 
                <strong>สถานะห้อง:</strong>
                <p class="ms-3 mb-0"><?php echo $room['room_status']; ?></p>
            </div>

            <div class="d-flex justify-content-start">
            <a href="stay_history.php?room_id=<?php echo $room['room_id']; ?>" class="btn btn-primary">
                รายการเข้าพัก
            </a>
        </div>
        </div>
    </div>
</div>



  <!-- Modal แก้ไข -->
<div class="modal fade" id="editroom<?php echo $room['room_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $room['room_id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- ฟอร์มแก้ไขข้อมูลห้อง -->
                <form id="editRoomForm<?php echo $room['room_id']; ?>" method="POST" onsubmit="event.preventDefault(); updateRoom(<?php echo $room['room_id']; ?>);">
                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">

                    <div class="mb-3">
                        <label for="room_number" class="form-label">เลขห้อง</label>
                        <input type="text" class="form-control" id="room_number" name="room_number" value="<?php echo $room['room_number']; ?>" required>
                    </div>
                    
                    <!-- ฟิลด์ประเภทห้อง (ใช้ select) -->
                    <div class="mb-3">
                        <label for="room_type" class="form-label">ประเภทห้อง</label>
                        <select class="form-select" id="room_type" name="room_type" required>
                            <option value="แอร์" <?php echo ($room['room_type'] == 'แอร์') ? 'selected' : ''; ?>>แอร์</option>
                            <option value="พัดลม" <?php echo ($room['room_type'] == 'พัดลม') ? 'selected' : ''; ?>>พัดลม</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="room_price" class="form-label">ราคา</label>
                        <input type="number" class="form-control" id="room_price" name="room_price" value="<?php echo $room['room_price']; ?>" required>
                    </div>
                    
                    <!-- ฟิลด์สถานะห้อง (ใช้ select) -->
                    <div class="mb-3">
                        <label for="room_status" class="form-label">สถานะห้อง</label>
                        <select class="form-select" id="room_status" name="room_status" required>
                            <option value="ว่าง" <?php echo ($room['room_status'] == 'ว่าง') ? 'selected' : ''; ?>>ว่าง</option>
                            <option value="มีผู้เช่า" <?php echo ($room['room_status'] == 'มีผู้เช่า') ? 'selected' : ''; ?>>มีผู้เช่า</option>
                            <option value="ซ่อมแซม" <?php echo ($room['room_status'] == 'ซ่อมแซม') ? 'selected' : ''; ?>>ซ่อมแซม</option>
                        </select>
                    </div>
                    
                    <!-- ปุ่มบันทึก -->
                    <button type="submit" class="btn btn-warning">บันทึกการเปลี่ยนแปลง</button>
                    <!-- ปุ่มลบ -->
                    <button type="button" class="btn btn-danger" onclick="deleteRoom(<?php echo $room['room_id']; ?>)">ลบข้อมูลห้อง</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
function updateRoom(roomId) {
    var form = document.getElementById('editRoomForm' + roomId);
    var formData = new FormData(form);

    // ส่งข้อมูลด้วย AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_room.php", true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // เมื่อบันทึกสำเร็จ ปิด modal
            $('#editroom' + roomId).modal('hide');  // ปิด modal หลังจากอัปเดตสำเร็จ

            // รีเฟรช backdrop
            $('.modal-backdrop').remove();  // ลบ backdrop ที่ค้างอยู่
            $('body').removeClass('modal-open');  // รีเซ็ต class modal-open

            // อัปเดตข้อมูลที่แสดงในหน้าโดยไม่ต้องโหลดใหม่
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // ใช้ SweetAlert แทน alert
                Swal.fire({
                    title: 'ข้อมูลห้องถูกอัปเดตแล้ว!',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // รีเฟรชหน้าใหม่หลังจากอัปเดตข้อมูล
                        location.reload();  // รีเฟรชหน้าเว็บทันที
                    }
                });
            } else {
                // ใช้ SweetAlert แทน alert
                Swal.fire({
                    title: 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล!',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        } else {
            // ใช้ SweetAlert แทน alert
            Swal.fire({
                title: 'เกิดข้อผิดพลาดในการส่งข้อมูล!',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    };
    xhr.send(formData);
}
function deleteRoom(roomId) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่ที่จะลบห้องนี้?',
        text: "การลบข้อมูลไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_room.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      
            xhr.onload = function () {
                if (xhr.status === 200) {
                 
                    $('#editroom' + roomId).modal('hide');

                 
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');

                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                       
                        Swal.fire({
                            title: 'ห้องถูกลบแล้ว!',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            
                            window.location.href = 'admin_dashboard.php';
                        });
                    } else {
                        // ใช้ SweetAlert แทน alert
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาดในการลบข้อมูล!',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                } else {
                    // ใช้ SweetAlert แทน alert
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาดในการส่งข้อมูล!',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            };

            // ส่งข้อมูลห้องที่ต้องการลบ
            xhr.send("room_id=" + roomId);
        }
    });
}

</script>






 <!-- ข้อมูลการเข้าพัก -->
<hr>
<h3 class="d-flex justify-content-center align-items-center">
    ข้อมูลการเข้าพัก
    <button class="btn btn-transparent ms-2" data-bs-toggle="modal" data-bs-target="#addStayModal">
        <i class="bi bi-plus-circle"></i> <i class="bi bi-person-plus"></i>
    </button>
</h3>

<div class="row d-flex justify-content-center">
<?php 
// รับค่า room_id จาก URL
$room_id = $_GET['room_id'];

// ดึงข้อมูลการเข้าพักล่าสุดของห้องนั้น
$stay_query = "
    SELECT s.stay_id, s.room_id, s.stay_start_date, s.stay_end_date, 
           m.mem_fname, m.mem_lname, m.mem_phone, m.mem_address,
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

if ($stay_result->num_rows > 0):
    $stay = $stay_result->fetch_assoc();
?>
        <div class="col-md-4">
            <div class="card mb-2">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-person-fill"></i> <?php echo "ชื่อ: " . $stay['mem_fname'] . " " . $stay['mem_lname']; ?>
                    </h5>
                    <p class="card-text">
                        <i class="bi bi-house-door"></i> <strong>ห้อง:</strong> <?php echo $stay['room_number']; ?><br>
                        <i class="bi bi-calendar-event"></i> <strong>วันที่เข้า:</strong> <?php echo $stay['stay_start_date']; ?><br>
                        <i class="bi bi-telephone"></i> <strong>เบอร์โทร:</strong> <?php echo $stay['mem_phone']; ?><br>
                        <i class="bi bi-house-door"></i> <strong>ที่อยู่:</strong> <?php echo $stay['mem_address']; ?><br>
                    </p>
                    <div class="btn-group">
                        <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editStayModal<?php echo $stay['stay_id']; ?>">
                            <i class="bi bi-pencil"></i> แก้ไข
                        </button>
                        <button class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#rentModal<?php echo $stay['stay_id']; ?>">
                            <i class="bi bi-cash"></i> คิดค่าเช่า
                        </button>
                    </div>
                </div>
            </div>
        </div>
<?php endif; ?>
</div>




<!-- Modal คิดค่าเช่า -->
<div class="modal fade" id="rentModal<?php echo $stay['stay_id']; ?>" tabindex="-1" aria-labelledby="rentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rentModalLabel">คิดค่าเช่า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="invoiceForm" method="POST">
                    <!-- ช่องฟอร์มทั้งหมดที่คุณมี -->
                    <div class="mb-3">
                        <label for="room_id" class="form-label">เลขห้อง</label>
                        <input type="text" name="room_id" class="form-control" 
                            value="<?php echo isset($room['room_number']) ? htmlspecialchars($room['room_number']) : ''; ?>" 
                            required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="rec_room_charge" class="form-label">ค่าห้อง</label>
                        <input type="number" class="form-control" id="room_price" name="rec_room_charge" 
                            value="<?php echo isset($room['room_price']) ? $room['room_price'] : 0; ?>" 
                            required readonly>
                    </div>
                    <!-- ค่าไฟ -->
                            <div class="mb-3">
                                <label for="rec_electricity" class="form-label">ค่าไฟ (หน่วย)</label>
                                <input type="number" name="rec_electricity" class="form-control" id="rec_electricity" 
                                    oninput="calculateTotal()" placeholder="กรอกจำนวนหน่วยค่าไฟ" required>
                                <small class="form-text text-muted">ค่าหน่วยไฟฟ้าคิดจาก: <?php echo $rate['electricity_rate']; ?> บาท/หน่วย</small>
                            </div>

                            <!-- ค่าน้ำ -->
                            <div class="mb-3">
                                <label for="rec_water" class="form-label">ค่าน้ำ (หน่วย)</label>
                                <input type="number" name="rec_water" class="form-control" id="rec_water" 
                                    oninput="calculateTotal()" placeholder="กรอกจำนวนหน่วยค่าน้ำ" required>
                                <small class="form-text text-muted">ค่าหน่วยน้ำคิดจาก: <?php echo $rate['water_rate']; ?> บาท/หน่วย</small>
                            </div>

                                                        <!-- ค่าไฟ (บาท) - คำนวณแล้ว -->
                            <div class="mb-3" style="display: none;">
                                <label for="rec_electricity_charge" class="form-label">ค่าไฟ (บาท)</label>
                                <input type="hidden" name="rec_electricity" id="rec_electricity_charge" value="0.00">
                            </div>

                            <!-- ค่าน้ำ (บาท) - คำนวณแล้ว -->
                            <div class="mb-3" style="display: none;">
                                <label for="rec_water_charge" class="form-label">ค่าน้ำ (บาท)</label>
                                <input type="hidden" name="rec_water" id="rec_water_charge" value="0.00">
                            </div>


                                                <div class="mb-3">
                        <label for="rec_room_type" class="form-label">ประเภทห้อง</label>
                        <input type="text" name="rec_room_type" class="form-control" 
                            value="<?php echo isset($room['room_type']) ? htmlspecialchars($room['room_type']) : ''; ?>" 
                            required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="rec_name" class="form-label">ชื่อผู้เช่า</label>
                        <input type="text" name="rec_name" class="form-control" 
                            value="<?php echo isset($stay['mem_fname']) && isset($stay['mem_lname']) 
                                        ? htmlspecialchars($stay['mem_fname'] . ' ' . $stay['mem_lname']) 
                                        : ''; ?>" 
                            readonly>
                    </div>
                    <div class="mb-3">
                        <label for="rec_date" class="form-label">วันที่คิดค่าเช่า</label>
                        <input type="date" name="rec_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="rec_total" class="form-label">รวมทั้งหมด</label>
                        <input type="text" name="rec_total" class="form-control" id="rec_total" value="0.00" readonly>
                    </div>
                    <button type="button" class="btn btn-success" onclick="submitInvoice()">คิดค่าเช่า</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

// ฟังก์ชันคำนวณยอดรวม
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
                            $('#rentModal<?php echo $stay['stay_id']; ?>').modal('hide'); 
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


</script>


<!-- Modal สำหรับแก้ไขข้อมูลการเข้าพัก -->
<div class="modal fade" id="editStayModal<?php echo $stay['stay_id']; ?>" tabindex="-1" aria-labelledby="editStayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStayModalLabel">
                    <i class="bi bi-pencil"></i> แก้ไขข้อมูลการเข้าพัก
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="manage_room.php?room_id=<?php echo $room_id; ?>" id="editStayForm<?php echo $stay['stay_id']; ?>">
                    <!-- ฟิลด์เลือกผู้เช่า -->
                    <div class="mb-3">
                        <label for="mem_id" class="form-label"><i class="bi bi-person-circle"></i> เลือกผู้เช่า</label>
                        <select name="mem_id" class="form-control" required>
                            <option value="">เลือกผู้เช่า</option>
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
                    <div class="mb-3">
                        <label for="stay_start_date" class="form-label"><i class="bi bi-calendar-event"></i> วันที่เข้า</label>
                        <input type="date" name="stay_start_date" class="form-control" value="<?php echo $stay['stay_start_date']; ?>" required>
                    </div>
                    
                    
                    <input type="hidden" name="stay_id" value="<?php echo $stay['stay_id']; ?>">
                    <button type="submit" name="update_stay" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> อัพเดตข้อมูลการเข้าพัก
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับการเพิ่มข้อมูลการเข้าพัก -->
<div class="modal fade" id="addStayModal" tabindex="-1" aria-labelledby="addStayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStayModalLabel">
                    <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลการเข้าพัก
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="manage_room.php?room_id=<?php echo $room_id; ?>">
                    <!-- ฟิลด์เลือกผู้เช่า -->
                    <div class="mb-3">
                        <label for="mem_id" class="form-label"><i class="bi bi-person-circle"></i> เลือกผู้เช่า</label>
                        <select name="mem_id" class="form-control" required>
                            <option value="">เลือกผู้เช่า</option>
                            <?php
                                $members_query = "SELECT mem_id, mem_fname, mem_lname FROM `member`";
                                $members_result = $conn->query($members_query);
                                while ($member = $members_result->fetch_assoc()) {
                                    echo "<option value='{$member['mem_id']}'>{$member['mem_fname']} {$member['mem_lname']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    
                    <!-- วันที่เข้า -->
                    <div class="mb-3">
                        <label for="stay_start_date" class="form-label"><i class="bi bi-calendar-event"></i> วันที่เข้า</label>
                        <input type="date" name="stay_start_date" class="form-control" required>
                    </div>
                    
                    <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                    <button type="submit" name="add_stay" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> เพิ่มข้อมูลการเข้าพัก
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

include('receipt_list.php');

?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
