<?php
session_start();
include '../includes/db.php';

$success = '';
$error = '';
$data = [];

// Fetch user data
if (isset($_SESSION['emel'])) {
    $emel = $_SESSION['emel'];
    $sql = "SELECT * FROM Pelajar_UKM WHERE pelajar_emel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}

// Alert from URL
if (isset($_GET['success'])) {
    $success = "Maklumat berjaya dikemas kini!";
} elseif (isset($_GET['error'])) {
    $error = "Ralat semasa mengemaskini data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Pelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">

<?php include '../includes/headerPel.php'; ?>



<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
    
    <h2 class="text-2xl font-bold mb-4 text-center">Profil Pelajar</h2>

    <?php if ($success): ?>
        <p class="text-green-600 mb-4"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="text-red-600 mb-4"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="../Controllers/updateProfilePel.php" enctype="multipart/form-data">

        <!-- 1. Nama -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Penuh</label>
            <input type="text" name="pelajar_nama" value="<?= htmlspecialchars($data['pelajar_nama']?? '') ?>"
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <!-- 2. Emel -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Nombor Matrik</label>
            <input type="text" value="<?= htmlspecialchars($data['pelajar_matrik']?? '') ?>" readonly
                   class="w-full mt-1 px-3 py-2 border border-gray-300 bg-gray-100 rounded text-gray-600">
        </div>

        <!-- 2. Emel -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Email</label>
            <input type="text" value="<?= htmlspecialchars($data['pelajar_emel']?? '') ?>" readonly
                   class="w-full mt-1 px-3 py-2 border border-gray-300 bg-gray-100 rounded text-gray-600">
        </div>

        <!-- 3. Kad Pengenalan -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Kad Pengenalan</label>
                <input type="text" name="pelajar_kadpengenalan"
                value="<?= htmlspecialchars($data['pelajar_kadpengenalan'] ?? '') ?>"
                <?= !empty($data['pelajar_kadpengenalan']) ? 'readonly' : '' ?>
                class="w-full mt-1 px-3 py-2 border border-gray-300 rounded 
                  <?= !empty($data['pelajar_kadpengenalan']) ? 'bg-gray-100 text-gray-600' : '' ?>">
    </div>

<!-- 4.1 Jantina -->
<div class="mb-4">
    <label class="block text-sm font-medium">Jantina</label>
    <select name="jantina" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded" required>
        <option value="">-- Pilih Jantina --</option>
        <option value="Lelaki" <?= ($data['jantina'] ?? '') === 'Lelaki' ? 'selected' : '' ?>>Lelaki</option>
        <option value="Perempuan" <?= ($data['jantina'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
    </select>
</div>



        <!-- 4. No Telefon -->
        <div class="mb-4">
            <label class="block text-sm font-medium">No Telefon</label>
            <input type="text" name="pelajar_telefon" value="<?= htmlspecialchars($data['pelajar_telefon']?? '') ?>"
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- 4. Tahun Pengajian -->
        <div class="mb-4">
    <label class="block text-sm font-medium">Tahun Pengajian</label>
    <select name="pelajar_tahun" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded" required>
        <option value="">-- Pilih Tahun Pengajian --</option>
        <option value="1" <?= ($data['pelajar_tahun'] ?? '') === '1' ? 'selected' : '' ?>>1</option>
        <option value="2" <?= ($data['pelajar_tahun'] ?? '') === '2' ? 'selected' : '' ?>>2</option>
        <option value="3" <?= ($data['pelajar_tahun'] ?? '') === '3' ? 'selected' : '' ?>>3</option>
        <option value="4" <?= ($data['pelajar_tahun'] ?? '') === '4' ? 'selected' : '' ?>>4</option>
        <option value="5" <?= ($data['pelajar_tahun'] ?? '') === '5' ? 'selected' : '' ?>>5</option>
    </select>
</div>

        <!-- 4. Fakulti -->
        <div class="mb-4">
    <label class="block text-sm font-medium">Fakulti Pengajian</label>
    <select name="pelajar_fakulti" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded" required>
    <option value="">-- Sila Pilih  Fakulti --</option>
    <option value="FTSM" <?= ($data['pelajar_fakulti'] ?? '') === 'FTSM' ? 'selected' : '' ?>>FTSM</option>
    <option value="FUU" <?= ($data['pelajar_fakulti'] ?? '') === 'FUU' ? 'selected' : '' ?>>FUU</option>
    <option value="FKAB" <?= ($data['pelajar_fakulti'] ?? '') === 'FKAB' ? 'selected' : '' ?>>FKAB</option>
    <option value="FPEND" <?= ($data['pelajar_fakulti'] ?? '') === 'FPEND' ? 'selected' : '' ?>>FPEND</option>
    <option value="PUSAT CITRA" <?= ($data['pelajar_fakulti'] ?? '') === 'PUSAT CITRA' ? 'selected' : '' ?>>PUSAT CITRA</option>
    <option value="FST" <?= ($data['pelajar_fakulti'] ?? '') === 'FST' ? 'selected' : '' ?>>FST</option>
    <option value="FPI" <?= ($data['pelajar_fakulti'] ?? '') === 'FPI' ? 'selected' : '' ?>>FPI</option>
    <option value="FSSK" <?= ($data['pelajar_fakulti'] ?? '') === 'FSSK' ? 'selected' : '' ?>>FSSK</option>
    <option value="FEP" <?= ($data['pelajar_fakulti'] ?? '') === 'FEP' ? 'selected' : '' ?>>FEP</option>
    <option value="FSK" <?= ($data['pelajar_fakulti'] ?? '') === 'FSK' ? 'selected' : '' ?>>FSK</option>
    <option value="FFAR" <?= ($data['pelajar_fakulti'] ?? '') === 'FFAR' ? 'selected' : '' ?>>FFAR</option>
    <option value="FPER" <?= ($data['pelajar_fakulti'] ?? '') === 'FPER' ? 'selected' : '' ?>>FPER</option>
    <option value="FGG" <?= ($data['pelajar_fakulti'] ?? '') === 'FGG' ? 'selected' : '' ?>>FGG</option>
</select>

</div>
    </div>

        <!-- 4. Program -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Program Pengajian</label>
            <input type="text" name="pelajar_program" value="<?= htmlspecialchars($data['pelajar_program']?? '') ?>"
                   class="w-full mt-1 px-3 py-2 border border-gray-300 rounded">
        </div>

        <!-- 4. Kolej kediaman -->
        <div class="mb-4">
    <label class="block text-sm font-medium">Kolej Kediaman</label>
    <select name="pelajar_kolej" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded" required>
    <option value="">-- Sila Pilih Kolej Kediaman --</option>
    <option value="Kolej Dato' Onn" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Dato' Onn" ? 'selected' : '' ?>>Kolej Dato' Onn</option>
    <option value="Kolej Aminuddin Baki" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Aminuddin Baki" ? 'selected' : '' ?>>Kolej Aminuddin Baki</option>
    <option value="Kolej Ungku Omar" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Ungku Omar" ? 'selected' : '' ?>>Kolej Ungku Omar</option>
    <option value="Kolej Burhanuddin Helmi" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Burhanuddin Helmi" ? 'selected' : '' ?>>Kolej Burhanuddin Helmi</option>
    <option value="Kolej Ibrahim Yaakub" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Ibrahim Yaakub" ? 'selected' : '' ?>>Kolej Ibrahim Yaakub</option>
    <option value="Kolej Rahim Kajai" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Rahim Kajai" ? 'selected' : '' ?>>Kolej Rahim Kajai</option>
    <option value="Kolej Ibu Zain" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Ibu Zain" ? 'selected' : '' ?>>Kolej Ibu Zain</option>
    <option value="Kolej Keris Mas" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Keris Mas" ? 'selected' : '' ?>>Kolej Keris Mas</option>
    <option value="Kolej Pendeta Za'ba" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Pendeta Za'ba" ? 'selected' : '' ?>>Kolej Pendeta Za'ba</option>
    <option value="Kolej Tun Hussein Onn" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Tun Hussein Onn" ? 'selected' : '' ?>>Kolej Tun Hussein Onn</option>
    <option value="Kolej Tun Syed Nasir" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Tun Syed Nasir" ? 'selected' : '' ?>>Kolej Tun Syed Nasir</option>
    <option value="Kolej Tun Dr. Ismail" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Tun Dr. Ismail" ? 'selected' : '' ?>>Kolej Tun Dr. Ismail</option>
    <option value="Kolej Kediaman Ke-13" <?= ($data['pelajar_kolej'] ?? '') === "Kolej Kediaman Ke-13" ? 'selected' : '' ?>>Kolej Kediaman Ke-13</option>
    <option value="Sewa Luar" <?= ($data['pelajar_kolej'] ?? '') === "Sewa Luar" ? 'selected' : '' ?>>Sewa Luar</option>
</select>


</div>

        <!-- 5. Alamat -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Alamat Tetap</label>
            <textarea name="pelajar_alamat" rows="3"
          class="w-full mt-1 px-3 py-2 border border-gray-300 rounded"><?= htmlspecialchars($data['pelajar_alamat'] ?? '') ?></textarea>

        </div>

        <!-- 6. Gambar -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Gambar Profil</label>
            <?php if (!empty($data['pelajar_gambar'])): ?>
                <img src="../uploads/<?= $data['pelajar_gambar'] ?>" class="w-24 h-24 rounded-full mb-2 object-cover">
            <?php endif; ?>
            <input type="file" name="pelajar_gambar" class="text-sm">
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
            Simpan Maklumat
        </button>
    </form>
</div>

</body>
</html>
