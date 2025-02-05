<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

$query = "SELECT * FROM receip_detail";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/receipt_detail.css">
    <title>จัดการใบเสร็จ</title>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">จัดการใบเสร็จใบเสร็จ</h2>
       
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>หมายเลขห้อง</th>
                    <th>ชื่อผู้เช่า</th>
                    <th>ค่าเช่าห้อง</th>
                    <th>ค่าไฟฟ้า</th>
                    <th>ค่าน้ำ</th>
                    <th>ชนิดห้อง</th>
                    <th>วันที่</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
    
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['receip_room_id'] . "</td>";
                        echo "<td>" . $row['receip_name'] . "</td>";
                        echo "<td>" . $row['receip_room_charge'] . "</td>";
                        echo "<td>" . $row['receip_electricity'] . "</td>";
                        echo "<td>" . $row['receip_water'] . "</td>";
                        echo "<td>" . $row['receip_type'] . "</td>";
                        echo "<td>" . $row['receip_date'] . "</td>";
                        echo "<td>
                                <a href='../view_receipt.php?receip_room_id=" . $row['receip_room_id'] . "' class='btn btn-info btn-sm'>ดูรายละเอียด</a>
                                <a href='edit_receipt.php?receip_room_id=" . $row['receip_room_id'] . "' class='btn btn-warning btn-sm'>แก้ไข</a>
                                <a href='delete_receipt.php?receip_room_id=" . $row['receip_room_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณต้องการลบข้อมูลนี้?\")'>ลบ</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>ไม่มีข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
