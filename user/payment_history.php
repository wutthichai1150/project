<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['mem_user'])) {
    echo "<p class='text-red-500'>กรุณาเข้าสู่ระบบ</p>";
    exit();
}

$username = $_SESSION['mem_user'];

// ค้นหาข้อมูลสมาชิกจากชื่อผู้ใช้ที่ล็อกอิน
$mem_query = "SELECT mem_id, mem_fname, mem_lname FROM `member` WHERE mem_user = ?";
$stmt_mem = $conn->prepare($mem_query);
if (!$stmt_mem) {
    die("Error in mem_query: " . $conn->error);
}
$stmt_mem->bind_param("s", $username);
$stmt_mem->execute();
$mem_result = $stmt_mem->get_result();
$stmt_mem->close();

if ($mem_result->num_rows > 0) {
    $mem_row = $mem_result->fetch_assoc();
    $mem_id = $mem_row['mem_id'];
    $mem_fullname = $mem_row['mem_fname'] . " " . $mem_row['mem_lname'];

    // ดึงเฉพาะข้อมูลการชำระเงินของผู้ใช้ที่ล็อกอินเท่านั้น
    $payment_query = "
        SELECT p.*, r.room_number 
        FROM payments p
        JOIN room r ON p.room_id = r.room_id  
        WHERE p.pay_name = ?
        ORDER BY p.pay_date DESC;
    ";

    $stmt_pay = $conn->prepare($payment_query);
    if (!$stmt_pay) {
        die("Error in payment_query: " . $conn->error);
    }
    $stmt_pay->bind_param("s", $mem_fullname);
    $stmt_pay->execute();
    $result = $stmt_pay->get_result();
    $stmt_pay->close();

    if ($result->num_rows > 0):
        
?>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm mt-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase whitespace-nowrap">วันที่ชำระ</th>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase whitespace-nowrap">ห้อง</th>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase hidden sm:table-cell">ประเภทห้อง</th>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase hidden sm:table-cell">ค่าเช่า</th>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase whitespace-nowrap">ยอดรวม</th>
                <th class="py-2 px-2 text-left text-xs font-medium text-gray-700 uppercase whitespace-nowrap">รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($payment = $result->fetch_assoc()): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-200">
                <td class="py-2 px-2 text-xs text-gray-700 whitespace-nowrap">
                    <?php
                    // แปลงวันที่จาก format Y-m-d เป็น d/m/Y
                    $pay_date = DateTime::createFromFormat('Y-m-d', $payment['pay_date']);
                    echo $pay_date ? $pay_date->format('d/m/Y') : $payment['pay_date'];
                    ?>
                </td>
                    <td class="py-2 px-2 text-xs text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars($payment['room_number']); ?></td>
                    <td class="py-2 px-2 text-xs text-gray-700 hidden sm:table-cell"><?php echo htmlspecialchars($payment['pay_room_type']); ?></td>
                    <td class="py-2 px-2 text-xs text-gray-700 hidden sm:table-cell"><?php echo number_format($payment['pay_room_charge'], 2); ?> บาท</td>
                    <td class="py-2 px-2 text-xs font-semibold text-gray-900 whitespace-nowrap"><?php echo number_format($payment['pay_total'], 2); ?> บาท</td>
                    <td class="py-2 px-2 whitespace-nowrap">
                        <button class="bg-gray-200 text-gray-700 px-2 py-1 rounded-md text-xs hover:bg-gray-300 transition duration-200" 
                            onclick="document.getElementById('paymentModal<?php echo $payment['pay_id']; ?>').style.display='block'">
                            <i class="fas fa-credit-card text-blue-600"></i> ดู
                        </button>
                    </td>
                </tr>

                <!-- Modal รายละเอียดการชำระเงิน -->
                <div id="paymentModal<?php echo $payment['pay_id']; ?>" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                    <div class="modal-content bg-white mx-auto mt-10 p-6 rounded-lg shadow-lg w-full max-w-md">
                        <span class="close text-gray-600 float-right text-2xl font-bold cursor-pointer" 
                              onclick="document.getElementById('paymentModal<?php echo $payment['pay_id']; ?>').style.display='none'">&times;</span>
                        
                        <h3 class="text-xl font-bold text-teal-600 mb-4">รายการชำระเงิน</h3>
                        <p><strong>รหัสชำระเงิน:</strong> <?php echo htmlspecialchars($payment['pay_id']); ?></p>
                        <p><strong>ห้อง:</strong> <?php echo htmlspecialchars($payment['room_number']); ?></p> 
                        <p><strong>ชื่อผู้ชำระ:</strong> <?php echo htmlspecialchars($payment['pay_name']); ?></p>
                        <p><strong>ค่าห้อง:</strong> <?php echo number_format($payment['pay_room_charge'], 2); ?> บาท</p>
                        <p><strong>ประเภทห้อง:</strong> <?php echo htmlspecialchars($payment['pay_room_type']); ?></p>
                        <p><strong>ค่าไฟฟ้า:</strong> <?php echo number_format($payment['pay_electricity'], 2); ?> บาท</p>
                        <p><strong>ค่าน้ำ:</strong> <?php echo number_format($payment['pay_water'], 2); ?> บาท</p>
                        <img src="<?php echo htmlspecialchars($payment['image']); ?>" alt="Payment Slip" class="w-full h-auto rounded-lg shadow" />
                    </div>
                </div>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php 
    else:
        echo "<p class='text-gray-600 text-sm'>ไม่มีประวัติการชำระเงิน</p>";
    endif;
} else {
    echo "<p class='text-gray-600 text-sm'>ไม่พบข้อมูลผู้ใช้</p>";
}
?>