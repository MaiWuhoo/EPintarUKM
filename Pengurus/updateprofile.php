<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}
$updateJumlah = $conn->prepare("
    UPDATE Persatuan p 
    SET jumlah_ahli = (
        SELECT COUNT(*) FROM Permohonan WHERE persatuan_id = p.persatuan_id
    ) 
    WHERE pman_emel = ?
");
$updateJumlah->bind_param("s", $emel);
$updateJumlah->execute();


$emel = $_SESSION['emel'];
$stmt = $conn->prepare("SELECT * FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $emel);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil Persatuan</title>
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

<div class="max-w-3xl mx-auto py-10 px-6">
    <form method="POST" action="../controllers/updateprofilePers.php" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-6">Kemaskini Profil Persatuan</h2>

        <input type="hidden" name="persatuan_id" value="<?= $data['persatuan_id'] ?>">

        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Persatuan</label>
            <input type="text" name="persatuan_nama" value="<?= htmlspecialchars($data['persatuan_nama']) ?>" class="w-full mt-1 px-3 py-2 border rounded">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Kod Negeri</label>
            <input type="text" name="persatuan_kodNegeri" value="<?= htmlspecialchars($data['persatuan_kodNegeri']) ?>" class="w-full mt-1 px-3 py-2 border rounded">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Pengerusi</label>
            <input type="text" name="pengerusi_nama" value="<?= htmlspecialchars($data['pengerusi_nama']) ?>" class="w-full mt-1 px-3 py-2 border rounded">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Jumlah Ahli</label>
            <input type="number" name="jumlah_ahli" value="<?= htmlspecialchars($data['jumlah_ahli']) ?>" class="w-full mt-1 px-3 py-2 border rounded">
        </div>

        <?php
        $fields = [
            'Pengenalan' => 'pengenalan',
            'Objektif' => 'objektif',
            'Misi' => 'misi',
            'Visi' => 'visi',
            'Kelebihan Menyertai' => 'benefit',
            'Laman Sosial Media' => 'sosial_media'
        ];
        foreach ($fields as $label => $name): ?>
            <div class="mb-4">
                <label class="block text-sm font-medium"><?= $label ?></label>
                <textarea name="<?= $name ?>" rows="3" class="w-full mt-1 px-3 py-2 border rounded"><?= htmlspecialchars($data[$name]) ?></textarea>
            </div>
        <?php endforeach; ?>

        <div class="mb-4">
            <label class="block text-sm font-medium">Logo Persatuan</label>
            <?php if (!empty($data['persatuan_logo'])): ?>
                <img src="../uploads/<?= $data['persatuan_logo'] ?>" class="w-24 h-24 object-contain rounded mb-2">
            <?php endif; ?>
            <input type="file" name="persatuan_logo">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium">Struktur Organisasi (Gambar)</label>
            <?php if (!empty($data['organisasi_img'])): ?>
                <img src="../uploads/<?= $data['organisasi_img'] ?>" class="w-48 h-auto rounded mb-2">
            <?php endif; ?>
            <input type="file" name="organisasi_img">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan</button>
    </form>
</div>
</body>
</html>
