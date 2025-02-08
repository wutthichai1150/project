<?php
include('../includes/navbar_admin.php');
include('../includes/db.php');

// ตรวจสอบการเริ่ม session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$room_id = $_GET['room_id']; 

// ตรวจสอบว่าได้รับคำสั่งจากฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stay'])) {
    $stay_id = $_POST['stay_id'];
    $stay_start_date = $_POST['stay_start_date'];
    $stay_end_date = $_POST['stay_end_date'];

    // คำสั่ง SQL สำหรับอัพเดตข้อมูลการเข้าพัก
    $update_sql = "UPDATE stay SET stay_start_date = ?, stay_end_date = ? WHERE stay_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $stay_start_date, $stay_end_date, $stay_id);

    if ($stmt->execute()) {
        // ถ้าการอัพเดตสำเร็จ
        $alert_message = "Swal.fire({
            title: 'สำเร็จ!',
            text: 'อัพเดตข้อมูลสำเร็จ',
            icon: 'success',
            confirmButtonText: 'ตกลง'
        });";
    } else {
        // ถ้ามีข้อผิดพลาด
        $alert_message = "Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถอัพเดตข้อมูลได้',
            icon: 'error',
            confirmButtonText: 'ตกลง'
        });";
    }
}

// คำสั่ง SQL เพื่อดึงข้อมูลการเข้าพัก
$sql = "
    SELECT s.stay_id, s.room_id, s.mem_id, s.stay_start_date, s.stay_end_date, 
           m.mem_fname, m.mem_lname, r.room_number
    FROM stay s
    LEFT JOIN `member` m ON s.mem_id = m.mem_id
    LEFT JOIN room r ON s.room_id = r.room_id
    WHERE s.room_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $stay_id = $_POST['stay_id'];

    // คำสั่ง SQL สำหรับลบข้อมูล
    $delete_sql = "DELETE FROM stay WHERE stay_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $stay_id);

    if ($stmt->execute()) {
        echo "success";  // ส่งข้อมูลกลับว่า successful
    } else {
        echo "error";  // ส่งข้อมูลกลับว่ามีข้อผิดพลาด
    }
    exit();
}
$room_sql = "SELECT room_number FROM room WHERE room_id = ?";
$room_stmt = $conn->prepare($room_sql);
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$room_data = $room_result->fetch_assoc();
$room_number = $room_data['room_number'];

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการค่าเช่า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
<h2 class="mb-4 text-center">รายการเข้าพักของห้อง <?php echo $room_number; ?></h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-light">
                <?php if ($result->num_rows > 0): ?>
                <tr>
                    <th>ลำดับ</th>
                    <th>เลขห้อง</th>
                    <th>ชื่อสมาชิก</th>
                    <th>วันที่เริ่มเข้าพัก</th>
                    <th>วันที่สิ้นสุดการเข้าพัก</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $index = 1;
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo sprintf("%03d", $index); ?></td>
                        <td><?php echo $row['room_number']; ?></td>
                        <td><?php echo $row['mem_fname'] . " " . $row['mem_lname']; ?></td>
                        <td><?php echo $row['stay_start_date']; ?></td>
                        <td><?php echo $row['stay_end_date']; ?></td>
                        <td>
                            <!-- ปุ่มแก้ไข -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editStayModal<?php echo $row['stay_id']; ?>">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </button>
                            <!-- ปุ่มลบ -->
                            <button class="btn btn-danger btn-sm" onclick="deleteStay(<?php echo $row['stay_id']; ?>)">
                            <i class="bi bi-trash"></i> ลบ
                        </button>

                        </td>
                    </tr>

                    <!-- Modal แก้ไข -->
                    <div class="modal fade" id="editStayModal<?php echo $row['stay_id']; ?>" tabindex="-1" aria-labelledby="editStayModalLabel<?php echo $row['stay_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editStayModalLabel<?php echo $row['stay_id']; ?>">แก้ไขข้อมูลการเข้าพัก</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="stay_history.php?room_id=<?php echo $room_id; ?>">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="stay_start_date" class="form-label">วันที่เริ่มเข้าพัก</label>
                                            <input type="date" class="form-control" name="stay_start_date" value="<?php echo $row['stay_start_date']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="stay_end_date" class="form-label">วันที่สิ้นสุดการเข้าพัก</label>
                                            <input type="date" class="form-control" name="stay_end_date" value="<?php echo $row['stay_end_date']; ?>" required>
                                        </div>
                                        <input type="hidden" name="stay_id" value="<?php echo $row['stay_id']; ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                        <button type="submit" name="update_stay" class="btn btn-primary">อัพเดต</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php 
                $index++; 
                endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>ไม่มีประวัติการเข้าพักสำหรับห้องนี้</p>
    <?php endif; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
    <?php 
    // ถ้ามีการตั้งค่าข้อความ SweetAlert
    if (isset($alert_message)) {
        echo $alert_message;
    }
    ?>

function deleteStay(stayId) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลการเข้าพักนี้?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // ใช้ AJAX ลบข้อมูล
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "stay_history.php?room_id=<?php echo $room_id; ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status == 200) {
                    // รีเฟรชตารางข้อมูลหลังลบ
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ลบข้อมูลสำเร็จ',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload(); // รีเฟรชหน้า
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถลบข้อมูลได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            };
            xhr.send("action=delete&stay_id=" + stayId);
        }
    });
}

</script>

</body>
</html>
