<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emelPengurus = $_SESSION['emel'];
    $persatuan_id = $_POST['persatuan_id'] ?? null;
    $tajuk = trim($_POST['tajuk'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tamat_tayang = $_POST['tamat_tayang'] ?? '';

    // Semakan input
    if (!$persatuan_id || empty($tajuk) || empty($isi) || empty($tamat_tayang)) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Maklumat Tidak Lengkap',
                text: 'Sila lengkapkan semua maklumat.'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body></html>";
        exit;
    }

    // Semak jika pengurus memang mengurus persatuan tersebut
    $stmt = $conn->prepare("SELECT * FROM Persatuan WHERE persatuan_id = ? AND pman_emel = ?");
    $stmt->bind_param("is", $persatuan_id, $emelPengurus);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: 'Anda tidak dibenarkan membuat pengumuman untuk persatuan ini.'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body></html>";
        exit;
    }

    // Masukkan pengumuman ke dalam database
    $insertStmt = $conn->prepare("INSERT INTO Pengumuman (persatuan_id, tajuk, isi, tarikh_umum, tamat_tayang) VALUES (?, ?, ?, NOW(), ?)");
    $insertStmt->bind_param("isss",$persatuan_id, $tajuk, $isi, $tamat_tayang);

    if ($insertStmt->execute()) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berjaya!',
                text: 'Pengumuman berjaya ditambah.',
                timer: 2200,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '../Penguguman/pengumumanlist.php';
            });
        </script>
        </body></html>";
    } else {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head><meta charset='UTF-8'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Ralat',
                text: 'Ralat ketika menambah pengumuman.'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body></html>";
    }
} else {
    header("Location: ../Penguguman/pengumumanlist.php");
    exit;
}
