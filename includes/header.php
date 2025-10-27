<?php
session_start(); // Needed to access login session
$isLoggedIn = isset($_SESSION['id']); // You can adjust this based on your login system
$isPelajar = isset($_SESSION['jenis']) && $_SESSION['jenis'] === 'pelajar';
$isPengurus = isset($_SESSION['jenis']) && $_SESSION['jenis'] === 'pengurus';
?>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<header class="bg-gradient-to-r from-blue-500 to-cyan-300 p-4 shadow-md flex justify-between items-center relative">
    <!-- Left: Logo -->
    <div class="flex items-center space-x-3">
        <a href="index.php">
            <img src="images/UKMWATAN.png" alt="UKM Logo" class="h-12">
        </a>
    </div>

    <!-- Right: Navigation Menu -->
    <nav class="flex space-x-6 text-black font-semibold items-center">
        <a href="index.php" class="hover:underline">Utama</a>

        <?php if ($isLoggedIn): ?>
            <!-- Direct link if logged in -->
            <a href="aktiviti/index.php" class="hover:underline">Aktiviti</a>
            <a href="persatuan/index.php" class="hover:underline">Persatuan</a>
        <?php else: ?>
            <!-- Trigger login prompt for guest -->
            <a href="#" onclick="guestRedirect()" class="hover:underline">Aktiviti</a>
            <a href="#" onclick="guestRedirect()" class="hover:underline">Persatuan</a>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <!-- User Dropdown -->
            <div class="relative">
                <button id="user-dropdown-btn" class="hover:underline flex items-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span><?= $_SESSION['nama'] ?? 'User'; ?></span>
                </button>

                <div id="user-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-50">
                    <ul>
                        <li>
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        </li>
                        <li>
                            <form method="POST" action="logout.php">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="hover:underline flex items-center space-x-2">
                <i class="fas fa-user"></i>
                <span>Log Masuk</span>
            </a>
        <?php endif; ?>
    </nav>
</header>

<!-- Dropdown Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userDropdownBtn = document.getElementById('user-dropdown-btn');
        const userDropdownMenu = document.getElementById('user-dropdown-menu');

        if (userDropdownBtn && userDropdownMenu) {
            userDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (event) => {
                if (!userDropdownBtn.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.add('hidden');
                }
            });
        }
    });

    function guestRedirect() {
        Swal.fire({
            title: 'Sila log masuk terlebih dahulu',
            text: "Anda perlu log masuk untuk melihat kandungan ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Log Masuk',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
    }
</script>
