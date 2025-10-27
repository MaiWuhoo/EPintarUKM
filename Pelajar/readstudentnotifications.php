<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pelajar') {
    $emel = $_SESSION['emel'];



    // Reset both types of notification flags
    $update = $conn->prepare("
        UPDATE AktivitiPenyertaan 
        SET notified_update = 0, notified_new = 0 
        WHERE pelajar_emel = ?
    ");
    $update->bind_param("s", $emel);
    $update->execute();
}
?>

