<?php 
include('../includes/db.php');
include('../includes/navbar_admin.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $room_number = $_POST['room_number'];
    $room_type = $_POST['room_type'];
    $room_price = $_POST['room_price'];
    $room_status = $_POST['room_status'];

    // ตรวจสอบเลขห้องซ้ำในฐานข้อมูล
    $sql_check = "SELECT * FROM room WHERE room_number = '$room_number'";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        // ถ้ามีเลขห้องซ้ำ ให้แจ้งเตือน
        echo "<script>
                window.onload = function() {
                    Swal.fire('เลขห้องนี้มีอยู่แล้ว กรุณากรอกใหม่', '', 'error');
                };
              </script>";
    } else {
        // ตรวจสอบค่าก่อนบันทึก
        if (empty($room_number) || empty($room_type) || empty($room_price) || empty($room_status)) {
            // แจ้งเตือนหากข้อมูลไม่ครบ
            echo "<script>
                    window.onload = function() {
                        Swal.fire('กรุณากรอกข้อมูลให้ครบถ้วน', '', 'error');
                    };
                  </script>";
        } else {
            // สร้างคำสั่ง SQL สำหรับบันทึกห้องพัก
            $sql_room = "INSERT INTO room (room_number, room_type, room_price, room_status) 
                         VALUES ('$room_number', '$room_type', '$room_price', '$room_status')";
            
            // ตรวจสอบการดำเนินการ SQL
            if ($conn->query($sql_room) === TRUE) {
                echo "<script>
                        window.onload = function() {
                            Swal.fire({
                                title: 'บันทึกข้อมูลสำเร็จ',
                                icon: 'success',
                                confirmButtonText: 'ตกลง'
                            }).then(function() {
                                window.location.href = 'add_rooms.php';
                            });
                        };
                      </script>";
            } else {
                echo "<script>
                        window.onload = function() {
                            Swal.fire('เกิดข้อผิดพลาดในการบันทึกข้อมูล', '', 'error');
                        };
                      </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลห้องพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>เพิ่มข้อมูลห้องพัก</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <!-- เลขห้อง -->
                            <div class="mb-3">
                                <label for="room_number" class="form-label">เลขห้อง</label>
                                <input type="text" name="room_number" class="form-control" id="room_number" required placeholder="กรุณากรอกเลขห้อง">
                            </div>

                            <!-- ประเภทห้อง -->
                            <div class="mb-3">
                                <label for="room_type" class="form-label">ประเภทห้อง</label>
                                <select name="room_type" class="form-select" id="room_type" required>
                                    <option value="แอร์">แอร์</option>
                                    <option value="พัดลม">พัดลม</option>
                                </select>
                            </div>

                            <!-- ค่าเช่า/เดือน -->
                            <div class="mb-3">
                                <label for="room_price" class="form-label">ค่าเช่า/เดือน</label>
                                <input type="number" name="room_price" class="form-control" id="room_price" required placeholder="กรุณากรอกค่าเช่า/เดือน" min="0">
                                <div class="form-text">กรอกตัวเลขเท่านั้น</div>
                            </div>

                            <!-- สถานะห้องพัก -->
                            <div class="mb-3">
                                <label for="room_status" class="form-label">สถานะห้องพัก</label>
                                <select name="room_status" class="form-select" id="room_status" required>
                                    <option value="ไม่มีผู้เช่า">ว่าง</option>
                                    <option value="มีผู้เช่า">มีผู้เช่า</option>
                                </select>
                            </div>

                            <!-- ปุ่มบันทึก -->
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">บันทึก</button>
                                <a href="admin_dashboard.php" class="btn btn-danger">ยกเลิก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
