<?php
session_start();
include '../includes/db.php';

$emel = $_SESSION['reset_email'] ?? '';
$newPass = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';

if ($newPass !== $confirmPass) {
    echo "
    <!DOCTYPE html>
    <html>
    <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Tidak Sepadan',
                text: 'Kata laluan dan pengesahan tidak sama.',
            }).then(() => {
                window.location.href = '../resetPass.php';
            });
        </script>
    </body>
    </html>";
    exit;
}

$hashed = password_hash($newPass, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE Pengguna SET kataLaluan = ? WHERE emel = ?");
$stmt->bind_param("ss", $hashed, $emel);
$stmt->execute();

unset($_SESSION['reset_email']);

echo "
<!DOCTYPE html>
<html>
<head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: 'Kata laluan telah dikemas kini.',
        }).then(() => {
            window.location.href = '../login.php';
        });
    </script>
</body>
</html>";
?>
