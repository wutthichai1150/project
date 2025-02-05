<?php 
include('../includes/db.php');
include('../includes/navbar_admin.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">ระบบจัดการห้องพัก</h2>

        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">
                    <i class="fas fa-bed"></i> ห้อง
                    </div>
                <div class="card-body">
                    <?php
                    $roomQuery = "SELECT COUNT(*) as room_count FROM room";
                    $roomResult = $conn->query($roomQuery);
                    if ($roomResult && $roomResult->num_rows > 0) {
                        $roomData = $roomResult->fetch_assoc();
                        echo "<h5 class='card-title'>{$roomData['room_count']} ห้อง </h5>";
                    } else {
                        echo "<h5 class='card-title'>ไม่สามารถดึงข้อมูลได้</h5>";
                    }
                    ?>
                    <p class="card-text" onclick="location.href='#rooms-all'">ดูรายละเอียดห้องพัก</p>
</div>
            </div>
</div>

<!-- Card 2: สมาชิก -->
<div class="col-md-3">
    <div class="card text-white bg-warning mb-3">
        <div class="card-header">
            <i class="fas fa-users"></i> ผู้เข้าพัก
        </div>
        <div class="card-body">
            <?php
            $memberQuery = "SELECT COUNT(*) as member_count FROM `member`";
            $memberResult = $conn->query($memberQuery);
            if ($memberResult && $memberResult->num_rows > 0) {
                $memberData = $memberResult->fetch_assoc();
                echo "<h5 class='card-title'>{$memberData['member_count']} คน </h5>";
            } else {
                echo "<h5 class='card-title'>ไม่สามารถดึงข้อมูลได้</h5>";
            }
            ?>
            <p class="card-text" onclick="location.href='manage_member.php'">ดูข้อมูลผู้เข้าพัก</p>
        </div>
    </div>
</div>

<!-- Card 3: การแจ้งซ่อม -->
<div class="col-md-3">
    <div class="card text-white bg-danger mb-3">
        <div class="card-header">
            <i class="fas fa-tools"></i> การแจ้งซ่อม
        </div>
        <div class="card-body">
            <?php
            // Query to count repair requests
            $repairQuery = "SELECT COUNT(*) as repair_count FROM repair_requests";
            $repairResult = $conn->query($repairQuery);
            if ($repairResult) {
                $repairData = $repairResult->fetch_assoc();
                echo "<h5 class='card-title'>{$repairData['repair_count']} รายการ </h5>";
            } else {
                echo "<h5 class='card-title'>ไม่สามารถดึงข้อมูลได้</h5>";
            }
            ?>
            <p class="card-text" onclick="location.href='repair_management.php'">ดูการแจ้งซ่อมทั้งหมด</p>
        </div>
    </div>
</div>

<!-- Card 4: การชำระเงิน -->
<div class="col-md-3">
    <div class="card text-white bg-success mb-3">
        <div class="card-header">
            <i class="fas fa-credit-card"></i> การชำระเงินทั้งหมด
        </div>
        <div class="card-body">
            <?php
            $paymentQuery = "SELECT SUM(pay_total) as total_payment FROM payments";
            $paymentResult = $conn->query($paymentQuery);

            if ($paymentResult && $paymentResult->num_rows > 0) {
                $paymentData = $paymentResult->fetch_assoc();
                echo "<h5 class='card-title'>". number_format($paymentData['total_payment'], 2) . " bath</h5>";
            } else {
                echo "<h5 class='card-title'>ไม่สามารถดึงข้อมูลได้</h5>";
            }
            ?>
            <p class="card-text" onclick="location.href='manage_payment.php'">ดูรายการการชำระเงิน</p>
        </div>
    </div>
</div>

<?php
// ตัวแปรสำหรับเก็บจำนวนห้องว่างและมีผู้เช่า
$vacantCount = 0;
$rentedCount = 0;

// คิวรีข้อมูลห้อง
$roomQuery = "SELECT * FROM room";
$roomResult = $conn->query($roomQuery);

// ตรวจสอบว่าเรามีข้อมูลห้องหรือไม่
if ($roomResult && $roomResult->num_rows > 0) {
    // คำนวณจำนวนห้องว่างและห้องที่มีผู้เช่า
    while ($room = $roomResult->fetch_assoc()) {
        if ($room['room_status'] == 'ว่าง') {
            $vacantCount++;
        } else {
            $rentedCount++;
        }
    }
}
?>

<h2 id="rooms-all" class="text-left mb-4" style="font-size: 1.25rem; font-weight: bold;">
    ห้องทั้งหมด
    <small class="text-muted" style="font-size: 0.9rem;">
        (ห้องว่าง: <span class="text-success"><?= $vacantCount ?></span> ห้อง | 
        มีผู้เช่า: <span class="text-danger"><?= $rentedCount ?></span> ห้อง)
    </small>
</h2>

<div class="row">
<?php

if ($roomResult && $roomResult->num_rows > 0) {

    $roomResult = $conn->query($roomQuery);
    while ($room = $roomResult->fetch_assoc()) {
        echo "
        <div class='col-md-3 mb-3'> 
            <div class='card shadow-lg border-0' style='font-size: 0.85rem; padding: 15px; border-radius: 10px; background-color: #f8f9fa; transition: transform 0.3s;'>
                <div class='card-header' style='font-size: 1rem; font-weight: bold; background-color:rgb(95, 95, 95); color: white; border-radius: 10px;'>
                    <i class='fas fa-door-closed'></i> ห้อง {$room['room_number']}
                </div>
                <div class='card-body'>
                    <p class='card-text' style='font-size: 0.85rem;'>
                        <i class='fas fa-list-alt'></i> ประเภทห้อง: {$room['room_type']}
                    </p>
                    <p class='card-text' style='font-size: 0.85rem;'>
                        <i class='fas fa-tag'></i> ราคา: {$room['room_price']} บาท
                    </p>
                    <p class='card-text " . ($room['room_status'] == 'ว่าง' ? 'text-success' : 'text-danger') . "' style='font-size: 0.85rem;'>
                        <i class='fas fa-info-circle'></i> สถานะ: {$room['room_status']}
                    </p>
                    <div class='d-grid gap-2'>
                        <a href='manage_room.php?room_id={$room['room_id']}' class='btn btn-warning btn-sm'>
                            <i class='fas fa-cog'></i> จัดการห้องพัก
                        </a>  
                        <button type='button' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#editroom{$room['room_id']}'>
                            <i class='fas fa-edit'></i> แก้ไข
                        </button>  
                    </div>
                </div>
            </div>
        </div>
        ";
    }
} else {
    echo "<p>ไม่พบข้อมูลห้อง</p>";
}
?>
</div>



        <!-- Modal แก้ไข -->
        <?php
        // ดึงข้อมูลห้องมาแสดงใน Modal
        $roomResult = $conn->query($roomQuery);
        while ($room = $roomResult->fetch_assoc()) {
        ?>
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

                                <div class="mb-3">
                                    <label for="room_status" class="form-label">สถานะห้อง</label>
                                    <select class="form-select" id="room_status" name="room_status" required>
                                        <option value="ว่าง" <?php echo ($room['room_status'] == 'ว่าง') ? 'selected' : ''; ?>>ว่าง</option>
                                        <option value="มีผู้เช่า" <?php echo ($room['room_status'] == 'มีผู้เช่า') ? 'selected' : ''; ?>>มีผู้เช่า</option>
                                        <option value="ซ่อมแซม" <?php echo ($room['room_status'] == 'ซ่อมแซม') ? 'selected' : ''; ?>>ซ่อมแซม</option>
                                    </select>
                                </div>
                                <div class='d-grid gap-2'>
                                <button type="submit" class="btn btn-warning">บันทึกการเปลี่ยนแปลง</button>
                                <button type="button" class="btn btn-danger" onclick="deleteRoom(<?php echo $room['room_id']; ?>)">ลบข้อมูลห้อง</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>

    <script>
    function updateRoom(room_id) {
        const form = document.getElementById('editRoomForm' + room_id);
        const formData = new FormData(form);

        fetch('update_room.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('ข้อมูลห้องพักถูกอัปเดตเรียบร้อยแล้ว');
                  location.reload();
              } else {
                  alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
              }
          }).catch(error => {
              alert('เกิดข้อผิดพลาด: ' + error);
          });
    }

    function deleteRoom(room_id) {
        if (confirm('คุณต้องการลบข้อมูลห้องนี้หรือไม่?')) {
            fetch('delete_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ room_id: room_id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('ข้อมูลห้องพักถูกลบเรียบร้อยแล้ว');
                    location.reload();  // รีเฟรชหน้าเพื่อแสดงผลการเปลี่ยนแปลง
                } else {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            })
            .catch(error => {
                alert('เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์');
            });
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
