<?php


include '../includes/db.php';

$isPengurus = isset($_SESSION['peranan']) && $_SESSION['peranan'] === 'pengurus';
$unreadCount = 0;
$notifications = [];

if ($isPengurus && isset($_SESSION['emel'])) {
    $emel = $_SESSION['emel'];

    // Count unread from Permohonan (status: disahkan ATAU menunggu)
// Count unread from Permohonan
$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM Permohonan pm
    JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id
    WHERE ps.pman_emel = ? AND pm.notified = 0 AND (pm.status = 'disahkan' OR pm.status = 'menunggu')");
$stmt1->bind_param("s", $emel);
$stmt1->execute();
$res1 = $stmt1->get_result()->fetch_assoc();
$unreadCount += (int)$res1['total'];



    // Count unread from AktivitiPenyertaan
    $stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM AktivitiPenyertaan ap
        JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id
        JOIN Persatuan ps ON a.persatuan_id = ps.persatuan_id
        WHERE ps.pman_emel = ? AND ap.notified = 0");
    $stmt2->bind_param("s", $emel);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $unreadCount += (int)$res2['total'];

    // Count unread from notifikasipengurus
    $stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM notifikasipengurus WHERE pman_emel = ? AND status_baca = 'belum'");
    $stmt3->bind_param("s", $emel);
    $stmt3->execute();
    $res3 = $stmt3->get_result()->fetch_assoc();
    $unreadCount += (int)$res3['total'];

    // Fetch latest 3 unread notifications
   $query = "
    (SELECT p.pelajar_nama, pm.permohonan_tarikh AS tarikh, 'persatuan' AS jenis
     FROM Permohonan pm
     JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id
     JOIN Pelajar_UKM p ON pm.pelajar_emel = p.pelajar_emel
     WHERE ps.pman_emel = ? 
       AND pm.notified = 0 
      AND (pm.status = 'disahkan' OR pm.status = 'menunggu'))

    UNION ALL

    (SELECT p.pelajar_nama, ap.penyertaan_tarikh AS tarikh, 'aktiviti' AS jenis
     FROM AktivitiPenyertaan ap
     JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id
     JOIN Persatuan ps ON a.persatuan_id = ps.persatuan_id
     JOIN Pelajar_UKM p ON ap.pelajar_emel = p.pelajar_emel
     WHERE ps.pman_emel = ? 
       AND ap.notified = 0)

    ORDER BY tarikh DESC
    LIMIT 3
";


    $stmt4 = $conn->prepare($query);
    $stmt4->bind_param("ss", $emel, $emel);
    $stmt4->execute();
    $notifications = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
<header class="bg-gradient-to-r from-blue-500 to-cyan-300 p-4 shadow-md flex flex-wrap justify-between items-center relative">
    <!-- Logo -->
    <div class="flex items-center space-x-3">
        <a href="../Pengurus/dashboardPengurus.php">
            <img src="../images/UKMWATAN.png" alt="UKM Logo" class="h-12">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-wrap gap-4 text-black font-semibold items-center space-x-4">
        <a href="../Pengurus/dashboardPengurus.php" class="hover:underline">Utama</a>
        <a href="../aktiviti/aktivitiTam.php" class="hover:underline">Aktiviti</a>
        <a href="../ahli/ahlist.php" class="hover:underline">Ahli</a>

        <!-- Notification Dropdown -->
        <?php if ($isPengurus): ?>
        <div class="relative" x-data="{ open: false }" x-cloak>
            <button id="notif-button" @click="open = !open; markNotificationsAsRead()" class="relative">
                <i class="fas fa-bell text-xl"></i>
                <?php if ($unreadCount > 0): ?>
  <span id="notif-badge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
    <?php echo $unreadCount; ?>
  </span>
<?php endif; ?>

            </button>
            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 top-full mt-2 w-80 bg-white border border-gray-300 rounded-lg shadow-lg z-[100]">
                <div class="p-4 font-semibold border-b text-black">Notifikasi</div>
                <ul class="max-h-60 overflow-y-auto divide-y">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $n): ?>
                            <li class="px-4 py-2 text-sm text-gray-700">
                                Pelajar <strong><?= htmlspecialchars($n['pelajar_nama']) ?></strong>
                                <?php if ($n['jenis'] === 'persatuan'): ?> telah berdaftar menjadi ahli Persatuan.
                                <?php else: ?> telah menyertai aktiviti.
                                <?php endif; ?>
                                <br>
                                <small class="text-gray-500"><?= date('d M Y', strtotime($n['tarikh'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="px-4 py-2 text-sm text-gray-500 text-center">Tiada notifikasi baharu.</li>
                    <?php endif; ?>
                </ul>
                <div class="text-center border-t p-2">
                    <a href="../Pengurus/Notifikasi.php" class="text-blue-600 text-sm hover:underline">Lihat Semua</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- User Dropdown -->
        <?php if (isset($_SESSION['emel'])): ?>
        <div class="relative" x-data="{ open: false }" x-cloak>
            <button @click="open = !open" class="hover:underline flex items-center space-x-2">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></span>
                <i class="fas fa-chevron-down text-sm"></i>
            </button>
            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 top-full mt-2 w-48 bg-white border border-gray-300 rounded-lg shadow-lg z-[100]">
                <ul>
                    <li><a href="../Pengurus/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a></li>
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

    <!-- Script to mark noti as read -->
    <script>
    function markNotificationsAsRead() {
        fetch('../Pengurus/readnotifivations.php')
            .then(response => response.text())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                if (badge) badge.remove();
            });
    }
    </script>
</header>
</body>
</html>
