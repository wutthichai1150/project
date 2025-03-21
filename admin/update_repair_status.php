<?php
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repair_id = $_POST['repair_id'];
    $repair_state = $_POST['repair_state'];

    // อัปเดตสถานะการซ่อม
    $query = "UPDATE repair_requests SET repair_state = ? WHERE repair_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $repair_state, $repair_id);

    if ($stmt->execute()) {
        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'อัปเดตสถานะการซ่อมเรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['alert'] = [
            'status' => 'error',
            'message' => 'ไม่สามารถอัปเดตสถานะได้: ' . $stmt->error
        ];
    }

    $stmt->close();
    $conn->close();

    // Redirect กลับไปยังหน้าเดิม
    header("Location: manage_repair.php");
    exit();
}
?>