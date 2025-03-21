<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('tcpdf/tcpdf.php'); // ใช้ TCPDF

// เชื่อมต่อฐานข้อมูล
include('includes/db.php');

// ตรวจสอบการส่ง ID มา
if (isset($_GET['rec_id'])) {
    $rec_id = $_GET['rec_id'];

    $query = "
        SELECT ir.*, r.room_number
        FROM invoice_receipt ir
        JOIN room r ON ir.room_id = r.room_id
        WHERE ir.rec_id = $rec_id
    ";
    $result = $conn->query($query);
    $receipt = $result->fetch_assoc();
}

if (!$receipt) {
    echo "ไม่พบข้อมูลใบเสร็จ";
    exit;
}

$total_amount = $receipt['rec_room_charge'] + $receipt['rec_electricity'] + $receipt['rec_water']; // คำนวณยอดรวม

// สร้าง PDF
$pdf = new TCPDF();
$pdf->AddPage();

$pdf->SetFont('freeserif', '', 12);

$pdf->SetXY(10, 10);
$pdf->Cell(0, 10, 'ใบแจ้งหนี้', 0, 1, 'C');

$pdf->SetXY(10, 30);
$pdf->Cell(0, 10, 'หมายเลขห้อง: ' . $receipt['room_number'], 0, 1); // ใช้ room_number จากตาราง room

$pdf->SetXY(10, 40);
$pdf->Cell(0, 10, 'ชื่อผู้เช่า: ' . $receipt['rec_name'], 0, 1);

$pdf->SetFont('freeserif', '', 10);
$pdf->Cell(50, 10, 'ค่าเช่าห้อง:', 0, 0);
$pdf->Cell(0, 10, number_format($receipt['rec_room_charge'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าไฟฟ้า:', 0, 0);
$pdf->Cell(0, 10, number_format($receipt['rec_electricity'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าน้ำ:', 0, 0);
$pdf->Cell(0, 10, number_format($receipt['rec_water'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ชนิดห้อง:', 0, 0);
$pdf->Cell(0, 10, $receipt['rec_room_type'], 0, 1);

$pdf->Cell(50, 10, 'วันที่ออกใบเสร็จ:', 0, 0);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($receipt['rec_date'])), 0, 1); 

$pdf->Cell(50, 10, 'ช่องทางชำระ:', 0, 0);
$pdf->Cell(50, 10, 'นาย พิทักษ์ พรหมชัยศรี ธนาคารกรุงไทย 815-032-7460 ', 0, 0);

$pdf->Ln(10);

$pdf->SetFont('freeserif', 'B', 12);
$pdf->Cell(0, 10, 'ยอดรวม: ' . number_format($total_amount, 2) . ' บาท', 0, 1, 'R');
$pdf->Ln(10);

// สร้าง PDF
$pdf->Output('ใบเสร็จ_' . $receipt['room_id'] . '.pdf', 'I');
?>
