<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test Chart Y-Axis</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    canvas { max-width: 100%; height: 400px !important; }
  </style>
</head>
<body>

<h2>TEST GRAF - PAKSI Y KEKAL</h2>
<canvas id="ahliChart"></canvas>

<script>
  const bulanLabels = ["Jan", "Feb", "Mac", "Apr", "Mei", "Jun", "Jul", "Ogos", "Sep", "Okt", "Nov", "Dis"];
  const dataLelaki = [0, 1, 0, 2, 5, 0, 0, 0, 0, 0, 0, 0];
  const dataPerempuan = [0, 0, 1, 3, 2, 0, 0, 0, 0, 0, 0, 0];

  const ctx = document.getElementById('ahliChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: bulanLabels,
      datasets: [
        { label: 'Lelaki', data: dataLelaki, backgroundColor: '#3B82F6' },
        { label: 'Perempuan', data: dataPerempuan, backgroundColor: '#EC4899' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          type: 'linear',
          beginAtZero: true,
          min: 0,
          max: 10,
          ticks: {
            stepSize: 1,
            callback: function (value) {
              return Number.isInteger(value) ? value : null;
            }
          }
        }
      }
    }
  });
</script>

</body>
</html>
