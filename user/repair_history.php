<?php
session_start();
include('../includes/db.php');
include('../includes/navbar_user.php');

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['mem_user'])) {
    header("Location: login.php");
    exit();
}

// รับค่า mem_user จากเซสชัน
$mem_user = $_SESSION['mem_user'];

// รับค่า room_id จาก URL
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

if ($room_id) {
    // ตรวจสอบว่า mem_user เป็นเจ้าของห้องหรือไม่
    $check_owner_sql = "SELECT s.room_id 
                        FROM stay s
                        JOIN `member` m ON s.mem_id = m.mem_id
                        WHERE s.room_id = '$room_id' AND m.mem_user = '$mem_user'";

    $check_owner_result = mysqli_query($conn, $check_owner_sql);

    if (!$check_owner_result) {
        die("Query failed: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($check_owner_result) == 0) {
        echo "คุณไม่มีสิทธิ์ดูข้อมูลของห้องนี้";
        exit();
    }

    // คำสั่ง SQL สำหรับการดึงข้อมูลประวัติการซ่อม
    $sql = "SELECT rr.repair_id, rr.room_id, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_date, rr.repair_state, r.room_number, rr.repair_image
            FROM repair_requests rr
            JOIN room r ON rr.room_id = r.room_id
            WHERE rr.room_id = '$room_id'
            ORDER BY rr.repair_date DESC";
} else {
    // กรณีไม่มี room_id (แสดงทั้งหมด)
    $sql = "SELECT rr.repair_id, rr.room_id, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_date, rr.repair_state, r.room_number, rr.repair_image
            FROM repair_requests rr
            JOIN room r ON rr.room_id = r.room_id
            ORDER BY rr.repair_date DESC";
}

// ตรวจสอบผลการ query
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- เพิ่ม Font Awesome -->
</head>
<body>
<div class="container mt-4">
<h3 class="mb-4 text-center text-black">
    <i class="fas fa-tools"></i>
    ประวัติการซ่อมครุภัณฑ์
</h3>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>หมายเลขห้อง</th>
                        <th>ชื่อผู้แจ้ง <i class="fas fa-user"></i></th> <!-- เพิ่มไอคอน -->
                        <th>ประเภทครุภัณฑ์ <i class="fas fa-cogs"></i></th> <!-- เพิ่มไอคอน -->
                        <th>ชื่อครุภัณฑ์ <i class="fas fa-cog"></i></th> <!-- เพิ่มไอคอน -->
                        <th>รายละเอียด <i class="fas fa-info-circle"></i></th> <!-- เพิ่มไอคอน -->
                        <th>วันที่แจ้งซ่อม <i class="fas fa-calendar-alt"></i></th> <!-- เพิ่มไอคอน -->
                        <th>สถานะ <i class="fas fa-check-circle"></i></th> <!-- เพิ่มไอคอน -->
                        <th>รูปภาพ <i class="fas fa-image"></i></th> <!-- เพิ่มไอคอน -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['room_number']; ?></td>
                            <td><?php echo $row['repair_name']; ?></td>
                            <td><?php echo $row['repair_type']; ?></td>
                            <td><?php echo $row['repair_eqm_name']; ?></td>
                            <td><?php echo $row['repair_detail']; ?></td>
                            <td><?php echo $row['repair_date']; ?></td>
                            <td><?php echo $row['repair_state']; ?></td>
                            <td>
                                <?php if ($row['repair_image']): ?>
                                    <img src="../uploads/repair/<?php echo $row['repair_image']; ?>" alt="Image" 
                                        style="max-width: 100px; max-height: 100px; object-fit: cover;" 
                                        data-bs-toggle="modal" data-bs-target="#imageModal" 
                                        data-bs-image="../uploads/repair/<?php echo $row['repair_image']; ?>" />
                                <?php else: ?>
                                    <i class="fas fa-times-circle" style="color: red;"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>ยังไม่มีข้อมูลการซ่อม</p>
    <?php endif; ?>
</div>

<!-- Modal for image view -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">รูปภาพขยาย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" id="modalImage" class="img-fluid" alt="Expanded Image">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set the image src for modal on click
    const imageElements = document.querySelectorAll('[data-bs-toggle="modal"]');
    const modalImage = document.getElementById('modalImage');

    imageElements.forEach(img => {
        img.addEventListener('click', (e) => {
            const imageUrl = e.target.getAttribute('data-bs-image');
            modalImage.src = imageUrl;
        });
    });
</script>

</body>
</html>
