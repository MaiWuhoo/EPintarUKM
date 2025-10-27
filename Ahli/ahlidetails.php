<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['emel'])) {
    echo "<p class='text-red-600'>Maklumat pelajar tidak sah.</p>";
    exit;
}

$pelajar_emel = $_GET['emel'];

// Get student details + join with Permohonan to get the date
$stmt = $conn->prepare("
    SELECT p.pelajar_nama,p.jantina, p.pelajar_matrik, p.pelajar_kadpengenalan, 
           p.pelajar_telefon, p.pelajar_alamat, p.pelajar_gambar, p.pelajar_emel,
           p.pelajar_fakulti, p.pelajar_program, p.pelajar_tahun, p.pelajar_kolej,
           m.permohonan_tarikh
    FROM Pelajar_UKM p
    LEFT JOIN Permohonan m ON p.pelajar_emel = m.pelajar_emel
    WHERE p.pelajar_emel = ?
");

$stmt->bind_param("s", $pelajar_emel);
$stmt->execute();
$result = $stmt->get_result();
$pelajar = $result->fetch_assoc();

if (!$pelajar) {
    echo "<p class='text-red-600'>Pelajar tidak dijumpai.</p>";
    exit;
}

$imagePath = !empty($pelajar['pelajar_gambar']) ? '../uploads/' . $pelajar['pelajar_gambar'] : '../images/default-user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maklumat Pelajar</title>
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
<h2 class="text-2xl pt-4 pl-52 font-bold text-blue-700 ">Maklumat Pelajar</h2>

<div class="max-w-4xl mx-auto py-8 px-6">
    <div class="bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row items-center md:items-start gap-6">
        <img src="<?= $imagePath ?>" alt="Gambar Pelajar" class="w-32 h-32 object-cover rounded-full border object-center">
        <div class="flex-1 space-y-2">
            <p><strong>Nama:</strong> <?= htmlspecialchars($pelajar['pelajar_nama']) ?></p>
            <p><strong>No. Matrik:</strong> <?= htmlspecialchars($pelajar['pelajar_matrik']) ?></p>
            <p><strong>IC:</strong> <?= htmlspecialchars($pelajar['pelajar_kadpengenalan']) ?></p>
            <p><strong>Jantina:</strong> <?= htmlspecialchars($pelajar['jantina']) ?></p>
            <p><strong>Telefon:</strong> <?= htmlspecialchars($pelajar['pelajar_telefon']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($pelajar['pelajar_emel']) ?></p>
            <p><strong>Fakulti:</strong> <?= htmlspecialchars($pelajar['pelajar_fakulti']) ?></p>
            <p><strong>Program:</strong> <?= htmlspecialchars($pelajar['pelajar_program']) ?></p>
            <p><strong>Tahun Pengajian:</strong> Tahun <?= htmlspecialchars($pelajar['pelajar_tahun']) ?></p>
            <p><strong>Kolej Kediaman:</strong> <?= htmlspecialchars($pelajar['pelajar_kolej']) ?></p>
            <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($pelajar['pelajar_alamat'])) ?></p>

            <!-- Only show if date exists -->
            <?php if (!empty($pelajar['permohonan_tarikh'])): ?>
                <p><strong>Ahli Sejak:</strong> <?= date('d M Y', strtotime($pelajar['permohonan_tarikh'])) ?></p>
            <?php else: ?>
                <p class="text-red-500"><em>Tarikh keahlian tidak ditemui.</em></p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
