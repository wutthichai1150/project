<?php
include('../includes/db.php');
session_start();

if (!isset($_SESSION['ad_user'])) {
    header("Location: ../login.php");
    exit();
}

$rec_id = isset($_GET['rec_id']) ? $_GET['rec_id'] : '';

if ($rec_id) {
    // ดึงข้อมูลใบเสร็จจาก invoice_receipt
    $query_receipt = "SELECT * FROM invoice_receipt WHERE rec_id = ?";
    $stmt_receipt = $conn->prepare($query_receipt);
    $stmt_receipt->bind_param("i", $rec_id);
    $stmt_receipt->execute();
    $result_receipt = $stmt_receipt->get_result();

    if ($result_receipt->num_rows > 0) {
        $receipt = $result_receipt->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>ไม่พบข้อมูลใบเสร็จ</div>";
        exit;
    }

    // ดึงข้อมูลการชำระเงิน
    $query_payment = "SELECT * FROM payments WHERE room_id = ? AND pay_date >= ? ORDER BY pay_date ASC LIMIT 1";
    $stmt_payment = $conn->prepare($query_payment);
    $stmt_payment->bind_param("is", $receipt['room_id'], $receipt['rec_date']);
    $stmt_payment->execute();
    $result_payment = $stmt_payment->get_result();
}
?>

<!DOCTYPE html>
<html lang="th" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการชำระเงิน</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dark/Light Mode Toggle -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
<div class="flex min-h-screen">
    
    <?php include('includes/sidebar.php'); // นำเข้า Sidebar ?>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                    <i class="fas fa-receipt mr-2 text-blue-500"></i>รายละเอียดการชำระเงิน
                </h1>
            </div>

            <!-- Main Card -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Invoice Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                            <i class="fas fa-file-invoice-dollar text-green-500 mr-2"></i>ใบแจ้งหนี้
                        </h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">หมายเลขใบเสร็จ:</span>
                            <span class="font-medium dark:text-gray-500">#<?= $receipt['rec_id'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 dark:text-gray-400">วันที่ออกใบเสร็จ:</span>
                            <span class="font-medium dark:text-gray-500"><?= date('d/m/Y', strtotime($receipt['rec_date'])) ?></span>
                        </div>
                        
                        <!-- Pricing Details -->
                        <div class="mt-6 space-y-3">
                            <!-- ค่าห้อง -->
                            <div class="flex justify-between p-3 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-400">ค่าห้อง:</span>
                                <span class="font-semibold dark:text-gray-500"><?= number_format($receipt['rec_room_charge'], 2) ?> บาท</span>
                            </div>
                            
                            <!-- ค่าน้ำ -->
                            <div class="flex justify-between p-3 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-400">ค่าน้ำ:</span>
                                <span class="font-semibold dark:text-gray-500"><?= number_format($receipt['rec_water'], 2) ?> บาท</span>
                            </div>
                            
                            <!-- ค่าไฟฟ้า -->
                            <div class="flex justify-between p-3 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-400">ค่าไฟฟ้า:</span>
                                <span class="font-semibold dark:text-gray-500"><?= number_format($receipt['rec_electricity'], 2) ?> บาท</span>
                            </div>
                            
                            <!-- ยอดรวมทั้งหมด -->
                            <div class="flex justify-between p-3 rounded-lg mt-4">
                                <span class="font-bold text-gray-600 dark:text-gray-400">ยอดรวมทั้งหมด:</span>
                                <span class="font-bold text-lg text-purple-600 dark:text-purple-400">
                                    <?= number_format($receipt['rec_total'], 2) ?> บาท
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
                                            <i class="fas fa-credit-card text-orange-500 mr-2"></i>สถานะการชำระเงิน
                                        </h2>
                                    </div>

                                    <?php if ($result_payment && $result_payment->num_rows > 0): ?>
                                        <?php while ($payment = $result_payment->fetch_assoc()): ?>
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <div>
                                                        <p class="font-medium text-gray-600 dark:text-gray-400">วันที่ชำระ:</p>
                                                        <p class="text-blue-600 dark:text-blue-400"><?= date('d/m/Y', strtotime($payment['pay_date'])) ?></p>
                                                    </div>
                                                    <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green px-3 py-1 rounded-full text-sm">
                                                        ชำระแล้ว
                                                    </span>
                                                </div>

                                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <h3 class="font-medium mb-2 text-gray-600 dark:text-gray-400">สลิปการชำระเงิน:</h3>
                                                    <?php if (!empty($payment['image']) && file_exists("../uploads/" . $payment['image'])): ?>
                                                        <!-- ถ้ามีรูปสลิป -->
                                                        <img src="../uploads/<?= htmlspecialchars($payment['image']) ?>" 
                                                            alt="Payment Slip" 
                                                            class="rounded-lg border shadow-sm hover:shadow-md transition-shadow duration-300 cursor-zoom-in"
                                                            onclick="window.open(this.src, '_blank')">
                                                    <?php else: ?>
                                                        <!-- ถ้าไม่มีรูปสลิป -->
                                                        <div class="text-center p-4 bg-gray-100 dark:bg-gray-600 rounded-lg">
                                                            <i class="fas fa-money-bill-wave text-gray-500 dark:text-gray-300 text-2xl mb-2"></i>
                                                            <p class="text-gray-600 dark:text-gray-400">ชำระเงินสด</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-center p-6 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 dark:text-yellow-400 text-2xl mb-3"></i>
                                            <p class="text-white-600 dark:text-gray-400">⚠️ ยังไม่มีการชำระเงินสำหรับใบเสร็จนี้</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

<!-- Custom Style -->
<style>
    body {
        font-family: 'Noto Sans Thai', sans-serif;
    }
    .sidebar {
        width: 280px;
        min-height: 100vh;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
    }
</style>

</body>
</html>