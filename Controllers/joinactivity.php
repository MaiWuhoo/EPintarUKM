<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_POST['aktiviti_id'])) {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=0&status=fail");
    exit;
}

$aktiviti_id = $_POST['aktiviti_id'];
$pelajar_emel = $_SESSION['emel'];

// ✅ 1. Check if already joined
$check = $conn->prepare("SELECT * FROM AktivitiPenyertaan WHERE pelajar_emel = ? AND aktiviti_id = ?");
$check->bind_param("si", $pelajar_emel, $aktiviti_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=overlap");
    exit;
}
$check->close();

// ✅ 2. Get datetime mula dan tamat aktiviti yang ingin disertai
$getTime = $conn->prepare("SELECT tarikh_mula, tarikh_tamat, aktiviti_mula, aktiviti_tamat FROM Aktiviti WHERE aktiviti_id = ?");
$getTime->bind_param("i", $aktiviti_id);
$getTime->execute();
$result = $getTime->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $tarikh_mula_baru = $row['tarikh_mula'];
    $tarikh_tamat_baru = $row['tarikh_tamat'];
    $mula_baru = $row['aktiviti_mula'];
    $tamat_baru = $row['aktiviti_tamat'];

    // Gabungkan datetime untuk semakan overlap
    $datetime_mula_baru = $tarikh_mula_baru . ' ' . $mula_baru;
    $datetime_tamat_baru = $tarikh_tamat_baru . ' ' . $tamat_baru;
} else {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=fail");
    exit;
}
$getTime->close();

// ✅ 3. Semak pertindihan penuh antara datetime aktiviti sedia ada & aktiviti baru
$checkOverlap = $conn->prepare("
    SELECT a.tarikh_mula, a.tarikh_tamat, a.aktiviti_mula, a.aktiviti_tamat
    FROM AktivitiPenyertaan ap
    JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id
    WHERE ap.pelajar_emel = ?
    AND (
        CONCAT(a.tarikh_mula, ' ', a.aktiviti_mula) < ?
        AND CONCAT(a.tarikh_tamat, ' ', a.aktiviti_tamat) > ?
    )
");
$checkOverlap->bind_param(
    "sss",
    $pelajar_emel,
    $datetime_tamat_baru, // aktiviti lama mula < tamat aktiviti baru
    $datetime_mula_baru   // aktiviti lama tamat > mula aktiviti baru
);
$checkOverlap->execute();
$result = $checkOverlap->get_result();

// ❌ Jika ada pertindihan — redirect dengan status overlap
if ($result->num_rows > 0) {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=overlap");
    exit;
}
$checkOverlap->close();




// ✅ 4. Get persatuan_id dari Aktiviti
$getPersatuan = $conn->prepare("SELECT persatuan_id FROM Aktiviti WHERE aktiviti_id = ?");
$getPersatuan->bind_param("i", $aktiviti_id);
$getPersatuan->execute();
$res = $getPersatuan->get_result();
$row = $res->fetch_assoc();
$persatuan_id = $row['persatuan_id'] ?? 0;

// ✅ 5. Insert penyertaan
$insert = $conn->prepare("INSERT INTO AktivitiPenyertaan (pelajar_emel, aktiviti_id, penyertaan_tarikh, persatuan_id) VALUES (?, ?, CURDATE(), ?)");
$insert->bind_param("sii", $pelajar_emel, $aktiviti_id, $persatuan_id);

if ($insert->execute()) {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=success");
} else {
    header("Location: ../Aktiviti/aktivitiDetails.php?id=$aktiviti_id&status=fail");
}
?>
