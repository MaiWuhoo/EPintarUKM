<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

// Get all joined activities
$sql = "SELECT a.*, p.persatuan_nama, a.status, a.sebab_batal
        FROM Aktiviti a
        JOIN AktivitiPenyertaan ap ON a.aktiviti_id = ap.aktiviti_id
        JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
        WHERE ap.pelajar_emel = ?
        ORDER BY a.tarikh_mula ASC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emel);
$stmt->execute();
$result = $stmt->get_result();
$joinedAktiviti = $result->fetch_all(MYSQLI_ASSOC);

$now = date('Y-m-d H:i:s');

$upcoming = [];
$past = [];
$canceled = [];

foreach ($joinedAktiviti as $a) {
    $tarikhMula = $a['tarikh_mula'] . ' ' . $a['aktiviti_mula'];
    $tarikhTamat = $a['tarikh_tamat'] . ' ' . $a['aktiviti_tamat'];

    if ($a['status'] === 'batal') {
        $canceled[] = $a;
    } elseif ($tarikhTamat >= $now) {
        $upcoming[] = $a;
    } else {
        $past[] = $a;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aktiviti Disertai</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
    <script>
        function countdown(targetId, dateStr) {
            const countDownDate = new Date(dateStr).getTime();
            const countdownElement = document.getElementById(targetId);

            const interval = setInterval(function () {
                const now = new Date().getTime();
                const distance = countDownDate - now;

                if (distance < 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "Aktiviti telah berakhir";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `${days}h ${hours}j ${minutes}m ${seconds}s`;
            }, 1000);
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPel.php'; ?>

<div class="max-w-6xl mx-auto py-8 px-6 space-y-10">
    <h1 class="text-2xl font-bold text-blue-600">Aktiviti Yang Anda Sertai</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-green-600 mb-4">Aktiviti Akan Datang</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($upcoming)): ?>
                <p class="text-gray-500 italic col-span-full">Tiada aktiviti akan datang buat masa ini.</p>
            <?php else: ?>
                <?php foreach ($upcoming as $index => $a): ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition p-4 flex flex-col justify-between h-full">
                        <div>
                            <a href="../Aktiviti/aktivitiDetails.php?id=<?= $a['aktiviti_id'] ?>" class="text-lg font-semibold text-blue-700 mb-1 hover:underline block">
                                <?= htmlspecialchars($a['aktiviti_nama']) ?>
                            </a>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-calendar mr-1 text-gray-700"></i>
                                <?= date('d M Y', strtotime($a['tarikh_mula'])) ?>

                            </p>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['aktiviti_tempat']) ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-user-friends mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['persatuan_nama']) ?>
                            </p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-red-600">
                                <i class="fas fa-hourglass-half mr-1 text-red-500"></i>
                                Status: <span id="countdown<?= $index ?>">Mengira...</span>
                            </p>
                            <script>
                            countdown("countdown<?= $index ?>", "<?= $a['tarikh_mula'] . ' ' . $a['aktiviti_mula'] ?>");
                            </script>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-red-600 mb-4">Aktiviti Lepas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($past)): ?>
                <p class="text-gray-500 italic col-span-full">Tiada aktiviti lepas buat masa ini.</p>
            <?php else: ?>
                <?php foreach ($past as $a): ?>
                    <?php
                        $reviewStmt = $conn->prepare("SELECT * FROM aktivitireview WHERE pelajar_emel = ? AND aktiviti_id = ?");
                        $reviewStmt->bind_param("si", $emel, $a['aktiviti_id']);
                        $reviewStmt->execute();
                        $alreadyReviewed = $reviewStmt->get_result()->num_rows > 0;
                    ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-md transition p-4 flex flex-col justify-between h-full">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-700 mb-1"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-calendar mr-1 text-gray-700"></i>
                               <?= date('d M Y', strtotime($a['tarikh_mula'])) ?>

                            </p>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['aktiviti_tempat']) ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-user-friends mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['persatuan_nama']) ?>
                            </p>
                        </div>
                        <div class="mt-4">
                            <?php if (!$alreadyReviewed): ?>
                                <a href="../aktiviti/aktivitiReviewPel.php?id=<?= $a['aktiviti_id'] ?>"
                                   class="inline-block bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-semibold text-sm px-4 py-2 rounded transition">
                                    <i class="fas fa-edit mr-1"></i> Bagi Ulasan
                                </a>
                            <?php else: ?>
                                <p class="text-sm text-green-600 font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Ulasan telah dihantar
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-red-600 mb-4">Aktiviti Dibatalkan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($canceled)): ?>
                <p class="text-gray-500 italic col-span-full">Tiada aktiviti batal yang anda sertai.</p>
            <?php else: ?>
                <?php foreach ($canceled as $a): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg shadow hover:shadow-md transition p-4 flex flex-col justify-between h-full">
                        <div>
                            <h3 class="text-lg font-bold text-red-700 mb-1"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-calendar mr-1 text-gray-700"></i>
                                <?= date('d M Y', strtotime($a['tarikh_mula'])) ?>

                            </p>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['aktiviti_tempat']) ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-user-friends mr-1 text-gray-700"></i>
                                <?= htmlspecialchars($a['persatuan_nama']) ?>
                            </p>
                        </div>
                        <p class="text-sm text-red-700 italic mt-2">
                            <strong>Sebab Batal:</strong> <?= htmlspecialchars($a['sebab_batal']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>

