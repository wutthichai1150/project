

<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ระบบจัดการหอพัก</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="./assets/js/init-alpine.js"></script>
  </head>

<!-- Desktop Sidebar -->
<aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0 shadow-lg">
  <div class="py-4 text-gray-500 dark:text-gray-400">
    <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="admin_dashboard.php">
      ระบบจัดการหอพัก
    </a>
    <ul class="mt-6">
      <li class="relative px-6 py-3">
        <a data-page="admin_dashboard.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="admin_dashboard.php">
          <i class="fas fa-tachometer-alt w-5 h-5"></i>
          <span class="ml-4">Dashboard</span>
        </a>
      </li>
    </ul>
    <ul>
      <li class="relative px-6 py-3">
        <a data-page="add_rooms.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="add_rooms.php">
          <i class="fas fa-plus-square w-5 h-5"></i>
          <span class="ml-4">เพิ่มห้องพัก</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="manage_member.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_member.php">
          <i class="fas fa-users w-5 h-5"></i>
          <span class="ml-4">จัดการผู้เข้าพัก</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="manage_invoice.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_invoice.php">
          <i class="fas fa-file-invoice w-5 h-5"></i>
          <span class="ml-4">จัดการใบแจ้งหนี้</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="payment_list.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="payment_list.php">
          <i class="fas fa-money-bill-wave w-5 h-5"></i>
          <span class="ml-4">การชำระเงิน</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="stay_list.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="stay_list.php">
          <i class="fas fa-bed w-5 h-5"></i>
          <span class="ml-4">รายการเข้าพัก</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="manage_rates.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_rates.php">
          <i class="fas fa-bolt w-5 h-5"></i>
          <span class="ml-4">จัดการหน่วยค่าไฟค่าน้ำ</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="manage_repair.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_repair.php">
          <i class="fas fa-tools w-5 h-5"></i>
          <span class="ml-4">การแจ้งซ่อม</span>
        </a>
      </li>
      <li class="relative px-6 py-3">
        <a data-page="equipment_list.php" class="menu-item inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="equipment_list.php">
          <i class="fas fa-box w-5 h-5"></i>
          <span class="ml-4">จัดการครุภัณฑ์</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
      <!-- Mobile Sidebar -->
      <div
        x-show="isSideMenuOpen"
        x-transition:enter="transition ease-in-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
      ></div>
      <aside
        class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
        x-show="isSideMenuOpen"
        x-transition:enter="transition ease-in-out duration-150"
        x-transition:enter-start="opacity-0 transform -translate-x-20"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 transform -translate-x-20"
        @click.away="closeSideMenu"
        @keydown.escape="closeSideMenu"
      >
        <div class="py-4 text-gray-500 dark:text-gray-400">
          <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="admin_dashboard.php">
            ระบบจัดการหอพัก
          </a>
          <ul class="mt-6">
            <li class="relative px-6 py-3">
              <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
              <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100" href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt w-5 h-5"></i>
                <span class="ml-4">Dashboard</span>
              </a>
            </li>
          </ul>
          <ul>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="add_rooms.php">
                <i class="fas fa-plus-square w-5 h-5"></i>
                <span class="ml-4">เพิ่มห้องพัก</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_member.php">
                <i class="fas fa-users w-5 h-5"></i>
                <span class="ml-4">จัดการผู้เข้าพัก</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_invoice.php">
                <i class="fas fa-file-invoice w-5 h-5"></i>
                <span class="ml-4">จัดการใบแจ้งหนี้</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="payment_list.php">
                <i class="fas fa-money-bill-wave w-5 h-5"></i>
                <span class="ml-4">การชำระเงิน</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="stay_list.php">
                <i class="fas fa-bed w-5 h-5"></i>
                <span class="ml-4">รายการเข้าพัก</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_rates.php">
                <i class="fas fa-bolt w-5 h-5"></i>
                <span class="ml-4">จัดการหน่วยค่าไฟค่าน้ำ</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="manage_repair.php">
                <i class="fas fa-tools w-5 h-5"></i>
                <span class="ml-4">การแจ้งซ่อม</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="equipment_list.php">
                <i class="fas fa-box w-5 h-5"></i>
                <span class="ml-4">จัดการครุภัณฑ์</span>
              </a>
            </li>
          </ul>
        </div>
      </aside>
      <div class="flex flex-col flex-1 w-full">
        <header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
          <div
            class="container flex items-center justify-between h-full px-6 mx-auto text-purple-600 dark:text-purple-300"
          >
            <!-- Mobile hamburger -->
            <button
              class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
              @click="toggleSideMenu"
              aria-label="Menu"
            >
              <svg
                class="w-6 h-6"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
            <div class="flex justify-center flex-1 lg:mr-32">
              <div
                class="relative w-full max-w-xl mr-6 focus-within:text-purple-500"
              >
                <div class="absolute inset-y-0 flex items-center pl-2">
                </div>
              </div>
            </div>
            <ul class="flex items-center flex-shrink-0 space-x-6">
              <!-- Theme toggler -->
              <li class="flex">
                <button
                  class="rounded-md focus:outline-none focus:shadow-outline-purple"
                  @click="toggleTheme"
                  aria-label="Toggle color mode"
                >
                  <template x-if="!dark">
                    <svg
                      class="w-5 h-5"
                      aria-hidden="true"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                      ></path>
                    </svg>
                  </template>
                  <template x-if="dark">
                    <svg
                      class="w-5 h-5"
                      aria-hidden="true"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </template>
                </button>
              </li>
              
<!-- Profile menu -->
<li class="relative">
    <button
        class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none transition-transform duration-200 hover:scale-110"
        @click="toggleProfileMenu"
        @keydown.escape="closeProfileMenu"
        aria-label="Account"
        aria-haspopup="true"
    >
        <img
            class="object-cover w-10 h-10 rounded-full border-2 border-green-500 hover:border-purple-700 transition-all duration-200"
            src="../assets/image/avatar.png"
            alt="Profile Picture"
            aria-hidden="true"
        />
    </button>
    <span class="text-sm font-medium text-gray-800 dark:text-gray-200 ml-2">
        <?php echo isset($_SESSION['ad_fname']) ? htmlspecialchars($_SESSION['ad_fname']) : 'ผู้ใช้'; ?>
    </span>
    <template x-if="isProfileMenuOpen">
        <ul
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="closeProfileMenu"
            @keydown.escape="closeProfileMenu"
            class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-lg shadow-lg dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
            aria-label="submenu"
        >
            <li class="flex">
                <a
                    class="inline-flex items-center w-full px-3 py-2 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    href="../logout.php"
                >
                    <svg
                        class="w-5 h-5 mr-3"
                        aria-hidden="true"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                        ></path>
                    </svg>
                    <span>ออกจากระบบ</span>
                </a>
            </li>
        </ul>
    </template>
</li>
</ul>
</template>
</li>
</ul>
</div>
<script>
  // ดึง URL ปัจจุบัน
  const currentPage = window.location.pathname.split("/").pop();

  // เลือกทุกเมนูที่มี class "menu-item"
  document.querySelectorAll(".menu-item").forEach(menu => {
    // ถ้า data-page ตรงกับไฟล์ปัจจุบัน ให้เพิ่ม active state
    if (menu.getAttribute("data-page") === currentPage) {
      menu.classList.add(
        "text-gray-900",       // สีตัวหนังสือเข้มขึ้น (Light mode)
        "dark:text-white",    // สีตัวหนังสือในโหมดมืด (Dark mode)
        "border",              // เพิ่มขอบ
        "border-purple-600",     // สีขอบ (Light mode)
        "dark:border-purple-600", // สีขอบใน Dark mode
        "rounded-md",          // ขอบโค้งเล็กน้อย
        "px-4", "py-2",        // Padding
        "bg-gray-100",        // พื้นหลังเมนู (Light mode)
        "dark:bg-gray-800"     // พื้นหลังเมนูใน Dark mode
      );

      // เพิ่มแถบสีม่วงด้านซ้ายของเมนู
      const activeBar = document.createElement("span");
      activeBar.classList.add(
        "absolute", "inset-y-0", "left-0", "w-1", 
        "bg-purple-600", "rounded-tr-lg", "rounded-br-lg"
      );
      menu.parentElement.prepend(activeBar);
    }
  });
</script>
</header>