<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('includes/db.php');  // เชื่อมต่อกับฐานข้อมูล
include('includes/navbar.php');

$showAlert = false;
$alertMessage = '';
$alertType = '';
$redirectUrl = '';

if (isset($_POST['login'])) {
    $username = $_POST['mem_user'];
    $password = $_POST['mem_password'];

    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // เช็คการล็อคอินของ Admin ก่อน
    $query_admin = $conn->prepare("SELECT * FROM `admin` WHERE ad_user = ?");
    $query_admin->bind_param("s", $username);
    $query_admin->execute();
    $result_admin = $query_admin->get_result();

    if ($result_admin->num_rows > 0) {
        $admin = $result_admin->fetch_assoc();
        if ($password === $admin['ad_password']) {
            $_SESSION['ad_id'] = $admin['ad_id'];
            $_SESSION['ad_user'] = $admin['ad_user'];
            $_SESSION['ad_fname'] = $admin['ad_fname'];
            $_SESSION['ad_lname'] = $admin['ad_lname'];

            $showAlert = true;
            $alertMessage = "ล็อคอินสำเร็จ! ยินดีต้อนรับผู้ดูแลระบบ";
            $alertType = "success";
            $redirectUrl = 'admin/admin_dashboard.php';
        } else {
            $showAlert = true;
            $alertMessage = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้องสำหรับผู้ดูแลระบบ!";
            $alertType = "error";
        }
    } else {
        // ถ้าไม่ใช่ผู้ดูแลระบบ
        $query_member = $conn->prepare("SELECT * FROM `member` WHERE mem_user = ?");
        $query_member->bind_param("s", $username);
        $query_member->execute();
        $result_member = $query_member->get_result();

        if ($result_member->num_rows > 0) {
            $user = $result_member->fetch_assoc();
            if ($password === $user['mem_password']) {
                $_SESSION['user_id'] = $user['mem_id'];
                $_SESSION['mem_user'] = $user['mem_user'];
                $_SESSION['mem_fname'] = $user['mem_fname'];
                $_SESSION['mem_lname'] = $user['mem_lname'];
                $_SESSION['mem_mail'] = $user['mem_mail'];
                $_SESSION['mem_address'] = $user['mem_address'];

                $showAlert = true;
                $alertMessage = "ล็อคอินสำเร็จ! ยินดีต้อนรับผู้ใช้";
                $alertType = "success";
                $redirectUrl = 'user/user_dashboard.php';
            } else {
                $showAlert = true;
                $alertMessage = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้องสำหรับผู้ใช้ทั่วไป!";
                $alertType = "error";
            }
        } else {
            $showAlert = true;
            $alertMessage = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
            $alertType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/login.css">

    
</head>
<body> 
    <div class="container d-flex justify-content-center align-items-start">
        <div class="card shadow-lg" style="max-width: 400px; width: 100%;">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">เข้าสู่ระบบ</h2>

                <?php if ($showAlert) { ?>
                    <script>
                        Swal.fire({
                            title: '<?php echo $alertMessage; ?>',
                            icon: '<?php echo $alertType; ?>',
                            confirmButtonText: 'ตกลง'
                        }).then(function() {
                            window.location.href = '<?php echo $redirectUrl; ?>';
                        });
                    </script>
                <?php } ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="mem_user" class="form-label">Username</label>
                        <input type="text" name="mem_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="mem_password" class="form-label">Password</label>
                        <input type="password" name="mem_password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2">
                        Login
                    </button>
                </form>
                <p class="text-center mt-3">ยังไม่มีบัญชี? <a href="register.php">ลงทะเบียนที่นี่</a></p>
            </div>
        </div>
    </div>
</body>
</html>
