<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db.php';

$namaPersatuan = $_POST['persatuan_nama'] ?? '';
$emel = $_POST['email'] ?? '';
$kataLaluan = $_POST['password'] ?? '';
$kataLaluan2 = $_POST['password_confirmation'] ?? '';

// 1. Basic validation
if (empty($namaPersatuan) || empty($emel) || empty($kataLaluan)) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
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
    </body>
    </html>";
    exit;
}

if ($kataLaluan !== $kataLaluan2) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Kata Laluan Tidak Sepadan',
                text: 'Sila pastikan kata laluan dan pengesahan sepadan.'
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
    exit;
}

$kataLaluanHashed = password_hash($kataLaluan, PASSWORD_BCRYPT);

// 2. Start transaction
$conn->begin_transaction();

try {
    // Step 1: Insert into Pengguna
    $sql1 = "INSERT INTO Pengguna (emel, kataLaluan, peranan) VALUES (?, ?, 'pengurus')";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ss", $emel, $kataLaluanHashed);
    $stmt1->execute();

    // Step 2: Insert into Pengurus_PMAN
    $sql2 = "INSERT INTO Pengurus_PMAN (pman_emel, pman_katalaluan) VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $emel, $kataLaluanHashed);
    $stmt2->execute();

    // Step 3: Insert into Persatuan
    $sql3 = "INSERT INTO Persatuan (pman_emel, persatuan_nama) VALUES (?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ss", $emel, $namaPersatuan);
    $stmt3->execute();

    // Commit transaction
    $conn->commit();

    // Success alert
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Pendaftaran Berjaya</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Pendaftaran Berjaya!',
                text: 'Pendaftaran persatuan berjaya! Sila log masuk.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../login.php';
                }
            });
        </script>
    </body>
    </html>";
} catch (Exception $e) {
    $conn->rollback();
    $errorMessage = addslashes($e->getMessage());
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Ralat</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Ralat Semasa Mendaftar',
                text: '$errorMessage',
                confirmButtonText: 'OK'
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
}
?>
