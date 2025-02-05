<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('includes/db.php');  // เชื่อมต่อกับฐานข้อมูล
include('includes/navbar.php');

if (isset($_POST['register'])) {
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname'];
    $mem_user = $_POST['mem_user'];
    $mem_password = $_POST['mem_password'];
    $mem_mail = $_POST['mem_mail'];
    $mem_phone = $_POST['mem_phone'];
    $mem_address = $_POST['mem_address'];
    $mem_id_card = $_FILES['mem_id_card']['name']; // ชื่อไฟล์รูปภาพบัตรประชาชน
    $target_dir = "uploads/member/"; // โฟลเดอร์สำหรับจัดเก็บรูปภาพ
    $target_file = $target_dir . basename($_FILES["mem_id_card"]["name"]);

    // อัปโหลดรูปภาพ
    if (!move_uploaded_file($_FILES["mem_id_card"]["tmp_name"], $target_file)) {
        die("<div class='alert alert-danger'>Failed to upload ID card image.</div>");
    }

    // เตรียมคำสั่ง SQL (เพิ่มฟิลด์ใหม่)
    $query = $conn->prepare("INSERT INTO `member` (mem_fname, mem_lname, mem_user, mem_password, mem_mail, mem_phone, mem_address, mem_id_card) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($query === false) {
        die("SQL Error: " . $conn->error);
    }

    $query->bind_param("ssssssss", $mem_fname, $mem_lname, $mem_user, $mem_password, $mem_mail, $mem_phone, $mem_address, $mem_id_card);

    if ($query->execute()) {
        echo "<div class='alert alert-success'>Registration successful! <a href='login.php'>Click here to login</a></div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $query->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- เพิ่ม Font Awesome -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 400px; /* กำหนดความกว้างสูงสุด */
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto; /* จัดให้อยู่กลางจอ */
        }
        .form-label i {
            font-size: 0.9rem; /* ลดขนาดไอคอน */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i> Register</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="mem_fname" class="form-label"><i class="fas fa-user me-2"></i>First Name</label>
                <input type="text" name="mem_fname" class="form-control form-control-sm" placeholder="Enter your first name" required>
            </div>
            <div class="mb-3">
                <label for="mem_lname" class="form-label"><i class="fas fa-user me-2"></i>Last Name</label>
                <input type="text" name="mem_lname" class="form-control form-control-sm" placeholder="Enter your last name" required>
            </div>
            <div class="mb-3">
                <label for="mem_user" class="form-label"><i class="fas fa-user-circle me-2"></i>Username</label>
                <input type="text" name="mem_user" class="form-control form-control-sm" placeholder="Choose a username" required>
            </div>
            <div class="mb-3">
                <label for="mem_password" class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" name="mem_password" class="form-control form-control-sm" placeholder="Enter your password" required>
            </div>
            <div class="mb-3">
                <label for="mem_mail" class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                <input type="email" name="mem_mail" class="form-control form-control-sm" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="mem_phone" class="form-label"><i class="fas fa-phone me-2"></i>Phone Number</label>
                <input type="text" name="mem_phone" class="form-control form-control-sm" placeholder="Enter your phone number" required>
            </div>
            <div class="mb-3">
                <label for="mem_address" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Address</label>
                <textarea name="mem_address" class="form-control form-control-sm" placeholder="Enter your address" required></textarea>
            </div>
            <div class="mb-3">
                <label for="mem_id_card" class="form-label"><i class="fas fa-id-card me-2"></i>ID Card Image</label>
                <input type="file" name="mem_id_card" class="form-control form-control-sm" accept="image/*" required>
            </div>
            <button type="submit" name="register" class="btn btn-sm w-100" style="background-color: #008080; color: white;">
    <i class="fas fa-check-circle me-2"></i> Register
</button>

        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
