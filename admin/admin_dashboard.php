<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

if (isset($_SESSION['ad_user'])) {
    $ad_user = $_SESSION['ad_user'];

    // ดึงข้อมูลห้องพักพร้อมกรองสถานะ
    $room_status_filter = isset($_GET['room_status']) ? $_GET['room_status'] : '';
    $query_rooms = "SELECT room_id, room_number, room_type, room_price, room_status FROM room";
    if (!empty($room_status_filter)) {
        $query_rooms .= " WHERE room_status = '$room_status_filter'";
    }
    $result_rooms = $conn->query($query_rooms);

    // ดึงข้อมูลผู้ดูแลระบบ
    $query = "SELECT ad_fname, ad_lname, ad_user FROM admin WHERE ad_user = '$ad_user'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $ad_fname = $admin['ad_fname'];
        $ad_lname = $admin['ad_lname'];
        $ad_user = $admin['ad_user'];
    } else {
        echo "ไม่สามารถดึงข้อมูลโปรไฟล์ได้.";
    }
} else {
    echo "กรุณาล็อกอิน.";
}
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบจัดการหอพัก</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="./assets/js/init-alpine.js"></script>
</head>
<body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <?php include 'includes/sidebar.php'; ?>

        <main class="h-full overflow-y-auto">
            <div class="container px-6 mx-auto grid">
                <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Dashboard</h2>

                <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
                        <?php
                        $cards = [
                            ['query' => "SELECT COUNT(*) as member_count FROM `member`", 'icon' => 'fas fa-users', 'color' => 'orange', 'title' => 'ผู้เข้าพัก', 'unit' => 'คน'],
                            ['query' => "SELECT SUM(pay_total) as total_payment FROM payments", 'icon' => 'fas fa-credit-card', 'color' => 'green', 'title' => 'การชำระเงิน', 'unit' => ' bath'],
                            ['query' => "SELECT COUNT(*) as room_count FROM room", 'icon' => 'fas fa-bed', 'color' => 'blue', 'title' => 'ห้องพัก', 'unit' => 'ห้อง'],
                            ['query' => "SELECT COUNT(*) as repair_count FROM repair_requests", 'icon' => 'fa-solid fa-wrench', 'color' => 'teal', 'title' => 'การแจ้งซ่อม', 'unit' => 'รายการ']
                        ];

                        foreach ($cards as $card) {
                            $result = $conn->query($card['query']);
                            $data = $result ? $result->fetch_assoc() : null;
                            $count = $data ? reset($data) : 'ไม่สามารถดึงข้อมูลได้';
                            echo "
                            <div class='flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800'>
                                <div class='p-3 mr-4 text-{$card['color']}-500 bg-{$card['color']}-100 rounded-full dark:text-{$card['color']}-100 dark:bg-{$card['color']}-500'>
                                    <i class='{$card['icon']} text-3xl'></i> <!-- ปรับขนาดไอคอนให้ใหญ่ขึ้น -->
                                </div>
                                <div>
                                    <p class='mb-2 text-sm font-medium text-gray-600 dark:text-gray-400'>{$card['title']}</p>
                                    <p class='text-lg font-semibold text-gray-700 dark:text-gray-200'>{$count} {$card['unit']}</p>
                                </div>
                            </div>
                            ";
                        }
                        ?>
                    </div>


                <!-- ปุ่มสลับโหมด -->
                <div class="flex justify-end mb-6">
                    <button id="toggleViewBtn" class="text-lg font-semibold text-gray-700 dark:text-gray-200 transition-opacity">
                        <i class="fas fa-table"></i> Viewlist
                    </button>
                </div>

                <!-- โหมดการ์ด -->
                <div id="cardView">
                    <?php
                    if ($result_rooms && $result_rooms->num_rows > 0) {
                        echo "<div class='flex justify-between items-center mb-4'>
                                <h2 id='rooms-all' class='text-lg font-semibold text-gray-700 dark:text-gray-200'>ห้องทั้งหมด</h2>
                                <form method='GET' class='flex items-center space-x-2'>
                                    <label class='text-gray-700 dark:text-gray-300'>กรองสถานะห้อง:</label>
                                    <select name='room_status' onchange='this.form.submit()' class='border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded'>
                                        <option value=''>ทั้งหมด</option>
                                        <option value='ว่าง' " . ($room_status_filter == 'ว่าง' ? 'selected' : '') . ">ว่าง</option>
                                        <option value='มีผู้เช่า' " . ($room_status_filter == 'มีผู้เช่า' ? 'selected' : '') . ">มีผู้เช่า</option>
                                        <option value='อยู่ระหว่างปรับปรุง' " . ($room_status_filter == 'อยู่ระหว่างปรับปรุง' ? 'selected' : '') . ">อยู่ระหว่างปรับปรุง</option>
                                    </select>
                                </form>
                              </div>";
                        echo "<div class='grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4'>";

                        while ($room = $result_rooms->fetch_assoc()) {
                            $room_id = $room['room_id'];
                            $room_number = $room['room_number'];
                            $room_type = $room['room_type'];
                            $room_price = $room['room_price'];
                            $room_status = $room['room_status'];

                            
                            $room_status_color = '';
                            if ($room_status == 'ว่าง') {
                                $room_status_color = 'text-green-500';
                            } elseif ($room_status == 'มีผู้เช่า') {
                                $room_status_color = 'text-red-500';
                            } elseif ($room_status == 'อยู่ระหว่างปรับปรุง') {
                                $room_status_color = 'text-yellow-500';
                            } else {
                                $room_status_color = 'text-gray-500';
                            }

                            echo "
                            <div class='flex items-center p-4 bg-white rounded-lg shadow-md border border-gray-300 dark:bg-gray-800 dark:border-gray-600'>
                                <div class='p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500'>
                                    <i class='fas fa-bed'></i>
                                </div>
                                <div>
                                    <p class='mb-2 text-sm font-medium text-gray-600 dark:text-gray-400'>ห้อง {$room_number}</p>
                                    <p class='text-lg font-semibold text-gray-700 dark:text-gray-200'>ประเภท: {$room_type}</p>
                                    <p class='text-sm text-gray-600 dark:text-gray-400'>ราคา: {$room_price} บาท</p>
                                    <p class='text-sm font-semibold {$room_status_color}'>สถานะ: {$room_status}</p>
                                    <div class='mt-3 flex'>
                                        <a href='edit_room.php?room_id={$room_id}' class='text-blue-500 hover:text-blue-700 text-sm font-semibold mr-4'>
                                            <i class='fas fa-edit'></i> แก้ไข
                                        </a>
                                        <a href='manage_room.php?room_id={$room_id}' class='text-green-500 hover:text-green-700 text-sm font-semibold'>
                                            <i class='fas fa-cogs'></i> จัดการห้องพัก
                                        </a>
                                    </div>
                                </div>
                            </div>";
                        }
                        echo "</div>";
                    } else {
                        echo "<p class='text-gray-600 dark:text-gray-400'>ไม่พบข้อมูลห้องพัก</p>";
                    }
                    ?>
                </div>

                <!-- โหมดตาราง -->
                <div id="tableView" class="hidden">
                    <?php
                    if ($result_rooms && $result_rooms->num_rows > 0) {
                        echo "<div class='flex justify-between items-center my-4'>
                                <h2 id='rooms-all' class='text-lg font-semibold text-gray-700 dark:text-gray-300'>ห้องทั้งหมด</h2>
                                <form method='GET' class='flex items-center space-x-2'>
                                    <label class='text-gray-700 dark:text-gray-300'>กรองสถานะห้อง:</label>
                                    <select name='room_status' onchange='this.form.submit()' class='border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 p-2 rounded'>
                                        <option value=''>ทั้งหมด</option>
                                        <option value='ว่าง' " . ($room_status_filter == 'ว่าง' ? 'selected' : '') . ">ว่าง</option>
                                        <option value='มีผู้เช่า' " . ($room_status_filter == 'มีผู้เช่า' ? 'selected' : '') . ">มีผู้เช่า</option>
                                        <option value='อยู่ระหว่างปรับปรุง' " . ($room_status_filter == 'อยู่ระหว่างปรับปรุง' ? 'selected' : '') . ">อยู่ระหว่างปรับปรุง</option>
                                    </select>
                                </form>
                              </div>";

                        echo "<div class='bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-x-auto'>
                                <table class='w-full'>
                                    <thead class='bg-gray-50 dark:bg-gray-700'>
                                        <tr>
                                            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>ห้อง</th>
                                            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>ประเภท</th>
                                            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>ราคา</th>
                                            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>สถานะ</th>
                                            <th class='px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider'>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class='bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700'>";

                        $result_rooms->data_seek(0); // รีเซ็ต pointer ของผลลัพธ์
                        while ($room = $result_rooms->fetch_assoc()) {
                            $room_id = $room['room_id'];
                            $room_number = $room['room_number'];
                            $room_type = $room['room_type'];
                            $room_price = $room['room_price'];
                            $room_status = $room['room_status'];

                            // กำหนดสีของสถานะห้อง
                            $room_status_color = '';
                            if ($room_status == 'ว่าง') {
                                $room_status_color = 'text-green-500';
                            } elseif ($room_status == 'มีผู้เช่า') {
                                $room_status_color = 'text-red-500';
                            } elseif ($room_status == 'อยู่ระหว่างปรับปรุง') {
                                $room_status_color = 'text-yellow-500';
                            } else {
                                $room_status_color = 'text-gray-500';
                            }

                            echo "
                            <tr>
                                <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>ห้อง {$room_number}</td>
                                <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>{$room_type}</td>
                                <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>{$room_price} บาท</td>
                                <td class='px-4 py-4 whitespace-nowrap text-sm {$room_status_color}'>{$room_status}</td>
                                <td class='px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>
                                    <a href='edit_room.php?room_id={$room_id}' class='text-blue-500 hover:text-blue-700 mr-4'>
                                        <i class='fas fa-edit'></i> แก้ไข
                                    </a>
                                    <a href='manage_room.php?room_id={$room_id}' class='text-green-500 hover:text-green-700'>
                                        <i class='fas fa-cogs'></i> จัดการ
                                    </a>
                                </td>
                            </tr>";
                        }
                        echo "</tbody>
                            </table>
                          </div>";
                    } else {
                        echo "<p class='text-gray-600 dark:text-gray-400'>ไม่พบข้อมูลห้องพัก</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript สำหรับสลับโหมด -->
    <script>
        const toggleViewBtn = document.getElementById('toggleViewBtn');
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');

        toggleViewBtn.addEventListener('click', () => {
            if (cardView.classList.contains('hidden')) {
                // สลับไปโหมดการ์ด
                cardView.classList.remove('hidden');
                tableView.classList.add('hidden');
                toggleViewBtn.innerHTML = "<i class='fas fa-table'></i> Viewlist";
            } else {
                // สลับไปโหมดตาราง
                cardView.classList.add('hidden');
                tableView.classList.remove('hidden');
                toggleViewBtn.innerHTML = "<i class='fas fa-th-large'></i> Viewcard";
            }
        });
    </script>
</body>
</html>