<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$id = $_POST['id'] ?? 0;
$tajuk = trim($_POST['tajuk'] ?? '');
$isi = trim($_POST['isi'] ?? '');
$tamat = $_POST['tamat_tayang'] ?? '';

if (!$id || empty($tajuk) || empty($isi) || empty($tamat)) {
    echo "<script>alert('Sila lengkapkan semua maklumat.'); window.history.back();</script>";
    exit;
}

// Semak jika pengumuman milik pengurus
$stmt = $conn->prepare("SELECT p.id FROM Pengumuman p JOIN Persatuan ps ON p.persatuan_id = ps.persatuan_id WHERE p.id = ? AND ps.pman_emel = ?");
$stmt->bind_param("is", $id, $emel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        document.write(`<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Pengumuman tidak sah atau anda tidak dibenarkan.',
        }).then(() => {
            window.location.href = '../Penguguman/pengumumanlist.php';
        });
        </script>
        </body></html>`);
    </script>";
    exit;
}

// Update data
$update = $conn->prepare("UPDATE Pengumuman SET tajuk = ?, isi = ?, tamat_tayang = ? WHERE id = ?");
$update->bind_param("sssi", $tajuk, $isi, $tamat, $id);

if ($update->execute()) {
    echo "<script>
        document.write(`<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Berjaya',
            text: 'Pengumuman telah dikemaskini.',
        }).then(() => {
            window.location.href = '../Penguguman/pengumumanlist.php';
        });
        </script>
        </body></html>`);
    </script>";
} else {
    echo "<script>alert('Ralat semasa mengemaskini pengumuman.'); window.history.back();</script>";
}
