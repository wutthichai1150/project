<?php
include('../includes/db.php'); // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eqm_type = $_POST['eqm_type'];

    $query = "SELECT eqm_name FROM equipment_detail WHERE eqm_type = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $eqm_type);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>เลือกชื่อครุภัณฑ์</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['eqm_name']) . "'>" . htmlspecialchars($row['eqm_name']) . "</option>";
    }
    $stmt->close();
}
?>
