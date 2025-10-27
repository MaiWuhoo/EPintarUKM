<?php
header('Content-Type: application/json');

$bulan = $_GET['bulan'] ?? 'all';
$ahli = [];

if ($bulan === 'all') {
    $ahli = [5, 7, 4, 6, 9, 2, 3, 6, 1, 2, 4, 5]; // 12 bulan
} else {
    $ahli = array_fill(0, 31, rand(0, 10)); // 31 hari
}

echo json_encode([
    "ahli" => $ahli,
    "aktiviti" => $ahli,
    "penyertaan" => $ahli
]);
