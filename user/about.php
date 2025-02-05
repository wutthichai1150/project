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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <!-- รูปภาพหอพัก -->
            <div class="col-md-6">
                <img src="../assets/image/about.jpg" alt="รูปหอพัก" class="img-fluid rounded shadow">
            </div>
            
            <!-- เนื้อหาเกี่ยวกับหอพัก -->
            <div class="col-md-6">
                <h2>เกี่ยวกับเรา</h2>
                <p>หอพักของเราเป็นสถานที่ที่เหมาะสำหรับการพักอาศัย ด้วยสิ่งอำนวยความสะดวกที่ครบครัน สะอาด และปลอดภัย เราตั้งอยู่ในพื้นที่ที่สะดวกต่อการเดินทาง ใกล้กับสถานที่สำคัญต่าง ๆ เช่น มหาวิทยาลัย ร้านค้า และสถานที่ราชการ</p>
                <p><strong>ที่อยู่:</strong></p>
                <address>
                เลขที่ 287/1, หมู่ที่ 7, ซอย วาสนา 3, ตำบล ถ้ำใหญ่, อำเภอทุ่งสง จังหวัดนครศรีธรรมราช 80110<br>
                    <i class="fas fa-phone"></i>เบอร์โทร 0828070488<br>
                    <i class="fas fa-envelope"></i> lungkunroomg@gmail.com
                </address>
            </div>
        </div>

        <!-- แผนที่ -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center">แผนที่</h3>
                <div class="ratio ratio-16x9">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d987.344175630654!2d99.71007926960772!3d8.164749327002891!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zOMKwMDknNTMuMSJOIDk5wrA0MiczOC42IkU!5e0!3m2!1sth!2sth!4v1737983225401!5m2!1sth!2sth" 
                        width="600" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
