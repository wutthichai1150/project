<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../tcpdf/tcpdf.php'); // ใช้ TCPDF

// เชื่อมต่อฐานข้อมูล
include('../includes/db.php');

// ตรวจสอบการส่ง ID มา
if (isset($_GET['rec_id'])) {
    $rec_id = $_GET['rec_id'];

    // สร้างคำสั่ง SQL เพื่อดึงข้อมูลใบแจ้งหนี้จากตาราง invoice_receipt และเชื่อมต่อกับตาราง rooms
    $query = "
    SELECT ir.rec_id, ir.room_id, r.room_number, ir.rec_room_charge, ir.rec_electricity, ir.rec_water, 
           ir.rec_room_type, ir.rec_name, ir.rec_date, ir.rec_total, ir.rec_status
    FROM invoice_receipt ir
    JOIN room r ON ir.room_id = r.room_id
    WHERE ir.rec_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $rec_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        echo "ไม่พบข้อมูลใบแจ้งหนี้";
        exit;
    }
}

// แปลงวันที่ออกใบแจ้งหนี้
$rec_date = isset($invoice['rec_date']) && !empty($invoice['rec_date']) ? date('d/m/Y', strtotime($invoice['rec_date'])) : 'ยังไม่มีข้อมูล';

// สร้าง PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('freeserif', '', 12);

// หัวข้อใบแจ้งหนี้
$pdf->SetXY(10, 10);
$pdf->Cell(0, 10, 'ใบแจ้งหนี้', 0, 1, 'C');

// หมายเลขห้อง (ดึงเลขห้องจากตาราง rooms)
$pdf->SetXY(10, 30);
$pdf->SetFont('freeserif', 'B', 12);
$pdf->Cell(0, 10, 'หมายเลขห้อง: ' . $invoice['room_number'], 0, 1); // เปลี่ยนจาก room_id เป็น room_number

// ชื่อผู้เช่า
$pdf->SetXY(10, 40);
$pdf->Cell(0, 10, 'ชื่อผู้เช่า: ' . $invoice['rec_name'], 0, 1);

// รายละเอียดค่าใช้จ่าย
$pdf->SetFont('freeserif', '', 10);
$pdf->Cell(50, 10, 'ค่าเช่าห้อง:', 0, 0);
$pdf->Cell(0, 10, number_format($invoice['rec_room_charge'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าไฟฟ้า:', 0, 0);
$pdf->Cell(0, 10, number_format($invoice['rec_electricity'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ค่าน้ำ:', 0, 0);
$pdf->Cell(0, 10, number_format($invoice['rec_water'], 2) . ' บาท', 0, 1);

$pdf->Cell(50, 10, 'ชนิดห้อง:', 0, 0);
$pdf->Cell(0, 10, $invoice['rec_room_type'], 0, 1);

// วันที่ออกใบแจ้งหนี้
$pdf->Cell(50, 10, 'วันที่ออกใบแจ้งหนี้:', 0, 0);
$pdf->Cell(0, 10, $rec_date, 0, 1);

$pdf->Ln(10);

// ยอดรวม
$pdf->SetFont('freeserif', 'B', 12);
$pdf->Cell(0, 10, 'ยอดรวม: ' . number_format($invoice['rec_total'], 2) . ' บาท', 0, 1, 'R');
$pdf->Ln(10);

// สร้าง PDF
$pdf->Output('ใบแจ้งหนี้_' . $invoice['rec_id'] . '.pdf', 'I');
?>
