<?php
if (!isset($conn)) {
    include '../includes/db.php';
}

$emel = $_SESSION['emel'] ?? '';
$data = [];

if ($emel) {
    $stmt = $conn->prepare("SELECT * FROM pelajar_UKM WHERE pelajar_emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}
