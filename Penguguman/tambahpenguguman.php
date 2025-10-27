<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emelPengurus = $_SESSION['emel'];

// Dapatkan senarai persatuan yang dikendalikan oleh pengurus
$stmt = $conn->prepare("SELECT persatuan_id, persatuan_nama FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emelPengurus);
$stmt->execute();
$result = $stmt->get_result();
$persatuanList = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengumuman</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen ">

<?php include '../includes/headerPeng.php'; ?>

   <div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 text-center">Tambah Penguguman</h2>
        <form method="POST" action="../controllers/tambahPengumumanController.php" class="space-y-4">
            <div>
                <label class="block font-semibold">Pilih Persatuan:</label>
                <select name="persatuan_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <option value="">-- Pilih Persatuan --</option>
                    <?php foreach ($persatuanList as $ps): ?>
                        <option value="<?= $ps['persatuan_id'] ?>"><?= htmlspecialchars($ps['persatuan_nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold">Tajuk Pengumuman:</label>
                <input type="text" name="tajuk" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Maklumat Pengumuman:</label>
                <textarea name="isi" rows="4" class="w-full border border-gray-300 rounded px-3 py-2" required></textarea>
            </div>

            <div>
                <label class="block font-semibold">Tarikh Tamat:</label>
                <input type="date" name="tamat_tayang" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Hantar</button>
            </div>
        </form>
    </div>

</body>
</html>
