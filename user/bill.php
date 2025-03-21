<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['mem_user'])) {
    echo "<p class='text-red-500'>กรุณาเข้าสู่ระบบ</p>";
    exit();
}

$username = $_SESSION['mem_user'];
$query = "
    SELECT * FROM room 
    WHERE room_id IN (
        SELECT room_id 
        FROM stay 
        WHERE mem_id = (SELECT mem_id FROM `member` WHERE mem_user = ?) 
        AND (stay_end_date IS NULL OR stay_end_date = '0000-00-00')
    );
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    while ($room = $result->fetch_assoc()):
?>
        
        <h4 class="text-lg font-semibold mt-4">ใบแจ้งหนี้ล่าสุด</h4>
        
        
        <?php
            $invoice_query = "SELECT * FROM invoice_receipt WHERE room_id = ? ORDER BY rec_date DESC LIMIT 1";
            $invoice_stmt = $conn->prepare($invoice_query);
            $invoice_stmt->bind_param("i", $room['room_id']);
            $invoice_stmt->execute();
            $invoice_result = $invoice_stmt->get_result();

            if ($invoice_result->num_rows > 0):
                while ($invoice = $invoice_result->fetch_assoc()):
                    $mem_fname = '';  // เริ่มต้นตัวแปรชื่อ
                    $mem_lname = '';  // 

                    $username = $_SESSION['mem_user'];
                    $user_query = "SELECT mem_fname, mem_lname FROM `member` WHERE mem_user = ?";
                    $stmt = $conn->prepare($user_query);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $user_result = $stmt->get_result();

                    // ตรวจสอบว่าเจอผู้ใช้หรือไม่
                    if ($user_result->num_rows > 0) {
                        $user_data = $user_result->fetch_assoc();
                        $mem_fname = $user_data['mem_fname'];
                        $mem_lname = $user_data['mem_lname'];
                    }
            ?>
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md mt-4 transition duration-300 hover:shadow-lg">
    <h4 class="text-lg sm:text-xl font-bold text-teal-700 mb-2">📜 รายละเอียดใบแจ้งหนี้</h4>

    <div class="mt-4 space-y-3">
        <!-- เพิ่มฟิลด์วันที่ออกใบแจ้งหนี้ -->
        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">📅 วันที่ออกใบแจ้งหนี้</span>
            <span class="font-semibold text-sm sm:text-base text-gray-900">
                <?php echo date('d/m/Y', strtotime($invoice['rec_date'])); ?>
            </span>
        </div>

        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">🏠 ประเภทห้อง</span>
            <span class="font-semibold text-sm sm:text-base text-gray-900"><?php echo $invoice['rec_room_type']; ?></span>
        </div>

        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">💰 ค่าเช่าห้อง</span>
            <span class="font-semibold text-sm sm:text-base text-gray-900"><?php echo number_format($invoice['rec_room_charge'], 2); ?> บาท</span>
        </div>

        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">⚡ ค่าไฟ</span>
            <span class="font-semibold text-sm sm:text-base text-gray-900"><?php echo number_format($invoice['rec_electricity'], 2); ?> บาท</span>
        </div>

        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">🚰 ค่าน้ำ</span>
            <span class="font-semibold text-sm sm:text-base text-gray-900"><?php echo number_format($invoice['rec_water'], 2); ?> บาท</span>
        </div>

        <!-- สถานะแสดงสีแตกต่างกัน -->
        <div class="flex flex-col sm:flex-row justify-between bg-gray-100 p-3 sm:p-4 rounded-lg shadow-sm">
            <span class="font-medium text-sm sm:text-base text-gray-700">🔄 สถานะ</span>
            <?php 
                $status = $invoice['rec_status'];
                $statusClass = "";
                $statusBg = "";

                if ($status == "ชำระเงินแล้ว") {
                    $statusClass = "text-green-600 font-bold";
                    $statusBg = "bg-green-100 px-2 py-1 sm:px-3 sm:py-1 rounded-lg";
                } elseif ($status == "ค้างชำระ") {
                    $statusClass = "text-red-600 font-bold";
                    $statusBg = "bg-red-100 px-2 py-1 sm:px-3 sm:py-1 rounded-lg";
                } elseif ($status == "รอดำเนินการ") {
                    $statusClass = "text-yellow-600 font-bold";
                    $statusBg = "bg-yellow-100 px-2 py-1 sm:px-3 sm:py-1 rounded-lg";
                }
            ?>
            <span class="<?php echo $statusClass . ' ' . $statusBg; ?> text-sm sm:text-base">
                <?php echo $status; ?>
            </span>
        </div>
    </div>

    <p class="text-right font-bold text-lg sm:text-xl mt-4">
        💳 ยอดรวมทั้งหมด: <span class="text-blue-600"><?php echo number_format($invoice['rec_total'], 2); ?> บาท</span>
    </p>
</div>
    
    <div class="flex space-x-3 mt-6">
        <a href="../generate_receipt_pdf.php?rec_id=<?php echo htmlspecialchars($invoice['rec_id']); ?>" 
            class="bg-green-500 text-white px-5 py-2 rounded-md text-sm hover:bg-green-600 transition duration-200">
            <i class="fas fa-print"></i> พิมพ์ใบแจ้งหนี้
        </a>
        <button class="bg-blue-500 text-white px-5 py-2 rounded-md text-sm hover:bg-blue-600 transition duration-200" 
            onclick="document.getElementById('paymentModal<?php echo $invoice['rec_id']; ?>').style.display='block'">
            <i class="fas fa-credit-card"></i> ชำระเงิน
        </button>
    </div>
</div>

           <!-- Modal สำหรับปุ่มชำระเงิน -->
                <div id="paymentModal<?php echo $invoice['rec_id']; ?>" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                    <div class="modal-content bg-white mx-auto mt-10 p-6 rounded-lg shadow-lg w-full max-w-md">
                        <span class="close text-gray-600 float-right text-2xl font-bold cursor-pointer" 
                            onclick="document.getElementById('paymentModal<?php echo $invoice['rec_id']; ?>').style.display='none'">&times;</span>
                        
                        <h3 class="text-2xl font-bold text-teal-600 mb-4">ชำระเงิน</h3>
                        
                        <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                            <input type="hidden" name="rec_id" value="<?php echo $invoice['rec_id']; ?>">
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_name">ชื่อผู้ชำระเงิน</label>
                                <input type="text" name="pay_name" id="pay_name" value="<?php echo $invoice['rec_name']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_room_type">ประเภทห้อง</label>
                                <input type="text" name="pay_room_type" id="pay_room_type" value="<?php echo $invoice['rec_room_type']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_room_charge">ค่าเช่าห้อง</label>
                                <input type="number" name="pay_room_charge" id="pay_room_charge" value="<?php echo $invoice['rec_room_charge']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_electricity">ค่าไฟ</label>
                                <input type="number" name="pay_electricity" id="pay_electricity" value="<?php echo $invoice['rec_electricity']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_water">ค่าน้ำ</label>
                                <input type="number" name="pay_water" id="pay_water" value="<?php echo $invoice['rec_water']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="pay_total">ยอดรวมทั้งหมด</label>
                                <input type="number" name="pay_total" id="pay_total" value="<?php echo $invoice['rec_total']; ?>" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" readonly>
                            </div>
                            
                            <div class="mb-5">
                                <label for="pay_date" class="block text-lg font-semibold text-gray-700">
                                    <i class="fas fa-calendar-day text-orange-500"></i> วันที่ชำระเงิน
                                </label>
                                <input type="hidden" name="pay_date" id="pay_date" value="<?php echo date('Y-m-d'); ?>">
                                <p class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 p-2">
                                    <?php echo date('d/m/Y'); ?>
                                </p>
                            </div>



                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-semibold mb-2" for="image">อัพโหลดสลิปการชำระเงิน</label>
                                <input type="file" name="image" id="image" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <button type="submit" name="submit_payment" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    ยืนยันการชำระเงิน
                                </button>
                                <button type="button" onclick="document.getElementById('paymentModal<?php echo $invoice['rec_id']; ?>').style.display='none'" class="bg-gray-500 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    ยกเลิก
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


        <?php 
                endwhile;
            else:
                echo "<p class='text-gray-600'>ไม่มีใบแจ้งหนี้</p>";
            endif;
        ?>
    </div>
<?php 
    endwhile;
else:
    echo "<p class='text-gray-600'>ยังไม่มีห้องที่เชื่อมโยงกับคุณ</p>";
endif;
?>
