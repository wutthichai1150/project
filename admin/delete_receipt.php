<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');


if (isset($_GET['receip_room_id'])) {
    $receip_room_id = $_GET['receip_room_id'];

   
    $delete_query = "DELETE FROM `receip_detail` WHERE receip_room_id = ?";
    $stmt = $conn->prepare($delete_query);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }


    $stmt->bind_param("i", $receip_room_id);

    if ($stmt->execute()) {
     
        echo "<script>alert('ลบข้อมูลใบเสร็จเรียบร้อย'); window.location.href = 'receipt_detail.php';</script>";
    } else {
      
        echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล'); window.location.href = 'receipt_detail.php';</script>";
    }

  
    $stmt->close();
} else {

    echo "<script>alert('ไม่พบข้อมูลใบเสร็จ'); window.location.href = 'receipt_detail.php';</script>";
}

$conn->close();
?>
