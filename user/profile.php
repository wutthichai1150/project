<?php
session_start();
include('../includes/db.php');
include('../includes/navbar_user.php');


// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php');
    exit();
}

$mem_user = $_SESSION['mem_user'];

// ดึงข้อมูลสมาชิกจากฐานข้อมูล
$sql = "SELECT * FROM `member` WHERE mem_user = '$mem_user'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $mem_fname = $row['mem_fname'];
    $mem_lname = $row['mem_lname'];
    $mem_email = $row['mem_mail'];
    $mem_phone = $row['mem_phone'];
    $mem_password = $row['mem_password'];
} else {
    echo "ไม่พบข้อมูลสมาชิก";
    exit();
}

// จัดการเปลี่ยนรหัสผ่าน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['current_password'])) {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // ตรวจสอบว่ารหัสผ่านปัจจุบันตรงกับที่เก็บไว้หรือไม่
    if ($current_password === $mem_password) {
        if ($new_password === $confirm_password) {
            // อัปเดตรหัสผ่านใหม่
            $update_sql = "UPDATE `member` SET mem_password = '$new_password' WHERE mem_user = '$mem_user'";
            if (mysqli_query($conn, $update_sql)) {
                echo "success";
            } else {
                echo "error";
            }
        } else {
            echo "mismatch";
        }
    } else {
        echo "incorrect";
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
    }
    .card {
        border-radius: 10px;
    }
    .card-header {
        background-color: #008080; /* สี teal */
        color: white;
        border-radius: 10px 10px 0 0;
    }
    .card-body {
        padding: 2rem;
    }
    .form-control-plaintext {
        font-size: 1.1rem;
        font-weight: 500;
    }
    .btn-warning {
        background-color: #20c997; /* สี teal สำหรับปุ่ม */
        color: white;
    }
    .btn-warning:hover {
        background-color: #17a2b8; /* สี teal เข้มขึ้นเมื่อ hover */
    }
    .modal-content {
        border-radius: 10px;
    }
    .modal-header {
        background-color: #008080; /* สี teal */
        color: white;
    }
    .modal-footer button {
        border-radius: 10px;
    }
    .container {
        margin-top: 30px;
    }
</style>

</head>
<body>
<div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-user-circle me-2"></i> โปรไฟล์ของฉัน</h4>
                    </div>
                    <div class="card-body">
                        <!-- ชื่อ -->
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user me-2"></i> ชื่อ</label>
                            <p class="form-control-plaintext"><?php echo $mem_fname . ' ' . $mem_lname; ?></p>
                        </div>
                        <!-- อีเมล -->
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i> อีเมล</label>
                            <p class="form-control-plaintext"><?php echo $mem_email; ?></p>
                        </div>
                        <!-- เบอร์โทร -->
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-phone me-2"></i> เบอร์โทรศัพท์</label>
                            <p class="form-control-plaintext"><?php echo $mem_phone; ?></p>
                        </div>

                        <!-- แก้ไขโปรไฟล์ -->
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#editModal">
                                <i class="fas fa-edit me-2"></i> แก้ไขโปรไฟล์
                            </button>
                            <button type="button" class="btn btn-danger mx-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fas fa-key me-2"></i> เปลี่ยนรหัสผ่าน
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal แก้ไขโปรไฟล์ -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">แก้ไขโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- ฟอร์มแก้ไขโปรไฟล์ -->
                    <form id="editProfileForm">
                        <!-- ชื่อ -->
                        <div class="mb-3">
                            <label class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" name="mem_fname" value="<?php echo $mem_fname; ?>" required>
                        </div>
                        <!-- นามสกุล -->
                        <div class="mb-3">
                            <label class="form-label">นามสกุล</label>
                            <input type="text" class="form-control" name="mem_lname" value="<?php echo $mem_lname; ?>" required>
                        </div>
                        <!-- อีเมล -->
                        <div class="mb-3">
                            <label class="form-label">อีเมล</label>
                            <input type="email" class="form-control" name="mem_email" value="<?php echo $mem_email; ?>" required>
                        </div>
                        <!-- เบอร์โทร -->
                        <div class="mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" name="mem_phone" value="<?php echo $mem_phone; ?>" required>
                        </div>
                    </form>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success" id="saveProfileBtn"><i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง</button>
                </div>
            </div>
        </div>
    </div>

<!-- Modal เปลี่ยนรหัสผ่าน -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เปลี่ยนรหัสผ่าน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label>รหัสผ่านปัจจุบัน</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" id="current_password" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleCurrentPassword"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>รหัสผ่านใหม่</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>ยืนยันรหัสผ่านใหม่</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="submit" class="btn btn-success" id="savePasswordBtn">บันทึก</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('saveProfileBtn').addEventListener('click', function() {
            // เก็บค่าจากฟอร์มใน Modal
            var fname = $("input[name='mem_fname']").val();
            var lname = $("input[name='mem_lname']").val();
            var email = $("input[name='mem_email']").val();
            var phone = $("input[name='mem_phone']").val();

            $.ajax({
                url: 'update_profile.php',  
                method: 'POST',
                data: {
                    mem_fname: fname,
                    mem_lname: lname,
                    mem_email: email,
                    mem_phone: phone
                },
                success: function(response) {
                    console.log(response);  

                    if (response == 'success') {
                        Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'โปรไฟล์ของคุณได้รับการอัปเดตแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var modalElement = document.getElementById('editModal');
                                var myModal = bootstrap.Modal.getInstance(modalElement);  
                                myModal.hide(); 

                                // ซ่อน backdrop
                                document.querySelector('.modal-backdrop').classList.remove('show');

                                location.reload();  
                            }
                        });
                    } else if (response == 'error') {
                        Swal.fire({
                            title: 'ผิดพลาด!',
                            text: 'ไม่สามารถบันทึกข้อมูลได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    } else if (response == 'no_session') {
                        Swal.fire({
                            title: 'ผิดพลาด!',
                            text: 'ไม่พบข้อมูลผู้ใช้ใน session',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);  
                }
            });
        });
        $('#savePasswordBtn').click(function() {
            var currentPassword = $("input[name='current_password']").val();
            var newPassword = $("input[name='new_password']").val();
            var confirmPassword = $("input[name='confirm_password']").val();

            $.ajax({
                url: 'profile.php',
                method: 'POST',
                data: {
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                },
                success: function(response) {
                    if (response === 'success') {
                        Swal.fire('สำเร็จ!', 'รหัสผ่านถูกเปลี่ยนเรียบร้อย', 'success').then(() => {
                            location.reload();
                        });
                    } else if (response === 'incorrect') {
                        Swal.fire('ผิดพลาด!', 'รหัสผ่านปัจจุบันไม่ถูกต้อง', 'error');
                    } else if (response === 'mismatch') {
                        Swal.fire('ผิดพลาด!', 'รหัสผ่านใหม่ไม่ตรงกัน', 'error');
                    } else {
                        Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'error');
                    }
                }
            });
        });
        function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
        const passwordField = document.getElementById(passwordFieldId);
        const toggleButton = document.getElementById(toggleButtonId);

        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>'; // เปลี่ยนเป็นไอคอน "ซ่อน"
        } else {
            passwordField.type = "password";
            toggleButton.innerHTML = '<i class="fas fa-eye"></i>'; // เปลี่ยนเป็นไอคอน "แสดง"
        }
    }

    // เชื่อมต่อกับปุ่มที่เกี่ยวข้อง
    document.getElementById("toggleCurrentPassword").addEventListener("click", function() {
        togglePasswordVisibility("current_password", "toggleCurrentPassword");
    });

    document.getElementById("toggleNewPassword").addEventListener("click", function() {
        togglePasswordVisibility("new_password", "toggleNewPassword");
    });

    document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
        togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
    });
    </script>
</body>
</html>
