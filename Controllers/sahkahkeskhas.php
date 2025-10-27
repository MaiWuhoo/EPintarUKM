<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

function hantarNotifikasiPelajar($conn, $pelajar_emel, $jenis_notifikasi, $tajuk, $mesej) {
    $status = 'baru';
    $insert = $conn->prepare("INSERT INTO notifikasipelajar (pelajar_emel, jenis_notifikasi, tajuk, mesej, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("sssss", $pelajar_emel, $jenis_notifikasi, $tajuk, $mesej, $status);
    $insert->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permohonan_id = $_POST['permohonan_id'] ?? null;
    $status = $_POST['status'] ?? '';
    $alasanTolak = $_POST['alasan_tolak'] ?? null;

    if (!$permohonan_id || !in_array($status, ['disahkan', 'ditolak'])) {
        echo "<script>alert('Permohonan tidak sah.'); window.history.back();</script>";
        exit;
    }

    if ($status === 'ditolak') {
        if (!$alasanTolak) {
            echo "<script>alert('Sila isi alasan penolakan.'); window.history.back();</script>";
            exit;
        }
        $stmt = $conn->prepare("UPDATE Permohonan SET status = ?, alasan_tolak = ?, notified = 0 WHERE permohonan_id = ?");
        $stmt->bind_param("ssi", $status, $alasanTolak, $permohonan_id);
    } else {
        $stmt = $conn->prepare("UPDATE Permohonan SET status = ?, notified = 0 WHERE permohonan_id = ?");
        $stmt->bind_param("si", $status, $permohonan_id);
    }

    if ($stmt->execute()) {
        $getPelajar = $conn->prepare("SELECT pelajar_emel FROM Permohonan WHERE permohonan_id = ?");
        $getPelajar->bind_param("i", $permohonan_id);
        $getPelajar->execute();
        $result = $getPelajar->get_result();
        $data = $result->fetch_assoc();
        $pelajar_emel = $data['pelajar_emel'];

        $jenis_notifikasi = $status === 'disahkan' ? 'kes_khas_disahkan' : 'kes_khas_ditolak';
        $tajuk = "Makluman Permohonan Kes Khas";
        $mesej = "Permohonan Kes Khas anda telah $status.";

        hantarNotifikasiPelajar($conn, $pelajar_emel, $jenis_notifikasi, $tajuk, $mesej);

        echo "<script>alert('Tindakan berjaya dikemaskini.'); window.location.href = '../Pengurus/dashboardPengurus.php';</script>";
    } else {
        echo "<script>alert('Ralat semasa mengemaskini tindakan.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Kaedah permintaan tidak sah.'); window.history.back();</script>";
}
?>
