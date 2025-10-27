<?php
include '../includes/db.php';
header('Content-Type: application/json');

$persatuan_id = isset($_GET['persatuan_id']) ? (int)$_GET['persatuan_id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;

if ($bulan === 0) {
    // Bila semua bulan, group ikut bulan
    $aktiviti = array_fill(0, 12, 0);

    $sql = "SELECT MONTH(tarikh_mula) AS bulan, COUNT(*) AS jumlah
            FROM Aktiviti
            WHERE persatuan_id = ? 
              AND YEAR(tarikh_mula) = ? 
              AND (status IS NULL OR status != 'batal')
            GROUP BY bulan";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $persatuan_id, $tahun);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $index = (int)$row['bulan'] - 1;
            $aktiviti[$index] = (int)$row['jumlah'];
        }

        echo json_encode(['labelTarikh' => ["Jan", "Feb", "Mac", "Apr", "Mei", "Jun", "Jul", "Ogos", "Sep", "Okt", "Nov", "Dis"], 'bilangan' => $aktiviti]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed']);
    }
} else {
    // Bila bulan spesifik, senaraikan aktiviti ikut tarikh mula (dd-mm)
    $sql = "SELECT DAY(tarikh_mula) AS hari, COUNT(*) AS jumlah
            FROM Aktiviti
            WHERE persatuan_id = ? 
              AND YEAR(tarikh_mula) = ? 
              AND MONTH(tarikh_mula) = ?
              AND (status IS NULL OR status != 'batal')
            GROUP BY hari";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iii", $persatuan_id, $tahun, $bulan);
        $stmt->execute();
        $result = $stmt->get_result();

        $labelTarikh = [];
        $bilangan = [];

        while ($row = $result->fetch_assoc()) {
            $labelTarikh[] = str_pad($row['hari'], 2, "0", STR_PAD_LEFT); // Contoh: "01", "15"
            $bilangan[] = (int)$row['jumlah'];
        }

        echo json_encode(['labelTarikh' => $labelTarikh, 'bilangan' => $bilangan]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed']);
    }
}
