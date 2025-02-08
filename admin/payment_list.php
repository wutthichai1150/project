<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');
include('../includes/navbar_admin.php');

// รับค่าจาก URL (rec_id)
$rec_id = isset($_GET['rec_id']) ? $_GET['rec_id'] : '';

if ($rec_id) {
    // ดึงข้อมูลใบเสร็จจาก invoice_receipt
    $query_receipt = "SELECT * FROM invoice_receipt WHERE rec_id = ?";
    $stmt_receipt = $conn->prepare($query_receipt);
    $stmt_receipt->bind_param("i", $rec_id);
    $stmt_receipt->execute();
    $result_receipt = $stmt_receipt->get_result();

    if ($result_receipt->num_rows > 0) {
        $receipt = $result_receipt->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>ไม่พบข้อมูลใบเสร็จ</div>";
        exit;
    }

    // ดึงข้อมูลการชำระเงิน
    $query_payment = "SELECT * FROM payments WHERE room_id = ? AND pay_date >= ? ORDER BY pay_date ASC LIMIT 1";
    $stmt_payment = $conn->prepare($query_payment);
    $stmt_payment->bind_param("is", $receipt['room_id'], $receipt['rec_date']);
    $stmt_payment->execute();
    $result_payment = $stmt_payment->get_result();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <!-- ส่วนแสดงใบเสร็จ -->
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🧾 รายละเอียดใบแจ้งหนี้</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>หมายเลขใบเสร็จ</th><td><?php echo $receipt['rec_id']; ?></td></tr>
                        <tr><th>วันที่ออกใบเสร็จ</th><td><?php echo $receipt['rec_date']; ?></td></tr>
                        <tr><th>ค่าห้อง</th><td><?php echo number_format($receipt['rec_room_charge'], 2); ?> บาท</td></tr>
                        <tr><th>ค่าน้ำ</th><td><?php echo number_format($receipt['rec_water'], 2); ?> บาท</td></tr>
                        <tr><th>ค่าไฟ</th><td><?php echo number_format($receipt['rec_electricity'], 2); ?> บาท</td></tr>
                        <tr class="table-success"><th>ยอดรวม</th><td><strong><?php echo number_format($receipt['rec_total'], 2); ?> บาท</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- ส่วนแสดงข้อมูลการชำระเงิน -->
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">💰 ข้อมูลการชำระเงิน</h5>
                </div>
                <div class="card-body">
                    <?php if ($result_payment && $result_payment->num_rows > 0) { 
                        while ($payment = $result_payment->fetch_assoc()) { ?>
                            <table class="table table-bordered">
                                <tr><th>ชื่อผู้ชำระเงิน</th><td><?php echo $payment['pay_name']; ?></td></tr>
                                <tr><th>ประเภทห้อง</th><td><?php echo $payment['pay_room_type']; ?></td></tr>
                                <tr><th>ยอดที่ชำระ</th><td><?php echo number_format($payment['pay_total'], 2); ?> บาท</td></tr>
                                <tr><th>วันที่ชำระเงิน</th><td><?php echo $payment['pay_date']; ?></td></tr>
                            </table>

                            <!-- แสดงสลิปการชำระเงิน -->
                            <div class="text-center mt-3">
                                <strong>🧾 สลิปการชำระเงิน:</strong>
                                <br>
                                <img src="../uploads/<?php echo htmlspecialchars($payment['image']); ?>" 
                                     alt="Payment Slip" 
                                     class="img-fluid rounded border shadow-sm" 
                                     style="max-width: 300px;">
                            </div>




                        <?php }
                    } else {
                        echo '<div class="alert alert-warning">⚠️ ไม่มีการชำระเงินสำหรับใบเสร็จนี้</div>';
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ฟังก์ชันพิมพ์ใบเสร็จ
function printReceipt() {
    window.print();
}
</script>

</body>
</html>
