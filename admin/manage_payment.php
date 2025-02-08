<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

if ($conn === false) {
    die("Error: Could not connect to the database. " . mysqli_connect_error());
}

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// ตรวจสอบค่าของเดือนและปี
echo "Month: $selected_month, Year: $selected_year"; // ลองตรวจสอบค่าที่เลือก

// Fetch payment data for the selected month and year
$query = "
    SELECT 
        r.room_number, 
        SUM(p.pay_total) AS total_payment
    FROM 
        payments p
    JOIN 
        room r ON p.room_id = r.room_id
    WHERE 
        MONTH(p.pay_date) = ? AND YEAR(p.pay_date) = ?
    GROUP BY 
        r.room_number
    ORDER BY 
        r.room_number
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();

// Prepare data for the chart
$room_numbers = [];
$total_payments = [];
while ($row = $result->fetch_assoc()) {
    $room_numbers[] = $row['room_number'];
    $total_payments[] = $row['total_payment'];
}

// คิวรีข้อมูลการชำระเงินทั้งหมด
$query = "
    SELECT 
        p.pay_id, p.room_id, r.room_number, p.pay_name, p.pay_room_charge, p.pay_room_type, p.pay_electricity, p.pay_water, p.pay_total, p.pay_date, p.image
    FROM 
        payments p
    JOIN 
        room r ON p.room_id = r.room_id
    WHERE 
        MONTH(p.pay_date) = ? AND YEAR(p.pay_date) = ?
    ORDER BY 
        p.pay_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error: Could not execute query. " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js library -->
    <link rel="stylesheet" href="../css/manage_payment.css">
    <title>หน้าจัดการข้อมูลการชำระเงิน</title>
</head>
<body>
<div class="container mt-5">
    <h2>จัดการข้อมูลการชำระเงิน</h2>

    <!-- ฟอร์มเลือกเดือนและปี -->
    <form method="get" action="manage_payment.php" class="form-inline mb-4" onchange="this.submit()">
        <div class="form-group mr-3">
            <label for="month">เลือกเดือน:</label>
            <select name="month" id="month" class="form-control ml-2">
                <option value="1" <?php echo ($selected_month == 1) ? 'selected' : ''; ?>>มกราคม</option>
                <option value="2" <?php echo ($selected_month == 2) ? 'selected' : ''; ?>>กุมภาพันธ์</option>
                <option value="3" <?php echo ($selected_month == 3) ? 'selected' : ''; ?>>มีนาคม</option>
                <option value="4" <?php echo ($selected_month == 4) ? 'selected' : ''; ?>>เมษายน</option>
                <option value="5" <?php echo ($selected_month == 5) ? 'selected' : ''; ?>>พฤษภาคม</option>
                <option value="6" <?php echo ($selected_month == 6) ? 'selected' : ''; ?>>มิถุนายน</option>
                <option value="7" <?php echo ($selected_month == 7) ? 'selected' : ''; ?>>กรกฎาคม</option>
                <option value="8" <?php echo ($selected_month == 8) ? 'selected' : ''; ?>>สิงหาคม</option>
                <option value="9" <?php echo ($selected_month == 9) ? 'selected' : ''; ?>>กันยายน</option>
                <option value="10" <?php echo ($selected_month == 10) ? 'selected' : ''; ?>>ตุลาคม</option>
                <option value="11" <?php echo ($selected_month == 11) ? 'selected' : ''; ?>>พฤศจิกายน</option>
                <option value="12" <?php echo ($selected_month == 12) ? 'selected' : ''; ?>>ธันวาคม</option>
            </select>
        </div>
        <div class="form-group ml-2">
            <label for="year">เลือกปี:</label>
            <select name="year" id="year" class="form-control ml-2">
                <?php
                for ($i = date('Y'); $i >= date('Y') - 5; $i--) {
                    echo "<option value='$i' " . ($selected_year == $i ? 'selected' : '') . ">$i</option>";
                }
                ?>
            </select>
        </div>
    </form>
<!-- ตารางแสดงข้อมูลการชำระเงิน -->
<table class="table table-bordered table-hover mt-3">
    <thead>
        <tr>
            <th class="text-center">หมายเลขห้อง</th>
            <th class="text-center">ชื่อ</th>
            <th class="text-center">ค่าห้อง</th>
            <th class="text-center">ประเภทห้อง</th>
            <th class="text-center">ค่าไฟ</th>
            <th class="text-center">ค่าน้ำ</th>
            <th class="text-center">ยอดรวม</th>
            <th class="text-center">วันที่ชำระเงิน</th>
            <th class="text-center">ใบเสร็จ</th>
            <th class="text-center">การดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $calculated_total = $row['pay_room_charge'] + $row['pay_electricity'] + $row['pay_water'];
            $pay_total = $row['pay_total'];  

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['pay_name']) . "</td>";
            echo "<td class='text-right'>" . number_format($row['pay_room_charge'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . htmlspecialchars($row['pay_room_type']) . "</td>";
            echo "<td class='text-right'>" . number_format($row['pay_electricity'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . number_format($row['pay_water'], 2) . " บาท</td>";
            echo "<td class='text-right'>" . number_format($pay_total, 2) . " บาท</td>";
            echo "<td>" . htmlspecialchars($row['pay_date']) . "</td>";
            echo "<td class='text-center'><a href='../uploads/" . htmlspecialchars($row['image']) . "' target='_blank'>ดูใบเสร็จ</a></td>";
            echo "<td>";
            echo "<div class='btn-group' role='group'>";
            echo "<a href='generate_payment_pdf.php?pay_id=" . htmlspecialchars($row['pay_id']) . "' class='btn btn-info btn-sm' target='_blank'>พิมพ์ใบเสร็จ</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10' class='text-center'>ไม่มีข้อมูลการชำระเงิน</td></tr>";
    }
    ?>
    </tbody>
    </table>
</div>
 <!-- กราฟแสดงข้อมูลการชำระเงิน -->
<div class="container mt-5">
    <h5>กราฟแสดงข้อมูลการชำระเงิน</h5>
    <canvas id="paymentChart" width="300" height="150"></canvas>  <!-- ลดขนาดกราฟ -->
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ส่งข้อมูลจาก PHP ไปยัง JavaScript
    var roomNumbers = <?php echo json_encode($room_numbers); ?>;
    var totalPayments = <?php echo json_encode($total_payments); ?>;

    // สร้างกราฟ
    var ctx = document.getElementById('paymentChart').getContext('2d');

    // สร้าง gradient เพื่อใช้ในกราฟ
    var gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(54, 162, 235, 0.5)');
    gradient.addColorStop(1, 'rgba(36, 156, 42, 0.5)');

    var paymentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: roomNumbers,  // หมายเลขห้อง
            datasets: [{
                label: 'ยอดชำระเงิน (บาท)',
                data: totalPayments,  // ยอดชำระเงินรวม
                backgroundColor: gradient,  // ใช้สี gradient
                borderColor: 'rgba(54, 162, 235, 1)',  // สีขอบแท่ง
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,  // ทำให้กราฟยืดหยุ่นกับหน้าจอ
            plugins: {
                legend: {
                    display: true,  // แสดง legend
                    labels: {
                        font: {
                            size: 12,  // ลดขนาดฟอนต์ใน legend
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',  // สีพื้นหลังของ tooltip
                    titleColor: '#fff',  // สีหัวข้อใน tooltip
                    bodyColor: '#fff',  // สีเนื้อหาของ tooltip
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'ยอดชำระเงิน: ' + tooltipItem.raw + ' บาท';  // แสดงข้อมูลใน tooltip
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,  // เริ่มจาก 0 บนแกน Y
                    ticks: {
                        callback: function(value) {
                            return value + ' บาท';  // เพิ่ม "บาท" หลังตัวเลขบนแกน Y
                        },
                        font: {
                            size: 10,  // ลดขนาดฟอนต์ของแกน Y
                            family: 'Arial, sans-serif',  // เลือกฟอนต์
                        },
                        color: '#333'  // สีของตัวเลขบนแกน Y
                    }
                }
            },
            animation: {
                duration: 1500,  // ระยะเวลาแอนิเมชั่น
                easing: 'easeOutQuad'  // ชนิดของการแอนิเมชั่น
            }
        }
    });
</script>

    

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
