<?php
session_start();
include '../includes/db.php';

// Get ID
if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>ID tidak sah.</p>";
    exit;
}

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

$id = $_GET['id'];

// Fetch aktiviti + persatuan info
$sql = "SELECT a.*, p.persatuan_nama,  p.pman_emel 
        FROM Aktiviti a
        JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
        WHERE a.aktiviti_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$isBatal = isset($data['status']) && $data['status'] === 'batal';


if (!$data) {
    echo "<p class='text-red-600'>Aktiviti tidak dijumpai.</p>";
    exit;
}

// Kira jumlah peserta semasa
$jumlahPeserta = 0;
$hadPeserta = $data['had_penyertaan'] ?? 0;

$pesertaQuery = $conn->prepare("SELECT COUNT(*) AS jumlah FROM AktivitiPenyertaan WHERE aktiviti_id = ?");
$pesertaQuery->bind_param("i", $id);
$pesertaQuery->execute();
$pesertaResult = $pesertaQuery->get_result();
if ($pesertaRow = $pesertaResult->fetch_assoc()) {
    $jumlahPeserta = $pesertaRow['jumlah'];
}


// Check if already joined (for pelajar only)
$hasJoined = false;
if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pelajar') {
    $check = $conn->prepare("SELECT * FROM AktivitiPenyertaan WHERE pelajar_emel = ? AND aktiviti_id = ?");
    $check->bind_param("si", $_SESSION['emel'], $id);
    $check->execute();
    $hasJoined = $check->get_result()->num_rows > 0;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maklumat Aktiviti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPel.php'; ?>



<div class="max-w-6xl mx-auto p-6 space-y-6" x-data="{ openConfirm: false, openCancel: false }">
    <!-- Title -->
    <div class="bg-gray-100 p-4 rounded">
        <h1 class="text-2xl font-semibold"><?= htmlspecialchars($data['aktiviti_nama']) ?></h1>
    </div>

    


    <!-- Layout -->
    <div class="flex flex-col md:flex-row gap-6 items-stretch">
        <!-- LEFT: Poster + About -->
        <div class="bg-white p-4 rounded shadow md:w-2/3 flex flex-col justify-between">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="md:w-1/2 h-full">
                   <img src="../uploads/<?= $data['aktiviti_gambar'] ?? 'default.jpg' ?>" 
                    alt="Poster" class="w-full h-[400px] object-cover rounded border">
                </div>

                <div class="md:w-1/2 h-full">
                    <h2 class="font-semibold mb-2 flex items-center gap-1">
                    <i class="fas fa-info-circle text-blue-500"></i> ABOUT
                    </h2>
                    <p class="text-sm leading-relaxed whitespace-pre-line overflow-y-auto max-h-[400px] pr-1">
                    <?= $data['aktiviti_maklumat'] ?>

                    </p>
                </div>
            </div>
        </div>

        <!-- RIGHT: Program + Contact -->
        <div class="md:w-1/3 flex flex-col justify-between gap-4">
            <!-- Butiran Program -->
            <div class="bg-white p-4 rounded shadow h-full flex flex-col justify-between">
                <div class="bg-white p-4 rounded shadow h-full flex flex-col justify-between">
    <div>
        <p class="font-semibold mb-4 flex items-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Butiran Program
        </p>

       <?php
        $dayMula = translateDayToMalay(date('l', strtotime($data['tarikh_mula'])));
        $dayTamat = translateDayToMalay(date('l', strtotime($data['tarikh_tamat'])));
        $timeFormatted = date('g:i A', strtotime($data['aktiviti_mula'])) . ' - ' . date('g:i A', strtotime($data['aktiviti_tamat']));
        ?>

        <div class="grid grid-cols-4 gap-x-2 text-sm">
            <div class="font-semibold col-span-1">Tarikh</div>
            <div class="col-span-3 mb-0.5">: <?= date('d M Y', strtotime($data['tarikh_mula'])) ?> - <?= date('d M Y', strtotime($data['tarikh_tamat'])) ?></div>

            <div class="font-semibold col-span-1">Hari</div>
            <div class="col-span-3 mb-0.5">: <?= $dayMula ?> - <?= $dayTamat ?></div>

            <div class="font-semibold col-span-1">Masa</div>
            <div class="col-span-3 mb-0.5">: <?= $timeFormatted ?></div>

            <div class="font-semibold col-span-1">Tempat</div>
            <div class="col-span-3 mb-0.5">: <?= htmlspecialchars($data['aktiviti_tempat']) ?></div>

            <div class="font-semibold col-span-1">Jenis</div>
            <div class="col-span-3 mb-0.5">: <?= htmlspecialchars($data['aktiviti_jenis']) ?></div>

            <div class="font-semibold col-span-1">Peserta</div>
            <div class="col-span-3 mb-0.5">: <?= $jumlahPeserta ?> / <?= $hadPeserta ?> telah mendaftar</div>
        </div>

        <?php if ($jumlahPeserta >= $hadPeserta): ?>
            <p class="text-red-500 font-semibold mt-2">Jumlah peserta telah mencapai had maksimum.</p>
        <?php endif; ?>
    </div>
</div>


                <!-- Join/Batal Button -->
               <?php if ($data['status'] === 'batal'): ?>
    <!-- Alert Aktiviti Batal -->
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <strong>Makluman:</strong> Aktiviti ini telah dibatalkan oleh pihak persatuan.<br>
        <em>Sebab: <?= htmlspecialchars($data['sebab_batal']) ?></em>
    </div>
<?php elseif ($hasJoined): ?>
    <p class="mt-4 text-green-600 font-semibold text-center">Anda telah menyertai aktiviti ini.</p>
    <button @click="openCancel = true" class="w-full bg-red-100 hover:bg-red-200 text-black font-semibold py-2 rounded mt-2">
        BATAL PENYERTAAN
    </button>
<?php elseif ($jumlahPeserta >= $hadPeserta): ?>
    <p class="mt-4 text-red-500 font-semibold text-center">Penyertaan telah penuh.</p>
    <button class="w-full bg-gray-200 text-gray-500 font-semibold py-2 rounded mt-2 cursor-not-allowed" disabled>
        SERTAI
    </button>
<?php else: ?>
    <button @click="openConfirm = true" class="w-full bg-indigo-100 hover:bg-indigo-200 text-black font-semibold py-2 rounded mt-4">
        SERTAI
    </button>
<?php endif; ?>


            </div>

            <!-- Contact Info -->
            <div class="bg-white p-4 rounded shadow h-full">
                <p class="font-semibold mb-2">Contact</p>
                <p class="font-medium"><?= htmlspecialchars($data['persatuan_nama']) ?></p>
                <p>Universiti Kebangsaan Malaysia</p>
                <p class="flex items-center gap-2">
    <i class="fas fa-envelope text-blue-500"></i>
    <a href="mailto:<?= htmlspecialchars($data['pman_emel']) ?>" class="text-blue-600 underline">
        <?= htmlspecialchars($data['pman_emel']) ?>
    </a>
</p>


            </div>
        </div>
        
    </div>

    <!-- Join Confirmation Modal -->
    <div x-show="openConfirm" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-transition>
        <div class="bg-white p-6 rounded-lg shadow w-96 text-center">
            <h2 class="text-xl font-bold mb-2">Adakah anda pasti?</h2>
            <p class="text-sm text-gray-600 mb-4">Adakah anda ingin menyertai aktiviti ini?</p>
            <div class="flex justify-center gap-4">
                <form method="POST" action="../Controllers/joinactivity.php">
                    <input type="hidden" name="aktiviti_id" value="<?= $data['aktiviti_id'] ?>">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Ya, Sertai
                    </button>
                </form>
                <button @click="openConfirm = false" class="px-4 py-2 bg-gray-200 text-black rounded hover:bg-gray-300">
                    Tidak
                </button>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div x-show="openCancel" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-transition>
        <div class="bg-white p-6 rounded-lg shadow w-96 text-center">
            <h2 class="text-xl font-bold mb-2">Batal Penyertaan?</h2>
            <p class="text-sm text-gray-600 mb-4">Adakah anda pasti ingin membatalkan penyertaan anda? Anda masih boleh menyertai semula jika mahu.</p>
            <div class="flex justify-center gap-4">
                <form method="POST" action="../Controllers/batalpenyertaan.php">
                    <input type="hidden" name="aktiviti_id" value="<?= $data['aktiviti_id'] ?>">
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Ya, Batalkan
                    </button>
                </form>
                <button @click="openCancel = false" class="px-4 py-2 bg-gray-200 text-black rounded hover:bg-gray-300">
                    Tidak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert Status Message -->
<?php if (isset($_GET['status'])): ?>
    <script>
        <?php if ($_GET['status'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berjaya!',
                text: 'Anda telah berjaya menyertai aktiviti ini.',
                confirmButtonColor: '#3085d6'
            });
        <?php elseif ($_GET['status'] === 'overlap'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Aktiviti Bertindih!',
                text: 'Anda telah menyertai aktiviti lain pada waktu ini.',
                confirmButtonColor: '#d33'
            });
        <?php elseif ($_GET['status'] === 'fail'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Penyertaan gagal dimasukkan. Sila cuba semula.',
                confirmButtonColor: '#d33'
            });
        <?php elseif ($_GET['status'] === 'cancelled'): ?>
            Swal.fire({
                icon: 'info',
                title: 'Penyertaan Dibatalkan',
                text: 'Anda telah membatalkan penyertaan untuk aktiviti ini.',
                confirmButtonColor: '#3085d6'
            });
                    <?php elseif ($_GET['status'] === 'penuh'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Penyertaan Penuh!',
                text: 'Maaf, penyertaan untuk aktiviti ini telah mencapai had maksimum.',
                confirmButtonColor: '#d33'
            });

        <?php endif; ?>
    </script>
<?php endif; ?>
</body>
</html>
