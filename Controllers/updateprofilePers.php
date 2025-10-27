<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['persatuan_id'];
    $nama = $_POST['persatuan_nama'];
    $kodNegeri = $_POST['persatuan_kodNegeri'];
    $pengerusi = $_POST['pengerusi_nama'];
    $jumlahAhli = $_POST['jumlah_ahli'];
    $pengenalan = $_POST['pengenalan'];
    $objektif = $_POST['objektif'];
    $misi = $_POST['misi'];
    $visi = $_POST['visi'];
    $benefit = $_POST['benefit'];
    $sosialMedia = $_POST['sosial_media'];

    // Handle file uploads (Logo and Struktur Organisasi)
    $logoPath = null;
    if (isset($_FILES['persatuan_logo']) && $_FILES['persatuan_logo']['error'] === 0) {
        $logoName = uniqid() . "_" . $_FILES['persatuan_logo']['name'];
        move_uploaded_file($_FILES['persatuan_logo']['tmp_name'], "../uploads/" . $logoName);
        $logoPath = $logoName;
    }

    $organisasiPath = null;
    if (isset($_FILES['organisasi_img']) && $_FILES['organisasi_img']['error'] === 0) {
        $organisasiName = uniqid() . "_" . $_FILES['organisasi_img']['name'];
        move_uploaded_file($_FILES['organisasi_img']['tmp_name'], "../uploads/" . $organisasiName);
        $organisasiPath = $organisasiName;
    }

    // Update Query
    $query = "UPDATE Persatuan SET 
                persatuan_nama = ?, 
                persatuan_kodNegeri = ?, 
                pengerusi_nama = ?, 
                jumlah_ahli = ?, 
                pengenalan = ?, 
                objektif = ?, 
                misi = ?, 
                visi = ?, 
                benefit = ?, 
                sosial_media = ?, 
                updated_at = NOW()";

    // Add file uploads if exists
    if ($logoPath) {
        $query .= ", persatuan_logo = '$logoPath'";
    }
    if ($organisasiPath) {
        $query .= ", organisasi_img = '$organisasiPath'";
    }

    $query .= " WHERE persatuan_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "sssissssssi", 
        $nama, 
        $kodNegeri, 
        $pengerusi, 
        $jumlahAhli, 
        $pengenalan, 
        $objektif, 
        $misi, 
        $visi, 
        $benefit, 
        $sosialMedia, 
        $id
    );

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
                window.location.href='../Pengurus/profile.php';
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
}
?>
