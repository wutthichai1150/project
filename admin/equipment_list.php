<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

$sql = "SELECT * FROM equipment_detail";  
$result = $conn->query($sql);


if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการครุภัณฑ์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">รายการครุภัณฑ์</h2>

        <div class="mb-3">
            <a href="add_equipment.php" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i> เพิ่มครุภัณฑ์
            </a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ประเภทครุภัณฑ์</th>
                    <th>ชื่อครุภัณฑ์</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ตัวนับลำดับ
                $index = 1;

                // แสดงข้อมูลครุภัณฑ์ในตาราง
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$index}</td>
                            <td>{$row['eqm_type']}</td>
                            <td>{$row['eqm_name']}</td>
                            <td>
                                <a href='delete_equipment.php?id={$row['eqm_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณต้องการลบครุภัณฑ์นี้?\")'>ลบ</a>
                            </td>
                        </tr>";
                        $index++; // เพิ่มตัวนับลำดับ
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-muted'>ไม่พบข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>


    <!-- เชื่อมต่อ JavaScript ของ Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
