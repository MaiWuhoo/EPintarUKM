<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

// Tandakan semua notifikasi sebagai sudah dibaca
$conn->query("UPDATE notifikasipelajar SET status = 'dibaca', notified = 1 WHERE pelajar_emel = '$emel' AND status = 'baru' AND notified = 0");


// Ambil semua notifikasi pelajar
$stmt = $conn->prepare("SELECT id, jenis_notifikasi, tajuk, mesej, created_at FROM notifikasipelajar 
WHERE pelajar_emel = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $emel);
$stmt->execute();
$notifikasi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="p-4 bg-white shadow-md flex justify-between items-center">
    <h1 class="text-xl font-semibold">Notifikasi Anda</h1>
    <a href="dashboardPelajar.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Menu</a>
</div>

<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-bold mb-4">Senarai Notifikasi</h2>
        <?php if (count($notifikasi) > 0): ?>
            <ul class="divide-y">
                <?php foreach ($notifikasi as $n): ?>
                    <li class="flex gap-4 items-start py-5">
                        <div class="text-2xl pt-1">
                            <?php
                            $icon = '🔔'; $warna = 'text-gray-400';
                            switch ($n['jenis_notifikasi']) {
                                case 'aktiviti_baru': $icon = '🗓️'; $warna = 'text-blue-500'; break;
                                case 'aktiviti_batal': $icon = '🚫'; $warna = 'text-red-500'; break;
                                case 'kes_khas_disahkan': $icon = '✅'; $warna = 'text-green-500'; break;
                                case 'kes_khas_ditolak': $icon = '❌'; $warna = 'text-red-500'; break;
                            }
                            ?>
                            <span class="<?= $warna ?>"><?= $icon ?></span>
                        </div>
                        <div class="flex-1 leading-relaxed">
                            <h3 class="text-sm text-blue-700 font-semibold mb-1"><?= htmlspecialchars($n['tajuk']) ?></h3>
                            <p class="text-sm text-gray-700"><?= htmlspecialchars($n['mesej']) ?></p>
                            <p class="text-xs text-gray-400 mt-2"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Tiada notifikasi buat masa ini.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
