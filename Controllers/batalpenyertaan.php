<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_POST['aktiviti_id'])) {
    echo "<p class='text-red-600'>Permintaan tidak sah.</p>";
    exit;
}

$aktiviti_id = $_POST['aktiviti_id'];
$pelajar_emel = $_SESSION['emel'];

// Semak jika benar-benar telah menyertai
$check = $conn->prepare("SELECT * FROM AktivitiPenyertaan WHERE pelajar_emel = ? AND aktiviti_id = ?");
$check->bind_param("si", $pelajar_emel, $aktiviti_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=fail");
    exit;
}

// Dapatkan info untuk notifikasi
$get = $conn->prepare("SELECT a.persatuan_id, a.aktiviti_nama, p.pman_emel FROM Aktiviti a JOIN Persatuan p ON a.persatuan_id = p.persatuan_id WHERE a.aktiviti_id = ?");
$get->bind_param("i", $aktiviti_id);
$get->execute();
$info = $get->get_result()->fetch_assoc();

// Delete dari AktivitiPenyertaan
$delete = $conn->prepare("DELETE FROM AktivitiPenyertaan WHERE pelajar_emel = ? AND aktiviti_id = ?");
$delete->bind_param("si", $pelajar_emel, $aktiviti_id);
$success = $delete->execute();

if ($success) {
    // Simpan notifikasi ke pengurus
    $msg = "Seorang pelajar telah membatalkan penyertaan dari aktiviti: " . $info['aktiviti_nama'];
    $insertNotif = $conn->prepare("INSERT INTO notifikasipengurus (pman_emel, pelajar_emel, tajuk, tarikh, status_baca, aktiviti_id) VALUES (?, ?, ?, NOW(), 'belum', ?)");
    $insertNotif->bind_param("sssi", $info['pman_emel'], $pelajar_emel, $msg, $aktiviti_id);
    
    $insertNotif->execute();

    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=cancelled");
} else {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=fail");
}
?>
