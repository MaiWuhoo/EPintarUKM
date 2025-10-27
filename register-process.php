<?php
include 'includes/db.php';
// fixed path
session_start();

$nama = $_POST['pelajar_nama'];
$matrik = $_POST['pelajar_matrik'];
$emel = $_POST['email'];
$kataLaluan = $_POST['password'];
$kataLaluan2 = $_POST['password_confirmation'];

if ($kataLaluan !== $kataLaluan2) {
    echo "<script>alert('Kata laluan tidak sepadan.'); window.history.back();</script>";
    exit;
}

$kataLaluanHashed = password_hash($kataLaluan, PASSWORD_BCRYPT);

// Pengguna insert
$sql1 = "INSERT INTO Pengguna (emel, kataLaluan, peranan) VALUES (?, ?, 'pelajar')";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("ss", $emel, $kataLaluanHashed);

// Pelajar_UKM insert
$sql2 = "INSERT INTO Pelajar_UKM (pelajar_emel, pelajar_matrik, pelajar_nama) VALUES (?, ?, ?)";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("sss", $emel, $matrik, $nama);

if ($stmt1->execute() && $stmt2->execute()) {
    echo "<script>alert('Pendaftaran berjaya!'); window.location.href='Pelajar/dashboardPelajar.php';</script>";
} else {
    echo "<script>alert('Ralat semasa pendaftaran: {$conn->error}'); window.history.back();</script>";
}
?>
