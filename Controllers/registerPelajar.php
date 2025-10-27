<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db.php';

// Ambil data dari borang
$nama   = $_POST['pelajar_nama'] ?? '';
$matrik = $_POST['pelajar_matrik'] ?? '';
$emel   = $_POST['email'] ?? '';
$kataLaluan  = $_POST['password'] ?? '';
$kataLaluan2 = $_POST['password_confirmation'] ?? '';

// Semakan awal
if (empty($nama) || empty($matrik) || empty($emel) || empty($kataLaluan)) {
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
                text: 'Sila lengkapkan semua maklumat.',
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
    exit;
}

// Semak pengesahan kata laluan
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
                text: 'Sila pastikan kata laluan dan pengesahan sepadan.',
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
    exit;

    // Semak panjang kata laluan
if (strlen($kataLaluan) <= 6) {
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
                title: 'Kata Laluan Lemah',
                text: 'Kata laluan mesti lebih daripada 6 aksara.',
            }).then(() => {
                window.history.back();
            });
        </script>
    </body>
    </html>";
    exit;
}

}

// Hash kata laluan
$kataLaluanHashed = password_hash($kataLaluan, PASSWORD_BCRYPT);

// Mula transaksi
$conn->begin_transaction();

try {
    // Step 1: Insert ke dalam table Pengguna
    $sql1 = "INSERT INTO Pengguna (emel, kataLaluan, peranan) VALUES (?, ?, 'pelajar')";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ss", $emel, $kataLaluanHashed);
    $stmt1->execute();

    // Step 2: Insert ke dalam table Pelajar_UKM
    $sql2 = "INSERT INTO Pelajar_UKM (pelajar_emel, pelajar_matrik, pelajar_nama) VALUES (?, ?, ?)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sss", $emel, $matrik, $nama);
    $stmt2->execute();

    // Commit transaksi
    $conn->commit();

    // SweetAlert Berjaya
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
                text: 'Pendaftaran pelajar berjaya! Sila log masuk.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../login.php';
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
