<?php
include '../includes/db.php';
header('Content-Type: application/json');

$persatuan_id = isset($_GET['persatuan_id']) ? (int)$_GET['persatuan_id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;

$lelaki = 0;
$perempuan = 0;

$sql = "SELECT LOWER(pel.jantina) AS jantina, COUNT(*) AS jumlah
        FROM Permohonan p
        JOIN pelajar_ukm pel 
          ON p.pelajar_emel COLLATE utf8mb4_general_ci = pel.pelajar_emel COLLATE utf8mb4_general_ci
        WHERE p.persatuan_id = ?
          AND YEAR(p.permohonan_tarikh) = ?
          AND LOWER(p.status) = 'disahkan'";

if ($bulan !== 0) {
    $sql .= " AND MONTH(p.permohonan_tarikh) = ?";
}

$sql .= " GROUP BY jantina";

$stmt = $conn->prepare($sql);

if ($bulan !== 0) {
    $stmt->bind_param("iii", $persatuan_id, $tahun, $bulan);
} else {
    $stmt->bind_param("ii", $persatuan_id, $tahun);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['jantina'] === 'lelaki') {
        $lelaki = (int)$row['jumlah'];
    } elseif ($row['jantina'] === 'perempuan') {
        $perempuan = (int)$row['jumlah'];
    }
}

echo json_encode([
    'lelaki' => $lelaki,
    'perempuan' => $perempuan
]);
