<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pengurus') {
    $emel = $_SESSION['emel'];

    // ✅ Mark permohonan biasa sebagai dibaca
    $stmt1 = $conn->prepare("UPDATE Permohonan pm 
        JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id 
        SET pm.notified = 1 
        WHERE ps.pman_emel = ? AND pm.notified = 0");
    $stmt1->bind_param("s", $emel);
    $stmt1->execute();

    // ✅ Mark permohonan biasa sebagai dibaca (hanya untuk status 'disahkan' atau 'menunggu')
$stmt2 = $conn->prepare("UPDATE Permohonan pm 
    JOIN Persatuan ps ON pm.persatuan_id = ps.persatuan_id 
    SET pm.notified = 1 
    WHERE ps.pman_emel = ? 
      AND pm.notified = 0 
      AND pm.status IN ('disahkan', 'menunggu')");
$stmt2->bind_param("s", $emel);
$stmt2->execute();


    // ✅ Mark penyertaan aktiviti sebagai dibaca
    $stmt3 = $conn->prepare("UPDATE AktivitiPenyertaan ap 
        JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
        JOIN Persatuan ps ON a.persatuan_id = ps.persatuan_id 
        SET ap.notified = 1 
        WHERE ps.pman_emel = ? AND ap.notified = 0");
    $stmt3->bind_param("s", $emel);
    $stmt3->execute();

    // ✅ Mark pembatalan penyertaan & kes khas sebagai dibaca
    $stmt4 = $conn->prepare("UPDATE notifikasipengurus 
        SET status_baca = 'sudah' 
        WHERE pman_emel = ? AND status_baca = 'belum' AND jenis_notifikasi IN ('batal_penyertaan', 'kes_khas_baru')");
    $stmt4->bind_param("s", $emel);
    $stmt4->execute();

    echo "updated";
} else {
    http_response_code(403);
    echo "unauthorized";
}

