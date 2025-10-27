<?php
session_start();
include 'includes/db.php';

$emel = $_POST['emel'];
$kataLaluan = $_POST['kataLaluan'];

$sql = "SELECT * FROM Pengguna WHERE emel = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($kataLaluan, $user['kataLaluan'])) {
        $_SESSION['emel'] = $user['emel'];
        $_SESSION['peranan'] = $user['peranan'];

        if ($user['peranan'] === 'pelajar') {
            $q = "SELECT pelajar_nama FROM Pelajar_UKM WHERE pelajar_emel = ?";
        } else {
            $q = "SELECT persatuan_nama FROM Persatuan WHERE pman_emel = ?";
        }

        $stmt2 = $conn->prepare($q);
        $stmt2->bind_param("s", $user['emel']);
        $stmt2->execute();
        $r = $stmt2->get_result();
        $nameRow = $r->fetch_assoc();

        $_SESSION['nama'] = $nameRow ? reset($nameRow) : 'Pengguna';

        if ($user['peranan'] === 'pelajar') {
            header("Location: Pelajar/dashboardPelajar.php");
        } else {
            header("Location: Pengurus/dashboardPengurus.php");
        }
        exit;
    } else {
        // ❌ Kata laluan salah
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
                    title: 'Kata Laluan Salah',
                    text: 'Sila cuba lagi.',
                }).then(() => {
                    window.location.href = 'login.php';
                });
            </script>
        </body>
        </html>";
        exit;
    }
} else {
    // ❌ Akaun tidak wujud
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
                title: 'Akaun Tidak Wujud',
                text: 'Sila semak emel anda.',
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>
    </body>
    </html>";
    exit;
}
?>
