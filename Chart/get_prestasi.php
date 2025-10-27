<?php
include '../includes/db.php';
header('Content-Type: application/json');

$persatuan_id = $_GET['persatuan_id'] ?? 0;
$tahun = $_GET['tahun'] ?? date('Y');
$bulan = $_GET['bulan'] ?? 0;

$persatuan_id = intval($persatuan_id);
$tahun = intval($tahun);
$bulan = intval($bulan);

// Target
$targetAhli = 50;
$targetAktiviti = 20;

// SQL helper
$bulanFilter = $bulan > 0 ? "AND MONTH(permohonan_tarikh) = $bulan" : "";
$bulanAktiviti = $bulan > 0 ? "AND MONTH(tarikh_mula) = $bulan" : "";
$bulanReview = $bulan > 0 ? "AND MONTH(a.tarikh_mula) = $bulan" : "";

// Jumlah Ahli Disahkan
$stmt = $conn->prepare("SELECT COUNT(*) AS total 
    FROM Permohonan 
    WHERE persatuan_id = ? AND status = 'disahkan' 
    AND YEAR(permohonan_tarikh) = ? $bulanFilter");
$stmt->bind_param("ii", $persatuan_id, $tahun);
$stmt->execute();
$totalAhli = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Jumlah Aktiviti
$stmt = $conn->prepare("SELECT COUNT(*) AS total 
    FROM Aktiviti 
    WHERE persatuan_id = ? AND YEAR(tarikh_mula) = ? $bulanAktiviti");
$stmt->bind_param("ii", $persatuan_id, $tahun);
$stmt->execute();
$totalAktiviti = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Jumlah Aktiviti Dibatalkan
$stmt = $conn->prepare("SELECT COUNT(*) AS total 
    FROM Aktiviti 
    WHERE persatuan_id = ? AND status = 'batal' 
    AND YEAR(tarikh_mula) = ? $bulanAktiviti");
$stmt->bind_param("ii", $persatuan_id, $tahun);
$stmt->execute();
$totalBatal = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Jumlah Penyertaan
$stmt = $conn->prepare("SELECT COUNT(*) AS total 
    FROM AktivitiPenyertaan ap 
    JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
    WHERE a.persatuan_id = ? AND YEAR(a.tarikh_mula) = ? $bulanAktiviti");
$stmt->bind_param("ii", $persatuan_id, $tahun);
$stmt->execute();
$jumlahPenyertaan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Kiraan Kadar Penyertaan
$maxPenyertaan = $totalAktiviti * ($totalAhli > 0 ? $totalAhli : 1);
$kadarPenyertaan = $maxPenyertaan > 0 ? ($jumlahPenyertaan / $maxPenyertaan) * 100 : 0;

// Maklum Balas / Review
$stmt = $conn->prepare("SELECT rr.rating 
    FROM reviewresponse rr 
    JOIN aktivitireview ar ON rr.review_id = ar.review_id 
    JOIN Aktiviti a ON ar.aktiviti_id = a.aktiviti_id 
    WHERE a.persatuan_id = ? AND YEAR(a.tarikh_mula) = ? $bulanReview");
$stmt->bind_param("ii", $persatuan_id, $tahun);
$stmt->execute();
$ratings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalRating = array_sum(array_column($ratings, 'rating'));
$totalCount = count($ratings);
$avgRating = $totalCount > 0 ? ($totalRating / $totalCount) : 0;

// ---------------------
// Kiraan Faktor
// ---------------------
$faktor_ahli = min($totalAhli / $targetAhli, 1.0) * 0.25;
$faktor_aktiviti = min($totalAktiviti / $targetAktiviti, 1.0) * 0.25;
$faktor_penyertaan = ($kadarPenyertaan / 100) * 0.20;
$faktor_feedback = ($avgRating / 5) * 0.25;
$faktor_penalti = $totalBatal * 0.01;

// ---------------------
// Kiraan Prestasi Keseluruhan
// ---------------------
$prestasi = ($faktor_ahli + $faktor_aktiviti + $faktor_penyertaan + $faktor_feedback - $faktor_penalti) * 100;
$prestasi = round(max(0, min(100, $prestasi)));

// Tukar setiap faktor kepada peratus mengikut berat masing-masing
$peratus_ahli = round($faktor_ahli / 0.25 * 100);
$peratus_aktiviti = round($faktor_aktiviti / 0.25 * 100);
$peratus_penyertaan = min(round($faktor_penyertaan / 0.20 * 100), 100);
$peratus_feedback = round($faktor_feedback / 0.25 * 100);
$peratus_penalti = $prestasi > 0 
    ? round($faktor_penalti / ($prestasi / 100) * 100) 
    : 0;


// ---------------------
// Return JSON
// ---------------------
echo json_encode([
  'prestasi' => $prestasi,
  'ahli' => $peratus_ahli,
  'aktiviti' => $peratus_aktiviti,
  'penyertaan' => $peratus_penyertaan,
  'feedback' => $peratus_feedback,
  'penalti' => $totalBatal
]);
