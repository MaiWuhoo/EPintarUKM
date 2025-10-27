<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || !isset($_POST['feedback'])) {
    exit;
}

$feedback = $_POST['feedback'];
$pelajar_emel = $_SESSION['emel'];

$stmt = $conn->prepare("INSERT INTO ChatFeedback (pelajar_emel, feedback, feedback_time) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $pelajar_emel, $feedback);
$stmt->execute();
?>
