<?php
// เชื่อมต่อฐานข้อมูล
include('includes/db.php');
include('includes/navbar_user.php');

// ตรวจสอบการส่ง ID มา
if (isset($_GET['receip_room_id'])) {
    $receip_id = $_GET['receip_room_id'];

    // ดึงข้อมูลใบเสร็จจากฐานข้อมูล
    $query = "SELECT * FROM receip_detail WHERE receip_room_id = ?";
    $stmt = $conn->prepare($query);

    // ตรวจสอบว่า prepare สำเร็จหรือไม่
    if ($stmt === false) {
        die("Error preparing the SQL query: " . $conn->error);
    }

    $stmt->bind_param("s", $receip_id); // ป้องกัน SQL Injection โดยใช้ 's' สำหรับตัวแปรที่เป็นตัวอักษร
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ตรวจสอบว่ามีข้อมูลใบเสร็จหรือไม่
    if ($result->num_rows > 0) {
        $receipt = $result->fetch_assoc();
        $total_amount = $receipt['receip_room_charge'] + $receipt['receip_electricity'] + $receipt['receip_water'];
    } else {
        // หากไม่พบข้อมูลใบเสร็จ
        $receipt = null;
    }
} else {
    // หากไม่ได้รับค่า `receip_room_id` มา
    $receipt = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>รายละเอียดใบเสร็จ</title>
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center mb-4">รายละเอียดใบเสร็จ</h3>

        <?php if ($receipt): ?>
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <strong>ใบเสร็จหมายเลขห้อง: <?php echo $receipt['receip_room_id']; ?></strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ชื่อผู้เช่า:</strong> <?php echo $receipt['receip_name']; ?></p>
                            <p><strong>ชนิดห้อง:</strong> <?php echo $receipt['receip_type']; ?></p>
                            <p><strong>วันที่ออกใบเสร็จ:</strong> <?php echo date('d/m/Y', strtotime($receipt['receip_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ค่าเช่าห้อง:</strong> <?php echo number_format($receipt['receip_room_charge'], 2); ?> บาท</p>
                            <p><strong>ค่าไฟฟ้า:</strong> <?php echo number_format($receipt['receip_electricity'], 2); ?> บาท</p>
                            <p><strong>ค่าน้ำ:</strong> <?php echo number_format($receipt['receip_water'], 2); ?> บาท</p>
                            <p><strong>ยอดรวม:</strong> <?php echo number_format($total_amount, 2); ?> บาท</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="generate_receipt_pdf.php?receip_id=<?php echo $receipt['receip_room_id']; ?>" class="btn btn-success" target="_blank">พิมพ์ใบเสร็จเป็น PDF</a>
                        <a href="javascript:history.back()" class="btn btn-secondary">ย้อนกลับ</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-4 text-center">
                <strong>ไม่พบข้อมูลใบเสร็จ</strong>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
