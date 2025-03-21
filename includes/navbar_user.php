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

if ($stmt === false) {
  die("ERROR: Failed to prepare the SQL query. " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $mem_fname = $row['mem_fname']; 
  $mem_lname = $row['mem_lname']; 
  $mem_email = $row['mem_mail']; 
  $mem_phone = $row['mem_phone']; 
} else {
  echo "ไม่พบข้อมูลผู้ใช้";
  exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="../assets/css/tailwind.css"></script>
    <title>Lung Kung Dormitory</title>
    <style>
        /* เพิ่ม animation สำหรับเมนู Hamburger */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="bg-teal-700 p-4">
  <div class="max-w-screen-xl mx-auto flex justify-between items-center">
    <!-- Logo และชื่อเว็บ -->
    <a class="flex items-center text-white text-2xl font-bold" href="user_dashboard.php">
      <img src="../assets/image/logo.png" alt="Logo" class="rounded-full w-10 h-10">
      <span class="ml-2">Lung Kung Dormitory</span>
    </a>

    <!-- ปุ่ม Hamburger สำหรับมือถือ -->
    <button class="lg:hidden text-white focus:outline-none" id="menuToggle">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
      </svg>
    </button>

    <!-- เมนูหลัก -->
    <div class="hidden lg:flex gap-x-4" id="navLinks">
      <a class="text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="user_dashboard.php">
        <i class="fas fa-home mr-2"></i> หน้าแรก
      </a>
      <a class="text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="contact.php">
        <i class="fas fa-envelope mr-2"></i> ติดต่อ
      </a>
      <a class="text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="about.php">
        <i class="fas fa-info-circle mr-2"></i> เกี่ยวกับเรา
      </a>
      <div class="relative">
        <button class="text-white hover:bg-teal-800 px-3 py-2 rounded flex items-center transition duration-300" id="profileDropdown">
          <i class="fas fa-user-circle mr-2"></i> <?php echo $mem_fname . ' ' . $mem_lname; ?>
        </button>
        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 scale-95 transition-transform duration-300 transform hidden" id="dropdownMenu">
          <div class="border-t border-gray-200"></div>
          <a class="block px-4 py-2 text-red-600 hover:bg-gray-200" href="../logout.php">
            <i class="fas fa-sign-out-alt mr-2"></i> ออกจากระบบ
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- เมนู Hamburger สำหรับมือถือ -->
  <div class="lg:hidden mt-4 hidden" id="mobileMenu">
    <a class="block text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="user_dashboard.php">
      <i class="fas fa-home mr-2"></i> หน้าแรก
    </a>
    <a class="block text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="contact.php">
      <i class="fas fa-envelope mr-2"></i> ติดต่อ
    </a>
    <a class="block text-white hover:bg-teal-800 px-3 py-2 rounded transition duration-300" href="about.php">
      <i class="fas fa-info-circle mr-2"></i> เกี่ยวกับเรา
    </a>
    <div class="relative">
      <button class="w-full text-left text-white hover:bg-teal-800 px-3 py-2 rounded flex items-center transition duration-300" id="mobileProfileDropdown">
        <i class="fas fa-user-circle mr-2"></i> <?php echo $mem_fname . ' ' . $mem_lname; ?>
      </button>
      <div class="mt-2 bg-white rounded-lg shadow-lg hidden" id="mobileDropdownMenu">
        <a class="block px-4 py-2 text-gray-800 hover:bg-gray-200" href="profile.php">โปรไฟล์ของฉัน</a>
        <div class="border-t border-gray-200"></div>
        <a class="block px-4 py-2 text-red-600 hover:bg-gray-200" href="../logout.php">
          <i class="fas fa-sign-out-alt mr-2"></i> ออกจากระบบ
        </a>
      </div>
    </div>
  </div>
</nav>

<script>
  // เปิด/ปิดเมนู Hamburger
  document.getElementById('menuToggle').addEventListener('click', function() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('hidden');
    mobileMenu.classList.toggle('block');
    mobileMenu.style.animation = 'slideDown 0.3s ease-out';
  });

  // เปิด/ปิด Dropdown โปรไฟล์ (สำหรับมือถือ)
  document.getElementById('mobileProfileDropdown').addEventListener('click', function() {
    const mobileDropdown = document.getElementById('mobileDropdownMenu');
    mobileDropdown.classList.toggle('hidden');
  });

  // เปิด/ปิด Dropdown โปรไฟล์ (สำหรับ Desktop)
  document.getElementById('profileDropdown').addEventListener('click', function() {
    const dropdown = document.getElementById('dropdownMenu');
    dropdown.classList.toggle('hidden');
    dropdown.classList.toggle('opacity-0');
    dropdown.classList.toggle('scale-95');
    dropdown.classList.toggle('opacity-100');
    dropdown.classList.toggle('scale-100');
  });
</script>

</body>
</html>