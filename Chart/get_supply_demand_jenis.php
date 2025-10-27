<?php
include '../includes/db.php';
header('Content-Type: application/json');

$persatuan_id = isset($_GET['persatuan_id']) ? (int)$_GET['persatuan_id'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0; // 0 = semua bulan

// ✅ Senarai jenis tetap (susunan ikut keperluan)
$jenisAktivitiList = ['Sukan','Khidmat Masyarakat','Debat / Pidato',
  'Ekspo/ Karnival / Pameran / Festival','Forum / Diskusi / Ceramah',
  'Jamuan Makan Malam / Ulang Tahun', 'Keagamaan','Kebudayaan / Kesenian','Keusahawanan','Latihan / Kursus / Bengkel',
  'Lawatan Sambil Belajar / Mobiliti','Penyertaan / Pertandingan',
  'Perjumpaan / Perhimpunan / Hari Keluarga','Perkhemahan / Eksplorasi','Seminar / Persidangan','Sukan' // duplicated? consider reviewing if needed
];

// ✅ Sediakan array kosong ikut jenis
$supply = array_fill_keys($jenisAktivitiList, 0);
$demand = array_fill_keys($jenisAktivitiList, 0);

// ✅ SQL: ambil data
$sql = "
  SELECT 
      a.aktiviti_jenis,
      COUNT(DISTINCT a.aktiviti_id) AS bilangan_aktiviti, 
      COUNT(ap.pelajar_emel) AS jumlah_penyertaan
  FROM Aktiviti a
  LEFT JOIN AktivitiPenyertaan ap ON a.aktiviti_id = ap.aktiviti_id
  WHERE 
      a.persatuan_id = ? 
      AND YEAR(a.tarikh_mula) = ? 
      AND (? = 0 OR MONTH(a.tarikh_mula) = ?) 
      AND (a.status IS NULL OR a.status != 'batal')
  GROUP BY a.aktiviti_jenis
";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("iiii", $persatuan_id, $tahun, $bulan, $bulan);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $jenis = $row['aktiviti_jenis'];
        if (isset($supply[$jenis])) {
            $supply[$jenis] = (int)$row['bilangan_aktiviti'];
            $demand[$jenis] = (int)$row['jumlah_penyertaan'];
        }
    }
    echo json_encode([
        'labels' => array_keys($supply),
        'supply' => array_values($supply),
        'demand' => array_values($demand)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
}

