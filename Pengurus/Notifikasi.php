<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

$conn->query("UPDATE Permohonan pm JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id SET pm.notified = 1 WHERE ps.pman_emel = '$emel' AND pm.notified = 0");
$conn->query("UPDATE AktivitiPenyertaan ap JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
JOIN Persatuan ps ON a.persatuan_id = ps.persatuan_id SET ap.notified = 1 WHERE ps.pman_emel = '$emel' AND ap.notified = 0");
$conn->query("UPDATE notifikasipengurus SET status_baca = 'sudah' WHERE pman_emel = '$emel' AND status_baca = 'belum'");

$permohonanStmt = $conn->prepare("SELECT 'persatuan' AS type, pm.permohonan_id AS id, p.pelajar_nama, pm.pelajar_emel, pm.permohonan_tarikh 
AS tarikh, pm.jenis_permohonan FROM Permohonan pm JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id 
JOIN Pelajar_UKM p ON pm.pelajar_emel = p.pelajar_emel WHERE ps.pman_emel = ?");
$permohonanStmt->bind_param("s", $emel);
$permohonanStmt->execute();
$permohonan = $permohonanStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$aktivitiStmt = $conn->prepare("SELECT 'aktiviti' AS type, ap.penyertaan_id AS id, p.pelajar_nama, ap.pelajar_emel, ap.penyertaan_tarikh AS tarikh, a.aktiviti_nama FROM AktivitiPenyertaan ap JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
JOIN Persatuan ps ON a.persatuan_id = ps.persatuan_id JOIN Pelajar_UKM p ON ap.pelajar_emel = p.pelajar_emel WHERE ps.pman_emel = ?");
$aktivitiStmt->bind_param("s", $emel);
$aktivitiStmt->execute();
$aktiviti = $aktivitiStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$batalStmt = $conn->prepare("SELECT 'batal' AS type, np.id, np.tarikh, np.mesej, np.pelajar_emel, p.pelajar_nama, a.aktiviti_nama 
FROM notifikasipengurus np JOIN Pelajar_UKM p ON np.pelajar_emel = p.pelajar_emel 
JOIN Aktiviti a ON np.aktiviti_id = a.aktiviti_id WHERE np.pman_emel = ?");
$batalStmt->bind_param("s", $emel);
$batalStmt->execute();
$batal = $batalStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$khasStmt = $conn->prepare("SELECT 'khas' AS type, id, tajuk, mesej, tarikh FROM notifikasipengurus WHERE pman_emel = ? AND jenis_notifikasi = 'kes_khas_baru'");
$khasStmt->bind_param("s", $emel);
$khasStmt->execute();
$khas = $khasStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$notifications = array_merge($permohonan, $aktiviti, $batal, $khas);
usort($notifications, function ($a, $b) {
    return strtotime($b['tarikh']) - strtotime($a['tarikh']);
});

$activeId = $_GET['id'] ?? ($notifications[0]['id'] ?? null);
$activeType = $_GET['type'] ?? ($notifications[0]['type'] ?? null);
$activeNotification = null;
foreach ($notifications as $notif) {
    if ($notif['id'] == $activeId && $notif['type'] == $activeType) {
        $activeNotification = $notif;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Home button -->
<div class="p-4 bg-white shadow-md flex justify-between items-center">
    <h1 class="text-xl font-semibold">Notifikasi</h1>
    <a href="../Pengurus/dashboardPengurus.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Menu</a>
</div>

<div class="max-w-5xl mx-auto p-6">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Senarai Notifikasi</h2>
        <?php if (count($notifications) > 0): ?>
            <ul class="divide-y">
                <?php foreach ($notifications as $notif): ?>
                    <li class="flex gap-4 items-start py-5">
                        <div class="text-2xl pt-1">🔔</div>
                        <div class="flex-1 leading-relaxed">
                            <?php if ($notif['type'] == 'khas'): ?>
                                <h3 class="text-sm text-blue-700 font-semibold mb-1"><?= htmlspecialchars($notif['tajuk']) ?></h3>
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($notif['mesej']) ?></p>
                            <?php else: ?>
                                <h3 class="text-sm text-blue-700 font-semibold mb-1"><?= strtoupper(htmlspecialchars($notif['pelajar_nama'])) ?></h3>
                                <p class="text-sm text-gray-700">
                                    <?php if ($notif['type'] == 'persatuan'): ?>
                                        <?php if (($notif['jenis_permohonan'] ?? '') === 'khas'): ?>
                                            telah menghantar permohonan <strong>kes khas</strong> untuk menyertai persatuan.
                                        <?php else: ?>
                                            telah memohon menjadi ahli persatuan.
                                        <?php endif; ?>
                                    <?php elseif ($notif['type'] == 'aktiviti'): ?>
                                        telah menyertai aktiviti <strong><?= htmlspecialchars($notif['aktiviti_nama']) ?></strong>.
                                    <?php elseif ($notif['type'] == 'batal'): ?>
                                        telah membatalkan penyertaan dari aktiviti <strong><?= htmlspecialchars($notif['aktiviti_nama']) ?></strong>.
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-400 mt-2"><?= date('d M Y ', strtotime($notif['tarikh'])) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">Tiada notifikasi ditemui.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
