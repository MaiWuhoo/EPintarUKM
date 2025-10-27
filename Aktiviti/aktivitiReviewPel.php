<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$emel = $_SESSION['emel'];
$aktiviti_id = $_GET['id'] ?? null;

if (!$aktiviti_id) {
    echo "<p>Ralat: ID aktiviti tidak sah.</p>";
    exit;
}

// Semak jika aktiviti sudah lepas
$stmt = $conn->prepare("SELECT * FROM Aktiviti WHERE aktiviti_id = ? AND tarikh_tamat < CURDATE()");

$stmt->bind_param("i", $aktiviti_id);
$stmt->execute();
$aktiviti = $stmt->get_result()->fetch_assoc();

if (!$aktiviti) {
    echo "<p>Aktiviti belum berakhir atau tidak dijumpai.</p>";
    exit;
}

// Soalan ulasan
$questions = $conn->query("SELECT * FROM ReviewQuestion")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borang Maklum Balas</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include '../includes/headerPel.php'; ?>

<div class="max-w-3xl mx-auto p-6 bg-white mt-10 rounded shadow space-y-6">
    <h2 class="text-xl font-bold text-blue-600">Maklum Balas untuk: <?= htmlspecialchars($aktiviti['aktiviti_nama']) ?></h2>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
            Maklum balas anda telah dihantar. Terima kasih!
        </div>
    <?php endif; ?>

    <form method="POST" action="../Controllers/hantarFeedback.php">
        <input type="hidden" name="aktiviti_id" value="<?= $aktiviti_id ?>">

        <?php foreach ($questions as $q): ?>
            <div class="bg-gray-50 p-4 rounded shadow">
                <label class="block font-medium text-gray-700 mb-3">
                    <?= htmlspecialchars($q['question_text']) ?>
                </label>
                <div class="flex gap-6">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="flex items-center space-x-1">
                            <input type="radio" name="rating[<?= $q['question_id'] ?>]" value="<?= $i ?>" required>
                            <span><?= $i ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="bg-gray-50 p-4 rounded shadow">
            <label class="block font-medium text-gray-700 mb-2">Ulasan / Komen Tambahan</label>
            <textarea name="review_text" rows="4" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Tulis ulasan anda di sini..."></textarea>
        </div>

        <div class="flex justify-between mt-6">
            <button type="reset" class="bg-gray-300 hover:bg-gray-400 text-black px-6 py-2 rounded">
                Reset
            </button>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                Hantar Maklum Balas
            </button>
        </div>
    </form>
</div>
</body>
</html>
