<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// ตรวจสอบการลบรายการแจ้งซ่อม
if (isset($_GET['delete_repair_id'])) {
    $repair_id = $_GET['delete_repair_id'];
    $delete_query = "DELETE FROM repair_requests WHERE repair_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $repair_id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'ลบรายการแจ้งซ่อมเรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['alert'] = [
            'status' => 'error',
            'message' => 'ไม่สามารถลบรายการได้: ' . $stmt->error
        ];
    }

    $stmt->close();
    header("Location: manage_repair.php");
    exit();
}

// ตัวกรองสถานะและเดือนปี
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';

// Pagination
$limit = 10; // จำนวนรายการต่อหน้า
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลรายการแจ้งซ่อม
$query = "SELECT r.room_number, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_image, rr.repair_date, rr.repair_state, rr.repair_id
          FROM repair_requests rr
          JOIN room r ON rr.room_id = r.room_id
          WHERE 1=1";

if (!empty($filter_status)) {
    $query .= " AND rr.repair_state = '$filter_status'";
}

if (!empty($filter_month)) {
    $query .= " AND DATE_FORMAT(rr.repair_date, '%Y-%m') = '$filter_month'";
}

$query .= " ORDER BY rr.repair_date DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}

if (!$stmt->execute()) {
    die('Error executing query: ' . $stmt->error);
}

$result = $stmt->get_result();

if ($result === false) {
    die('Error fetching result: ' . $conn->error);
}

// นับจำนวนรายการทั้งหมดสำหรับ Pagination
$count_query = "SELECT COUNT(*) as total FROM repair_requests rr WHERE 1=1";

if (!empty($filter_status)) {
    $count_query .= " AND rr.repair_state = '$filter_status'";
}

if (!empty($filter_month)) {
    $count_query .= " AND DATE_FORMAT(rr.repair_date, '%Y-%m') = '$filter_month'";
}

$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// ตรวจสอบข้อความแจ้งเตือน
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
} else {
    $alert = null;
}
?>

<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรายการแจ้งซ่อม</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

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

            <h1 class="text-2xl font-semibold mb-6 dark:text-gray-100">จัดการรายการแจ้งซ่อม</h1>

            <!-- ตัวกรองสถานะและเดือนปี -->
                    <form method="GET" action="manage_repair.php" class="mb-6">
                        <div class="flex space-x-4">
                            <!-- ตัวกรองสถานะ -->
                            <select name="status" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <option value="">ทั้งหมด</option>
                                <option value="รอรับเรื่อง" <?php echo $filter_status === 'รอรับเรื่อง' ? 'selected' : ''; ?>>รอรับเรื่อง</option>
                                <option value="กำลังดำเนินการ" <?php echo $filter_status === 'กำลังดำเนินการ' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                <option value="ซ่อมบำรุงเรียบร้อย" <?php echo $filter_status === 'ซ่อมบำรุงเรียบร้อย' ? 'selected' : ''; ?>>ซ่อมบำรุงเรียบร้อย</option>
                            </select>

                            <!-- ตัวกรองเดือน -->
                            <select name="month" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <option value="">ทั้งหมด</option>
                                <?php
                                // ฟังก์ชันแปลงเดือนเป็นภาษาไทย
                                function getThaiMonth($month) {
                                    $thai_months = [
                                        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                    ];
                                    return $thai_months[$month];
                                }

                                for ($i = 0; $i < 12; $i++) {
                                    $month = date('Y-m', strtotime("-$i months"));
                                    $month_num = date('n', strtotime("-$i months")); // ดึงเลขเดือน (1-12)
                                    $year = date('Y', strtotime("-$i months")); // ดึงปี
                                    $month_th = getThaiMonth($month_num) . ' ' . $year; // แปลงเป็นภาษาไทย
                                    echo "<option value='$month' " . ($filter_month === $month ? 'selected' : '') . ">$month_th</option>";
                                }
                                ?>
                            </select>

                            <!-- ปุ่มกรอง -->
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700">
                                ค้นหา
                            </button>
                        </div>
                    </form>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <!-- ส่วนหัวตาราง -->
        <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-door-closed"></i> หมายเลขห้อง
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-couch"></i> ชื่อครุภัณฑ์
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-exclamation-circle"></i> หัวข้อปัญหา
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-calendar-day"></i> วันที่แจ้งซ่อม
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-info-circle"></i> สถานะ
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <i class="fas fa-cog"></i> จัดการ
                    </th>
                </tr>
            </thead>
        <tbody class="bg-white dark:bg-gray-800">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = '';
                    switch ($row['repair_state']) {
                        case 'รอรับเรื่อง':
                            $status_class = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                            break;
                        case 'กำลังดำเนินการ':
                            $status_class = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                            break;
                        case 'ซ่อมบำรุงเรียบร้อย':
                            $status_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                            break;
                        default:
                            $status_class = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                            break;
                    }

                    echo "<tr class='bg-gray-50 dark:bg-gray-700'>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$row['room_number']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$row['repair_eqm_name']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>{$row['repair_detail']}</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100'>" . date("d/m/Y", strtotime($row['repair_date'])) . "</td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm'>
                            <span class='px-2 py-1 rounded-full text-xs font-medium {$status_class}'>{$row['repair_state']}</span>
                          </td>";
                    echo "<td class='px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 flex space-x-2'>
                            <button onclick='openViewModal(" . json_encode($row) . ")' class='text-green-500 hover:text-green-700 dark:text-green-300 dark:hover:text-green-500'>
                                <i class='fas fa-eye text-xl'></i>
                            </button>
                            <button onclick='openEditModal(" . json_encode($row) . ")' class='text-blue-500 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-500'>
                                <i class='fas fa-edit text-xl'></i>
                            </button>
                            <button onclick='confirmDelete({$row['repair_id']})' class='text-red-500 hover:text-red-700 dark:text-red-300 dark:hover:text-red-500'>
                                <i class='fas fa-trash text-xl'></i>
                            </button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='px-3 py-3 text-center text-gray-500 dark:text-gray-300'>ไม่มีข้อมูลรายการแจ้งซ่อม</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

            <!-- Pagination -->
            <div class="flex justify-center mt-6">
                <nav class="inline-flex rounded-md shadow-sm">
                    <?php if ($page > 1): ?>
                        <a href="manage_repair.php?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&month=<?php echo $filter_month; ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100">ก่อนหน้า</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="manage_repair.php?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&month=<?php echo $filter_month; ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 <?php echo $i === $page ? 'bg-blue-500 text-white' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="manage_repair.php?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&month=<?php echo $filter_month; ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100">ถัดไป</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับดูข้อมูล -->
    <div id="viewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">ดูรายละเอียดการแจ้งซ่อม</h3>
            <div id="viewModalContent" class="space-y-4">
                <!-- ข้อมูลจะถูกโหลดโดย JavaScript -->
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeViewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">ปิด</button>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแก้ไขข้อมูล -->
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">แก้ไขสถานะการแจ้งซ่อม</h3>
            <form method="POST" action="update_repair_status.php">
                <input type="hidden" name="repair_id" id="editRepairId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">สถานะการซ่อม</label>
                        <select name="repair_state" id="editRepairState" class="w-full px-3 py-2 mt-1 text-sm text-gray-700 bg-gray-100 border rounded-md" required>
                            <option value="รอรับเรื่อง">รอรับเรื่อง</option>
                            <option value="กำลังดำเนินการ">กำลังดำเนินการ</option>
                            <option value="ซ่อมบำรุงเรียบร้อย">ซ่อมบำรุงเรียบร้อย</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">ยกเลิก</button>
                    <button type="submit" name="update_repair" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">บันทึก</button>
                </div>
            </form>
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
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    if (status === 'success') {
                        window.location.href = 'manage_repair.php';
                    }
                });
            }
        }

        // เรียกใช้ฟังก์ชันแสดง SweetAlert2 เมื่อหน้าเว็บโหลดเสร็จ
        document.addEventListener('DOMContentLoaded', showAlert);

        // ฟังก์ชันเปิด Modal ดูข้อมูล
        function openViewModal(repair) {
            document.getElementById('viewModalContent').innerHTML = `
                <p><strong>หมายเลขห้อง:</strong> ${repair.room_number}</p>
                <p><strong>ชื่อผู้แจ้ง:</strong> ${repair.repair_name}</p>
                <p><strong>ประเภทครุภัณฑ์:</strong> ${repair.repair_type}</p>
                <p><strong>ชื่อครุภัณฑ์:</strong> ${repair.repair_eqm_name}</p>
                <p><strong>หัวข้อปัญหา:</strong> ${repair.repair_detail}</p>
                <p><strong>วันที่แจ้งซ่อม:</strong> ${new Date(repair.repair_date).toLocaleString()}</p>
                <p><strong>สถานะ:</strong> ${repair.repair_state}</p>
                <img src="../uploads/repair/${repair.repair_image}" alt="Repair Image" class="w-64 h-64 object-cover rounded-lg">
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        }

        // ฟังก์ชันปิด Modal ดูข้อมูล
        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        // ฟังก์ชันเปิด Modal แก้ไขข้อมูล
        function openEditModal(repair) {
            document.getElementById('editRepairId').value = repair.repair_id;
            document.getElementById('editRepairState').value = repair.repair_state;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // ฟังก์ชันปิด Modal แก้ไขข้อมูล
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // ฟังก์ชันยืนยันการลบ
        function confirmDelete(repairId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `manage_repair.php?delete_repair_id=${repairId}`;
                }
            });
        }
    </script>
</body>
</html>