<!-- เพิ่มการเชื่อมโยงกับ Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #00796b;">
    <div class="container-fluid">
        <!-- โลโก้พร้อมไอคอนฟันเฟือง -->
        <a class="navbar-brand text-white fw-bold" href="#">
            <i class="fas fa-cogs me-2"></i> Lung Kung Dormitory
        </a>
        <!-- ปุ่มสำหรับแสดงเมนูขนาดเล็ก -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- เมนูหลัก -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- หน้าแรก -->
                <li class="nav-item">
                    <a class="nav-link text-white fw-semibold" href="index.php">
                        <i class="fas fa-home me-2"></i> หน้าแรก
                    </a>
                </li>
                <!-- เข้าสู่ระบบ -->
                <li class="nav-item">
                    <a class="nav-link text-white fw-semibold" href="login.php">
                        <i class="fas fa-sign-in-alt me-2"></i> เข้าสู่ระบบ
                    </a>
                </li>
                <!-- ติดต่อ -->
                <li class="nav-item">
                    <a class="nav-link text-white fw-semibold" href="contact.php">
                        <i class="fas fa-envelope me-2"></i> ติดต่อ
                    </a>
                </li>
                <!-- เกี่ยวกับ -->
                <li class="nav-item">
                    <a class="nav-link text-white fw-semibold" href="about.php">
                        <i class="fas fa-info-circle me-2"></i> เกี่ยวกับ
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- เรียกใช้งาน Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
