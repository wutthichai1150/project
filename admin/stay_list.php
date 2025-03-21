<?php 
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

// ดึงข้อมูลห้องทั้งหมด
$room_query = "SELECT * FROM room";
$result_rooms = $conn->query($room_query);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบจัดการหอพัก</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  </head>
  <body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
      <?php include 'includes/sidebar.php'; ?>
      
      <main class="h-full overflow-y-auto flex-1">
        <div class="container px-6 mx-auto">
          <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">รายการเข้าพัก</h2>

          <!-- ตารางแสดงห้องทั้งหมด -->
          <div id="tableView" class="block">
            <?php
            if ($result_rooms && $result_rooms->num_rows > 0) {
                echo "<h2 id='rooms-all' class='text-lg font-semibold text-gray-700 dark:text-gray-300'>ห้องทั้งหมด</h2>";
                echo "<div class='bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto'>";
                echo "<table class='w-full'>";
                echo "<thead class='bg-gray-50 dark:bg-gray-700'>
                <tr>
                    <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap'>
                        <i class='fas fa-door-closed'></i> ห้อง
                    </th>
                    <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap'>
                        <i class='fas fa-tags'></i> ประเภท
                    </th>
                    <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap'>
                        <i class='fas fa-money-bill-wave'></i> ราคา
                    </th>
                    <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell'>
                        <i class='fas fa-info-circle'></i> สถานะ
                    </th>
                    <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap'>
                        <i class='fas fa-cog'></i> จัดการ
                    </th>
                </tr>
              </thead>";
                echo "<tbody class='bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700'>";

                // แสดงข้อมูลห้อง
                $result_rooms->data_seek(0);  // เริ่มต้นที่แถวแรก
                while ($room = $result_rooms->fetch_assoc()) {
                    $room_id = $room['room_id'];
                    $room_number = $room['room_number'];
                    $room_type = $room['room_type'];
                    $room_price = $room['room_price'];
                    $room_status = $room['room_status'];

                    echo "<tr>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>ห้อง {$room_number}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 '>{$room_type}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 '>{$room_price} บาท</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300 hidden sm:table-cell'>{$room_status}</td>
                        <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>
                            <a href='stay_history.php?room_id={$room_id}' class='text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mr-4'>
                                <i class='fas fa-history text-2xl'></i>
                            </a>

                            <a href='manage_room.php?room_id={$room_id}' class='text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300'>
                                <i class='fas fa-eye text-2xl'></i>
                            </a>
                        </td>
                    </tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-700 dark:text-gray-300'>ไม่พบข้อมูลห้องพัก</p>";
            }
            ?>
          </div>
        </div>
      </main>
    </div>
  </body>
</html>