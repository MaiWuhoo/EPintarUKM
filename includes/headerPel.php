<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['emel']);
$isPelajar = isset($_SESSION['peranan']) && $_SESSION['peranan'] === 'pelajar';
$displayName = $_SESSION['nama'] ?? 'User';

$notiCount = 0;
$notiList = [];

if ($isPelajar) {
    include '../includes/db.php';
    $emel = $_SESSION['emel'];

    // Count unread notifications (status = 'baru' & notified = 0)
$countStmt = $conn->prepare("SELECT COUNT(*) as total 
                             FROM NotifikasiPelajar 
                             WHERE pelajar_emel = ? AND status = 'baru' AND notified = 0");
$countStmt->bind_param("s", $emel);
$countStmt->execute();
$notiCount = $countStmt->get_result()->fetch_assoc()['total'];

// Fetch latest 3 unread notifications
$listStmt = $conn->prepare("SELECT id, tajuk, mesej, created_at 
                            FROM NotifikasiPelajar 
                            WHERE pelajar_emel = ? AND status = 'baru' AND notified = 0 
                            ORDER BY created_at DESC LIMIT 3");
$listStmt->bind_param("s", $emel);
$listStmt->execute();
$notiList = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);

}
?>

<!-- Include Alpine.js & FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>

<header class="bg-gradient-to-r from-blue-500 to-cyan-300 p-4 shadow-md flex justify-between items-center relative">

    <!-- Logo -->
    <div class="flex items-center space-x-3">
        <a href="../pelajar/dashboardPelajar.php">
            <img src="../images/UKMWATAN.png" alt="UKM Logo" class="h-12">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex space-x-6 text-black font-semibold items-center">
        <a href="../pelajar/dashboardPelajar.php" class="hover:underline">Utama</a>
        <a href="../Aktiviti/dashAktiviti.php" class="hover:underline">Aktiviti</a>
        <a href="../Persatuan/dashPersatuan.php" class="hover:underline">Persatuan</a>

        <?php if ($isPelajar): ?>
        <!-- Notification Button -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" id="student-notif-btn" class="relative">
                <i class="fas fa-bell text-xl"></i>
                <?php if ($notiCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        <?= $notiCount ?>
                    </span>
                <?php endif; ?>
            </button>
            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                <div class="p-4 font-semibold border-b text-black">Notifikasi Aktiviti</div>
                <ul class="max-h-60 overflow-y-auto divide-y">
                    <?php if (count($notiList) > 0): ?>
                        <?php foreach ($notiList as $notif): ?>
                            <li class="px-4 py-2 text-sm text-gray-700">
                                <strong><?= htmlspecialchars($notif['tajuk']) ?></strong><br>
                                <?= htmlspecialchars($notif['mesej']) ?><br>
                                <small class="text-gray-500"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="px-4 py-2 text-sm text-gray-500 text-center">Tiada notifikasi terkini.</li>
                    <?php endif; ?>
                </ul>
                <div class="text-center border-t p-2">
                    <a href="../Pelajar/notifikasiPel.php" class="text-blue-600 text-sm hover:underline">Lihat Semua</a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="hover:underline flex items-center space-x-2">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($displayName) ?></span>
                <i class="fas fa-chevron-down text-sm"></i>
            </button>
            <div x-show="open" @click.outside="open = false"
                class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-lg shadow-lg z-50">
                <ul>
                    <li>
                        <a href="../Pelajar/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
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
        <a href="../login.php" class="hover:underline flex items-center space-x-2">
            <i class="fas fa-user"></i>
            <span>Login</span>
        </a>
        <?php endif; ?>
    </nav>
</header>
