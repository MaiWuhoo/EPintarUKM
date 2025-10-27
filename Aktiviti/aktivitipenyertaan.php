<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "<p class='text-red-600'>ID aktiviti tidak sah.</p>";
    exit;
}

$aktiviti_id = (int) $_GET['id'];

// Get list of pelajar who joined
$stmt = $conn->prepare("SELECT p.pelajar_nama, p.pelajar_matrik, p.pelajar_gambar, ap.penyertaan_tarikh
                        FROM AktivitiPenyertaan ap
                        JOIN Pelajar_UKM p ON ap.pelajar_emel = p.pelajar_emel
                        WHERE ap.aktiviti_id = ?");
$stmt->bind_param("i", $aktiviti_id);
$stmt->execute();
$result = $stmt->get_result();
$peserta = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Senarai Peserta</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPeng.php'; ?>

<div class="max-w-6xl mx-auto py-8 px-6">
    <h1 class="text-2xl font-bold text-blue-700 mb-6">Senarai Peserta Aktiviti</h1>

    <?php if (count($peserta) === 0): ?>
        <p class="text-gray-600">Tiada pelajar telah menyertai aktiviti ini.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($peserta as $p): ?>
                <div class="bg-white rounded-lg shadow p-4 flex items-center space-x-4">
                    <img src="../uploads/<?= htmlspecialchars($p['pelajar_gambar']) ?? 'default-user.png' ?>" class="w-16 h-16 rounded-full object-cover border" alt="Gambar Pelajar">
                    <div>
                        <p class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($p['pelajar_nama']) ?></p>
                        <p class="text-sm text-gray-600">Nombor Matrik: <?= htmlspecialchars($p['pelajar_matrik']) ?></p>
                        <p class="text-sm text-gray-500">Tarikh Sertai: <?= date('d M Y', strtotime($p['penyertaan_tarikh'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
