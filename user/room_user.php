<?php
include('../includes/db.php');
if (!isset($_SESSION['mem_user'])) {
    header('Location: login.php'); // ถ้าไม่ได้เข้าสู่ระบบให้ redirect ไปที่หน้า login
    exit();
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT water_rate, electricity_rate FROM rate WHERE id = 1"; // เปลี่ยนเงื่อนไขตามต้องการ
$result = $conn->query($sql);

// ตรวจสอบว่ามีข้อมูลหรือไม่
if ($result->num_rows > 0) {
    $rate = $result->fetch_assoc(); // ดึงข้อมูลจากฐานข้อมูล
} else {
    // ถ้าไม่มีข้อมูล
    $rate = ['water_rate' => '0.00', 'electricity_rate' => '0.00'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/room_user.css">
    <title>รายละเอียดห้อง</title>
</head>
<body>

    <div class="container mt-4">
    <h3 class="mt-4">ห้องของคุณ</h3>
    <div class="card-header">
    <i class="fas fa-bed"></i> รายละเอียดห้อง
        </div>

        <div class="card-body">
            <?php
            $username = $_SESSION['mem_user'];
            $query = "
            SELECT * FROM room 
            WHERE room_id IN (
                SELECT room_id 
                FROM stay 
                WHERE mem_id = (SELECT mem_id FROM `member` WHERE mem_user = ?) 
                AND (stay_end_date IS NULL OR stay_end_date = '0000-00-00')
            );
        ";  // ปิดสตริงด้วย "

        
        
        
                    
            // ตรวจสอบคำสั่ง SQL และเตรียม statement
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die('SQL Error: ' . $conn->error);  // แสดงข้อความผิดพลาดจากฐานข้อมูล
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $rooms = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                echo "<p>ยังไม่มีห้องที่เชื่อมโยงกับคุณ</p>";
                exit();
            }

            foreach ($rooms as $room):
            ?>
            <ul class="list-group">
    <li class="list-group-item">
                    <strong><i class="fas fa-door-open"></i> ห้องที่:</strong> <?php echo $room['room_number']; ?><br>
                    <strong><i class="fas fa-cogs"></i> ประเภท:</strong> <?php echo $room['room_type']; ?><br>
                    <strong><i class="fas fa-tag"></i> ราคา:</strong> <?php echo $room['room_price']; ?> บาท<br>
                    <strong><i class="fas fa-check-circle"></i> สถานะ:</strong> <?php echo $room['room_status']; ?><br>
                    <a href="repair_form.php?room_id=<?php echo $room['room_id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-tools"></i> แจ้งซ่อมครุภัณฑ์
                    </a>
                    <a href="repair_history.php?room_id=<?php echo $room['room_id']; ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-history"></i> ประวัติการซ่อมแซม
                    </a>
                </li>
            </ul>

            <?php
            $invoice_query = "SELECT * FROM invoice_receipt WHERE room_id = ? ORDER BY rec_date DESC LIMIT 1";
            $invoice_stmt = $conn->prepare($invoice_query);
            $invoice_stmt->bind_param("i", $room['room_id']);
            $invoice_stmt->execute();
            $invoice_result = $invoice_stmt->get_result();

            if ($invoice_result->num_rows > 0):
                while ($invoice = $invoice_result->fetch_assoc()):
                    $mem_fname = '';  // เริ่มต้นตัวแปรชื่อ
$mem_lname = '';  // 

$username = $_SESSION['mem_user'];
$user_query = "SELECT mem_fname, mem_lname FROM `member` WHERE mem_user = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();

// ตรวจสอบว่าเจอผู้ใช้หรือไม่
if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $mem_fname = $user_data['mem_fname'];
    $mem_lname = $user_data['mem_lname'];
}
            ?>
           <div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-info-circle"></i> รายละเอียดค่าเช่า (เดือนล่าสุด)
    </div>
    <div class="card-body">
        <?php 
        // ตรวจสอบว่า rec_name และ mem_fname, mem_lname ตรงกันหรือไม่
        if ($invoice['rec_name'] === $_SESSION['mem_fname'] . ' ' . $_SESSION['mem_lname']) {
        ?>
            <strong><i class="fas fa-user"></i> ชื่อ:</strong> <?php echo $invoice['rec_name']; ?><br>
            <strong><i class="fas fa-bed"></i> ประเภท:</strong> <?php echo $invoice['rec_room_type']; ?><br>
            <strong><i class="fas fa-coins"></i> ค่าเช่าห้อง:</strong> <?php echo $invoice['rec_room_charge']; ?> บาท<br>
            <strong><i class="fas fa-bolt"></i> ค่าไฟ:</strong> <?php echo $invoice['rec_electricity']; ?> บาท
            <span style="font-size: 0.8em; color: #888;">(เรท: <?php echo $rate['electricity_rate']; ?> บาท/หน่วย)</span><br>
            <strong><i class="fas fa-tint"></i> ค่าน้ำ:</strong> <?php echo $invoice['rec_water']; ?> บาท
            <span style="font-size: 0.8em; color: #888;">(เรท: <?php echo $rate['water_rate']; ?> บาท/หน่วย)</span><br>

            <strong><i class="fas fa-calendar-day"></i> วันที่:</strong> <?php echo $invoice['rec_date']; ?><br>
            <strong><i class="fas fa-check-circle"></i> ยอดรวม:</strong> <?php echo $invoice['rec_total']; ?> บาท<br>
            <strong><i class="fas fa-sync-alt"></i> สถานะ:</strong> <?php echo $invoice['rec_status']; ?><br>
            <!-- ปุ่มดูรายละเอียด -->
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $invoice['rec_id']; ?>">
                <i class="fas fa-info-circle"></i> ดูรายละเอียด
            </button>

            <!-- ปุ่มดูประวัติ -->
            <a href="payment_history.php?room_number=<?php echo $room['room_number']; ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-history"></i> ประวัติการชำระเงิน
            </a>
            <!-- ปุ่มชำระเงิน -->
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $invoice['rec_id']; ?>">
                <i class="fas fa-credit-card"></i> ชำระเงิน
            </button>
        <?php 
        } else {
            // ถ้าไม่ตรงกัน
            echo "<p>ไม่มีค่าเช่าสำหรับเดือนนี้</p>";
        }
        ?>
    </div>
</div>


            <!-- Modal: ดูรายละเอียด -->
            <div class="modal fade" id="detailsModal<?php echo $invoice['rec_id']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $invoice['rec_id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailsModalLabel<?php echo $invoice['rec_id']; ?>">รายละเอียดใบเสร็จ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- ข้อมูลรายละเอียดค่าเช่าของใบเสร็จ -->
                            <p><strong>ชื่อ:</strong> <?php echo $invoice['rec_name']; ?></p>
                            <p><strong>ประเภท:</strong> <?php echo $invoice['rec_room_type']; ?></p>
                            <p><strong>ค่าเช่าห้อง:</strong> <?php echo $invoice['rec_room_charge']; ?> บาท</p>
                            <p><strong>ค่าไฟ:</strong> <?php echo $invoice['rec_electricity']; ?> บาท</p>
                            <p><strong>ค่าน้ำ:</strong> <?php echo $invoice['rec_water']; ?> บาท</p>
                            <p><strong>วันที่:</strong> <?php echo $invoice['rec_date']; ?></p>
                            <p><strong>ยอดรวม:</strong> <?php echo $invoice['rec_total']; ?> บาท</p>
                            <p><strong>สถานะ:</strong> <?php echo $invoice['rec_status']; ?></p>

                            <!-- ปุ่มพิมพ์ใบเสร็จ -->
                            <a href="../generate_receipt_pdf.php?rec_id=<?php echo $invoice['rec_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-print"></i> พิมพ์ใบแจ้งหนี้ชำระเงิน
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: ชำระเงิน -->
            <div class="modal fade" id="paymentModal<?php echo htmlspecialchars($invoice['rec_id']); ?>" tabindex="-1" aria-labelledby="paymentModalLabel<?php echo htmlspecialchars($invoice['rec_id']); ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel<?php echo htmlspecialchars($invoice['rec_id']); ?>">ชำระเงิน</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- แสดงข้อมูลใบเสร็จที่เกี่ยวข้อง -->
                            <div class="mb-3">
                                <label class="form-label">ชื่อผู้ชำระเงิน</label>
                                <p><?php echo htmlspecialchars($invoice['rec_name']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ประเภทห้อง</label>
                                <p><?php echo htmlspecialchars($invoice['rec_room_type']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ค่าเช่าห้อง</label>
                                <p><?php echo number_format($invoice['rec_room_charge'], 2); ?> บาท</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ค่าไฟฟ้า</label>
                                <p><?php echo number_format($invoice['rec_electricity'], 2); ?> บาท</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ค่าน้ำ</label>
                                <p><?php echo number_format($invoice['rec_water'], 2); ?> บาท</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ยอดรวม</label>
                                <p><strong><?php echo number_format($invoice['rec_total'], 2); ?> บาท</strong></p>
                            </div>

                            <!-- ฟอร์มวันที่ชำระเงิน -->
                            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                                <!-- ส่งค่าจาก invoice -->
                                <input type="hidden" name="pay_id" value="<?php echo htmlspecialchars($invoice['pay_id']); ?>">
                                <input type="hidden" name="pay_name" value="<?php echo htmlspecialchars($invoice['rec_name']); ?>">
                                <input type="hidden" name="pay_room_type" value="<?php echo htmlspecialchars($invoice['rec_room_type']); ?>"> 
                                <input type="hidden" name="pay_room_charge" value="<?php echo htmlspecialchars($invoice['rec_room_charge']); ?>">
                                <input type="hidden" name="pay_electricity" value="<?php echo htmlspecialchars($invoice['rec_electricity']); ?>">
                                <input type="hidden" name="pay_water" value="<?php echo htmlspecialchars($invoice['rec_water']); ?>">
                                <input type="hidden" name="pay_total" value="<?php echo htmlspecialchars($invoice['rec_total']); ?>">
                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($invoice['room_id']); ?>">

                                <!-- ฟอร์มวันที่ชำระเงิน -->
                                <div class="mb-3">
                                    <label for="payment_date_<?php echo htmlspecialchars($invoice['pay_id']); ?>" class="form-label">วันที่ชำระเงิน</label>
                                    <input type="date" name="payment_date" id="payment_date_<?php echo htmlspecialchars($invoice['pay_id']); ?>" class="form-control" required>
                                </div>

                                <!-- ฟอร์มอัปโหลดสลิป -->
                                <div class="mb-3">
                                    <label for="payment_slip" class="form-label">อัปโหลดสลิปการชำระเงิน</label>
                                    <input type="file" name="payment_slip" id="payment_slip" class="form-control" accept="image/*" required>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> ชำระเงิน
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php endwhile; endif; endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>