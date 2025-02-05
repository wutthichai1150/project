<?php
// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');
include('../includes/navbar_admin.php');

// ดึงข้อมูลทั้งหมดจากตาราง member
$sql = "SELECT ci.transaction_id, r.room_number, m.mem_fname, m.mem_lname, ci.check_in_date, ci.check_out_date 
        FROM check_in_out ci
        JOIN room r ON ci.room_id = r.room_id
        JOIN `member` m ON ci.mem_id = m.mem_id";  // ใช้   ครอบคำว่า member

$result = $conn->query($sql);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล และการทำงานของคำสั่ง SQL
if (!$result) {
    die("เกิดข้อผิดพลาดในการทำงานของคำสั่ง SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>จัดการสมาชิก</title>
    <style>
        .btn {
            margin: 0 2px;
        }
        .btn-view {
            background-color: #4CAF50;
            color: white;
        }
        .btn-edit {
            background-color: #FFA500;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Main content area -->
    <div class="container mt-4">
        <h2 class="text-center my-4">ข้อมูลผู้เข้าพักและการเช็คอิน/เช็คเอาท์</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>หมายเลขห้อง</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>วันที่เช็คอิน</th>
                    <th>วันที่เช็คเอาท์</th>
                    <th>จัดการ</th> 
                </tr>
            </thead>
            <tbody>
                <?php
                // ตรวจสอบว่ามีข้อมูลหรือไม่
                if ($result && $result->num_rows > 0) {
                    // แสดงผลข้อมูลในตาราง
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                
                                <td>" . $row['room_number'] . "</td>
                                <td>" . $row['mem_fname'] . "</td>
                                <td>" . $row['mem_lname'] . "</td>
                                <td>" . $row['check_in_date'] . "</td>
                                <td>" . $row['check_out_date'] . "</td>
                                <td>
                                    <a href='edit_checkin.php?id=" . $row['transaction_id'] . "' class='btn btn-warning btn-sm'>แก้ไข</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted'>ไม่พบข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- เชื่อมต่อ Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
