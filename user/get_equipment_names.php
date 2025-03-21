<?php
include('../includes/db.php');

if (isset($_GET['eqm_type'])) {
    $eqm_type = $_GET['eqm_type'];

    // คิวรีเพื่อดึงชื่อครุภัณฑ์ตามประเภท
    $sql = "SELECT eqm_name FROM equipment_detail WHERE eqm_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $eqm_type);
    $stmt->execute();
    $result = $stmt->get_result();

    // สร้างตัวเลือกใน select
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['eqm_name'] . "'>" . $row['eqm_name'] . "</option>";
        }
    } else {
        echo "<option value=''>ไม่พบข้อมูล</option>";
    }
    $stmt->close();
}
?>
