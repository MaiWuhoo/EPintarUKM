<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

$updateJumlah = $conn->prepare("
    UPDATE Persatuan p
    SET jumlah_ahli = (
        SELECT COUNT(*) 
        FROM Permohonan pm 
        JOIN Pelajar_UKM pel ON pm.pelajar_emel = pel.pelajar_emel
        WHERE pm.persatuan_id = p.persatuan_id 
          AND pm.status = 'disahkan'
          
    )
    WHERE pman_emel = ?
");

$updateJumlah->bind_param("s", $emel);
$updateJumlah->execute();

$stmt = $conn->prepare("SELECT * FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$displayDate = $data['updated_at'] ?? $data['created_at'] ?? null;
$formattedDate = $displayDate ? date('d F Y', strtotime($displayDate)) : 'Tidak diketahui';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Persatuan</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">
<?php include '../includes/headerPeng.php'; ?>

<div class="max-w-5xl mx-auto py-10 px-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <img src="../uploads/<?= $data['persatuan_logo'] ?? 'default-logo.png' ?>" alt="Logo"
                     class="w-16 h-16 object-contain border rounded-full">
                <div>
                    <h1 class="text-2xl font-bold mb-1"><?= htmlspecialchars($data['persatuan_nama']) ?></h1>
                    <p class="text-sm text-gray-500">Kod Negeri: <?= htmlspecialchars($data['persatuan_kodNegeri']) ?></p>
                    <p class="text-xs text-gray-500">Tarikh terakhir kemaskini: <?= $formattedDate ?></p>
                </div>
            </div>
            <a href="updateprofile.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Kemaskini</a>
        </div>

        <?php
        $sections = [
            'Pengerusi' => 'pengerusi_nama',
            'Jumlah Ahli' => 'jumlah_ahli',
            'Pengenalan' => 'pengenalan',
            'Objektif' => 'objektif',
            'Misi' => 'misi',
            'Visi' => 'visi',
            'Kelebihan Menyertai' => 'benefit',
            'Laman Sosial' => 'sosial_media'
        ];
        foreach ($sections as $title => $key): ?>
            <div class="mb-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm p-4">
                <h2 class="text-blue-600 font-semibold mb-1 text-lg"><?= $title ?></h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">
                    <?= !empty($data[$key]) ? nl2br(htmlspecialchars($data[$key])) : '<span class="italic text-gray-400">Maklumat belum dikemas kini</span>' ?>
                </p>
            </div>
        <?php endforeach; ?>

        <div class="mb-5 bg-gray-50 border border-gray-200 rounded-lg shadow-sm p-4">
            <h2 class="text-blue-600 font-semibold mb-2 text-lg">Struktur Organisasi</h2>
            <?php if (!empty($data['organisasi_img'])): ?>
                <img src="../uploads/<?= $data['organisasi_img'] ?>" class="w-full h-auto rounded" alt="Organisasi">
            <?php else: ?>
                <p class="italic text-gray-400">Maklumat belum dikemaskini</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
