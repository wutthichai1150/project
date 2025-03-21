<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // ดึงข้อมูลห้องพัก
    $query = "SELECT room_id, room_number, room_type, room_price, room_status FROM room WHERE room_id = $room_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $room = $result->fetch_assoc();
    } else {
        die("ไม่พบข้อมูลห้องพัก");
    }

    // ดึงข้อมูลอุปกรณ์ทั้งหมด
    $equipment_query = "SELECT * FROM equipment_detail";
    $equipment_result = $conn->query($equipment_query);

    // ดึงอุปกรณ์ที่ถูกเลือกไว้แล้วสำหรับห้องพักนี้
    $selected_equipment_query = "SELECT eqm_id FROM room_equipment WHERE room_id = $room_id";
    $selected_equipment_result = $conn->query($selected_equipment_query);
    $selected_equipment = [];
    while ($row = $selected_equipment_result->fetch_assoc()) {
        $selected_equipment[] = $row['eqm_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขห้องพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900">

<div class="flex h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 p-6 overflow-y-auto">
        <div class="max-w-2xl mx-auto bg-gray-100 dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">แก้ไขห้องพัก</h1>
            <form id="edit-room-form" action="update_room.php" method="POST">
                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">

                <!-- ฟิลด์ข้อมูลห้องพัก -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">หมายเลขห้อง</label>
                    <input type="text" name="room_number" value="<?php echo $room['room_number']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ประเภทห้อง</label>
                    <input type="text" name="room_type" value="<?php echo $room['room_type']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ราคา</label>
                    <input type="text" name="room_price" value="<?php echo $room['room_price']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">สถานะ</label>
                    <select name="room_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="ว่าง" <?php echo ($room['room_status'] == 'ว่าง') ? 'selected' : ''; ?>>ว่าง</option>
                        <option value="มีผู้เช่า" <?php echo ($room['room_status'] == 'มีผู้เช่า') ? 'selected' : ''; ?>>มีผู้เช่า</option>
                        <option value="อยู่ระหว่างปรับปรุง" <?php echo ($room['room_status'] == 'อยู่ระหว่างปรับปรุง') ? 'selected' : ''; ?>>อยู่ระหว่างปรับปรุง</option>
                    </select>
                </div>

                <!-- ส่วนของอุปกรณ์ -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">อุปกรณ์</label>
                    <div class="mt-1 grid grid-cols-2 gap-4">
                        <?php while ($equipment = $equipment_result->fetch_assoc()) : ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="equipment[]" value="<?php echo $equipment['eqm_id']; ?>"
                                    <?php echo in_array($equipment['eqm_id'], $selected_equipment) ? 'checked' : ''; ?>
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 dark:text-gray-100"><?php echo $equipment['eqm_name']; ?></span>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- ปุ่มบันทึกและลบ -->
                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        บันทึก
                    </button>
                    <button type="button" id="delete-room" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                        ลบ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // JavaScript สำหรับการบันทึกและลบ
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();

        fetch('update_room.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: data.message,
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = 'admin_dashboard.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: data.message,
                    confirmButtonText: 'ตกลง'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                confirmButtonText: 'ตกลง'
            });
        });
    });

    document.getElementById('delete-room').addEventListener('click', function () {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "การลบห้องพักจะไม่สามารถย้อนกลับได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('update_room.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        'delete_room': 'true',
                        'room_id': '<?php echo $room['room_id']; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบห้องพักสำเร็จ!',
                            text: data.message,
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            window.location.href = 'admin_dashboard.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด!',
                            text: data.message,
                            confirmButtonText: 'ตกลง'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        confirmButtonText: 'ตกลง'
                    });
                });
            }
        });
    });
</script>
</body>
</html>