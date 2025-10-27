<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$stmt = $conn->prepare("SELECT persatuan_id FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<script>alert('Maklumat persatuan tidak dijumpai.'); window.location.href='../login.php';</script>";
    exit;
}
$row = $result->fetch_assoc();
$persatuan_id = $row['persatuan_id'];

// Ambil pengumuman untuk persatuan ini
$stmt = $conn->prepare("SELECT * FROM Pengumuman WHERE persatuan_id = ? ORDER BY tarikh_umum DESC");
$stmt->bind_param("i", $persatuan_id);
$stmt->execute();
$pengumuman = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pengurusan Pengumuman</title>
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

<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-blue-700 mb-6">📢 Pengurusan Pengumuman</h1>

    <!-- Butang Tambah -->
    <div class="mb-10">
        <a href="tambahpenguguman.php" class="flex items-center justify-center border-2 border-dashed border-gray-400 p-6 rounded hover:bg-gray-100">
            <span class="text-4xl text-gray-400">+</span>
            <span class="ml-4 text-gray-600 font-semibold">TAMBAH PENGUMUMAN</span>
        </a>
    </div>

    <!-- Senarai Pengumuman -->
    <?php if (count($pengumuman) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pengumuman as $p): ?>
                <div class="bg-white p-5 rounded shadow">
                    <h3 class="text-lg font-bold text-blue-700 mb-2"><?= htmlspecialchars($p['tajuk']) ?></h3>
                   
                    <p class="text-xs text-gray-400 italic mb-3">Tarikh: <?= date('d M Y', strtotime($p['tarikh_umum'])) ?> | Tamat: <?= date('d M Y', strtotime($p['tamat_tayang'])) ?></p>
                    <div class="flex space-x-2">
                        <a href="editpengumuman.php?id=<?= $p['id'] ?>" class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white text-sm rounded">Edit</a>
                        <a href="deletepengumuman.php?id=<?= $p['id'] ?>" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-sm rounded" onclick="return confirm('Padam pengumuman ini?')">Padam</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500 italic">Tiada pengumuman buat masa ini.</p>
    <?php endif; ?>
</div>
</body>
</html>
