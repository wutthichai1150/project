<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../tcpdf/tcpdf.php'); // ใช้ TCPDF

// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบการส่ง ID มา
if (isset($_GET['pay_id'])) {
    $pay_id = $_GET['pay_id'];

    // สร้างคำสั่ง SQL เพื่อดึงข้อมูลการชำระเงินจากตาราง payments และหมายเลขห้องจากตาราง rooms
    $query = "
    SELECT p.pay_id, r.room_number, p.pay_name, p.pay_room_charge, p.pay_room_type, 
           p.pay_electricity, p.pay_water, p.pay_total, p.pay_date
    FROM payments p
    JOIN room r ON p.room_id = r.room_id
    WHERE p.pay_id = $pay_id
    ";
    $result = $conn->query($query);
    $payment = $result->fetch_assoc();

    if (!$payment) {
        echo "ไม่พบข้อมูลการชำระเงิน";
        exit;
    }
}

// คำนวณยอดรวม
$total_amount = $payment['pay_room_charge'] + $payment['pay_electricity'] + $payment['pay_water']; 

// แปลงวันที่ชำระเงินจากฐานข้อมูล
$pay_date = isset($payment['pay_date']) && !empty($payment['pay_date']) ? $payment['pay_date'] : null;
if ($pay_date) {
    // แปลงวันที่จากฐานข้อมูลเป็นรูปแบบที่ต้องการ
    $formatted_pay_date = date('d/m/Y', strtotime($pay_date));
} else {
    // กรณีไม่มีข้อมูลวันที่ชำระเงิน
    $formatted_pay_date = 'ยังไม่มีการชำระเงิน';
}

// สร้าง PDF
$pdf = new TCPDF();
$pdf->AddPage();

$pdf->SetFont('freeserif', '', 12);

// หัวข้อใบเสร็จ
$pdf->SetXY(10, 10);
$pdf->Cell(0, 10, 'ใบเสร็จ', 0, 1, 'C');

// หมายเลขห้อง
$pdf->SetXY(10, 30);
$pdf->SetFont('freeserif', 'B', 12);
$pdf->Cell(0, 10, 'หมายเลขห้อง: ' . $payment['room_number'], 0, 1);

// ชื่อผู้เช่า
$pdf->SetXY(10, 40);
$pdf->Cell(0, 10, 'ชื่อผู้เช่า: ' . $payment['pay_name'], 0, 1);

// รายละเอียดค่าใช้จ่าย
$pdf->SetFont('freeserif', '', 10);
$pdf->Cell(50, 10, 'ค่าเช่าห้อง:', 0, 0);
$pdf->Cell(0, 10, number_format($payment['pay_room_charge'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าไฟฟ้า:', 0, 0);
$pdf->Cell(0, 10, number_format($payment['pay_electricity'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าน้ำ:', 0, 0);
$pdf->Cell(0, 10, number_format($payment['pay_water'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ชนิดห้อง:', 0, 0);
$pdf->Cell(0, 10, $payment['pay_room_type'], 0, 1);

// วันที่ชำระเงิน
$pdf->Cell(50, 10, 'วันที่ชำระเงิน:', 0, 0);
$pdf->Cell(0, 10, $formatted_pay_date, 0, 1);

$pdf->Ln(10);

// ยอดรวม
$pdf->SetFont('freeserif', 'B', 12);
$pdf->Cell(0, 10, 'ยอดรวม: ' . number_format($total_amount, 2) . ' บาท', 0, 1, 'R');
$pdf->Ln(10);

// สร้าง PDF
$pdf->Output('ใบเสร็จ_' . $payment['pay_id'] . '.pdf', 'I');

?>
