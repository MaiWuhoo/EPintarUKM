<?php
session_start();
include '../includes/db.php';

$where = [];
$params = [];
$types = "";

function translateDayToMalay($englishDay) {
    $days = [
        'Sunday' => 'Ahad',
        'Monday' => 'Isnin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Khamis',
        'Friday' => 'Jumaat',
        'Saturday' => 'Sabtu'
    ];
    return $days[$englishDay] ?? $englishDay;
}

if (!empty($_GET['keyword'])) {
    $where[] = "a.aktiviti_nama LIKE ?";
    $params[] = "%" . $_GET['keyword'] . "%";
    $types .= "s";
}
if (!empty($_GET['location'])) {
    $where[] = "a.aktiviti_tempat LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}
if (!empty($_GET['date'])) {
    $where[] = "a.tarikh_mula <= ? AND a.tarikh_tamat >= ?";
    $params[] = $_GET['date'];
    $params[] = $_GET['date'];
    $types .= "ss";
}
if (!empty($_GET['jenis_aktiviti'])) {
    $where[] = "a.aktiviti_jenis = ?";
    $params[] = $_GET['jenis_aktiviti'];
    $types .= "s";
}
$where[] = "(a.status IS NULL OR a.status != 'batal')";

$sql = "SELECT a.*, p.persatuan_nama 
        FROM Aktiviti a
        JOIN Persatuan p ON a.persatuan_id = p.persatuan_id";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY a.tarikh_mula DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$aktiviti = [];
while ($row = $result->fetch_assoc()) {
    $aktiviti[] = $row;
}
$now = date('d M Y h:i A');

$perPage = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$pastSql = "SELECT a.*, p.persatuan_nama 
            FROM Aktiviti a
            JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
            WHERE a.tarikh_tamat < CURDATE() AND (a.status IS NULL OR a.status != 'batal')
            ORDER BY a.tarikh_mula DESC
            LIMIT $perPage OFFSET $offset";
$past = $conn->query($pastSql)->fetch_all(MYSQLI_ASSOC);

$countSql = "SELECT COUNT(*) as total FROM Aktiviti WHERE tarikh_tamat < CURDATE() AND (status IS NULL OR status != 'batal')";
$totalAktiviti = $conn->query($countSql)->fetch_assoc()['total'];
$totalPages = ceil($totalAktiviti / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aktiviti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-white min-h-screen">
<?php include '../includes/headerPel.php'; ?>
<div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-blue-600 mb-4">Aktiviti</h1>
    <form method="GET" action="">
        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            <input type="text" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Keywords" class="p-2 border rounded w-full md:w-48">
            <select name="jenis_aktiviti" class="p-2 border rounded w-full md:w-48">
                <option value="">Semua Jenis</option>
                <option value="Sukan" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Sukan' ? 'selected' : '' ?>>Sukan</option>
                <option value="Baktisiswa/Khidmat Masyarakat" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Baktisiswa/Khidmat Masyarakat' ? 'selected' : '' ?>>Baktisiswa/Khidmat Masyarakat</option>
                <option value="Debat / Pidato" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Debat / Pidato' ? 'selected' : '' ?>>Debat / Pidato</option>
                <option value="Ekspo/ Karnival / Pameran / Festival" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Ekspo/ Karnival / Pameran / Festival' ? 'selected' : '' ?>>Ekspo/ Karnival / Pameran / Festival</option>
                <option value="Forum / Diskusi / Ceramah" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Forum / Diskusi / Ceramah' ? 'selected' : '' ?>>Forum / Diskusi / Ceramah</option>
                <option value="Jamuan Makan Malam / Ulang Tahun" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Jamuan Makan Malam / Ulang Tahun' ? 'selected' : '' ?>>Jamuan Makan Malam / Ulang Tahun</option>
                <option value="Keagamaan" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Keagamaan' ? 'selected' : '' ?>>Keagamaan</option>
                <option value="Kebudayaan / Kesenian" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Kebudayaan / Kesenian' ? 'selected' : '' ?>>Kebudayaan / Kesenian</option>
                <option value="Keusahawanan" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Keusahawanan' ? 'selected' : '' ?>>Keusahawanan</option>
                <option value="Latihan / Kursus / Bengkel" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Latihan / Kursus / Bengkel' ? 'selected' : '' ?>>Latihan / Kursus / Bengkel</option>
                <option value="Lawatan Sambil Belajar / Mobiliti" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Lawatan Sambil Belajar / Mobiliti' ? 'selected' : '' ?>>Lawatan Sambil Belajar / Mobiliti</option>
                <option value="Penyertaan / Pertandingan" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Penyertaan / Pertandingan' ? 'selected' : '' ?>>Penyertaan / Pertandingan</option>
                <option value="Perjumpaan / Perhimpunan / Hari Keluarga" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Perjumpaan / Perhimpunan / Hari Keluarga' ? 'selected' : '' ?>>Perjumpaan / Perhimpunan / Hari Keluarga</option>
                <option value="Perkhemahan / Eksplorasi" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Perkhemahan / Eksplorasi' ? 'selected' : '' ?>>Perkhemahan / Eksplorasi</option>
                <option value="Seminar / Persidangan" <?= ($_GET['jenis_aktiviti'] ?? '') === 'Seminar / Persidangan' ? 'selected' : '' ?>>Seminar / Persidangan</option>
            </select>
            <input type="text" name="location" value="<?= $_GET['location'] ?? '' ?>" placeholder="Lokasi" class="p-2 border rounded w-full md:w-48">
            <input type="date" name="date" value="<?= $_GET['date'] ?? '' ?>" class="p-2 border rounded w-full md:w-48">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full md:w-auto">Cari</button>
            <a href="dashAktiviti.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded text-center w-full md:w-auto hover:bg-gray-400 transition">🔄 Reset</a>
        </div>
    </form>

    <!-- Aktiviti Akan Datang -->
    <h2 class="text-xl font-bold text-blue-500 mb-3">Aktiviti Akan Datang</h2>
    <?php
    $adaAktivitiAkanDatang = false;
    foreach ($aktiviti as $a) {
        $aktivitiTamat = strtotime($a['tarikh_tamat'] . ' ' . $a['aktiviti_tamat']);
if ($aktivitiTamat >= strtotime($now)){
            $adaAktivitiAkanDatang = true;
            break;
        }
    }
    ?>
<?php if ($adaAktivitiAkanDatang): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <?php 
    $now = date('Y-m-d h:i A');
    $nowTime = strtotime($now);
    foreach ($aktiviti as $a): 
        $aktivitiTamatString = $a['tarikh_tamat'] . ' ' . $a['aktiviti_tamat'];
        $aktivitiTamat = strtotime($aktivitiTamatString);
    ?>
        <?php if ($aktivitiTamat >= $nowTime): ?>
            <?php
            $tarikh = strtotime($a['tarikh_mula']);
            $hari = date('d', $tarikh);
            $bulan = strtoupper(date('M', $tarikh));
            ?>
                <div class="relative bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                    <div class="absolute top-2 left-2 bg-white text-center px-2 py-1 rounded shadow text-xs font-bold">
                        <?= $hari ?><br><?= $bulan ?>
                    </div>
                    <img src="../uploads/<?= $a['aktiviti_gambar'] ?? 'default.jpg' ?>" class="w-full h-48 object-cover" alt="Poster">
                    <div class="p-4">
                        <h3 class="text-md font-bold mb-1">
                            <a href="aktivitiDetails.php?id=<?= $a['aktiviti_id'] ?>" class="hover:underline">
                                <?= $a['aktiviti_nama'] ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500 mb-1">🗓️ <?= date('d M Y', strtotime($a['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($a['tarikh_tamat'])) ?>
                        </p>
                        <p class="text-sm text-gray-500 mb-1">⏰ <?= date('g:i A', strtotime($a['aktiviti_mula'])) ?> – <?= date('g:i A', strtotime($a['aktiviti_tamat'])) ?>
                        </p>
                        <p class="text-sm text-gray-500 mb-2">
                            📍
                            <?= $a['aktiviti_tempat'] ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-2">🏛️<?= $a['persatuan_nama'] ?></p>
                        <?php if (!empty($a['aktiviti_jenis'])): ?>
                            <span class="inline-block bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded">
                                <?= $a['aktiviti_jenis'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500 italic mb-10">
        Tiada aktiviti akan datang buat masa ini.
    </div>
<?php endif; ?>


    <!-- Aktiviti Lepas -->
<hr class="my-10 border-gray-300">
<h2 class="text-xl font-bold text-blue-700 mb-3 mt-12">AKTIVITI LEPAS</h2>
<?php if (!empty($past)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($past as $a): ?>
            <?php $tarikh = strtotime($a['tarikh_mula']); $hari = date('d', $tarikh); $bulan = strtoupper(date('M', $tarikh)); ?>
            <div class="relative bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                <div class="absolute top-2 left-2 bg-white text-center px-2 py-1 rounded shadow text-xs font-bold">
                    <?= $hari ?><br><?= $bulan ?>
                </div>
                <img src="../uploads/<?= $a['aktiviti_gambar'] ?? 'default.jpg' ?>" class="w-full h-48 object-cover" alt="Poster">
                <div class="p-4">
                    <h3 class="text-md font-bold mb-1"><?= $a['aktiviti_nama'] ?></h3>
                    <p class="text-sm text-gray-500 mb-1">
                        🗓️
                        <?= date('d M Y', strtotime($a['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($a['tarikh_tamat'])) ?> 
                    </p>
                    <p class="text-sm text-gray-500 mb-1">⏰ <?= date('g:i A', strtotime($a['aktiviti_mula'])) ?> – <?= date('g:i A', strtotime($a['aktiviti_tamat'])) ?>
                        </p>
                    <p class="text-sm text-gray-500 mb-2">
                        📍
                        <?= $a['aktiviti_tempat'] ?>
                    </p>
                    <p class="text-sm text-gray-600 mb-2">🏛️ <?= $a['persatuan_nama'] ?></p>
                    <?php if (!empty($a['aktiviti_jenis'])): ?>
                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                            <?= $a['aktiviti_jenis'] ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 flex justify-center space-x-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="px-3 py-1 rounded border <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-100' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php else: ?>
    <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500 italic mb-10">
        Tiada rekod aktiviti lepas buat masa ini.
    </div>
<?php endif; ?>

</div>
<?php include '../Chatbot/chatbot.php'; ?>
</body>
</html>