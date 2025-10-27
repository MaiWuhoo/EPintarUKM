<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$id = $_GET['id'] ?? 0;

// Semak jika pengumuman milik persatuan pengurus
$stmt = $conn->prepare("SELECT p.id FROM Pengumuman p JOIN Persatuan ps ON p.persatuan_id = ps.persatuan_id WHERE p.id = ? AND ps.pman_emel = ?");
$stmt->bind_param("is", $id, $emel);
$stmt->execute();
$result = $stmt->get_result();

$isValid = ($result->num_rows > 0);

if ($isValid) {
    $delete = $conn->prepare("DELETE FROM Pengumuman WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    $status = 'berjaya';
    $msg = 'Pengumuman telah dipadam.';
    $icon = 'success';
} else {
    $status = 'gagal';
    $msg = 'Akses ditolak atau pengumuman tidak sah.';
    $icon = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Padam Pengumuman</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: '<?= $icon ?>',
    title: '<?= $status === "berjaya" ? "Berjaya" : "Ralat" ?>',
    text: '<?= $msg ?>',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = '../Penguguman/pengumumanlist.php';
});
</script>
</body>
</html>
