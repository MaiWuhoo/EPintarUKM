<?php
session_start();
include '../includes/db.php';

$pelajarNama = 'Pelajar';
$persatuan_id = null;
$emel = '';
if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pelajar') {
    $emel = $_SESSION['emel'];
    $stmt = $conn->prepare("SELECT pelajar_nama FROM Pelajar_UKM WHERE pelajar_emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->bind_result($pelajarNama);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT persatuan_id FROM Permohonan WHERE pelajar_emel = ? AND status = 'disahkan' LIMIT 1");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->bind_result($persatuan_id);
    $stmt->fetch();
    $stmt->close();
}

$leaderboard = [];
if ($persatuan_id) {
    $query = $conn->prepare("SELECT p.pelajar_nama, p.pelajar_emel, p.pelajar_gambar, COUNT(ap.aktiviti_id) AS penyertaan 
                             FROM AktivitiPenyertaan ap
                             JOIN Aktiviti a ON a.aktiviti_id = ap.aktiviti_id
                             JOIN Pelajar_UKM p ON ap.pelajar_emel = p.pelajar_emel
                             JOIN Permohonan pm ON pm.pelajar_emel = p.pelajar_emel
                             WHERE a.persatuan_id = ? 
                               AND a.status = 'aktif'
                               AND pm.status = 'disahkan'
                               AND pm.persatuan_id = a.persatuan_id
                             GROUP BY ap.pelajar_emel
                             ORDER BY penyertaan DESC LIMIT 10");
    $query->bind_param("i", $persatuan_id);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
    $query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard Persatuan</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
    <style>
        .podium-box {
            transition: all 0.4s ease-in-out;
            animation: fadeInUp 0.6s ease-in-out;
        }
        .podium-box:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        @keyframes fadeInUp {
            0% {
                transform: translateY(30px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include '../includes/headerPel.php'; ?>
<div class="max-w-5xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl text-center font-bold mb-6 text-indigo-700 flex items-center gap-2">
            🏆 Leaderboard Persatuan
        </h2>

        <?php if (count($leaderboard) > 0): ?>
        <div class="flex items-end justify-center gap-4 h-64 mb-8">
            <?php
                $styles = [
                    0 => 'bg-yellow-400 h-56',
                    1 => 'bg-gray-400 h-48',
                    2 => 'bg-orange-400 h-40'
                ];
                $labels = [
                    0 => '🥇 1st',
                    1 => '🥈 2nd',
                    2 => '🥉 3rd'
                ];
                foreach ([2, 0, 1] as $i):
                    if (!isset($leaderboard[$i])) continue;
                    $entry = $leaderboard[$i];
                    $gambar = !empty($entry['pelajar_gambar']) ? '../uploads/' . $entry['pelajar_gambar'] : '../images/defaultProfile.png';
                    $warna = $styles[$i];
            ?>
            <div class="<?= $warna ?> w-24 rounded-t-lg flex flex-col items-center justify-end relative podium-box">
                <img src="<?= $gambar ?>" class="w-12 h-12 rounded-full border-2 border-white absolute -top-6" alt="avatar">
                <span class="absolute -top-12 text-xs font-bold text-white bg-black bg-opacity-50 px-2 py-1 rounded-full">
                    <?= $labels[$i] ?>
                </span>
                <span class="text-white font-semibold mt-16 text-sm text-center px-1">
                    <?= htmlspecialchars($entry['pelajar_nama']) ?>
                    <?= $entry['pelajar_emel'] === $emel ? '<br><span class=\"text-blue-100\"></span>' : '' ?>
                </span>
                <span class="text-white text-xs mb-2">
                    <?= $entry['penyertaan'] ?> aktiviti
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <table class="min-w-full table-auto text-sm text-left">
            <thead class="bg-blue-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2 text-center">Penyertaan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($leaderboard as $index => $entry): ?>
                    <?php if (in_array($index, [0,1,2])) continue; ?>
                    <tr class="<?= $entry['pelajar_emel'] === $emel ? 'bg-yellow-50 font-semibold' : '' ?>">
                        <td class="px-4 py-2"><?= $index + 1 ?></td>
                        <td class="px-4 py-2">
                            <?= htmlspecialchars($entry['pelajar_nama']) ?>
                            <?= $entry['pelajar_emel'] === $emel ? '<span class="text-blue-600"> </span>' : '' ?>
                        </td>
                        <td class="px-4 py-2 text-center text-blue-600 font-medium">
                            <?= $entry['penyertaan'] ?> aktiviti
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-sm text-gray-500">Tiada penyertaan dalam aktiviti anjuran persatuan anda.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
