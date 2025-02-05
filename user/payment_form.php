<?php
session_start();
include('../includes/db.php'); 
include('../includes/navbar_user.php'); 

if ($conn === false) {
    die("Error: Could not connect to the database.");
}

$resultMessage = ''; 
$pay_room_id = ''; 
$pay_name = ''; 
$pay_date = ''; 
$pay_charge = ''; 
$pay_electricity = ''; 
$pay_water = ''; 
$pay_total = 0;  // เพิ่มตัวแปรสำหรับยอดรวม

// ดึงข้อมูลจากฐานข้อมูลหากมีการส่งค่า room_number
if (isset($_GET['room_number'])) {
    $pay_room_id = $_GET['room_number'];
    
   
    $query = "SELECT * FROM receip_detail WHERE receip_room_id = ? ORDER BY receip_date DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pay_room_id); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pay_name = $row['receip_name']; 
        $pay_date = $row['receip_date'];
        $pay_charge = $row['receip_room_charge'];
        $pay_electricity = $row['receip_electricity'];
        $pay_water = $row['receip_water'];

       
        $pay_total = $pay_charge + $pay_electricity + $pay_water;
    } else {
        $resultMessage = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $resultMessage = '';
    
    $pay_room_id = $_POST['room_number'];  
    $pay_name = $_POST['first_name'];     
    $pay_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d'); 
    $image = $_FILES['receipt_image']['name']; 
    
   
    $pay_charge = $_POST['room_charge'];
    $pay_electricity = $_POST['electricity'];
    $pay_water = $_POST['water'];
    
   
    $pay_total = $pay_charge + $pay_electricity + $pay_water;

    
    if ($_FILES['receipt_image']['error'] === 0) {
        $target_dir = "../uploads/payments"; 
        $target_file = $target_dir . basename($image);
      
        move_uploaded_file($_FILES['receipt_image']['tmp_name'], $target_file);
    }

  
    $query = "INSERT INTO pay_detail (pay_room_id, pay_name, pay_date, image, pay_state, pay_charge, pay_electricity, pay_water, pay_total) 
              VALUES (?, ?, ?, ?, 'รอดำเนินการ', ?, ?, ?, ?)";


    $stmt = $conn->prepare($query);

    
    if ($stmt === false) {
        die('Error: ' . $conn->error);
    }

    // ผูกค่าตัวแปรกับคำสั่ง SQL
    $stmt->bind_param("ssssdddd", $pay_room_id, $pay_name, $pay_date, $image, $pay_charge, $pay_electricity, $pay_water, $pay_total);

    
    if ($stmt->execute()) {
        $resultMessage = 'success'; 
    } else {
        $resultMessage = 'error'; 
        die('Error: ' . $stmt->error);  
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/payment_form.css">
    <title>ฟอร์มการชำระเงิน</title>
</head>
<body>
    <div class="form-container">
        <h2>ฟอร์มการชำระเงิน</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="room_number">หมายเลขห้อง:</label>
            <input type="text" name="room_number" value="<?php echo $pay_room_id; ?>" required readonly>
            
            <label for="first_name">ชื่อ:</label>
            <input type="text" name="first_name" value="<?php echo $pay_name; ?>" required>
            
            <label for="payment_date">วันที่:</label>
            <input type="date" name="payment_date" value="<?php echo $pay_date; ?>" required>
            
            <label for="room_charge">ค่าห้อง:</label>
            <input type="text" name="room_charge" value="<?php echo $pay_charge; ?>" required id="room_charge" oninput="calculateTotal()">

            <label for="electricity">ค่าไฟ:</label>
            <input type="text" name="electricity" value="<?php echo $pay_electricity; ?>" required>

            <label for="water">ค่าน้ำ:</label>
            <input type="text" name="water" value="<?php echo $pay_water; ?>" required id="water" oninput="calculateTotal()">

            <label for="pay_total">ยอดรวม:</label>
            <input type="text" name="pay_total" value="<?php echo $pay_total; ?>" required readonly id="pay_total">

            <label for="receipt_image">ใบเสร็จ:</label>
            <input type="file" name="receipt_image" accept="image/*" required>
            
            <button type="submit" class="btn btn-primary">ชำระเงิน</button>
            <a href="javascript:history.back()" class="btn btn-secondary">ย้อนกลับ</a>

        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
