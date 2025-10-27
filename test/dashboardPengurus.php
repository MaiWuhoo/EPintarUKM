<?php
$persatuan_id = 1; // hardcode untuk test
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Test</title>
  <script>
    window.persatuanId = <?= $persatuan_id ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="padding: 2rem; font-family: sans-serif; background: #f9f9f9;">

  <h2>📊 Dashboard Test</h2>

  <label>Bulan:</label>
  <select id="perbandinganBulan">
    <option value="all">Semua Bulan</option>
    <?php for ($i = 1; $i <= 12; $i++): ?>
      <option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
    <?php endfor; ?>
  </select>

  <label>Tahun:</label>
  <select id="perbandinganTahun">
    <option value="2024">2024</option>
    <option value="2025" selected>2025</option>
  </select>

  <canvas id="ahliChart" height="120"></canvas>

  <script src="chart-handler.js"></script>
</body>
</html>
