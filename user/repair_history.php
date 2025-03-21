<?php
session_start();
include('../includes/db.php');
include('../includes/navbar_user.php');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['mem_user'])) {
    header("Location: login.php");
    exit();
}

$mem_user = $_SESSION['mem_user'];
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

$check_user_sql = "SELECT mem_fname, mem_lname FROM `member` WHERE mem_user = '$mem_user'";
$check_user_result = mysqli_query($conn, $check_user_sql);

if (!$check_user_result) {
    die("Query failed: " . mysqli_error($conn));
}

$user_data = mysqli_fetch_assoc($check_user_result);
$mem_fname = $user_data['mem_fname'];
$mem_lname = $user_data['mem_lname'];

if ($room_id) {
    $check_owner_sql = "SELECT s.room_id 
                        FROM stay s
                        JOIN `member` m ON s.mem_id = m.mem_id
                        WHERE s.room_id = '$room_id' AND m.mem_user = '$mem_user'";

    $check_owner_result = mysqli_query($conn, $check_owner_sql);

    if (!$check_owner_result) {
        die("Query failed: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($check_owner_result) == 0) {
        echo "คุณไม่มีสิทธิ์ดูข้อมูลของห้องนี้";
        exit();
    }

    $sql = "SELECT rr.repair_id, rr.room_id, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_date, rr.repair_state, r.room_number, rr.repair_image
    FROM repair_requests rr
    JOIN room r ON rr.room_id = r.room_id
    JOIN `member` m ON rr.repair_name = CONCAT(m.mem_fname, ' ', m.mem_lname)
    WHERE m.mem_user = '$mem_user'
    ORDER BY rr.repair_date DESC";

} else {
    $sql = "SELECT rr.repair_id, rr.room_id, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_date, rr.repair_state, r.room_number, rr.repair_image
            FROM repair_requests rr
            JOIN room r ON rr.room_id = r.room_id
            JOIN member m ON rr.repair_name = CONCAT(m.mem_fname, ' ', m.mem_lname)
            WHERE m.mem_fname = '$mem_fname' AND m.mem_lname = '$mem_lname'
            ORDER BY rr.repair_date DESC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการซ่อม</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h3 class="text-xl font-bold text-center mb-6">
        <i class="fas fa-tools"></i>
        ประวัติการซ่อมครุภัณฑ์
    </h3>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="overflow-x-auto">
            <table id="repairTable" class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm mt-4 text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">ห้อง</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">ผู้แจ้ง</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase hidden sm:table-cell">ประเภท</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase hidden sm:table-cell">ชื่อครุภัณฑ์</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">รายละเอียด</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">วันที่แจ้ง</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">สถานะ</th>
                        <th class="py-1 px-2 text-left font-medium text-gray-700 uppercase">รูปภาพ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-200">
                            <td class="py-1 px-2 text-gray-700"><?php echo $row['room_number']; ?></td>
                            <td class="py-1 px-2 text-gray-700"><?php echo $row['repair_name']; ?></td>
                            <td class="py-1 px-2 text-gray-700 hidden sm:table-cell"><?php echo $row['repair_type']; ?></td>
                            <td class="py-1 px-2 text-gray-700 hidden sm:table-cell"><?php echo $row['repair_eqm_name']; ?></td>
                            <td class="py-1 px-2 text-gray-700"><?php echo $row['repair_detail']; ?></td>
                            <td class="py-1 px-2 text-gray-700"><?php echo $row['repair_date']; ?></td>
                            <td class="py-1 px-2 text-gray-700">
                                <?php 
                                $status_class = '';
                                switch ($row['repair_state']) {
                                    case 'กำลังดำเนินการ':
                                        $status_class = 'text-yellow-500'; 
                                        break;
                                    case 'ซ่อมบำรุงเรียบร้อย':
                                        $status_class = 'text-green-500'; 
                                        break;
                                    case 'รอรับเรื่อง':
                                        $status_class = 'text-blue-500';
                                        break;
                                    default:
                                        $status_class = 'text-gray-700'; 
                                }
                                ?>
                                <span class="<?php echo $status_class; ?>"><?php echo $row['repair_state']; ?></span>
                            </td>
                            <td class="py-1 px-2 text-gray-700">
                                <?php if ($row['repair_image']): ?>
                                    <img src="../uploads/repair/<?php echo $row['repair_image']; ?>" alt="Image" 
                                        class="w-12 h-12 object-cover cursor-pointer" 
                                        onclick="openModal('../uploads/repair/<?php echo $row['repair_image']; ?>')" />
                                <?php else: ?>
                                    <span class="text-red-500">ไม่มีรูปภาพ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-600">ยังไม่มีข้อมูลการซ่อม</p>
    <?php endif; ?>
</div>

<!-- Modal for image view -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="closeModal()">
    <div class="bg-white rounded-lg overflow-hidden max-w-2xl w-full">
        <img src="" id="modalImage" class="w-full h-auto" alt="Expanded Image">
    </div>
</div>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script>
    // Initialize DataTables
    $(document).ready(function() {
        $('#repairTable').DataTable({
            paging: true, // Enable pagination
            searching: true, // Enable search
            ordering: true, // Enable sorting
            info: true, // Show table information
            responsive: true // Enable responsive design
        });
    });

    function openModal(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }
</script>

</body>
</html>