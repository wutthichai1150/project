<?php
include('../includes/db.php');

if (!isset($_SESSION['mem_user'])) {
  header('Location: login.php');
  exit();
}

$user = $_SESSION['mem_user']; // ดึงข้อมูลจากเซสชัน

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn === false) {
  die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}

// คิวรีข้อมูลจากฐานข้อมูล
$query = "SELECT * FROM `member` WHERE mem_user = ?";
$stmt = $conn->prepare($query);

// ตรวจสอบว่าคำสั่ง prepare สำเร็จหรือไม่
if ($stmt === false) {
  die("ERROR: Failed to prepare the SQL query. " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $mem_fname = $row['mem_fname']; // ชื่อ
  $mem_lname = $row['mem_lname']; // นามสกุล
  $mem_email = $row['mem_mail']; // อีเมล
  $mem_phone = $row['mem_phone']; // เบอร์โทรศัพท์
} else {
  echo "ไม่พบข้อมูลผู้ใช้";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/navbar_user.css">
    <title>Lung Kung Dormitory</title>
    <style>
        /* กำหนดสีเทลให้ navbar */
        .bg-teal {
            background-color: #008080 !important; /* สีเทล */
        }

        .nav-link, .dropdown-item {
            color: white !important; /* ทำให้ข้อความใน navbar เป็นสีขาว */
        }

        .nav-link:hover, .dropdown-item:hover {
            background-color: #006666 !important; /* เพิ่มสีเมื่อเอาเมาส์ไปอยู่ที่ลิงก์ */
        }
        .navbar {
    background-color: #00796b; /* สี Teal ของ Navbar */
}

.navbar-brand {
    font-size: 1.6rem;
    font-weight: 600;
    color: white; /* ใช้สีขาวเพื่อให้คอนทราสต์กับพื้นหลัง */
    transition: color 0.3s ease-in-out;
}

.navbar-brand:hover {
    color: #ffc107; /* เปลี่ยนสีเมื่อ hover เป็นสีทอง */
}

.navbar .nav-link {
    color: white; /* สีลิงค์ให้เป็นสีขาว */
}

.navbar .nav-link:hover {
    color: #ffc107; /* สีทองเมื่อ hover */
}

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-teal">
  <div class="container-fluid">
  <a class="navbar-brand" href="user_dashboard.php">
    <img src="../assets/image/logo.png" alt="Logo" class="rounded-circle" width="40" height="40">
    <span class="ms-2" style="font-family: 'Arial', sans-serif; font-size: 1.5rem; color:rgb(255, 255, 255); font-weight: bold;">Lung Kung Dormitory</span>
</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- หน้าแรก -->
        <li class="nav-item">
          <a class="nav-link text-white" href="user_dashboard.php">
            <i class="fas fa-home me-2"></i> หน้าแรก
          </a>
        </li>
        
 
          <li class="nav-item">
            <a class="nav-link text-white" href="repair_form.php?room_id=<?php echo $room['room_id']; ?>">
              <i class="fas fa-tools me-2"></i> แจ้งซ่อมครุภัณฑ์
            </a>
          </li>

        </li>
        <!-- ติดต่อ -->
        <li class="nav-item">
          <a class="nav-link text-white" href="contact.php">
            <i class="fas fa-envelope me-2"></i> ติดต่อ
          </a>
        </li>

        <!-- เกี่ยวกับเรา -->
        <li class="nav-item">
          <a class="nav-link text-white" href="about.php">
            <i class="fas fa-info-circle me-2"></i> เกี่ยวกับเรา
          </a>
        </li>

        <!-- แสดงโปรไฟล์ผู้ใช้ -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle text-white" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-user-circle me-2"></i> <?php echo $mem_fname . ' ' . $mem_lname; ?>
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
    <li><a class="dropdown-item text-dark" href="profile.php">โปรไฟล์ของฉัน</a></li>
    <li><a class="dropdown-item text-dark" href="change_password.php">เปลี่ยนรหัสผ่าน</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
  </ul>
</li>
</ul>
</div>
</div>
</nav>

<!-- Add the rest of your page content below here -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
