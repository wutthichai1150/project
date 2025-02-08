<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

// รับค่าจาก URL (room_id)
$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : '';

// ตรวจสอบว่าได้รับ room_id หรือไม่
if ($room_id) {
    // เขียน SQL Query เพื่อดึงข้อมูลใบเสร็จจากตาราง invoice_receipt
    $query = "SELECT * FROM invoice_receipt WHERE room_id = ?"; // ใช้ room_id
    $stmt = $conn->prepare($query);

    // ตรวจสอบว่า prepare() สำเร็จหรือไม่
    if ($stmt === false) {
        die('Error in preparing query: ' . $conn->error);
    }

    $stmt->bind_param("i", $room_id);  // bind ค่าห้องพัก
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "ไม่พบข้อมูลห้องนี้";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการค่าเช่า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .manage-column button {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4 text-center">รายการค่าเช่า</h2>

        <!-- ตารางแสดงข้อมูลใบเสร็จ -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ลำดับ</th>
                        <th>วันที่ออกใบแจ้งหนี้</th>
                        <th>ค่าห้อง</th>
                        <th>ค่าน้ำ</th>
                        <th>ค่าไฟ</th>
                        <th>ประเภทห้อง</th>
                        <th>จำนวนเงินทั้งหมด</th>
                        <th>สถานะการชำระเงิน</th>
                        <th class="manage-column">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $numrow = 1; // ตัวแปรสำหรับนับลำดับหมายเลขใบแจ้งหนี้
                    while ($invoice = $result->fetch_assoc()): ?>
                        <tr id="invoiceRow<?php echo $invoice['rec_id']; ?>">
                            <td><?php echo str_pad($numrow, 3, '0', STR_PAD_LEFT); ?></td> <!-- ใช้ numrow แสดงหมายเลขใบแจ้งหนี้ -->
                            <td><?php echo $invoice['rec_date']; ?></td>
                            <td><?php echo number_format($invoice['rec_room_charge'], 2); ?> บาท</td>
                            <td><?php echo number_format($invoice['rec_water'], 2); ?> บาท</td>
                            <td><?php echo number_format($invoice['rec_electricity'], 2); ?> บาท</td>
                            <td><?php echo $invoice['rec_room_type']; ?></td>
                            <td><?php echo number_format($invoice['rec_total'], 2); ?> บาท</td>
                            <td><?php echo $invoice['rec_status']; ?></td>
                            <td class="manage-column">
                                <div class="d-flex justify-content-start gap-2">
                                    <!-- ปุ่มแก้ไข -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $invoice['rec_id']; ?>">
                                        <i class="bi bi-pencil"></i> แก้ไข
                                    </button>

                                    <!-- ปุ่มลบ -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteInvoice(<?php echo $invoice['rec_id']; ?>)">
                                        <i class="bi bi-trash"></i> ลบ
                                    </button>
                                    <!-- ปุ่มดูการชำระเงิน -->
                                    <a href="payment_list.php?rec_id=<?php echo $invoice['rec_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="bi bi-file-earmark-check"></i> ดูการชำระเงิน
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal แก้ไข -->
                        <div class="modal fade" id="editModal<?php echo $invoice['rec_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $invoice['rec_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $invoice['rec_id']; ?>">แก้ไขใบแจ้งหนี้</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- ฟอร์มแก้ไขใบเสร็จ -->
                                        <form id="editForm" method="POST" action="edit_invoice.php">
                                            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>"> <!-- ส่งค่า room_id -->
                                            <div class="mb-3">
                                                <label for="invoice_id" class="form-label">หมายเลขใบเสร็จ</label>
                                                <input type="text" name="invoice_id" class="form-control" value="<?php echo $invoice['rec_id']; ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_room_charge" class="form-label">ค่าห้อง</label>
                                                <input type="number" name="rec_room_charge" class="form-control" value="<?php echo $invoice['rec_room_charge']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_water" class="form-label">ค่าน้ำ</label>
                                                <input type="number" name="rec_water" class="form-control" value="<?php echo $invoice['rec_water']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_electricity" class="form-label">ค่าไฟ</label>
                                                <input type="number" name="rec_electricity" class="form-control" value="<?php echo $invoice['rec_electricity']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_total" class="form-label">ยอดรวม</label>
                                                <input type="number" name="rec_total" class="form-control" value="<?php echo $invoice['rec_total']; ?>" readonly> <!-- คำนวณยอดรวมในเซิร์ฟเวอร์ -->
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_date" class="form-label">วันที่ออกใบเสร็จ</label>
                                                <input type="date" name="rec_date" class="form-control" value="<?php echo $invoice['rec_date']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rec_status" class="form-label">สถานะชำระเงิน</label>
                                                <select name="rec_status" class="form-control" required>
                                                    <option value="ชำระเงินแล้ว" <?php echo ($invoice['rec_status'] == 'ชำระเงินแล้ว') ? 'selected' : ''; ?>>ชำระเงินแล้ว</option>
                                                    <option value="กำลังดำเนินการ" <?php echo ($invoice['rec_status'] == 'กำลังดำเนินการ') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                    <option value="รอชำระ" <?php echo ($invoice['rec_status'] == 'รอชำระ') ? 'selected' : ''; ?>>รอชำระ</option>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-pencil-square"></i> แก้ไขใบเสร็จ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                    $numrow++; // เพิ่มตัวแปร numrow
                    endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    function deleteInvoice(invoiceId) {
        Swal.fire({
            title: 'คุณต้องการลบใบเสร็จนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "/project/admin/delete_invoice_receipt.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("rec_id=" + invoiceId);

                xhr.onload = function() {
                    if (xhr.status == 200) {
                        Swal.fire({
                            title: 'ลบใบเสร็จเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'ตกลง'
                        });
                        document.getElementById("invoiceRow" + invoiceId).remove(); // ลบแถวในตาราง
                    } else {
                        Swal.fire({
                            title: 'ลบใบเสร็จไม่สำเร็จ',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                };
            }
        });
    }
</script>

    <!-- Bootstrap 5 JS และ Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>