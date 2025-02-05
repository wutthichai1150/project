<?php
// เชื่อมต่อกับฐานข้อมูล
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่า room_id มีค่าหรือไม่
    if (isset($_POST['room_id'])) {
        // รับข้อมูลจากฟอร์ม
        $room_number = $_POST['room_id'];  // รับหมายเลขห้องจากฟอร์ม

        // ตรวจสอบว่า rec_status มีค่าหรือไม่
        if (isset($_POST['rec_status'])) {
            $rec_status = $_POST['rec_status'];
        } else {
            $rec_status = 'รอชำระ';  // กำหนดค่าเริ่มต้นให้ rec_status
        }

        // ค้นหาหมายเลขห้อง (room_number) เพื่อรับค่า room_id
        $sql = "SELECT room_id FROM room WHERE room_number = '$room_number'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $room_id = $row['room_id'];  // ได้ค่า room_id จาก room_number

            // รับข้อมูลที่เหลือจากฟอร์ม
            $rec_room_charge = $_POST['rec_room_charge'];
            $rec_electricity = $_POST['rec_electricity'];
            $rec_water = $_POST['rec_water'];
            $rec_room_type = $_POST['rec_room_type'];
            $rec_name = $_POST['rec_name'];
            $rec_date = $_POST['rec_date'];
            $rec_total = $_POST['rec_total'];
            $rec_electricity_charge = $_POST['rec_electricity'];  // รับค่าไฟฟ้าที่คำนวณแล้ว
            $rec_water_charge = $_POST['rec_water'];  // รับค่าน้ำที่คำนวณแล้ว

            // ตรวจสอบว่า rec_date มีค่าหรือไม่
            if (empty($rec_date)) {
                echo json_encode(["success" => false, "message" => "กรุณากรอกวันที่"]);
                exit;
            }

            // สร้างคำสั่ง SQL เพื่อบันทึกข้อมูลลงในตาราง invoice_receipt
            $query = "INSERT INTO `invoice_receipt` (
                room_id, rec_room_charge, rec_electricity, 
                rec_water, rec_room_type, rec_name, rec_date, rec_total, rec_status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            // ตรวจสอบว่าเตรียมคำสั่ง SQL ได้หรือไม่
            if ($stmt = $conn->prepare($query)) {
                // ผูกค่ากับตัวแปร
                $stmt->bind_param("iddssssds", 
                    $room_id, $rec_room_charge, $rec_electricity, $rec_water, 
                    $rec_room_type, $rec_name, $rec_date, $rec_total, $rec_status);

                // Execute the query
                if ($stmt->execute()) {
                    // บันทึกข้อมูลสำเร็จ
                    echo json_encode(["success" => true, "message" => "บันทึกข้อมูลสำเร็จ"]);
                } else {
                    // ถ้ามีข้อผิดพลาดในการบันทึกข้อมูล
                    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการบันทึกข้อมูล"]);
                    echo json_encode(["error" => $stmt->error]); // แสดงข้อความข้อผิดพลาด
                }
                $stmt->close();  // ปิดคำสั่ง SQL
            } else {
                // หากไม่สามารถเตรียมคำสั่ง SQL ได้
                echo json_encode(["success" => false, "message" => "ไม่สามารถเตรียมคำสั่ง SQL ได้"]);
                echo json_encode(["error" => $conn->error]);  // แสดงข้อความข้อผิดพลาดของการเตรียมคำสั่ง
            }
        } else {
            // หากไม่พบหมายเลขห้องที่ตรงกับฐานข้อมูล
            echo json_encode(["success" => false, "message" => "ไม่พบห้องที่ตรงกับหมายเลขห้อง"]);
        }
    } else {
        // หากไม่ได้รับค่า room_id จากฟอร์ม
        echo json_encode(["success" => false, "message" => "ไม่พบหมายเลขห้องจากฟอร์ม"]);
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn->close();
}
?>
