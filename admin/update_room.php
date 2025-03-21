<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // สำหรับการแก้ไขห้องพัก
    if (isset($_POST['room_id']) && !isset($_POST['delete_room'])) {
        $room_id = $_POST['room_id'];
        $room_number = $_POST['room_number'];
        $room_type = $_POST['room_type'];
        $room_price = $_POST['room_price'];
        $room_status = $_POST['room_status'];
        $selected_equipment = $_POST['equipment'] ?? []; // อุปกรณ์ที่เลือก

        // อัปเดตข้อมูลห้องพัก
        $query = "UPDATE room SET room_number = '$room_number', room_type = '$room_type', room_price = '$room_price', room_status = '$room_status' WHERE room_id = $room_id";
        
        if ($conn->query($query) === TRUE) {
            // ลบอุปกรณ์เดิมที่เกี่ยวข้องกับห้องพักนี้
            $conn->query("DELETE FROM room_equipment WHERE room_id = $room_id");

            // เพิ่มอุปกรณ์ใหม่ที่เลือก
            if (!empty($selected_equipment)) {
                foreach ($selected_equipment as $eqm_id) {
                    $conn->query("INSERT INTO room_equipment (room_id, eqm_id) VALUES ($room_id, $eqm_id)");
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'อัพเดตห้องพักและอุปกรณ์สำเร็จ']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัพเดตห้องพัก']);
        }
    }
    
    // สำหรับการลบห้องพัก
    if (isset($_POST['delete_room']) && $_POST['delete_room'] === 'true') {
        $room_id = $_POST['room_id'];

        // ลบข้อมูลห้องพักและอุปกรณ์ที่เกี่ยวข้อง
        $conn->query("DELETE FROM room WHERE room_id = $room_id");
        $conn->query("DELETE FROM room_equipment WHERE room_id = $room_id");

        if ($conn->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'ลบห้องพักและอุปกรณ์สำเร็จ']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'ลบห้องพักและอุปกรณ์สำเร็จ']);
        }
    }
}
?>