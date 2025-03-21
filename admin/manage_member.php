<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// ดึงข้อมูลผู้เข้าพักจากฐานข้อมูล
$sql = "SELECT * FROM `member`";
$result = $conn->query($sql);

// ตรวจสอบการลบข้อมูล
if (isset($_GET['delete_mem_id'])) {
    $mem_id = $_GET['delete_mem_id'];
    $delete_sql = "DELETE FROM `member` WHERE mem_id = $mem_id";
    if ($conn->query($delete_sql)) {
        $alert = [
            'status' => 'success',
            'message' => 'ลบข้อมูลผู้เข้าพักเรียบร้อย'
        ];
    } else {
        $alert = [
            'status' => 'error',
            'message' => 'ไม่สามารถลบข้อมูลได้'
        ];
    }
}

if (isset($_POST['update_member'])) {
    $mem_id = $_POST['mem_id'];
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname'];
    $mem_user = $_POST['mem_user'];
    $mem_password = $_POST['mem_password'];
    $mem_mail = $_POST['mem_mail'];
    $mem_phone = $_POST['mem_phone'];
    $mem_address = $_POST['mem_address'];
    $mem_id_card = $_POST['mem_id_card'];

    // อัปเดตรูปภาพบัตรประชาชน (ถ้ามี)
    $id_card_image = '';
    if (isset($_FILES['id_card_image']) && $_FILES['id_card_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['id_card_image']['name']);
        if (move_uploaded_file($_FILES['id_card_image']['tmp_name'], $target_file)) {
            $id_card_image = $target_file;
        }
    }

    // สร้างคำสั่ง SQL สำหรับอัปเดตข้อมูล
    $update_sql = "UPDATE `member` SET 
                   mem_fname = '$mem_fname', 
                   mem_lname = '$mem_lname', 
                   mem_user = '$mem_user', 
                   mem_password = '$mem_password', 
                   mem_mail = '$mem_mail', 
                   mem_phone = '$mem_phone', 
                   mem_address = '$mem_address', 
                   mem_id_card = '$mem_id_card'";

    // เพิ่มการอัปเดตรูปภาพถ้ามี
    if (!empty($id_card_image)) {
        $update_sql .= ", id_card_image = '$id_card_image'";
    }

    $update_sql .= " WHERE mem_id = $mem_id";

    if ($conn->query($update_sql)) {
        $alert = [
            'status' => 'success',
            'message' => 'อัปเดตข้อมูลเรียบร้อย'
        ];
    } else {
        $alert = [
            'status' => 'error',
            'message' => 'ไม่สามารถอัปเดตข้อมูลได้'
        ];
    }
}
if (isset($_POST['add_member'])) {
    $mem_fname = $_POST['mem_fname'];
    $mem_lname = $_POST['mem_lname'];
    $mem_user = $_POST['mem_user'];
    $mem_password = $_POST['mem_password'];
    $mem_mail = $_POST['mem_mail'];
    $mem_phone = $_POST['mem_phone'];
    $mem_address = $_POST['mem_address'];
    $mem_id_card = $_POST['mem_id_card'];

    // ตรวจสอบข้อมูลซ้ำ
    $check_sql = "SELECT * FROM `member` WHERE 
                  mem_fname = '$mem_fname' OR 
                  mem_lname = '$mem_lname' OR 
                  mem_user = '$mem_user' OR 
                  mem_mail = '$mem_mail' OR 
                  mem_id_card = '$mem_id_card'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // หากพบข้อมูลซ้ำ
        $alert = [
            'status' => 'error',
            'message' => 'ข้อมูลซ้ำกับที่มีอยู่ในระบบ กรุณาตรวจสอบอีกครั้ง'
        ];
    } else {
        // หากไม่พบข้อมูลซ้ำ
        // อัปโหลดรูปภาพบัตรประชาชน
        $id_card_image = '';
        if (isset($_FILES['id_card_image'])) {
            $target_dir = "../uploads/member";
            $target_file = $target_dir . basename($_FILES['id_card_image']['name']);
            if (move_uploaded_file($_FILES['id_card_image']['tmp_name'], $target_file)) {
                $id_card_image = $target_file;
            }
        }

        // เพิ่มข้อมูลลงในฐานข้อมูล
        $insert_sql = "INSERT INTO `member` (mem_fname, mem_lname, mem_user, mem_password, mem_mail, mem_phone, mem_address, mem_id_card, id_card_image) 
                       VALUES ('$mem_fname', '$mem_lname', '$mem_user', '$mem_password', '$mem_mail', '$mem_phone', '$mem_address', '$mem_id_card', '$id_card_image')";

        if ($conn->query($insert_sql)) {
            $alert = [
                'status' => 'success',
                'message' => 'เพิ่มผู้เข้าพักเรียบร้อย'
            ];
        } else {
            $alert = [
                'status' => 'error',
                'message' => 'ไม่สามารถเพิ่มผู้เข้าพักได้'
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้เข้าพัก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- ใช้ Flexbox เพื่อจัดวาง sidebar และเนื้อหาหลัก -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- เนื้อหาหลัก -->
        <div class="flex-1 p-6 overflow-y-auto">
            <!-- ส่งสถานะการอัปเดตผ่าน data-* attributes -->
            <div id="alertMessage" 
                 data-status="<?php echo isset($alert['status']) ? $alert['status'] : ''; ?>" 
                 data-message="<?php echo isset($alert['message']) ? $alert['message'] : ''; ?>">
            </div>

            <div class="flex items-center mb-6">
                    <h1 class="text-2xl font-semibold dark:text-gray-100">จัดการผู้เข้าพัก</h1>
                    <button onclick="openAddMemberModal()" class="flex items-center bg-transparent text-blue-500 p-2 text-sm sm:text-base rounded-md hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white transition-all duration-300 ml-4">
                        <i class="fas fa-user-plus mr-2"></i> เพิ่มผู้เข้าพัก
                    </button>
                </div>
                <?php
// จำนวนแถวที่จะแสดงในแต่ละหน้า
$items_per_page = 10;

// คำนวณหน้าปัจจุบันจากค่า GET 'page' หากไม่มีจะใช้หน้าแรก (หน้า 1)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// คำนวณจำนวนแถวทั้งหมด
$total_items_result = $conn->query("SELECT COUNT(*) as total FROM `member`");
$total_items = $total_items_result->fetch_assoc()['total'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_items / $items_per_page);

// ดึงข้อมูลจากฐานข้อมูลตามหน้า
$sql = "SELECT * FROM `member` LIMIT $offset, $items_per_page";
$result = $conn->query($sql);
?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ลำดับ</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ชื่อ</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">นามสกุล</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">เบอร์โทร</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">ที่อยู่</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <?php
            if ($result->num_rows > 0) {
                $counter = $offset + 1; // เริ่มนับจากค่าที่เป็น offset
                while ($row = $result->fetch_assoc()) {
                    // เติมเลขศูนย์ให้ mem_id มีความยาว 3 หลัก
                    $formatted_id = str_pad($counter, 3, '0', STR_PAD_LEFT);
                    echo "<tr>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$formatted_id}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$row['mem_fname']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$row['mem_lname']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 hidden sm:table-cell'>{$row['mem_phone']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 hidden sm:table-cell'>{$row['mem_address']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>
                        <button onclick='openViewModal(" . json_encode($row) . ")' class='text-green-500 hover:text-green-700 dark:text-green-300 dark:hover:text-green-500'>
                            <i class='fas fa-eye text-2xl'></i>
                        </button>
                        <button onclick='openEditModal(" . json_encode($row) . ")' class='text-blue-500 hover:text-blue-700 ml-2 dark:text-blue-300 dark:hover:text-blue-500'>
                            <i class='fas fa-edit text-2xl'></i>
                        </button>
                        <button onclick='confirmDelete({$row['mem_id']})' class='text-red-500 hover:text-red-700 ml-2 dark:text-red-300 dark:hover:text-red-500'>
                            <i class='fas fa-trash text-2xl'></i>
                        </button>
                    </td>";
                    echo "</tr>";
                    $counter++; // เพิ่มค่าลำดับ
                }
            } else {
                echo "<tr><td colspan='6' class='px-4 py-4 text-center text-gray-500 dark:text-gray-300'>ไม่มีข้อมูลผู้เข้าพัก</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="flex justify-center mt-4">
    <nav class="inline-flex rounded-md shadow-sm">
        <a href="?page=1" class="px-4 py-2 mx-1 text-sm font-medium text-gray-500 dark:text-gray-300 bg-white border border-gray-300 rounded-l-md hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
            &laquo; แรก
        </a>
        <a href="?page=<?php echo max($current_page - 1, 1); ?>" class="px-4 py-2 mx-1 text-sm font-medium text-gray-500 dark:text-gray-300 bg-white border border-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
            &lsaquo; ก่อนหน้า
        </a>
        <a href="?page=<?php echo min($current_page + 1, $total_pages); ?>" class="px-4 py-2 mx-1 text-sm font-medium text-gray-500 dark:text-gray-300 bg-white border border-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
            ถัดไป &rsaquo;
        </a>
        <a href="?page=<?php echo $total_pages; ?>" class="px-4 py-2 mx-1 text-sm font-medium text-gray-500 dark:text-gray-300 bg-white border border-gray-300 rounded-r-md hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600">
            สุดท้าย &raquo;
        </a>
    </nav>
</div>


            <!-- Modal สำหรับเพิ่มผู้เข้าพัก -->
                        <div id="addMemberModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                            <div class="bg-white dark:bg-gray-800 w-full max-w-full sm:max-w-md p-4 sm:p-6 rounded-lg shadow-md">
                                <h3 class="text-lg sm:text-xl font-semibold mb-4 dark:text-gray-100">เพิ่มผู้เข้าพัก</h3>
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อ</label>
                                            <input type="text" name="mem_fname" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">นามสกุล</label>
                                            <input type="text" name="mem_lname" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อผู้ใช้</label>
                                            <input type="text" name="mem_user" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รหัสผ่าน</label>
                                            <div class="relative">
                                                <input type="password" name="mem_password" id="memPassword" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                                <button type="button" onclick="togglePasswordVisibility('memPassword')" class="absolute inset-y-0 right-0 px-3 py-2 flex items-center text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-eye"></i> 
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">อีเมล</label>
                                            <input type="email" name="mem_mail" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เบอร์โทร</label>
                                            <input type="text" name="mem_phone" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ที่อยู่</label>
                                            <textarea name="mem_address" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เลขบัตรประชาชน</label>
                                            <input type="text" name="mem_id_card" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รูปภาพบัตรประชาชน</label>
                                            <input type="file" name="id_card_image" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end space-x-4">
                                        <button type="button" onclick="closeAddMemberModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">ยกเลิก</button>
                                        <button type="submit" name="add_member" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
                                    </div>
                                </form>
                            </div>
                        </div>

            <!-- Modal สำหรับดูข้อมูล -->
                <div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                    <div class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold mb-4 dark:text-gray-100">ข้อมูลผู้เข้าพัก</h3>
                        <div id="viewModalContent" class="space-y-4 text-gray-700 dark:text-gray-100">
                            <!-- ข้อมูลจะถูกโหลดโดย JavaScript -->
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button onclick="closeViewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">ปิด</button>
                        </div>
                    </div>
                </div>

                <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 w-full h-screen sm:max-w-md sm:h-auto sm:rounded-lg shadow-md overflow-y-auto">
    <div class="p-4 sm:p-6 pt-16 sm:pt-6 max-h-xs overflow-y-auto"> 
    <h3 class="text-lg sm:text-xl font-semibold mb-4 dark:text-gray-100">แก้ไขข้อมูลผู้เข้าพัก</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="mem_id" id="editMemId">
                <div class="space-y-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อ</label>
                        <input type="text" name="mem_fname" id="editMemFname" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">นามสกุล</label>
                        <input type="text" name="mem_lname" id="editMemLname" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ชื่อผู้ใช้</label>
                        <input type="text" name="mem_user" id="editMemUser" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รหัสผ่าน</label>
                        <div class="relative">
                            <input type="password" name="mem_password" id="editMemPassword" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                            <button type="button" onclick="togglePasswordVisibility('editMemPassword')" class="absolute inset-y-0 right-0 px-3 py-2 flex items-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">อีเมล</label>
                        <input type="email" name="mem_mail" id="editMemMail" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เบอร์โทร</label>
                        <input type="text" name="mem_phone" id="editMemPhone" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ที่อยู่</label>
                        <textarea name="mem_address" id="editMemAddress" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">เลขบัตรประชาชน</label>
                        <input type="text" name="mem_id_card" id="editMemIdCard" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">รูปภาพบัตรประชาชน</label>
                        <input type="file" name="id_card_image" id="editMemIdCardImage" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 border rounded-md">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">ยกเลิก</button>
                    <button type="submit" name="update_member" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ฟังก์ชันแสดง SweetAlert2 จากสถานะที่ส่งมาจาก PHP
        function showAlert() {
            const alertElement = document.getElementById('alertMessage');
            const status = alertElement.getAttribute('data-status');
            const message = alertElement.getAttribute('data-message');

            if (status && message) {
                Swal.fire({
                    title: status === 'success' ? 'สำเร็จ!' : 'เกิดข้อผิดพลาด!',
                    text: message,
                    icon: status,
                    confirmButtonText: 'ตกลง',
                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#000000',
                }).then(() => {
                    if (status === 'success') {
                        window.location.href = 'manage_member.php';
                    }
                });
            }
        }

        // เรียกใช้ฟังก์ชันแสดง SweetAlert2 เมื่อหน้าเว็บโหลดเสร็จ
        document.addEventListener('DOMContentLoaded', showAlert);

        // ฟังก์ชันเปิด Modal ดูข้อมูล
        function openViewModal(member) {
            document.getElementById('viewModalContent').innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <p><strong>รหัสสมาชิก:</strong></p>
                    <p>${member.mem_id}</p>
                    <p><strong>ชื่อ:</strong></p>
                    <p>${member.mem_fname}</p>
                    <p><strong>นามสกุล:</strong></p>
                    <p>${member.mem_lname}</p>
                    <p><strong>ชื่อผู้ใช้:</strong></p>
                    <p>${member.mem_user}</p>
                    <p><strong>รหัสผ่าน:</strong></p>
                    <p>${member.mem_password}</p>
                    <p><strong>อีเมล:</strong></p>
                    <p>${member.mem_mail}</p>
                    <p><strong>เบอร์โทร:</strong></p>
                    <p>${member.mem_phone}</p>
                    <p><strong>ที่อยู่:</strong></p>
                    <p>${member.mem_address}</p>
                    <p><strong>เลขบัตรประชาชน:</strong></p>
                    <p>${member.mem_id_card}</p>
                    <p><strong>รูปภาพบัตรประชาชน:</strong></p>
                    <img src="${member.id_card_image}" alt="รูปภาพบัตรประชาชน" class="w-48 h-auto">
                </div>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        }

        // ฟังก์ชันปิด Modal ดูข้อมูล
        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        // ฟังก์ชันเปิด Modal แก้ไขข้อมูล
        function openEditModal(member) {
            document.getElementById('editMemId').value = member.mem_id;
            document.getElementById('editMemFname').value = member.mem_fname;
            document.getElementById('editMemLname').value = member.mem_lname;
            document.getElementById('editMemUser').value = member.mem_user;
            document.getElementById('editMemPassword').value = member.mem_password;
            document.getElementById('editMemMail').value = member.mem_mail;
            document.getElementById('editMemPhone').value = member.mem_phone;
            document.getElementById('editMemAddress').value = member.mem_address;
            document.getElementById('editMemIdCard').value = member.mem_id_card;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // ฟังก์ชันปิด Modal แก้ไขข้อมูล
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // ฟังก์ชันยืนยันการลบ
        function confirmDelete(memId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#000000',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `manage_member.php?delete_mem_id=${memId}`;
                }
            });
        }

        // ฟังก์ชันเปิด Modal เพิ่มผู้เข้าพัก
        function openAddMemberModal() {
            document.getElementById('addMemberModal').classList.remove('hidden');
        }

        // ฟังก์ชันปิด Modal เพิ่มผู้เข้าพัก
        function closeAddMemberModal() {
            document.getElementById('addMemberModal').classList.add('hidden');
        }
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = passwordInput.nextElementSibling.querySelector('i');

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash'); 
            } else {
                passwordInput.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye'); 
            }
        }
    </script>
</body>
</html>