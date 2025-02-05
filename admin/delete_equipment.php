<?php
session_start();
include('../includes/db.php');


if (isset($_GET['id'])) {
    $eqm_id = $_GET['id'];

   
    $query = "DELETE FROM equipment_detail WHERE eqm_id = ?";

   
    $stmt = $conn->prepare($query);

 
    if ($stmt === false) {
        die('Error preparing the statement: ' . $conn->error);
    }

  
    $stmt->bind_param("i", $eqm_id);

  
    if ($stmt->execute()) {
        
        header("Location: equipment_list.php");
        exit(); 
    } else {
       
        echo "ไม่สามารถลบข้อมูลได้: " . $stmt->error;
    }
}
?>
