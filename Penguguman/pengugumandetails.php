<?php
session_start();
include '../includes/db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('Pengumuman tidak sah.'); window.history.back();</script>";
    exit;
}

$pengumuman_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT pn.*, ps.persatuan_nama 
    FROM Pengumuman pn
    JOIN Persatuan ps ON pn.persatuan_id = ps.persatuan_id
    WHERE pn.id = ?
");
$stmt->bind_param("i", $pengumuman_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Pengumuman tidak dijumpai.'); window.history.back();</script>";
    exit;
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maklumat Pengumuman</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include '../includes/headerPel.php'; ?>

    <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow mt-10">
        <h2 class="text-2xl font-bold mb-2 text-blue-700"><?= htmlspecialchars($row['tajuk']) ?></h2>
        <p class="text-sm text-gray-500 mb-4">
            Diumumkan oleh: <span class="font-semibold"><?= htmlspecialchars($row['persatuan_nama']) ?></span> 
            pada <?= date('d M Y', strtotime($row['tarikh_umum'])) ?>
        </p>
        <div class="text-gray-800 text-sm whitespace-pre-line">
            <?= nl2br(htmlspecialchars($row['isi'])) ?>
        </div>

        <?php if (!empty($row['gambar'])): ?>
            <div class="mt-6">
                <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="Gambar Pengumuman" class="rounded shadow max-w-full">
            </div>
        <?php endif; ?>

        <div class="mt-6 text-right">
            <a href="../Pelajar/dashboardPelajar.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali</a>
        </div>
    </div>
</body>
</html>
