<?php
session_start();
include '../includes/db.php';

$emel = $_SESSION['emel'] ?? '';
$data = [];

if ($emel) {
    include '../controllers/fetchPelajarInfo.php';
}

// Fallback profile image
$defaultImage = '../images/defaultProfile.png'; // pastikan fail ni ada
$uploadedImagePath = '../uploads/' . ($data['pelajar_gambar'] ?? '');

$profileImage = (!empty($data['pelajar_gambar']) && file_exists($uploadedImagePath))
    ? $uploadedImagePath
    : $defaultImage;


// Total joined activities
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM AktivitiPenyertaan WHERE pelajar_emel=?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Upcoming activities
$stmt = $conn->prepare("SELECT COUNT(*) AS upcoming FROM Aktiviti a 
                        JOIN AktivitiPenyertaan ap ON a.aktiviti_id = ap.aktiviti_id 
                        WHERE ap.pelajar_emel=? AND a.tarikh_mula >= CURDATE()");
$stmt->bind_param("s", $emel);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_assoc()['upcoming'] ?? 0;

// Total reviews submitted by student
$stmt = $conn->prepare("SELECT COUNT(*) AS total_ulasan FROM aktivitireview WHERE pelajar_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$total_ulasan = $stmt->get_result()->fetch_assoc()['total_ulasan'] ?? 0;

$badge = [];

// 🏅 10 Aktiviti Disertai
if ($total >= 10) {
    $badge[] = '🏅 10 Aktiviti Disertai';
}

// ✅ Semua Aktiviti Diulas
$stmt = $conn->prepare("SELECT COUNT(*) as total_joined FROM AktivitiPenyertaan WHERE pelajar_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$total_joined = $stmt->get_result()->fetch_assoc()['total_joined'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(DISTINCT aktiviti_id) as total_reviewed FROM aktivitireview WHERE pelajar_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$total_reviewed = $stmt->get_result()->fetch_assoc()['total_reviewed'] ?? 0;

if ($total_joined > 0 && $total_joined == $total_reviewed) {
    $badge[] = '✅ Semua Aktiviti Diulas';
}

// 🎓 Senior Cemerlang
if (($data['pelajar_tahun'] ?? 0) >= 3 && $total >= 5) {
    $badge[] = '🎓 Senior Cemerlang';
}



// Membership info
// Membership info (hanya jika DISAHKAN)
$persatuanText = "Belum menyertai mana-mana persatuan";
$stmt = $conn->prepare("SELECT ps.persatuan_nama 
                        FROM Permohonan pm 
                        JOIN Persatuan ps ON ps.persatuan_id = pm.persatuan_id 
                        WHERE pm.pelajar_emel = ? AND pm.status = 'disahkan'
                        ORDER BY pm.permohonan_tarikh DESC LIMIT 1");

$stmt->bind_param("s", $emel);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $persatuanText = "Ahli Aktif : " . htmlspecialchars($row['persatuan_nama']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css' rel='stylesheet'>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<?php include '../includes/headerPel.php'; ?>

<div class="flex">
    <?php include '../includes/sidebarPel.php'; ?>

    <div class="flex-1 p-8">
        <!-- Profile Summary -->
        <div class="bg-white shadow rounded-lg p-6 text-center relative">
            <div class="w-full h-32 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-t-lg -mt-6"></div>
            <div class="-mt-16 flex justify-center">
                <img src="<?= $profileImage ?>" alt="Avatar" class="w-28 h-28 rounded-full border-4 border-white shadow">
            </div>
            <h2 class="text-2xl font-bold mt-4"><?= htmlspecialchars($data['pelajar_nama'] ?? '-') ?></h2>
            <p class="text-gray-600"><?= htmlspecialchars($data['pelajar_matrik'] ?? '-') ?></p>
            <p class="text-sm text-gray-500 mt-1"><?= $persatuanText ?></p>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-6 mt-6">
                <div class="bg-blue-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Aktiviti Disertai</p>
                    <p class="text-2xl font-semibold text-blue-600"><?= $total ?></p>
                </div>
                <div class="bg-blue-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Aktiviti Akan Datang</p>
                    <p class="text-2xl font-semibold text-blue-600"><?= $upcoming ?></p>
                </div>
                <div class="bg-blue-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Ulasan Dihantar</p>
                    <p class="text-2xl font-semibold text-blue-600"><?= $total_ulasan ?></p>
                </div>
            </div>
        </div>

        <!-- Contact Info Card -->
        <!-- Contact & Academic Info -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

    <!-- My Contact -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">My Contact</h3>
            <a href="updateprofile.php" class="text-blue-600 text-sm hover:underline">Edit</a>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-xs text-gray-500 font-medium">EMAIL</p>
                <p class="text-sm flex items-center gap-2">
                    <i class="fas fa-envelope text-blue-600"></i>
                    <span><?= htmlspecialchars($data['pelajar_emel'] ?? $emel) ?></span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">PHONE</p>
                <p class="text-sm flex items-center gap-2">
                    <i class="fas fa-phone text-blue-600"></i>
                    <span><?= htmlspecialchars($data['pelajar_telefon'] ?? '-') ?></span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">LOCATION</p>
                <p class="text-sm flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                    <span><?= htmlspecialchars($data['pelajar_alamat'] ?? '-') ?></span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">STATUS KEAHLIAN</p>
                <p class="text-sm flex items-center gap-2">
                    <i class="fas fa-users text-blue-600"></i>
                    <span><?= $persatuanText ?></span>
                </p>
            </div>
        </div>
    </div>

    <!-- Maklumat Akademik -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Maklumat Akademik</h3>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-xs text-gray-500 font-medium">FAKULTI</p>
                <p class="text-sm"><?= htmlspecialchars($data['pelajar_fakulti'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">PROGRAM</p>
                <p class="text-sm"><?= htmlspecialchars($data['pelajar_program'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">TAHUN PENGAJIAN</p>
                <p class="text-sm">Tahun <?= htmlspecialchars($data['pelajar_tahun'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">KOLEJ KEDIAMAN</p>
                <p class="text-sm"><?= htmlspecialchars($data['pelajar_kolej'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    


</div>


    </div>
</div>

</body>
</html>
