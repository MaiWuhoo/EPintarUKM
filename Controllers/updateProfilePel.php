<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$nama = $_POST['pelajar_nama'];
$telefon = $_POST['pelajar_telefon'];
$alamat = $_POST['pelajar_alamat'];
$kadpengenalan = $_POST['pelajar_kadpengenalan'] ?? '';
$jantina = $_POST['jantina'] ?? null;

$fakulti = $_POST['pelajar_fakulti'];
$program = $_POST['pelajar_program'];
$tahun = $_POST['pelajar_tahun'];
$kolej = $_POST['pelajar_kolej'];

// Semak kad pengenalan sedia ada
$sqlCheck = "SELECT pelajar_kadpengenalan FROM Pelajar_UKM WHERE pelajar_emel = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $emel);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
$current = $result->fetch_assoc();
$existingIC = $current['pelajar_kadpengenalan'];

// Semak jika gambar dimuat naik
if (isset($_FILES['pelajar_gambar']) && $_FILES['pelajar_gambar']['error'] === 0) {
    $gambarName = uniqid() . "_" . $_FILES['pelajar_gambar']['name'];
    $target = "../uploads/" . $gambarName;
    move_uploaded_file($_FILES['pelajar_gambar']['tmp_name'], $target);

    $sql = "UPDATE Pelajar_UKM SET pelajar_nama=?, pelajar_telefon=?, pelajar_alamat=?, jantina=?, pelajar_gambar=?, pelajar_fakulti=?, pelajar_program=?, pelajar_tahun=?, pelajar_kolej=?";

    if (empty($existingIC) && !empty($kadpengenalan)) {
        $sql .= ", pelajar_kadpengenalan=?";
        $sql .= " WHERE pelajar_emel=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $nama, $telefon, $alamat, $jantina, $gambarName, $fakulti, $program, $tahun, $kolej, $kadpengenalan, $emel);
    } else {
        $sql .= " WHERE pelajar_emel=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nama, $telefon, $alamat, $jantina, $gambarName, $fakulti, $program, $tahun, $kolej, $emel);
    }
} else {
    $sql = "UPDATE Pelajar_UKM SET pelajar_nama=?, pelajar_telefon=?, pelajar_alamat=?, jantina=?, pelajar_fakulti=?, pelajar_program=?, pelajar_tahun=?, pelajar_kolej=?";

    if (empty($existingIC) && !empty($kadpengenalan)) {
        $sql .= ", pelajar_kadpengenalan=?";
        $sql .= " WHERE pelajar_emel=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nama, $telefon, $alamat, $jantina, $fakulti, $program, $tahun, $kolej, $kadpengenalan, $emel);
    } else {
        $sql .= " WHERE pelajar_emel=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $nama, $telefon, $alamat, $jantina, $fakulti, $program, $tahun, $kolej, $emel);
    }
}

if ($stmt->execute()) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kemaskini Berjaya</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berjaya!',
    text: 'Maklumat berjaya dikemaskini.',
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href='../Pelajar/profile.php';
    }
});
</script>
</body>
</html>
<?php
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ralat</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'error',
    title: 'Ralat!',
    text: 'Ralat semasa mengemaskini data.',
    confirmButtonColor: '#d33',
    confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        window.history.back();
    }
});
</script>
</body>
</html>
<?php
}
?>
