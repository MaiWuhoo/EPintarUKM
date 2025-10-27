<?php
session_start();
include '../includes/db.php';

$emel = $_POST['email'] ?? '';
$matrik = $_POST['matrik'] ?? '';

// Semak dalam database
$stmt = $conn->prepare("SELECT * FROM Pelajar_UKM WHERE pelajar_emel = ? AND pelajar_matrik = ?");
$stmt->bind_param("ss", $emel, $matrik);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $_SESSION['reset_email'] = $emel;

    echo "
    <!DOCTYPE html>
    <html>
    <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Disahkan!',
                text: 'Maklumat ditemui. Sila tukar kata laluan.',
            }).then(() => {
                window.location.href = '../password/resetpass.php';
            });
        </script>
    </body>
    </html>";
} else {
    echo "
    <!DOCTYPE html>
    <html>
    <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Tidak Dijumpai',
                text: 'Maklumat emel atau matrik tidak sah.',
            }).then(() => {
                window.location.href = '../password/lupaPass.php';
            });
        </script>
    </body>
    </html>";
}
?>
