<?php
session_start();
include '../includes/db.php'; // Adjust path if needed

header('Content-Type: application/json');

// Check if logged in
if (!isset($_SESSION['emel'])) {
    echo json_encode([]);
    exit;
}

$pelajar_emel = $_SESSION['emel'];

// Fetch all chat history for this student
$stmt = $conn->prepare("SELECT sender, message, created_at FROM ChatHistory WHERE pelajar_emel = ? ORDER BY created_at ASC");
$stmt->bind_param("s", $pelajar_emel);
$stmt->execute();
$result = $stmt->get_result();

$chats = [];
while ($row = $result->fetch_assoc()) {
    $chats[] = $row;
}

echo json_encode($chats);
exit;
?>
