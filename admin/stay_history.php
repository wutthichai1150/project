<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// รับ room_id จาก URL
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
} else {
    header("Location: stay_list.php");
    exit();
}

// ลบข้อมูลการเข้าพัก
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM stay WHERE stay_id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $delete_id);
    $stmt_delete->execute();
    header("Location: " . $_SERVER['PHP_SELF'] . "?room_id=" . $room_id);
    exit();
}

// แก้ไขข้อมูลการเข้าพัก
if (isset($_POST['edit_stay'])) {
    $stay_id = $_POST['stay_id'];
    $stay_start_date = $_POST['stay_start_date'] ?: '-';
    $stay_end_date = $_POST['stay_end_date'] ?: '-';
    $stay_deposit = $_POST['stay_deposit'] ?: 'NULL'; // ถ้าไม่มีค่ามัดจำให้ส่ง NULL

    // ใช้ SQL ปรับปรุงข้อมูล
    $update_query = "UPDATE stay SET stay_start_date = ?, stay_end_date = ?, stay_deposit = ? WHERE stay_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sssi", $stay_start_date, $stay_end_date, $stay_deposit, $stay_id);
    $stmt_update->execute();
    header("Location: " . $_SERVER['PHP_SELF'] . "?room_id=" . $room_id);
    exit();
}

// ดึงข้อมูลหมายเลขห้องจากฐานข้อมูล
$room_query = "SELECT room_number FROM room WHERE room_id = ?";
$stmt_room = $conn->prepare($room_query);
$stmt_room->bind_param("i", $room_id);
$stmt_room->execute();
$result_room = $stmt_room->get_result();

$room_number = '';
if ($result_room && $result_room->num_rows > 0) {
    $room_data = $result_room->fetch_assoc();
    $room_number = $room_data['room_number'];
}

// ดึงข้อมูลการเข้าพักตาม room_id
$stay_query = "SELECT s.stay_id, s.room_id, s.mem_id, s.stay_start_date, s.stay_end_date, s.stay_deposit, m.mem_fname, m.mem_lname
               FROM stay s
               JOIN `member` m ON s.mem_id = m.mem_id
               WHERE s.room_id = ?";
$stmt = $conn->prepare($stay_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result_stay = $stmt->get_result();
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ประวัติการเข้าพัก</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
  </head>
  <body>
  <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>

    <main class="h-full overflow-y-auto">
        <div class="container px-6 mx-auto grid">
            <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">ประวัติการเข้าพักของห้องหมายเลข: <?php echo $room_number; ?></h2>

            <?php
            if ($result_stay && $result_stay->num_rows > 0) {
                echo "<div class='bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto'>";
                echo "<table class='w-full'>";
                echo "<thead class='bg-gray-50 dark:bg-gray-700'>
        <tr>
            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>
                <i class='fas fa-user'></i> ชื่อผู้เข้าพัก
            </th>
            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>
                <i class='fas fa-calendar-day'></i> วันที่เริ่มเข้าพัก
            </th>
            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>
                <i class='fas fa-calendar-times'></i> วันที่สิ้นสุดการเข้าพัก
            </th>
            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>
                <i class='fas fa-money-check-alt'></i> มัดจำ
            </th>
            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>
                <i class='fas fa-cog'></i> จัดการ
            </th>
        </tr>
      </thead>";
                echo "<tbody class='bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700'>";

                // แสดงประวัติการเข้าพัก
                while ($stay = $result_stay->fetch_assoc()) {
                    $stay_id = $stay['stay_id'];
                    $guest_name = $stay['mem_fname'] . ' ' . $stay['mem_lname'];
                    $stay_start_date = $stay['stay_start_date'] == '-' ? '-' : $stay['stay_start_date'];
                    $stay_end_date = $stay['stay_end_date'] == '-' ? '-' : $stay['stay_end_date'];
                    $stay_deposit = $stay['stay_deposit'] ? $stay['stay_deposit'] : 'ไม่มีค่ามัดจำ';

                    echo "<tr>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>{$guest_name}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 '>{$stay_start_date}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 '>{$stay_end_date}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 '>{$stay_deposit}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>
                            <button onclick='openEditModal({$stay_id}, \"{$stay_start_date}\", \"{$stay_end_date}\", \"{$stay_deposit}\")' class='text-blue-500 hover:text-blue-700'>แก้ไข</button> | 
                            <button onclick='openDeleteModal({$stay_id})' class='text-red-500 hover:text-red-700'>ลบ</button>
                        </td>
                      </tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-700 dark:text-gray-300'>ไม่มีประวัติการเข้าพักสำหรับห้องนี้</p>";
            }
            ?>
        </div>
    </main>
</div>

    <!-- Modal แก้ไข -->
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full sm:w-1/2 md:w-1/3 mx-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">แก้ไขข้อมูลการเข้าพัก</h3>
        <form method="POST">
            <input type="hidden" id="edit_stay_id" name="stay_id" />
            <div class="mb-4">
                <label for="stay_start_date" class="block text-sm text-gray-600 dark:text-gray-300">วันที่เริ่มเข้าพัก</label>
                <input type="date" id="stay_start_date" name="stay_start_date" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div class="mb-4">
                <label for="stay_end_date" class="block text-sm text-gray-600 dark:text-gray-300">วันที่สิ้นสุดการเข้าพัก</label>
                <input type="date" id="stay_end_date" name="stay_end_date" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div class="mb-4">
                <label for="stay_deposit" class="block text-sm text-gray-600 dark:text-gray-300">มัดจำ</label>
                <input type="number" id="stay_deposit" name="stay_deposit" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div class="mt-4 flex justify-end">
                <button onclick="closeEditModal()" type="button" class="text-gray-500 dark:text-gray-300 px-4 py-2 mr-2">ยกเลิก</button>
                <button type="submit" name="edit_stay" class="bg-blue-500 text-white px-4 py-2 rounded">บันทึก</button>
            </div>
        </form>
    </div>
</div>

    <!-- Modal ลบ -->
    <div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full sm:w-1/2 md:w-1/3 mx-4">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">ยืนยันการลบข้อมูล</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลการเข้าพักนี้?</p>
        <div class="mt-4 flex justify-end">
          <button onclick="closeDeleteModal()" class="text-gray-500 dark:text-gray-300 px-4 py-2 mr-2">ยกเลิก</button>
          <button id="deleteButton" class="bg-red-500 text-white px-4 py-2">ลบ</button>
        </div>
      </div>
    </div>

    <script>
      function openEditModal(stayId, startDate, endDate, deposit) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('edit_stay_id').value = stayId;
        document.getElementById('stay_start_date').value = startDate === '-' ? '' : startDate;
        document.getElementById('stay_end_date').value = endDate === '-' ? '' : endDate;
        document.getElementById('stay_deposit').value = deposit === 'ไม่มีค่ามัดจำ' ? '' : deposit;
      }

      function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
      }

      function openDeleteModal(stayId) {
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteButton').onclick = function() {
          window.location.href = '?room_id=<?php echo $room_id; ?>&delete_id=' + stayId;
        };
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
      }
    </script>
  </body>
</html>
