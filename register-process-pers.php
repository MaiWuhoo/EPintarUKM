<?php
session_start();
include 'includes/db.php'; // Ensure path is correct

$namaPersatuan = $_POST['persatuan_nama'];
$emel = $_POST['email'];
$kataLaluan = $_POST['password'];
$kataLaluan2 = $_POST['password_confirmation'];

// 1. Validate password confirmation
if ($kataLaluan !== $kataLaluan2) {
    echo "<script>alert('Kata laluan tidak sepadan.'); window.history.back();</script>";
    exit;
}

$kataLaluanHashed = password_hash($kataLaluan, PASSWORD_BCRYPT);

// Begin transaction
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
    $sql3 = "INSERT INTO Persatuan (pman_emel , persatuan_nama) VALUES (?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ss", $namaPersatuan, $emel);
    $stmt3->execute();

    // Commit if all goes well
    $conn->commit();
    echo "<script>alert('Pendaftaran persatuan berjaya! Sila log masuk.'); window.location.href='Pengurus/dashboardPengurus.php';</script>";

} catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    echo "<script>alert('Ralat semasa mendaftar: " . $conn->error . "'); window.history.back();</script>";
}
?>
