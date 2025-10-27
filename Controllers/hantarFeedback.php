<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db.php';

function debugLog($message) {
    file_put_contents("debug_feedback_log.txt", $message . "\n", FILE_APPEND);
}

// Debug: Check session
if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    debugLog("Akses ditolak - tiada sesi.");
    exit("Akses ditolak");
}

$pelajar_emel = $_SESSION['emel'];
$aktiviti_id = $_POST['aktiviti_id'] ?? null;
$ratingData = $_POST['rating'] ?? [];
$review_text = trim($_POST['review_text'] ?? '');

debugLog("POST data: " . print_r($_POST, true));

// Validate input
if (!$aktiviti_id || empty($ratingData)) {
    debugLog("Data tidak lengkap: aktiviti_id=$aktiviti_id, rating kosong=" . empty($ratingData));
    exit("Data tidak lengkap");
}

// Check duplicate review
$check = $conn->prepare("SELECT * FROM aktivitiReview WHERE aktiviti_id = ? AND pelajar_emel = ?");
if (!$check) {
    debugLog("Prepare gagal (check review): " . $conn->error);
    exit("DB error on check");
}
$check->bind_param("is", $aktiviti_id, $pelajar_emel);
$check->execute();
$result = $check->get_result();
if ($result->num_rows > 0) {
    debugLog("Review telah dihantar sebelum ini.");
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Maklum Balas</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'info',
    title: 'Maklum Balas Diterima',
    text: 'Anda telah menghantar maklum balas untuk aktiviti ini.',
}).then(() => {
    window.location.href = '../pelajar/aktivitiSaya.php';
});
</script>
</body>
</html>";
    exit;
}

// Begin insert
$conn->begin_transaction();
try {
    $now = date('Y-m-d');

    // Insert review
    $stmt = $conn->prepare("INSERT INTO aktivitiReview (aktiviti_id, pelajar_emel, review_text, review_date) VALUES (?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare review failed: " . $conn->error);
    $stmt->bind_param("isss", $aktiviti_id, $pelajar_emel, $review_text, $now);
    if (!$stmt->execute()) throw new Exception("Execute review failed: " . $stmt->error);
    $review_id = $stmt->insert_id;
    debugLog("Review inserted: ID = $review_id");

    // Insert ratings
    $stmtResp = $conn->prepare("INSERT INTO reviewResponse (review_id, question_id, rating) VALUES (?, ?, ?)");
    if (!$stmtResp) throw new Exception("Prepare response failed: " . $conn->error);

    foreach ($ratingData as $question_id => $rating) {
        $stmtResp->bind_param("iii", $review_id, $question_id, $rating);
        if (!$stmtResp->execute()) throw new Exception("Execute response failed: " . $stmtResp->error);
        debugLog("Response inserted: Q=$question_id, R=$rating");
    }

    $conn->commit();
    debugLog("Maklum balas berjaya dihantar.");

    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Maklum Balas</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'success',
    title: 'Terima Kasih!',
    text: 'Maklum balas berjaya dihantar.',
    showConfirmButton: false,
    timer: 2200
}).then(() => {
    window.location.href = '../pelajar/aktivitiSaya.php';
});
</script>
</body>
</html>";
    exit;

} catch (Exception $e) {
    $conn->rollback();
    debugLog("TRANSACTION FAILED: " . $e->getMessage());
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Ralat</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
<script>
Swal.fire({
    icon: 'error',
    title: 'Ralat!',
    text: 'Ralat semasa menghantar maklum balas.',
});
</script>
</body>
</html>";
}
?>
