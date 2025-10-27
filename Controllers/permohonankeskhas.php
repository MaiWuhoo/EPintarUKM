<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$pelajar_emel = $_SESSION['emel'];
$persatuan_id = $_POST['persatuan_id'] ?? null;
$alasan = $_POST['alasan'] ?? '';

if (!$persatuan_id || empty($alasan)) {
    echo "<script>alert('Maklumat tidak lengkap.'); window.history.back();</script>";
    exit;
}

// ✅ Sekat permohonan kes khas jika ada satu lagi yang masih menunggu
$pendingKhas = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND jenis_permohonan = 'khas' AND status = 'menunggu'");
$pendingKhas->bind_param("s", $pelajar_emel);
$pendingKhas->execute();
$pendingRes = $pendingKhas->get_result();

if ($pendingRes->num_rows > 0) {
    echo "<script>
        alert('Anda telah menghantar permohonan kes khas yang masih menunggu pengesahan. Sila tunggu keputusan sebelum memohon lagi.');
        window.history.back();
    </script>";
    exit;
}


// Muat naik fail sokongan
$targetDir = "../uploads/dokumen/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileName = $_FILES['dokumen_sokongan']['name'] ?? '';
$fileTmp = $_FILES['dokumen_sokongan']['tmp_name'] ?? '';
$filePath = '';

if (!empty($fileName)) {
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid("sokongan_") . "." . $ext;
    $filePath = $targetDir . $newFileName;
    move_uploaded_file($fileTmp, $filePath);
} else {
    echo "<script>alert('Sila muat naik dokumen sokongan.'); window.history.back();</script>";
    exit;
}


// Check duplicate permohonan
$check = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND persatuan_id = ? AND status IN ('menunggu', 'disahkan')");
$check->bind_param("si", $pelajar_emel, $persatuan_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo "<script>alert('Anda sudah menghantar permohonan.'); window.history.back();</script>";
    exit;
}

// Insert permohonan kes khas
$tarikh = date('Y-m-d');
$jenis = 'khas';
$status = 'menunggu';
$notified = 1;

$insert = $conn->prepare("INSERT INTO Permohonan (
    pelajar_emel, persatuan_id, permohonan_tarikh, notified, alasan, jenis_permohonan, dokumen_sokongan, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("sisissss", $pelajar_emel, $persatuan_id, $tarikh, $notified,$alasan, $jenis, $newFileName, $status);

if ($insert->execute()) {
    $_SESSION['status'] = 'success';
    $_SESSION['message'] = 'Permohonan kes khas berjaya dihantar. Sila tunggu pengesahan.';
    header("Location: ../Persatuan/dashpersatuan.php");
    exit;
} else {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Ralat semasa menghantar permohonan.';
    header("Location: ../Persatuan/dashpersatuan.php");
    exit;
}
