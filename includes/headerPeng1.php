<?php

$isLoggedIn = isset($_SESSION['id']);

$isPengurus = isset($_SESSION['peranan']) && $_SESSION['peranan'] === 'pengurus';

include '../includes/db.php';

$notificationCount = 0;
$notifications = [];

if ($isPengurus && isset($_SESSION['emel'])) {
    $emel = $_SESSION['emel'];
    $stmt = $conn->prepare("SELECT pm.pelajar_emel, pm.permohonan_tarikh, p.pelajar_nama, pm.notified FROM Permohonan pm JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id JOIN Pelajar_UKM p ON pm.pelajar_emel = p.pelajar_emel WHERE ps.pman_emel = ? ORDER BY pm.permohonan_tarikh DESC LIMIT 5");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Only count the unread ones
    $notificationCount = count(array_filter($notifications, fn($n) => $n['notified'] == 0));
}
?>

<!-- Include Alpine.js -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<header class="bg-gradient-to-r from-blue-500 to-cyan-300 p-4 shadow-md flex justify-between items-center relative">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
        <a href="../Pengurus/dashboardPengurus.php">
            <img src="../images/UKMWATAN.png" alt="UKM Logo" class="h-12">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex space-x-6 text-black font-semibold items-center">
        <a href="../Pengurus/dashboardPengurus.php" class="hover:underline">Utama</a>
        <a href="../aktiviti/aktivitiTam.php" class="hover:underline">Aktiviti</a>
        <a href="../ahli/ahlist.php" class="hover:underline">Ahli</a>

        

        <!-- User Dropdown -->
        <?php if (isset($_SESSION['emel'])): ?>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="hover:underline flex items-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></span>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>

                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                    <ul>
                        <li>
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        </li>
                        <li>
                            <form method="POST" action="../index.php">
                                <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="hover:underline flex items-center space-x-2">
                <i class="fas fa-user"></i>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </nav>
</header>
