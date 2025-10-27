<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

// Dapatkan ID persatuan
$stmt = $conn->prepare("SELECT persatuan_id FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$persatuan = $stmt->get_result()->fetch_assoc();

if (!$persatuan) {
    echo "<p class='text-red-600'>Persatuan tidak dijumpai.</p>";
    exit;
}

$persatuan_id = $persatuan['persatuan_id'];

// Dapatkan senarai ahli
$stmt = $conn->prepare("
    SELECT p.pelajar_nama, p.pelajar_matrik, p.pelajar_emel, p.pelajar_gambar 
    FROM Permohonan pm 
    JOIN Pelajar_UKM p ON pm.pelajar_emel = p.pelajar_emel 
    WHERE pm.persatuan_id = ? AND pm.status = 'disahkan'
");
$stmt->bind_param("i", $persatuan_id);
$stmt->execute();
$ahli = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Senarai Ahli Persatuan</title>
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
    <h1 class="text-2xl font-bold text-blue-700 mb-6">Senarai Ahli Persatuan</h1>

    <?php if (empty($ahli)): ?>
        <p class="text-gray-600">Tiada pelajar menyertai persatuan ini.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($ahli as $row): 
                $gambar = !empty($row['pelajar_gambar']) ? '../uploads/' . $row['pelajar_gambar'] : '../images/defaultProfile.png';
            ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-4">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="<?= $gambar ?>" alt="Pelajar" class="w-14 h-14 rounded-full border object-cover">
                        <div>
                            <h3 class="text-blue-700 font-semibold"><?= htmlspecialchars($row['pelajar_nama']) ?></h3>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($row['pelajar_matrik']) ?></p>
                        </div>
                    </div>
                    <a href="ahlidetails.php?emel=<?= urlencode($row['pelajar_emel']) ?>" 
                       class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded text-sm font-medium transition">
                        Lihat Butiran
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
