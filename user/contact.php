<?php
session_start();

if (!isset($_SESSION['mem_fname'])) {
    header('Location: login.php');
    exit();
}

include('../includes/db.php');
include('../includes/navbar_user.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>ติดต่อ</title>
</head>
<body class="bg-gray-50">
<div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-center text-2xl font-bold mb-6">ติดต่อเรา</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ข้อมูลที่อยู่ -->
        <div class="p-4 border rounded-lg shadow-md">
            <h5 class="text-lg font-semibold mb-2">
                <i class="fas fa-map-marker-alt mr-2"></i> ที่อยู่
            </h5>
            <p>เลขที่ 287/1, หมู่ที่ 7, ซอย วาสนา 3, ตำบล ถ้ำใหญ่, อำเภอทุ่งสง จังหวัดนครศรีธรรมราช 80110</p>
            
            <h5 class="text-lg font-semibold mt-4 mb-2">
                <i class="fas fa-phone-alt mr-2"></i> เบอร์โทรศัพท์
            </h5>
            <p>082-807-0488</p>

            <h5 class="text-lg font-semibold mt-4 mb-2">
                <i class="fas fa-envelope mr-2"></i> อีเมล
            </h5>
            <p>lungkunroomg@gmail.com</p>

            <h5 class="text-lg font-semibold mt-4 mb-2">
                <i class="fas fa-map mr-2"></i> แผนที่
            </h5>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d987.344175630654!2d99.71007926960772!3d8.164749327002891!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zOMKwMDknNTMuMSJOIDk5wrA0MiczOC42IkU!5e0!3m2!1sth!2sth!4v1737983225401!5m2!1sth!2sth" 
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>

        <!-- รูปภาพด้านขวา -->
        <div class="flex items-center justify-center p-4">
            <img src="../assets/image/bg.png" alt="Background" class="rounded-lg shadow-lg w-full h-auto">
        </div>
    </div>
</div>
</body>
</html>
