<?php
session_start();

if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php');
    exit();
}
include('../includes/db.php');
include('../includes/navbar_user.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>ติดต่อ</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">ติดต่อเรา</h2>
    <div class="row">
        <!-- ข้อมูลที่อยู่ -->
        <div class="col-md-6">
            <h5><i class="fas fa-map-marker-alt me-2"></i> ที่อยู่</h5>
            <p>เลขที่ 287/1, หมู่ที่ 7, ซอย วาสนา 3, ตำบล ถ้ำใหญ่, อำเภอทุ่งสง จังหวัดนครศรีธรรมราช 80110</p>
            
            <h5><i class="fas fa-phone-alt me-2"></i> เบอร์โทรศัพท์</h5>
            <p>082-807-0488</p>

            <h5><i class="fas fa-envelope me-2"></i> อีเมล</h5>
            <p>lungkunroomg@gmail.com</p>

            <h5><i class="fas fa-map me-2"></i> แผนที่</h5>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d987.344175630654!2d99.71007926960772!3d8.164749327002891!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zOMKwMDknNTMuMSJOIDk5wrA0MiczOC42IkU!5e0!3m2!1sth!2sth!4v1737983225401!5m2!1sth!2sth" 
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>

        <!-- ฟอร์มติดต่อ -->
        <div class="col-md-6">
            <h5>ส่งข้อความถึงเรา</h5>
            <form action="send_message.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">ชื่อของคุณ</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">อีเมลของคุณ</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">ข้อความ</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i> ส่งข้อความ</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
