<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Akses Ditolak</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Akses Ditolak',
            text: 'Sila log masuk sebagai pengurus.',
        }).then(() => {
            window.location.href = '../login.php';
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

$aktiviti_id        = $_POST['aktiviti_id'];
$aktiviti_nama      = $_POST['aktiviti_nama'];
$aktiviti_jenis     = $_POST['aktiviti_jenis'];
$tarikh_mula        = $_POST['tarikh_mula'];
$tarikh_tamat       = $_POST['tarikh_tamat'];
$aktiviti_mula      = $_POST['aktiviti_mula'];
$aktiviti_tamat     = $_POST['aktiviti_tamat'];
$aktiviti_tempat    = $_POST['aktiviti_tempat'];
$had_penyertaan     = $_POST['had_penyertaan'];
$aktiviti_maklumat  = $_POST['aktiviti_maklumat'];

// Mula query
$updateQuery = "UPDATE Aktiviti SET 
    aktiviti_nama = ?, 
    aktiviti_jenis = ?, 
    tarikh_mula = ?, 
    tarikh_tamat = ?,
    aktiviti_mula = ?, 
    aktiviti_tamat = ?, 
    aktiviti_tempat = ?,
    had_penyertaan = ?, 
    aktiviti_maklumat = ?";

$params = [
    $aktiviti_nama,
    $aktiviti_jenis,
    $tarikh_mula,
    $tarikh_tamat,
    $aktiviti_mula,
    $aktiviti_tamat,
    $aktiviti_tempat,
    $had_penyertaan,
    $aktiviti_maklumat
];
$types = "sssssssss";

// Optional: Handle image upload
if (isset($_FILES['aktiviti_gambar']) && $_FILES['aktiviti_gambar']['error'] === 0) {
    $imgName = uniqid("img_", true) . "." . pathinfo($_FILES['aktiviti_gambar']['name'], PATHINFO_EXTENSION);
    $uploadPath = "../uploads/" . $imgName;
    move_uploaded_file($_FILES['aktiviti_gambar']['tmp_name'], $uploadPath);

    $updateQuery .= ", aktiviti_gambar = ?";
    $params[] = $imgName;
    $types .= "s";
}

// Tambah WHERE clause
$updateQuery .= " WHERE aktiviti_id = ?";
$params[] = $aktiviti_id;
$types .= "i";

// Execute update
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Kemaskini status notifikasi penyertaan
    $updateNotify = $conn->prepare("UPDATE AktivitiPenyertaan SET notified_update = 1 WHERE aktiviti_id = ?");
    $updateNotify->bind_param("i", $aktiviti_id);
    $updateNotify->execute();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Berjaya</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: 'Aktiviti berjaya dikemas kini!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../Aktiviti/AktivitiTam.php';
            }
        });
    </script>
    </body>
    </html>
    <?php
    exit;
} else {
    $error = $conn->error;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Ralat</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Ralat!',
            text: 'Ralat semasa mengemas kini aktiviti: <?= addslashes($error) ?>',
        }).then(() => {
            window.history.back();
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}
?>
