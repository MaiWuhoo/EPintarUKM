<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];

// Fetch persatuan info
$getPersatuan = $conn->prepare("
    SELECT pp.pman_id, p.persatuan_nama 
    FROM pengurus_pman pp
    JOIN persatuan p ON pp.pman_emel = p.pman_emel
    WHERE pp.pman_emel = ?
");
$getPersatuan->bind_param("s", $emel);
$getPersatuan->execute();
$row = $getPersatuan->get_result()->fetch_assoc();

if (!$row) {
    echo "<script>alert('Ralat: Maklumat persatuan tidak dijumpai.'); window.history.back();</script>";
    exit;
}

$persatuan_id = $row['pman_id'];
$persatuan_nama = $row['persatuan_nama'];

// Tangkap semua input dari borang
$aktiviti_nama     = $_POST['aktiviti_nama'];
$aktiviti_jenis    = $_POST['aktiviti_jenis'];
$tarikh_mula       = $_POST['tarikh_mula'];
$tarikh_tamat      = $_POST['tarikh_tamat'];
$aktiviti_mula     = $_POST['aktiviti_mula'];
$aktiviti_tamat    = $_POST['aktiviti_tamat'];
$aktiviti_tempat   = $_POST['aktiviti_tempat'];
$had_penyertaan    = $_POST['had_penyertaan'];
$aktiviti_maklumat = $_POST['aktiviti_maklumat'];

// Handle upload gambar
$aktiviti_gambar = '';
if (isset($_FILES['aktiviti_gambar']) && $_FILES['aktiviti_gambar']['error'] === 0) {
    $originalName = basename($_FILES['aktiviti_gambar']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extension, $allowed)) {
        $aktiviti_gambar = uniqid("img_", true) . "." . $extension;
        $uploadPath = "../uploads/" . $aktiviti_gambar;
        move_uploaded_file($_FILES['aktiviti_gambar']['tmp_name'], $uploadPath);
    }
}

$sql = "INSERT INTO Aktiviti (
    persatuan_id, persatuan_nama, aktiviti_nama, aktiviti_jenis, tarikh_mula, tarikh_tamat,
    aktiviti_mula, aktiviti_tamat, aktiviti_tempat, had_penyertaan, aktiviti_maklumat, aktiviti_gambar
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssssssss",
    $persatuan_id, $persatuan_nama, $aktiviti_nama, $aktiviti_jenis,
    $tarikh_mula, $tarikh_tamat, $aktiviti_mula, $aktiviti_tamat,
    $aktiviti_tempat, $had_penyertaan, $aktiviti_maklumat, $aktiviti_gambar
);

if ($stmt->execute()) {
    $aktiviti_id = $conn->insert_id;

    $getStudents = $conn->prepare("SELECT pelajar_emel FROM Permohonan WHERE persatuan_id = ?");
    $getStudents->bind_param("i", $persatuan_id);
    $getStudents->execute();
    $studentsResult = $getStudents->get_result();

    while ($student = $studentsResult->fetch_assoc()) {
        $pelajar_emel = $student['pelajar_emel'];
        $tajuk = "Aktiviti Baru Ditambah";
        $mesej = "Aktiviti baru \"$aktiviti_nama\" telah ditambah oleh persatuan $persatuan_nama.";

        $notif = $conn->prepare("INSERT INTO NotifikasiPelajar (pelajar_emel, persatuan_id, aktiviti_id, tajuk, mesej) VALUES (?, ?, ?, ?, ?)");
        $notif->bind_param("siiss", $pelajar_emel, $persatuan_id, $aktiviti_id, $tajuk, $mesej);
        $notif->execute();
    }
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
            text: 'Aktiviti berjaya ditambah dan notifikasi dihantar!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../Pengurus/dashboardPengurus.php';
            }
        });
    </script>
    </body>
    </html>
    <?php
    exit;
} else {
    echo "<script>alert('Ralat semasa menambah aktiviti: {$conn->error}'); window.history.back();</script>";
}
?>
