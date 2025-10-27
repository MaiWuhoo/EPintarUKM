<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_POST['aktiviti_id'], $_POST['sebab'])) {
    header("Location: ../aktiviti/aktivitiTam.php?status=fail");
    exit;
}

$aktiviti_id = $_POST['aktiviti_id'];
$sebab = $_POST['sebab'];

// Kemaskini status dan sebab batal
$stmt = $conn->prepare("UPDATE Aktiviti SET status = 'batal', sebab_batal = ? WHERE aktiviti_id = ?");
$stmt->bind_param("si", $sebab, $aktiviti_id);

if ($stmt->execute()) {
    header("Location: ../aktiviti/aktivitiTam.php?status=batal");
} else {
    header("Location: ../aktiviti/aktivitiTam.php?status=fail");
}

// Hantar notifikasi kepada pelajar yang telah sertai
$notifStmt = $conn->prepare("SELECT pelajar_emel FROM AktivitiPenyertaan WHERE aktiviti_id = ?");
$notifStmt->bind_param("i", $aktiviti_id);
$notifStmt->execute();
$pelajarList = $notifStmt->get_result();

while ($row = $pelajarList->fetch_assoc()) {
    $tajuk = "Aktiviti Dibatalkan";
    $mesej = "Aktiviti \"" . getAktivitiNama($conn, $aktiviti_id) . "\" telah dibatalkan. Sebab: $sebab";
    $jenis = "batal";
    $notified = 1;

    $insert = $conn->prepare("INSERT INTO notifikasipelajar (pelajar_emel, aktiviti_id, tajuk, mesej, jenis, notified) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("sisssi", $row['pelajar_emel'], $aktiviti_id, $tajuk, $mesej, $jenis, $notified);
    $insert->execute();
}

// Fungsi ambil nama aktiviti
function getAktivitiNama($conn, $id) {
    $stmt = $conn->prepare("SELECT aktiviti_nama FROM Aktiviti WHERE aktiviti_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['aktiviti_nama'] ?? '';
}

?>
