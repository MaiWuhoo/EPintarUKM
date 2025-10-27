<?php
session_start();
include '../includes/db.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>ID tidak sah.</p>";
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM Persatuan WHERE persatuan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<p class='text-red-600'>Persatuan tidak dijumpai.</p>";
    exit;
}

// Fetch jumlah ahli real-time
$stmtJumlah = $conn->prepare("SELECT COUNT(*) as total FROM Permohonan WHERE persatuan_id = ?");
$stmtJumlah->bind_param("i", $id);
$stmtJumlah->execute();
$resultJumlah = $stmtJumlah->get_result()->fetch_assoc();
$jumlahAhli = $resultJumlah['total'];


$emel = $_SESSION['emel'] ?? null;
$hasJoined = false;
$isPelajar = $_SESSION['peranan'] === 'pelajar';

$adaKhasMenunggu = false;

if ($emel && $isPelajar) {
    $stmtKhas = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND jenis_permohonan = 'khas' AND status = 'menunggu'");
    $stmtKhas->bind_param("s", $emel);
    $stmtKhas->execute();
    $adaKhasMenunggu = $stmtKhas->get_result()->num_rows > 0;
}


if ($emel && $isPelajar) {
   $check = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND persatuan_id = ? AND status = 'disahkan'");
    $check->bind_param("si", $emel, $id);
    $check->execute();
    $hasJoined = $check->get_result()->num_rows > 0;
}

$updatedAt = $data['updated_at'] ?? null;
$createdAt = $data['created_at'] ?? null;
$displayDate = $updatedAt ? date('d F Y', strtotime($updatedAt)) : ($createdAt ? date('d F Y', strtotime($createdAt)) : 'Tidak diketahui');

// Pelajar info
$pelajar = [];
if ($isPelajar) {
    $stmtPel = $conn->prepare("SELECT * FROM Pelajar_UKM WHERE pelajar_emel = ?");
    $stmtPel->bind_param("s", $emel);
    $stmtPel->execute();
    $pelajar = $stmtPel->get_result()->fetch_assoc();
}

// Kod negeri from IC
$kodNegeri = [
    '01' => 'Johor', '02' => 'Kedah', '03' => 'Kelantan', '04' => 'Melaka', '05' => 'Negeri Sembilan',
    '06' => 'Pahang', '07' => 'Pulau Pinang', '08' => 'Perak', '09' => 'Perlis', '10' => 'Selangor',
    '11' => 'Terengganu', '12' => 'Sabah', '13' => 'Sarawak'
];

$pelajarIC = $pelajar['pelajar_kadpengenalan'] ?? '';
$icKod = substr($pelajarIC, 6, 2);
$kodPersatuan = $data['persatuan_kodNegeri'] ?? '';
$isValid = $icKod === $kodPersatuan;
$namaNegeri = $kodNegeri[$kodPersatuan] ?? 'Tidak diketahui';

// Jumlah aktiviti dianjurkan (tidak batal)
$stmtAktif = $conn->prepare("SELECT COUNT(*) AS total FROM Aktiviti WHERE persatuan_id = ? AND (status IS NULL OR status != 'batal')");
$stmtAktif->bind_param("i", $id);
$stmtAktif->execute();
$jumlahAktiviti = $stmtAktif->get_result()->fetch_assoc()['total'] ?? 0;

// Jumlah ahli
$stmtJumlah = $conn->prepare("SELECT COUNT(*) as total FROM Permohonan WHERE persatuan_id = ? AND status = 'disahkan'");
$stmtJumlah->bind_param("i", $id);
$stmtJumlah->execute();
$resultJumlah = $stmtJumlah->get_result()->fetch_assoc();
$jumlahAhli = $resultJumlah['total'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($data['persatuan_nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPel.php'; ?>

<div class="max-w-6xl mx-auto p-6 space-y-6" x-data="{}">
    <!-- Header with only name -->
    <div class="bg-white p-6 rounded shadow flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <img src="../uploads/<?= $data['persatuan_logo'] ?? 'default-logo.png' ?>" alt="Logo" class="w-16 h-16 object-contain">
            <div>
                <h1 class="text-2xl font-bold"><?= htmlspecialchars($data['persatuan_nama']) ?></h1>
                <p class="text-sm text-gray-600 mt-1">
                    Tarikh terakhir kemaskini: <?= $displayDate ?: '<span class="italic text-gray-400">Tiada tarikh dikemas kini</span>' ?>
                </p>

                <?php if (!$hasJoined): ?>
    <?php if ($adaKhasMenunggu): ?>
        <button disabled class="bg-gray-100 mt-4 px-4 py-2 rounded text-black cursor-not-allowed" title="Tunggu permohonan kes khas diproses">SERTAI</button>
    <?php else: ?>
        <button onclick="openModal()" class="bg-gray-200 mt-4 px-4 py-2 rounded hover:bg-gray-300">SERTAI</button>
    <?php endif; ?>
<?php endif; ?>

            </div>

            
        </div>
        <!-- Statistik Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
  <!-- Aktiviti Dianjurkan -->
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">Aktiviti Dianjurkan</p>
    <p class="text-3xl font-bold text-blue-600"><?= $jumlahAktiviti ?></p>
  </div>

  <!-- Jumlah Ahli -->
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">Jumlah Ahli</p>
    <p class="text-3xl font-bold text-green-600"><?= $jumlahAhli ?></p>
  </div>
</div>


        
    </div>

    <!-- Sections -->
    <?php
    $sections = [
        'Pengerusi' => 'pengerusi_nama',
        'Pengenalan' => 'pengenalan',
        'Objektif' => 'objektif',
        'Misi' => 'misi',
        'Visi' => 'visi',
        'Kelebihan Menyertai' => 'benefit',
        'Laman Sosial Media' => 'sosial_media',
    ];
    foreach ($sections as $title => $field): ?>
        <div class="bg-white rounded shadow overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full text-left p-4 flex justify-between items-center hover:bg-gray-50">
                <h2 class="font-semibold text-blue-600 uppercase"><?= $title ?></h2>
                <span x-text="open ? '-' : '+'" class="text-blue-600 font-bold"></span>
            </button>
            <div x-show="open" x-collapse class="p-4 border-t text-sm text-gray-700 whitespace-pre-line">
                <?php
                if ($field === 'jumlahAhli') {
                    echo $jumlahAhli . ' orang';
                } else {
                    echo !empty($data[$field]) ? nl2br(htmlspecialchars($data[$field])) : '<span class="italic text-gray-400">Maklumat belum dikemas kini</span>';
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Organisasi Image -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold text-blue-600 uppercase mb-2">Ahli Jawatankuasa</h2>
        <?php if (!empty($data['organisasi_img'])): ?>
            <img src="../uploads/<?= $data['organisasi_img'] ?>" alt="Struktur Organisasi" class="w-[500px] max-w-full h-auto object-contain mx-auto rounded">
        <?php else: ?>
            <p class="italic text-gray-400">Maklumat belum dikemas kini</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for Join -->
<div id="joinModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md space-y-4">
        <h2 class="text-lg font-bold text-center">Sahkan Permohonan</h2>
        <p><strong>Nama:</strong> <?= htmlspecialchars($pelajar['pelajar_nama'] ?? '-') ?></p>
        <p><strong>No Matrik:</strong> <?= htmlspecialchars($pelajar['pelajar_matrik'] ?? '-') ?></p>
        <p><strong>No IC:</strong> <?= htmlspecialchars($pelajar['pelajar_kadpengenalan'] ?? '-') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($pelajar['pelajar_emel'] ?? '-') ?></p>
        <p><strong>Telefon:</strong> <?= htmlspecialchars($pelajar['pelajar_telefon'] ?? '-') ?></p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($pelajar['pelajar_alamat'] ?? '-') ?></p>
        <p><strong>Negeri dalam IC:</strong> <?= $kodNegeri[$icKod] ?? '-' ?></p>
        <p><strong>Negeri Persatuan:</strong> <?= $namaNegeri ?></p>

        <div class="flex justify-end gap-4">
            <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Batal</button>
            <form method="POST" action="../Controllers/joinpersatuan.php" onsubmit="return validateJoin()">
                <input type="hidden" name="persatuan_id" value="<?= $data['persatuan_id'] ?>">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Sahkan</button>
            </form>
        </div>
    </div>
</div>




<script>
function openModal() {
    document.getElementById('joinModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('joinModal').classList.add('hidden');
}
function validateJoin() {
    const isValid = <?= json_encode($isValid) ?>;
    const negeri = "<?= $namaNegeri ?>";
    const persatuanId = <?= json_encode($data['persatuan_id']) ?>;

    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak layak',
            text: 'Anda bukan pelajar kelahiran negeri ' + negeri,
            showCancelButton: true,
            confirmButtonText: 'Mohon Kes Khas',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../Persatuan/keskhasform.php?persatuan_id=' + persatuanId;
            }
        });
        return false;
    }
    return true;
}


</script>
</body>
</html>
