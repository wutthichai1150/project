<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหอพัก</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <style>
        .icon-circle {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            font-size: 24px;
            color: white;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">แจ้งซ่อมครุภัณฑ์</h2>

    <div class="row g-4 justify-content-center">
        <!-- การ์ด: ครุภัณฑ์ -->
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-primary shadow h-100">
                <div class="card-header d-flex align-items-center">
                    <div class="icon-circle bg-light text-primary">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="fs-5">ครุภัณฑ์</span>
                </div>
                <div class="card-body text-center">
                    <?php
                    // Query to count equipment
                    $equipmentQuery = "SELECT COUNT(*) as equipment_count FROM equipment_detail";
                    $equipmentResult = $conn->query($equipmentQuery);
                    if ($equipmentResult) {
                        $equipmentData = $equipmentResult->fetch_assoc();
                        echo "<h3 class='card-title'>ข้อมูครุภัณฑ์ {$equipmentData['equipment_count']} รายการ</h3>";
                    } else {
                        echo "<h3 class='card-title'>ไม่สามารถดึงข้อมูลได้</h3>";
                    }
                    ?>
                    <p class="card-text mt-3">
                        <a href="equipment_list.php" class="btn btn-light btn-sm">ดูรายการครุภัณฑ์ทั้งหมด</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- การ์ด: การแจ้งซ่อม -->
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-danger shadow h-100">
                <div class="card-header d-flex align-items-center">
                    <div class="icon-circle bg-light text-danger">
                        <i class="fas fa-tools"></i>
                    </div>
                    <span class="fs-5">การแจ้งซ่อม</span>
                </div>
                <div class="card-body text-center">
                    <?php
                    // Query to count repair requests
                    $repairQuery = "SELECT COUNT(*) as repair_count FROM repair_requests";
                    $repairResult = $conn->query($repairQuery);
                    if ($repairResult) {
                        $repairData = $repairResult->fetch_assoc();
                        echo "<h3 class='card-title'>แจ้งซ่อม {$repairData['repair_count']} รายการ</h3>";
                    } else {
                        echo "<h3 class='card-title'>ไม่สามารถดึงข้อมูลได้</h3>";
                    }
                    ?>
                    <p class="card-text mt-3">
                        <a href="manage_repair.php" class="btn btn-light btn-sm">ดูการแจ้งซ่อมทั้งหมด</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- เชื่อมต่อ Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
