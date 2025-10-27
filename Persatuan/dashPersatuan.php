<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

// ✅ Persatuan yang disahkan
$sql = "SELECT ps.persatuan_id, ps.persatuan_nama, ps.persatuan_logo,  pm.permohonan_tarikh
        FROM Permohonan pm
        JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id
        WHERE pm.pelajar_emel = ? AND pm.status = 'disahkan'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emel);
$stmt->execute();
$joinedPersatuan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ✅ Permohonan yang masih menunggu, tetapi bukan auto
$sqlMenunggu = "SELECT ps.persatuan_id, ps.persatuan_nama, ps.persatuan_logo, pm.permohonan_tarikh, pm.jenis_permohonan
                FROM Permohonan pm
                JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id
                WHERE pm.pelajar_emel = ? AND pm.status = 'menunggu' AND pm.jenis_permohonan != 'auto'";
$stmt3 = $conn->prepare($sqlMenunggu);
$stmt3->bind_param("s", $emel);
$stmt3->execute();
$pendingPersatuan = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

// ✅ Persatuan lain yang boleh dipohon
$sqlAll = "SELECT * FROM Persatuan WHERE persatuan_id NOT IN (
               SELECT persatuan_id FROM Permohonan WHERE pelajar_emel = ? AND status IN ('disahkan', 'menunggu')
           )";
$stmt2 = $conn->prepare($sqlAll);
$stmt2->bind_param("s", $emel);
$stmt2->execute();
$resAll = $stmt2->get_result();
$availablePersatuan = $resAll->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Persatuan Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- ✅ Persatuan yang disertai -->
    <h2 class="text-2xl font-bold text-blue-600 mb-6">Persatuan yang Disertai</h2>
    <?php if (count($joinedPersatuan) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($joinedPersatuan as $persatuan): ?>
                <div class="bg-white shadow-md hover:shadow-xl transition-all rounded-lg overflow-hidden">
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <img src="../uploads/<?= htmlspecialchars($persatuan['persatuan_logo'] ?? 'default-logo.png') ?>" class="h-full object-contain p-4">
                    </div>
                    <div class="p-4">
                        <a href="PersatuanDetails.php?id=<?= $persatuan['persatuan_id'] ?>" class="text-lg font-bold text-blue-600 hover:underline block mb-1">
                            <?= htmlspecialchars($persatuan['persatuan_nama']) ?>
                        </a>
                        
                        <p class="text-xs text-gray-500 italic">Ahli sejak: <?= date('d M Y', strtotime($persatuan['permohonan_tarikh'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600 text-center mb-8">Anda belum menyertai mana-mana persatuan.</p>
    <?php endif; ?>


    <!-- ✅ Permohonan Dalam Proses -->
    <?php if (count($pendingPersatuan) > 0): ?>
        <h2 class="text-2xl font-bold text-yellow-600 mt-10 mb-4">Permohonan Dalam Proses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pendingPersatuan as $persatuan): ?>
                <div class="bg-yellow-50 shadow-md rounded-lg overflow-hidden border border-yellow-300">
                    <div class="h-40 bg-gray-100 flex items-center justify-center">
                        <img src="../uploads/<?= htmlspecialchars($persatuan['persatuan_logo'] ?? 'default-logo.png') ?>" class="h-full object-contain p-4">
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-yellow-700 mb-1"><?= htmlspecialchars($persatuan['persatuan_nama']) ?></h3>
                       
                        <p class="text-xs text-yellow-600 italic">Sedang tunggu pengesahan permohonan (<?= $persatuan['jenis_permohonan'] ?>) pada <?= date('d M Y', strtotime($persatuan['permohonan_tarikh'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- ✅ Persatuan lain yang boleh disertai -->
    <h2 class="text-2xl font-bold text-gray-700 mt-10 mb-4">Senarai Persatuan Lain</h2>
    <?php if (count($availablePersatuan) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($availablePersatuan as $persatuan): ?>
                <div class="bg-white shadow-md hover:shadow-lg transition rounded-lg overflow-hidden">
                    <div class="h-40 bg-gray-100 flex items-center justify-center">
                        <img src="../uploads/<?= htmlspecialchars($persatuan['persatuan_logo'] ?? 'default-logo.png') ?>" class="h-full object-contain p-3">
                    </div>
                    <div class="p-4">
                        <a href="PersatuanDetails.php?id=<?= $persatuan['persatuan_id'] ?>" class="text-lg font-bold text-blue-600 hover:underline block mb-1">
                            <?= htmlspecialchars($persatuan['persatuan_nama']) ?>
                        </a>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500 italic">Tiada persatuan lain tersedia buat masa ini.</p>
    <?php endif; ?>
</div>
</body>
</html>
