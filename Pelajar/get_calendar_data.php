<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

$response = ['dates' => [], 'details' => []];

if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pelajar') {
    $emel = $_SESSION['emel'];
    $bulan = isset($_GET['bulan']) ? str_pad($_GET['bulan'], 2, '0', STR_PAD_LEFT) : date('m');
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

    $stmt = $conn->prepare("
        SELECT a.tarikh_mula, a.tarikh_tamat, a.aktiviti_nama, a.aktiviti_mula, a.aktiviti_tamat, p.persatuan_nama
        FROM AktivitiPenyertaan ap 
        JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
        LEFT JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
        WHERE ap.pelajar_emel = ? 
        AND a.tarikh_mula LIKE ? 
        AND a.status = 'aktif'
    ");

    $likePattern = "$tahun-$bulan-%";
    $stmt->bind_param("ss", $emel, $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $mula = new DateTime($row['tarikh_mula']);
        $tamat = new DateTime($row['tarikh_tamat']);

        while ($mula <= $tamat) {
            $tarikh = $mula->format('Y-m-d');
            $response['dates'][] = $tarikh;
            $response['details'][$tarikh][] = [
                'nama' => $row['aktiviti_nama'],
                'masa' => date('g:i A', strtotime($row['aktiviti_mula'])) . ' - ' . date('g:i A', strtotime($row['aktiviti_tamat'])),
                'persatuan' => $row['persatuan_nama'] ?? '-'
            ];
            $mula->modify('+1 day');
        }
    }
}
echo json_encode($response);
?>

