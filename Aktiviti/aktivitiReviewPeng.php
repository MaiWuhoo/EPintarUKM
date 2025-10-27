<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$aktiviti_id = $_GET['id'] ?? null;
if (!$aktiviti_id) {
    echo "ID aktiviti tidak sah.";
    exit;
}

// Get aktiviti info
$stmt = $conn->prepare("SELECT aktiviti_nama FROM Aktiviti WHERE aktiviti_id = ?");
$stmt->bind_param("i", $aktiviti_id);
$stmt->execute();
$aktiviti = $stmt->get_result()->fetch_assoc();

// Fetch all questions
$questions = $conn->query("SELECT * FROM ReviewQuestion")->fetch_all(MYSQLI_ASSOC);

// Prepare data
$data = [];
foreach ($questions as $q) {
    $qid = $q['question_id'];
    $label = $q['question_text'];

    // Count each rating scale
    $scaleData = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    $stmt = $conn->prepare("SELECT rating FROM ReviewResponse WHERE question_id = ? AND review_id IN (SELECT review_id FROM AktivitiReview WHERE aktiviti_id = ?)");
    $stmt->bind_param("ii", $qid, $aktiviti_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $scaleData[$row['rating']]++;
    }

    $percentages = array_map(fn($v) => $total ? round(($v / $total) * 100, 1) : 0, $scaleData);

    $data[] = [
        'label' => $label,
        'qid' => $qid,
        'percentages' => $percentages
    ];
}

// Fetch feedback (written only)
$stmt = $conn->prepare("SELECT review_text FROM AktivitiReview WHERE aktiviti_id = ? AND review_text IS NOT NULL AND review_text != ''");
$stmt->bind_param("i", $aktiviti_id);
$stmt->execute();
$feedbacks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hasil Ulasan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPeng.php'; ?>
<div class="max-w-6xl mx-auto py-10 px-4 space-y-10">
    <h1 class="text-2xl font-bold text-blue-700">Ulasan untuk: <?= htmlspecialchars($aktiviti['aktiviti_nama']) ?></h1>

    <?php foreach ($data as $index => $q): ?>
        <!-- Inside your loop where the pie chart is rendered -->
<div class="bg-white p-6 rounded shadow mb-6">
    <h3 class="text-md font-semibold text-gray-700 mb-4">
        <?= htmlspecialchars($q['label']) ?>
    </h3>
    <div class="flex justify-center">
        <canvas id="chart<?= $index ?>" class="w-[300px] h-[300px]"></canvas>
    </div>
</div>

<script>
    const ctx<?= $index ?> = document.getElementById('chart<?= $index ?>').getContext('2d');
    new Chart(ctx<?= $index ?>, {
        type: 'pie',
        data: {
            labels: ['1', '2', '3', '4', '5'],
            datasets: [{
                label: 'Rating (%)',
                data: <?= json_encode(array_values($q['percentages'])) ?>,
                backgroundColor: ['#f87171','#fbbf24','#34d399','#60a5fa','#c084fc']
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false
        }
    });
</script>

    <?php endforeach; ?>

    <?php if (count($feedbacks) > 0): ?>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="font-semibold text-gray-800 mb-4">Komen & Ulasan Pelajar</h2>
            <ul class="space-y-2 list-disc list-inside text-sm text-gray-700">
                <?php foreach ($feedbacks as $f): ?>
                    <li>"<?= htmlspecialchars($f['review_text']) ?>"</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
