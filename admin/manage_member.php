<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');
include('../includes/navbar_admin.php');

// ดึงข้อมูลทั้งหมดจากตาราง member
$query = "SELECT mem_id, mem_fname, mem_lname, mem_user, mem_mail, mem_phone, mem_address, mem_id_card FROM `member`";
$result = $conn->query($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบค่าที่ส่งมาจากฟอร์ม
    $mem_id = $_POST['mem_id'];
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname'];
    $mem_user = $_POST['mem_user'];
    $mem_mail = $_POST['mem_mail'];
    $mem_phone = $_POST['mem_phone'];
    $mem_address = $_POST['mem_address'];

    // ตรวจสอบว่ามีค่าข้อมูลทั้งหมดหรือไม่
    if (empty($mem_id) || empty($mem_fname) || empty($mem_lname) || empty($mem_user) || empty($mem_mail)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
        exit;
    }

    // อัพเดตข้อมูลในฐานข้อมูล
    $query = "UPDATE `member` SET 
                mem_fname = '$mem_fname', 
                mem_lname = '$mem_lname', 
                mem_user = '$mem_user', 
                mem_mail = '$mem_mail', 
                mem_phone = '$mem_phone', 
                mem_address = '$mem_address' 
              WHERE mem_id = '$mem_id'";

    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัพเดตข้อมูล: ' . $conn->error]);
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- FontAwesome -->
    <title>จัดการสมาชิก</title>
    <style>
        .btn {
            margin: 0 5px;
            border-radius: 50px; /* ทำให้ปุ่มกลม */
            padding: 10px 20px;
            font-size: 14px;
        }
        .btn-view {
            background-color: #4CAF50;
            color: white;
        }
        .btn-edit {
            background-color: #FFA500;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        table th, table td {
            vertical-align: middle; /* จัดแนวตัวอักษรในเซลล์ */
        }
        .modal-body img {
            max-width: 100%; /* ปรับขนาดของรูปให้พอดีกับขนาดหน้าจอ */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">จัดการสมาชิก</h2>
        
        <!-- ตารางแสดงข้อมูลสมาชิก -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result === false) {
                    die("Query failed: " . $conn->error);
                }

                if ($result->num_rows > 0) {
                    $i = 1;  // เริ่มนับลำดับที่ 1
                    while ($row = $result->fetch_assoc()) {
                        // รวมชื่อและนามสกุล
                        $fullName = $row['mem_fname'] . ' ' . $row['mem_lname'];

                        // Modal trigger links
                        echo "<tr>
                                <td>" . $i++ . "</td>
                                <td>" . $fullName . "</td>
                                <td>" . $row['mem_user'] . "</td>
                                <td>" . $row['mem_mail'] . "</td>
                                <td>
                                    <button class='btn btn-view' data-bs-toggle='modal' data-bs-target='#viewModal" . $row['mem_id'] . "'><i class='fas fa-eye'></i> ดู</button>
                                    <button class='btn btn-edit' data-bs-toggle='modal' data-bs-target='#editModal" . $row['mem_id'] . "'><i class='fas fa-edit'></i> แก้ไข</button>
                                    <button class='btn btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['mem_id'] . "'><i class='fas fa-trash-alt'></i> ลบ</button>
                                </td>
                              </tr>";

                        // View Modal
                        echo "
                        <div class='modal fade' id='viewModal" . $row['mem_id'] . "' tabindex='-1' aria-labelledby='viewModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='viewModalLabel'>รายละเอียดสมาชิก</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <p><strong>ชื่อ:</strong> " . $fullName . "</p>
                                        <p><strong>Username:</strong> " . $row['mem_user'] . "</p>
                                        <p><strong>Email:</strong> " . $row['mem_mail'] . "</p>
                                        <p><strong>เบอร์โทร:</strong> " . $row['mem_phone'] . "</p>
                                        <p><strong>ที่อยู่:</strong> " . $row['mem_address'] . "</p>
                                        <p><strong>เลขบัตรประชาชน:</strong> " . $row['mem_id_card'] . "</p>
                                        <img src='../uploads/member/" . $row['mem_id_card'] . "' alt='ID Card'>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>";

                        // Edit Modal
                        echo "
                        <div class='modal fade' id='editModal" . $row['mem_id'] . "' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='editModalLabel'>แก้ไขข้อมูลสมาชิก</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <form id='editForm" . $row['mem_id'] . "'>
                                            <input type='hidden' name='mem_id' value='" . $row['mem_id'] . "'>
                                            <div class='mb-3'>
                                                <label for='mem_fname' class='form-label'>ชื่อ</label>
                                                <input type='text' class='form-control' id='mem_fname" . $row['mem_id'] . "' name='mem_fname' value='" . $row['mem_fname'] . "' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='mem_lname' class='form-label'>นามสกุล</label>
                                                <input type='text' class='form-control' id='mem_lname" . $row['mem_id'] . "' name='mem_lname' value='" . $row['mem_lname'] . "' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='mem_user' class='form-label'>Username</label>
                                                <input type='text' class='form-control' id='mem_user" . $row['mem_id'] . "' name='mem_user' value='" . $row['mem_user'] . "' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='mem_mail' class='form-label'>Email</label>
                                                <input type='email' class='form-control' id='mem_mail" . $row['mem_id'] . "' name='mem_mail' value='" . $row['mem_mail'] . "' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='mem_phone' class='form-label'>เบอร์โทร</label>
                                                <input type='text' class='form-control' id='mem_phone" . $row['mem_id'] . "' name='mem_phone' value='" . $row['mem_phone'] . "'>
                                            </div>
                                            <div class='mb-3'>
                                                <label for='mem_address' class='form-label'>ที่อยู่</label>
                                                <textarea class='form-control' id='mem_address" . $row['mem_id'] . "' name='mem_address' required>" . $row['mem_address'] . "</textarea>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>ปิด</button>
                                                <button type='button' class='btn btn-primary' onclick='updateMember(" . $row['mem_id'] . ")'>บันทึก</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>";

                        // Delete Modal
                        echo "
                        <div class='modal fade' id='deleteModal" . $row['mem_id'] . "' tabindex='-1' aria-labelledby='deleteModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='deleteModalLabel'>ยืนยันการลบสมาชิก</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <p>คุณต้องการลบสมาชิก <strong>" . $fullName . "</strong> ใช่หรือไม่?</p>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>ยกเลิก</button>
                                        <button type='button' class='btn btn-danger' onclick='deleteMember(" . $row['mem_id'] . ")'>ลบ</button>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript สำหรับ Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ฟังก์ชันสำหรับลบสมาชิก
        function deleteMember(memId) {
            if (confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')) {
                fetch('delete_member.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'mem_id=' + memId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'สมาชิกถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            location.reload(); // รีเฟรชหน้าเว็บเพื่ออัปเดตข้อมูล
                        });
                    } else {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: data.message || 'ไม่สามารถลบสมาชิกได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                });
            }
        }

        // ฟังก์ชันสำหรับอัพเดตสมาชิก
        function updateMember(memId) {
            const form = document.getElementById('editForm' + memId);
            const formData = new FormData(form);
            fetch('update_member.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'ข้อมูลสมาชิกถูกอัพเดตแล้ว',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload(); // รีเฟรชหน้าเว็บ
                    });
                } else {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
        }
    </script>
</body>
</html>
