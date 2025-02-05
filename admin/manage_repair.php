<?php
include('../includes/db.php');
include('../includes/navbar_admin.php');

if ($conn === false) {
    die("Error: Could not connect to the database.");
}

// เปลี่ยนคำสั่ง SQL เพื่อ JOIN ตาราง repair_requests กับ room
$query = "SELECT r.room_number, rr.repair_name, rr.repair_type, rr.repair_eqm_name, rr.repair_detail, rr.repair_image, rr.repair_date, rr.repair_state, rr.repair_id
          FROM repair_requests rr
          JOIN room r ON rr.room_id = r.room_id
          ORDER BY rr.repair_date DESC"; // เรียงตามวันที่แจ้งซ่อมล่าสุด

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}

if (!$stmt->execute()) {
    die('Error executing query: ' . $stmt->error);
}

$result = $stmt->get_result();

if ($result === false) {
    die('Error fetching result: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/manage_repair.css" rel="stylesheet">
    <title>จัดการรายการแจ้งซ่อม</title>
</head>
<body>
    <div class="container">
        <h2>จัดการรายการแจ้งซ่อม</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>หมายเลขห้อง</th>
                    <th>ชื่อผู้แจ้ง</th>
                    <th>ประเภทครุภัณฑ์</th>
                    <th>ชื่อครุภัณฑ์</th>
                    <th>หัวข้อปัญหา</th>
                    <th>รูปภาพ</th>
                    <th>วันที่แจ้งซ่อม</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_class = '';
                        switch ($row['repair_state']) {
                            case 'รอรับเรื่อง':
                                $status_class = 'status-waiting';
                                break;
                            case 'กำลังดำเนินการ':
                                $status_class = 'status-processing';
                                break;
                            case 'ซ่อมบำรุงเรียบร้อย':
                                $status_class = 'status-completed';
                                break;
                            default:
                                $status_class = 'status-default';
                                break;
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_eqm_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_detail']) . "</td>";
                        echo "<td class='view-image'>
                                <img src='../uploads/repair/" . htmlspecialchars($row['repair_image']) . "' alt='Image' 
                                     style='max-width: 100px; max-height: 100px; object-fit: cover;' 
                                     data-bs-toggle='modal' data-bs-target='#imageModal' 
                                     data-bs-image='../uploads/repair/" . htmlspecialchars($row['repair_image']) . "'>
                              </td>";
                        echo "<td>" . date("d/m/Y H:i", strtotime($row['repair_date'])) . "</td>";
                        echo "<td class='" . $status_class . "'>" . htmlspecialchars($row['repair_state']) . "</td>";
                        echo "<td class='action-buttons'>
                                <button class='btn btn-info btn-sm' data-bs-toggle='modal' data-bs-target='#viewRepairModal' 
                                        data-repair-id='" . $row['repair_id'] . "' 
                                        data-room-number='" . htmlspecialchars($row['room_number']) . "' 
                                        data-repair-name='" . htmlspecialchars($row['repair_name']) . "' 
                                        data-repair-type='" . htmlspecialchars($row['repair_type']) . "' 
                                        data-repair-eqm-name='" . htmlspecialchars($row['repair_eqm_name']) . "' 
                                        data-repair-detail='" . htmlspecialchars($row['repair_detail']) . "' 
                                        data-repair-image='../uploads/repair/" . htmlspecialchars($row['repair_image']) . "' 
                                        data-repair-date='" . date("d/m/Y H:i", strtotime($row['repair_date'])) . "' 
                                        data-repair-state='" . htmlspecialchars($row['repair_state']) . "'>
                                    <i class='bi bi-eye'></i> ดูรายละเอียด
                                </button>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editRepairModal' 
                                        data-repair-id='" . $row['repair_id'] . "' 
                                        data-repair-state='" . htmlspecialchars($row['repair_state']) . "'>
                                    <i class='bi bi-pencil'></i> แก้ไขสถานะ
                                </button>
                                <button type='button' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteModal' 
                                        data-bs-repair-id='" . $row['repair_id'] . "'>
                                    <i class='bi bi-trash'></i> ลบข้อมูล
                                </button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>ไม่พบข้อมูลรายการแจ้งซ่อม</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>


    <!-- Modal for image view -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">รูปภาพขยาย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" id="modalImage" class="img-fluid" alt="Expanded Image">
            </div>
        </div>
    </div>
</div>


    <!-- Modal for viewing repair details -->
    <div class="modal fade" id="viewRepairModal" tabindex="-1" aria-labelledby="viewRepairModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRepairModalLabel">ดูรายละเอียดการแจ้งซ่อม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>หมายเลขห้อง:</strong> <span id="view-room-number"></span></p>
                    <p><strong>ชื่อผู้แจ้ง:</strong> <span id="view-repair-name"></span></p>
                    <p><strong>ประเภทครุภัณฑ์:</strong> <span id="view-repair-type"></span></p>
                    <p><strong>ชื่อครุภัณฑ์:</strong> <span id="view-repair-eqm-name"></span></p>
                    <p><strong>หัวข้อปัญหา:</strong> <span id="view-repair-detail"></span></p>
                    <p><strong>วันที่แจ้งซ่อม:</strong> <span id="view-repair-date"></span></p>
                    <p><strong>สถานะ:</strong> <span id="view-repair-state"></span></p>
                    <img src="" id="view-repair-image" class="img-fluid" alt="Repair Image">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for editing repair status -->
    <div class="modal fade" id="editRepairModal" tabindex="-1" aria-labelledby="editRepairModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRepairModalLabel">แก้ไขสถานะการแจ้งซ่อม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_repair_status.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="edit-repair-id" name="repair_id">
                        <div class="mb-3">
                            <label for="edit-repair-state" class="form-label">สถานะการซ่อม</label>
                            <select class="form-select" id="edit-repair-state" name="repair_state">
                                <option value="รอรับเรื่อง">รอรับเรื่อง</option>
                                <option value="กำลังดำเนินการ">กำลังดำเนินการ</option>
                                <option value="ซ่อมบำรุงเรียบร้อย">ซ่อมบำรุงเรียบร้อย</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกสถานะ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal สำหรับยืนยันการลบ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                คุณแน่ใจหรือไม่ว่าจะลบรายการนี้?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <form id="deleteForm" action="delete_repair.php" method="POST">
                    <input type="hidden" name="repair_id" id="repair_id">
                    <button type="submit" class="btn btn-danger">ลบข้อมูล</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // การแสดงรายละเอียดรูปภาพในโมดัล
const imageModal = document.getElementById('imageModal');
imageModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const imageUrl = button.getAttribute('data-bs-image');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageUrl;
});

    const deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const repairId = button.getAttribute('data-bs-repair-id');
            document.getElementById('repair_id').value = repairId;
        });
    });


        const viewRepairModal = document.getElementById('viewRepairModal');
        viewRepairModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('view-room-number').textContent = button.getAttribute('data-room-number');
            document.getElementById('view-repair-name').textContent = button.getAttribute('data-repair-name');
            document.getElementById('view-repair-type').textContent = button.getAttribute('data-repair-type');
            document.getElementById('view-repair-eqm-name').textContent = button.getAttribute('data-repair-eqm-name');
            document.getElementById('view-repair-detail').textContent = button.getAttribute('data-repair-detail');
            document.getElementById('view-repair-date').textContent = button.getAttribute('data-repair-date');
            document.getElementById('view-repair-state').textContent = button.getAttribute('data-repair-state');
            document.getElementById('view-repair-image').src = button.getAttribute('data-repair-image');
        });

        const editRepairModal = document.getElementById('editRepairModal');
        editRepairModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit-repair-id').value = button.getAttribute('data-repair-id');
            document.getElementById('edit-repair-state').value = button.getAttribute('data-repair-state');
        });
    </script>
</body>
</html>
