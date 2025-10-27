<?php
session_start();
include '../includes/db.php';

$pman_emel = $_SESSION['emel'];

// Fetch activities created by this pengurus
$sql = "SELECT a.* FROM Aktiviti a
JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
WHERE p.pman_emel = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pman_emel);
$stmt->execute();
$result = $stmt->get_result();
$activities = $result->fetch_all(MYSQLI_ASSOC);

$today = date('Y-m-d');
$upcoming = array_filter($activities, fn($a) => $a['tarikh_mula'] >= $today && $a['status'] === 'aktif');
$past = array_filter($activities, fn($a) => $a['tarikh_tamat'] < $today && $a['status'] === 'aktif');
$batal = array_filter($activities, fn($a) => $a['status'] === 'batal');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aktiviti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">
<?php include '../includes/headerPeng.php'; ?>
<div class="max-w-6xl mx-auto p-6 space-y-10">
    <h2 class="text-xl font-bold text-blue-700">Senarai Aktiviti</h2>

    <!-- TAMBAH PROGRAM -->
    <div class="mb-6">
        <a href="../aktiviti/aktivitiForm.php" class="bg-white p-6 flex items-center justify-center border-2 border-dashed border-gray-300 rounded hover:bg-gray-50">
            <div class="text-center">
                <div class="text-4xl text-gray-400 font-bold">+</div>
                <p class="text-sm text-gray-600 mt-2">TAMBAH AKTIVITI</p>
            </div>
        </a>
    </div>

    <!-- AKTIVITI AKAN DATANG -->
<div>
    <h3 class="text-lg font-semibold text-green-600 mb-4">Aktiviti Akan Datang</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($upcoming as $a): ?>
        <div class="bg-white p-4 rounded shadow">
            <img src="../uploads/<?= htmlspecialchars($a['aktiviti_gambar'] ?? 'default.jpg') ?>" alt="Poster" class="w-full h-40 object-cover rounded mb-3">
            
            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
            <p class="text-sm"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($a['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($a['tarikh_tamat'])) ?></p>
            <p class="text-sm"><i class="fa-regular fa-clock"></i> <?= date('g:i A', strtotime($a['aktiviti_mula'])) ?> – <?= date('g:i A', strtotime($a['aktiviti_tamat']))  ?></p>
            <p class="text-sm"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($a['aktiviti_tempat']) ?></p>

            <!-- Dropdown Actions -->
<div x-data="{ open: false }" class="relative text-right mt-4">
  <button @click="open = !open" class="text-gray-600 hover:text-gray-800 px-2">
    <i class="fas fa-ellipsis-v text-xl"></i>
  </button>

  <div x-show="open" @click.away="open = false"
     class="absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg z-10 p-2">
  <a href="aktivitiupdate.php?id=<?= $a['aktiviti_id'] ?>"
     class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 font-semibold">
    <i class="fas fa-pen text-orange-500"></i> Kemas Kini
  </a>

  <a href="aktivitipenyertaan.php?id=<?= $a['aktiviti_id'] ?>"
     class="flex items-center gap-2 px-4 py-2 text-sm text-indigo-600 hover:bg-gray-100 font-semibold">
    <i class="fas fa-users text-purple-700"></i> Senarai Peserta
  </a>

  <button onclick="batalkanAktiviti(<?= $a['aktiviti_id'] ?>)"
     class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 font-semibold">
    <i class="fas fa-times text-red-500"></i> Batalkan
  </button>
</div>

</div>

        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- AKTIVITI TELAH BERAKHIR -->
<div>
    <h3 class="text-lg font-semibold text-red-600 mb-4">Aktiviti Telah Berakhir</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($past as $a): ?>
        <div class="bg-white p-4 rounded shadow">
            <img src="../uploads/<?= htmlspecialchars($a['aktiviti_gambar'] ?? 'default.jpg') ?>" alt="Poster" class="w-full h-40 object-cover rounded mb-3">

            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
            <p class="text-sm"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($a['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($a['tarikh_tamat'])) ?></p>
             <p class="text-sm"><i class="fa-regular fa-clock"></i> <?= date('g:i A', strtotime($a['aktiviti_mula'])) ?> – <?= date('g:i A', strtotime($a['aktiviti_tamat']))  ?></p>
            <p class="text-sm"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($a['aktiviti_tempat']) ?></p>

            <div class="flex gap-2 mt-4">
                <a href="../aktiviti/aktivitiReviewPeng.php?id=<?= $a['aktiviti_id'] ?>" class="flex-1 text-center font-bold  bg-yellow-100 text-yellow-700 py-2 rounded">LIHAT ULASAN</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- AKTIVITI DIBATALKAN -->
<?php if (count($batal) > 0): ?>
<div>
    <h3 class="text-lg font-semibold text-red-700 mb-4">Aktiviti Dibatalkan</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($batal as $a): ?>
        <div class="bg-white p-4 rounded shadow border border-red-300">
            <img src="../uploads/<?= htmlspecialchars($a['aktiviti_gambar'] ?? 'default.jpg') ?>" alt="Poster" class="w-full h-40 object-cover rounded mb-3">

            <h3 class="font-bold text-lg text-red-700 mb-2"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
            <p class="text-sm"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($a['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($a['tarikh_tamat'])) ?></p>
                         <p class="text-sm"><i class="fa-regular fa-clock"></i> <?= date('g:i A', strtotime($a['aktiviti_mula'])) ?> – <?= date('g:i A', strtotime($a['aktiviti_tamat']))  ?></p>
            <p class="text-sm"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($a['aktiviti_tempat']) ?></p>
            <p class="text-sm mt-2 text-red-500"><strong>Sebab:</strong> <?= htmlspecialchars($a['sebab_batal']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function batalkanAktiviti(id) {
    Swal.fire({
        title: 'Sebab Pembatalan',
        input: 'textarea',
        inputLabel: 'Nyatakan sebab kenapa aktiviti ini dibatalkan',
        inputPlaceholder: 'Contoh: Bertembung dengan majlis kolej...',
        inputAttributes: {
            'aria-label': 'Sebab pembatalan'
        },
        showCancelButton: true,
        confirmButtonText: 'Hantar',
        cancelButtonText: 'Batal',
        preConfirm: (sebab) => {
            if (!sebab) {
                Swal.showValidationMessage('Sebab wajib diisi');
            }
            return sebab;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit ke controller
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../Aktiviti/aktivitiBatal.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'aktiviti_id';
            idInput.value = id;
            form.appendChild(idInput);

            const sebabInput = document.createElement('input');
            sebabInput.type = 'hidden';
            sebabInput.name = 'sebab';
            sebabInput.value = result.value;
            form.appendChild(sebabInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

</body>
</html>
