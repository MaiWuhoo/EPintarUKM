<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$pman_emel = $_SESSION['emel'];

// Get persatuan info
$sql = "SELECT persatuan_id, persatuan_nama FROM Persatuan WHERE pman_emel = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pman_emel);
$stmt->execute();
$result = $stmt->get_result();
$persatuan = $result->fetch_assoc();

if (!$persatuan) {
    echo "<script>alert('Maklumat persatuan tidak dijumpai!'); window.history.back();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Aktiviti</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
    .ck-editor__editable_inline {
        min-height: 200px;
        resize: vertical;
        overflow: auto;
    }
    </style>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>



</head>
<body class="bg-gray-100">

<?php include '../includes/headerPeng.php'; ?>

<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">Tambah Aktiviti</h2>

    <form action="../controllers/tambahAktiviti.php" method="POST" enctype="multipart/form-data">

        <!-- Hidden ID -->
        <input type="hidden" name="persatuan_id" value="<?= $persatuan['persatuan_id'] ?>">

        <!-- Persatuan Nama (readonly) -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Persatuan</label>
            <input type="text" value="<?= htmlspecialchars($persatuan['persatuan_nama']) ?>" readonly
                   class="w-full mt-1 px-3 py-2 border border-gray-300 bg-gray-100 text-gray-600 rounded">
            <input type="hidden" name="persatuan_nama" value="<?= $persatuan['persatuan_nama'] ?>">
        </div>

        <!-- Nama Aktiviti -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Aktiviti</label>
            <input type="text" name="aktiviti_nama" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <!-- Tarikh -->
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
        <div class="mb-4">
            <label class="block text-sm font-medium">Jenis Aktiviti</label>
            <select name="aktiviti_jenis" required
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
                <option value="">-- Pilih Jenis Aktiviti --</option>
                <option value="Sukan">Sukan</option>
                <option value="Khidmat Masyarakat">Khidmat Masyarakat</option>
<option value="Debat / Pidato">Debat / Pidato</option>
<option value="Ekspo/ Karnival / Pameran / Festival">Ekspo/ Karnival / Pameran / Festival</option>
<option value="Forum / Diskusi / Ceramah">Forum / Diskusi / Ceramah</option>
<option value="Jamuan Makan Malam / Ulang Tahun">Jamuan Makan Malam / Ulang Tahun</option>
<option value="Keagamaan">Keagamaan</option>
<option value="Kebudayaan / Kesenian">Kebudayaan / Kesenian</option>
<option value="Keusahawanan">Keusahawanan</option>
<option value="Latihan / Kursus / Bengkel">Latihan / Kursus / Bengkel</option>
<option value="Lawatan Sambil Belajar / Mobiliti">Lawatan Sambil Belajar / Mobiliti</option>
<option value="Penyertaan / Pertandingan">Penyertaan / Pertandingan</option>
<option value="Perjumpaan / Perhimpunan / Hari Keluarga">Perjumpaan / Perhimpunan / Hari Keluarga</option>
<option value="Perkhemahan / Eksplorasi">Perkhemahan / Eksplorasi</option>
<option value="Seminar / Persidangan">Seminar / Persidangan</option>

            </select>
        </div>


        <!-- Tempat -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Tempat</label>
            <input type="text" name="aktiviti_tempat" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <!-- Had Penyertaan -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Had Penyertaan</label>
            <input type="number" name="had_penyertaan" required
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <!-- Maklumat -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Maklumat</label>
            <textarea name="aktiviti_maklumat" id="maklumat" rows="3"
              class="w-full mt-1 px-3 py-2 border border-gray-300 rounded"></textarea>
        </div>

        <!-- Gambar -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Gambar Aktiviti</label>
            <input type="file" name="aktiviti_gambar" class="text-sm">
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
            Tambah Aktiviti
        </button>
    </form>
</div>

<script>
    ClassicEditor
        .create(document.querySelector('#maklumat'), {
            toolbar: [ 'bold', 'italic', 'bulletedList', 'numberedList', 'link' ]
        })
        .catch(error => {
            console.error(error);
        });
</script>

</body>
</html>

