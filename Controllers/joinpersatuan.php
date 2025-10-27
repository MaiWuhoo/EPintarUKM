<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$pelajar_emel = $_SESSION['emel'];
$persatuan_id = $_POST['persatuan_id'] ?? null;

// Sekat jika ada permohonan khas yang masih menunggu
$pendingCheck = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND status = 'menunggu' AND jenis_permohonan = 'khas'");
$pendingCheck->bind_param("s", $pelajar_emel);
$pendingCheck->execute();
$pendingResult = $pendingCheck->get_result();

if ($pendingResult->num_rows > 0) {
    showAlert("Gagal", "Anda telah menghantar permohonan kes khas yang masih menunggu pengesahan. Sila tunggu sebelum memohon persatuan lain.", "error", true);
    exit;
}

if (!$persatuan_id) {
    showAlert("Ralat", "ID persatuan tidak sah.", "error", true);
    exit;
}

$sqlPersatuan = $conn->prepare("SELECT persatuan_kodNegeri, persatuan_nama FROM Persatuan WHERE persatuan_id = ?");
$sqlPersatuan->bind_param("i", $persatuan_id);
$sqlPersatuan->execute();
$persatuanData = $sqlPersatuan->get_result()->fetch_assoc();

if (!$persatuanData) {
    showAlert("Ralat", "Persatuan tidak dijumpai.", "error", true);
    exit;
}

$kodNegeriPersatuan = $persatuanData['persatuan_kodNegeri'];
$namaPersatuan = $persatuanData['persatuan_nama'];

$sqlPelajar = $conn->prepare("SELECT pelajar_kadpengenalan FROM Pelajar_UKM WHERE pelajar_emel = ?");
$sqlPelajar->bind_param("s", $pelajar_emel);
$sqlPelajar->execute();
$pelajarData = $sqlPelajar->get_result()->fetch_assoc();

if (!$pelajarData || strlen($pelajarData['pelajar_kadpengenalan']) < 8) {
    showAlert("Ralat", "Kad pengenalan pelajar tidak sah atau tidak lengkap.", "error", true);
    exit;
}

$icCode = substr($pelajarData['pelajar_kadpengenalan'], 6, 2);

// Validate kod negeri
if ($icCode !== $kodNegeriPersatuan) {
    showAlert("Gagal", "Anda bukan pelajar kelahiran negeri yang layak untuk menyertai $namaPersatuan.", "error", true);
    exit;
}

// Check duplicate
$check = $conn->prepare("SELECT * FROM Permohonan WHERE pelajar_emel = ? AND persatuan_id = ?");
$check->bind_param("si", $pelajar_emel, $persatuan_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    showAlert("Gagal", "Anda sudah menjadi ahli persatuan ini.", "warning", true);
    exit;
}

// Insert permohonan automatik
$now = date('Y-m-d H:i:s');
$jenis_permohonan = 'auto';
$status = 'disahkan';

$insert = $conn->prepare("INSERT INTO Permohonan (pelajar_emel, persatuan_id, jenis_permohonan, status, permohonan_tarikh, notified) VALUES (?, ?, ?, ?, ?, 0)");
$insert->bind_param("sisss", $pelajar_emel, $persatuan_id, $jenis_permohonan, $status, $now);

if ($insert->execute()) {
    $update = $conn->prepare("
        UPDATE Persatuan 
        SET jumlah_ahli = (
            SELECT COUNT(*) FROM Permohonan WHERE persatuan_id = ?
        )
        WHERE persatuan_id = ?
    ");
    $update->bind_param("ii", $persatuan_id, $persatuan_id);
    $update->execute();

    showAlert("Berjaya!", "Pendaftaran berjaya dihantar dan anda kini ahli $namaPersatuan.", "success", false, "../Persatuan/dashpersatuan.php");
} else {
    showAlert("Ralat", "Ralat semasa menghantar permohonan.", "error", true);
}


// 🟢 SWEET ALERT HELPER
function showAlert($title, $message, $icon, $goBack = false, $redirect = '')
{
    $action = '';

    if (!empty($redirect)) {
        $action = "window.location.href = '$redirect';";
    } elseif ($goBack) {
        $action = "window.history.back();";
    }

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <meta charset="UTF-8">
        <title>Notifikasi</title>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$message',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                $action
            }
        });
    </script>
    </body>
    </html>
    HTML;
}

?>
