<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$aktiviti_id = $_GET['id'] ?? null;

if (!$aktiviti_id) {
    echo "<script>alert('ID aktiviti tidak sah.'); window.history.back();</script>";
    exit;
}

// Get aktiviti info
$stmt = $conn->prepare("SELECT * FROM Aktiviti WHERE aktiviti_id = ?");
$stmt->bind_param("i", $aktiviti_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Maklumat aktiviti tidak dijumpai.'); window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kemaskini Aktiviti</title>
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

<div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Kemaskini Aktiviti</h2>

    <form method="POST" action="../Controllers/updateActivity.php" enctype="multipart/form-data">
        <input type="hidden" name="aktiviti_id" value="<?= $data['aktiviti_id'] ?>">

        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Aktiviti</label>
            <input type="text" name="aktiviti_nama" value="<?= htmlspecialchars($data['aktiviti_nama']) ?>" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Masa Mula -->
                <div class="mb-4 mt-4">
                    <label class="block text-sm font-medium">Tarikh Mula</label>
                    <input type="date" name="tarikh_mula" value="<?= htmlspecialchars($data['tarikh_mula']) ?>" required
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                </div>

            <!-- Masa Tamat -->
                <div class="mb-4 mt-4">
                    <label class="block text-sm font-medium">Tarikh Tamat</label>
                    <input type="date" name="tarikh_tamat" value="<?= htmlspecialchars($data['tarikh_tamat']) ?>" required
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                </div>
        </div>

            

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Masa Mula -->
                <div class="mb-4 mt-4">
                    <label class="block text-sm font-medium">Masa Mula</label>
                    <input type="time" name="aktiviti_mula" value="<?= htmlspecialchars($data['aktiviti_mula']) ?>" required
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                </div>

            <!-- Masa Tamat -->
                <div class="mb-4 mt-4">
                    <label class="block text-sm font-medium">Masa Tamat</label>
                    <input type="time" name="aktiviti_tamat" value="<?= htmlspecialchars($data['aktiviti_tamat']) ?>" required
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                </div>
        </div>

        <!-- Jenis Aktiviti -->
        <div class="mb-4 mt-4">
            <label class="block text-sm font-medium">Jenis Aktiviti</label>
            <select name="aktiviti_jenis" required class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                <option value="">-- Pilih Jenis Aktiviti --</option>
                <option value="Sukan" <?= $data['aktiviti_jenis'] == 'Sukan' ? 'selected' : '' ?>>Sukan</option>
                <option value="Khidmat Masyarakat" <?= $data['aktiviti_jenis'] == 'Khidmat Masyarakat' ? 'selected' : '' ?>>Khidmat Masyarakat</option>
                <option value="Debat / Pidato" <?= $data['aktiviti_jenis'] == 'Debat / Pidato' ? 'selected' : '' ?>>Debat / Pidato</option>
                <option value="Ekspo/ Karnival / Pameran / Festival" <?= $data['aktiviti_jenis'] == 'Ekspo/ Karnival / Pameran / Festival' ? 'selected' : '' ?>>Ekspo/ Karnival / Pameran / Festival</option>
                <option value="Forum / Diskusi / Ceramah" <?= $data['aktiviti_jenis'] == 'Forum / Diskusi / Ceramah' ? 'selected' : '' ?>>Forum / Diskusi / Ceramah</option>
                <option value="Jamuan Makan Malam / Ulang Tahun" <?= $data['aktiviti_jenis'] == 'Jamuan Makan Malam / Ulang Tahun' ? 'selected' : '' ?>>Jamuan Makan Malam / Ulang Tahun</option>
                <option value="Keagamaan" <?= $data['aktiviti_jenis'] == 'Keagamaan' ? 'selected' : '' ?>>Keagamaan</option>
                <option value="Kebudayaan / Kesenian" <?= $data['aktiviti_jenis'] == 'Kebudayaan / Kesenian' ? 'selected' : '' ?>>Kebudayaan / Kesenian</option>
                <option value="Keusahawanan" <?= $data['aktiviti_jenis'] == 'Keusahawanan' ? 'selected' : '' ?>>Keusahawanan</option>
                <option value="Latihan / Kursus / Bengkel" <?= $data['aktiviti_jenis'] == 'Latihan / Kursus / Bengkel' ? 'selected' : '' ?>>Latihan / Kursus / Bengkel</option>
                <option value="Lawatan Sambil Belajar / Mobiliti" <?= $data['aktiviti_jenis'] == 'Lawatan Sambil Belajar / Mobiliti' ? 'selected' : '' ?>>Lawatan Sambil Belajar / Mobiliti</option>
                <option value="Penyertaan / Pertandingan" <?= $data['aktiviti_jenis'] == 'Penyertaan / Pertandingan' ? 'selected' : '' ?>>Penyertaan / Pertandingan</option>
                <option value="Perjumpaan / Perhimpunan / Hari Keluarga" <?= $data['aktiviti_jenis'] == 'Perjumpaan / Perhimpunan / Hari Keluarga' ? 'selected' : '' ?>>Perjumpaan / Perhimpunan / Hari Keluarga</option>
                <option value="Perkhemahan / Eksplorasi" <?= $data['aktiviti_jenis'] == 'Perkhemahan / Eksplorasi' ? 'selected' : '' ?>>Perkhemahan / Eksplorasi</option>
                <option value="Seminar / Persidangan" <?= $data['aktiviti_jenis'] == 'Seminar / Persidangan' ? 'selected' : '' ?>>Seminar / Persidangan</option>
            </select>
        </div>

        <div class="mb-4 mt-4">
            <label class="block text-sm font-medium">Tempat</label>
            <input type="text" name="aktiviti_tempat" value="<?= htmlspecialchars($data['aktiviti_tempat']) ?>" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

         <div class="mb-4 mt-4">
            <label class="block text-sm font-medium">Had Penyertaan</label>
            <input type="text" name="had_penyertaan" value="<?= htmlspecialchars($data['had_penyertaan']) ?>" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Maklumat Aktiviti</label>
            <textarea name="aktiviti_maklumat" rows="4" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded"><?= htmlspecialchars($data['aktiviti_maklumat']) ?></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Gambar Baru (optional)</label>
            <?php if (!empty($data['aktiviti_gambar'])): ?>
                <img src="../uploads/<?= $data['aktiviti_gambar'] ?>" alt="Poster" class="w-32 h-32 object-cover rounded mb-2">
            <?php endif; ?>
            <input type="file" name="aktiviti_gambar" class="text-sm">
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Simpan Perubahan</button>
    </form>
</div>

</body>
</html>
