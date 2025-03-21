<?php
session_start();

if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php');
    exit();
}
include('../includes/navbar_user.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เกี่ยวกับเรา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- รูปภาพหอพัก -->
            <div>
                <img src="../assets/image/about.jpg" alt="รูปหอพัก" class="w-full rounded shadow">
            </div>
            
            <!-- เนื้อหาเกี่ยวกับหอพัก -->
            <div class="flex flex-col justify-center">
                <h2 class="text-2xl font-bold mb-4">เกี่ยวกับเรา</h2>
                <p class="mb-2">หอพักของเราเป็นสถานที่ที่เหมาะสำหรับการพักอาศัย ด้วยสิ่งอำนวยความสะดวกที่ครบครัน สะอาด และปลอดภัย เราตั้งอยู่ในพื้นที่ที่สะดวกต่อการเดินทาง ใกล้กับสถานที่สำคัญต่าง ๆ เช่น มหาวิทยาลัย ร้านค้า และสถานที่ราชการ</p>
                <p><strong>ที่อยู่:</strong></p>
                <address class="mb-4">
                    เลขที่ 287/1, หมู่ที่ 7, ซอย วาสนา 3, ตำบล ถ้ำใหญ่, อำเภอทุ่งสง จังหวัดนครศรีธรรมราช 80110<br>
                    <i class="fas fa-phone"></i> เบอร์โทร 0828070488<br>
                    <i class="fas fa-envelope"></i> lungkunroomg@gmail.com
                </address>
            </div>
        
            </div>
        </div>

    </div>
</body>
</html>
