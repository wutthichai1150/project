<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('db.php');
// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (isset($_SESSION['ad_user'])) {
    $ad_user = $_SESSION['ad_user']; 

    $query = "SELECT ad_fname, ad_lname, ad_user FROM admin WHERE ad_user = '$ad_user'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $ad_fname = $admin['ad_fname'];
        $ad_lname = $admin['ad_lname'];
        $ad_user = $admin['ad_user'];
    } else {
        echo "ไม่สามารถดึงข้อมูลโปรไฟล์ได้.";
    }
} else {
    echo "กรุณาล็อกอิน.";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../css/navbar_admin.css">
    <title>ระบบจัดการหอพัก</title>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="navbar-header">
        <img src="../assets/image/logo.png" alt="Logo" class="rounded-circle">
        <span>ระบบจัดการหอพัก</span>
    </div>
    <ul class="nav flex-column px-3">
        <li class="nav-item mb-3">
            <a class="nav-link text-white" href="admin_dashboard.php">
                <i class="fas fa-home me-2"></i> หน้าแรก
            </a>
        </li>
        <li class="nav-item mb-3">
            <a class="nav-link text-white" href="add_rooms.php">
                <i class="fas fa-door-open me-2"></i> เพิ่มห้องพัก
            </a>
        </li>
        <li class="nav-item mb-3">
            <a class="nav-link text-white" href="manage_member.php">
                <i class="fas fa-users me-2"></i> จัดการสมาชิก
            </a>
        <li class="nav-item mb-3">
            <a class="nav-link text-white" href="manage_rates.php">
                <i class="fas fa-calculator me-2"></i> จัดการเรทค่าไฟค่าน้ำ
            </a>
        </li>
        <li class="nav-item mb-3">
            <a class="nav-link text-white" href="manage_repair.php">
                <i class="fas fa-tools me-2"></i> การแจ้งซ่อม
            </a>
        </li>
    </ul>

    <div class="profile-info">
    <img src="../assets/image/avatar.png" alt="Profile Picture" class="profile-logo">
    <p class="profile-username">บัญชีผู้ใช้:<?php echo $ad_user; ?></p>
    <p class="profile-name"><?php echo $ad_fname . " " . $ad_lname; ?></p>
    <li class="nav-item mb-3">
    <a class="nav-link logout-button" href="../logout.php">
        <i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ
    </a>
</li>

</div>

</div>

<button class="sidebar-toggler" type="button" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("open"); // Toggle open class to show/hide sidebar
    }
</script>

</body>
</html>
