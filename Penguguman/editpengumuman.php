<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$pengumuman_id = $_GET['id'] ?? null;

if (!$pengumuman_id) {
    echo "<script>alert('ID tidak sah.'); window.location.href='pengumuman.php';</script>";
    exit;
}

// Dapatkan ID persatuan pengurus
$stmt = $conn->prepare("SELECT persatuan_id FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$persatuan_id = $row['persatuan_id'] ?? 0;

// Dapatkan pengumuman untuk diedit
$stmt = $conn->prepare("SELECT * FROM Pengumuman WHERE id = ? AND persatuan_id = ?");
$stmt->bind_param("ii", $pengumuman_id, $persatuan_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<script>alert('Pengumuman tidak dijumpai atau anda tidak dibenarkan.'); window.location.href='pengumuman.php';</script>";
    exit;
}
$pengumuman = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tajuk = trim($_POST['tajuk'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tamat_tayang = $_POST['tamat_tayang'] ?? '';

    if ($tajuk && $isi && $tamat_tayang) {
        $stmt = $conn->prepare("UPDATE Pengumuman SET tajuk = ?, isi = ?, tamat_tayang = ? WHERE id = ? AND persatuan_id = ?");
        $stmt->bind_param("sssii", $tajuk, $isi, $tamat_tayang, $pengumuman_id, $persatuan_id);
        if ($stmt->execute()) {
            echo "<script>alert('Pengumuman berjaya dikemaskini!'); window.location.href='pengumumanlist.php';</script>";
            exit;
        } else {
            $error = "Ralat semasa mengemaskini.";
        }
    } else {
        $error = "Sila lengkapkan semua maklumat.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kemaskini Pengumuman</title>
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
<div class="max-w-3xl mx-auto px-4 py-10">
    

    <?php if (!empty($error)): ?>
        <p class="text-red-500 mb-4"><?= $error ?></p>
    <?php endif; ?>

    <div class="max-w-2xl  mx-auto mt-10 bg-white p-6 rounded shadow">
        <h1 class="text-3xl text-center font-bold text-blue-700 mb-6">✏️ Kemaskini Pengumuman</h1>
    <form method="POST">
        <div class="mb-4">
            <label class="block text-sm font-semibold">Tajuk Pengumuman : </label>
            <input type="text" name="tajuk" value="<?= htmlspecialchars($pengumuman['tajuk']) ?>" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-semibold">Maklumat Pengumuman :</label>
            <textarea name="isi" rows="10" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($pengumuman['isi']) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-semibold">Tarikh Tamat :</label>
            <input type="date" name="tamat_tayang" value="<?= $pengumuman['tamat_tayang'] ?>" class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Kemaskini</button>
        <a href="pengumumanlist.php" class="ml-4 text-gray-600 hover:underline">Batal</a>
    </form>
    </div>
</div>
</body>
</html>
