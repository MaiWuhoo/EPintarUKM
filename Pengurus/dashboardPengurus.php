
<?php
session_start();
include '../includes/db.php';

  if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
      header("Location: ../login.php");
      exit;
  }

  $emel = $_SESSION['emel'];
  $stmt = $conn->prepare("SELECT ps.persatuan_id, ps.persatuan_nama, ps.persatuan_logo FROM Persatuan ps WHERE ps.pman_emel = ?");
  $stmt->bind_param("s", $emel);
  $stmt->execute();
  $persatuan = $stmt->get_result()->fetch_assoc();

  $persatuan_id = $persatuan['persatuan_id'] ?? 0;
  $namaPersatuan = $persatuan['persatuan_nama'] ?? 'Persatuan Saya';
  $logo = !empty($persatuan['persatuan_logo']) ? '../uploads/' . $persatuan['persatuan_logo'] : '../images/default-logo.png';

  function getCount($conn, $sql, $param) {
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $param);
      $stmt->execute();
      return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
  }

  $totalAktiviti = getCount($conn, "SELECT COUNT(*) AS total FROM Aktiviti WHERE persatuan_id = ?", $persatuan_id);
  $totalBatal = getCount($conn, "SELECT COUNT(*) AS total FROM Aktiviti WHERE persatuan_id = ? AND status = 'batal'", $persatuan_id);
  $totalAhli = getCount($conn, "SELECT COUNT(*) AS total FROM Permohonan WHERE persatuan_id = ? AND status = 'disahkan'", $persatuan_id);
  $permohonanKhas = getCount($conn, "SELECT COUNT(*) AS total FROM Permohonan WHERE jenis_permohonan = 'khas' AND status = 'menunggu' AND persatuan_id = ?", $persatuan_id);
  $totalBerjaya = $totalAktiviti - $totalBatal;

  $stmt = $conn->prepare("SELECT rr.rating FROM reviewresponse rr 
      JOIN aktivitireview ar ON rr.review_id = ar.review_id 
      JOIN Aktiviti a ON ar.aktiviti_id = a.aktiviti_id 
      WHERE a.persatuan_id = ?");
  $stmt->bind_param("i", $persatuan_id);
  $stmt->execute();
  $ratings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

  $totalRating = array_sum(array_column($ratings, 'rating'));
  $totalCount = count($ratings);
  $avgRating = $totalCount > 0 ? ($totalRating / $totalCount) : 0;

  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM aktivitipenyertaan ap 
      JOIN Aktiviti a ON ap.aktiviti_id = a.aktiviti_id 
      WHERE a.persatuan_id = ?");
  $stmt->bind_param("i", $persatuan_id);
  $stmt->execute();
  $jumlahPenyertaan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

  $maxPenyertaan = $totalAktiviti * ($totalAhli > 0 ? $totalAhli : 1);
  $kadarPenyertaan = $maxPenyertaan > 0 ? ($jumlahPenyertaan / $maxPenyertaan) * 100 : 0;

  $targetAhli = 50;
  $targetAktiviti = 20;

  $faktor_ahli = min($totalAhli / $targetAhli, 1.0) * 0.25;
  $faktor_aktiviti = min($totalAktiviti / $targetAktiviti, 1.0) * 0.25;
  $faktor_penyertaan = ($kadarPenyertaan / 100) * 0.20;
  $faktor_feedback = ($avgRating / 5) * 0.25;
  $faktor_penalti = $totalBatal * 0.01;

  $prestasi = ($faktor_ahli + $faktor_aktiviti + $faktor_penyertaan + $faktor_feedback - $faktor_penalti) * 100;
  $prestasi = round(max(0, min(100, $prestasi)));

  $peratus_ahli = round($faktor_ahli / 0.25 * 100);
  $peratus_aktiviti = round($faktor_aktiviti / 0.25 * 100);
$peratus_penyertaan = min(round($faktor_penyertaan / 0.20 * 100), 100);
  $peratus_feedback = round($faktor_feedback / 0.25 * 100);
$peratus_penalti = ($prestasi > 0) ? round($faktor_penalti / ($prestasi / 100) * 100) : 0;


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Pengurus</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
  <script src="../assets/js/chart-handler.js"></script>
  <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {font-family: 'Fira Sans', sans-serif;}
  </style>
  <style>
    .highcharts-title {
      font-size: 12px !important;
      font-family: 'Fira Sans', sans-serif !important;
      
      fill: #1f2937 !important; /* equivalent to Tailwind gray-800 */
    }
  </style>

  <script>
    window.persatuanId = <?= $persatuan_id ?>;
  </script>

  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const prestasi = <?= $prestasi ?>;

      Highcharts.chart('prestasiDonut', {
        chart: {
          type: 'pie',
          backgroundColor: 'transparent'
        },
        title: {
    text: '<span class="prestasi-title">' + prestasi + '%</span>',
    useHTML: true,
    verticalAlign: 'middle',
    align: 'center',
    y: 10},
        tooltip: { enabled: false },
        plotOptions: {
          pie: {
            innerSize: '80%',
            dataLabels: { enabled: false },
            borderWidth: 0
          }
        },
        series: [{
          name: 'Prestasi',
          data: [
            { name: 'Isi', y: prestasi, color: '#8b5cf6' },
            { name: 'Kosong', y: 100 - prestasi, color: '#e5e7eb' }
          ]
        }],
        credits: { enabled: false }
      });
    });
  </script>
  <style>
    .prestasi-title {
      font-size: 24px !important;
      font-weight: bold;
      color: #8b5cf6 !important;
      font-family: 'Fira Sans', sans-serif;
    }
  </style>

</head>

<body class="bg-gray-50 text-gray-800">
      <?php include '../includes/headerPeng.php'; ?>

    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
          <div>
            <p class="text-gray-500 text-sm">Selamat datang </p>
            <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($namaPersatuan) ?></h1>
          </div>
          <img src="<?= $logo ?>" class="w-20 h-20 object-contain rounded-full border border-gray-300" alt="Logo">
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
          <a href="../Pengurus/senaraipermohonankhas.php" class="bg-white p-5 rounded shadow hover:bg-yellow-50">
            <h2 class="text-xs text-gray-500">Permohonan Khas</h2>
            <p class="text-2xl font-bold text-yellow-600 mt-1"><?= $permohonanKhas ?></p>
          </a>
          <div class="bg-white p-5 rounded shadow">
            <h2 class="text-xs text-gray-500">Jumlah Aktiviti</h2>
            <p class="text-2xl font-bold text-gray-700 mt-1"><?= $totalAktiviti ?></p>
          </div>
          <div class="bg-white p-5 rounded shadow">
            <h2 class="text-xs text-gray-500">Aktiviti Berjaya</h2>
            <p class="text-2xl font-bold text-emerald-600 mt-1"><?= $totalBerjaya ?></p>
          </div>
          <div class="bg-white p-5 rounded shadow">
            <h2 class="text-xs text-gray-500">Aktiviti Dibatalkan</h2>
            <p class="text-2xl font-bold text-red-500  mt-1"><?= $totalBatal ?></p>
          </div>
          <div class="bg-white p-5 rounded shadow">
            <h2 class="text-xs text-gray-500">Jumlah Ahli</h2>
            <p class="text-2xl font-bold text-indigo-600 mt-1"><?= $totalAhli ?></p>
          </div>
        </div>

        



        <div class="bg-white p-4 rounded shadow mb-8">
              <div class="flex justify-between items-center mb-4">
                  <h2 class="text-lg font-semibold text-black">Statistik Tahunan</h2>
                  <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 gap-2 mb-4">
                    
                    <div class="flex items-center gap-2">
                      <label for="analisisTahun" class="text-sm text-gray-700">Tahun:</label>
                      <select id="analisisTahun" class="border px-2 py-1 rounded text-sm">
                        <?php
                        $year = date('Y');
                        for ($i = $year; $i <= $year + 1; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                      </select>
                    </div>
                    <div class="flex items-center gap-2">
                      <label for="analisisBulan" class="text-sm text-gray-700">Bulan:</label>
                      <select id="analisisBulan" class="border px-2 py-1 rounded text-sm">
                        <option value="0">Semua Bulan</option>
                        <?php
                        $bulan = ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
                        foreach ($bulan as $i => $b) {
                            echo "<option value='".($i+1)."'>$b</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>
              </div>


              <!--Prestasi -->

              <div class="flex flex-col md:flex-row md:items-start gap-6">
                <!-- Kiri: Donut Prestasi -->
                <div class="text-center md:w-1/3 w-full">
                  <h2 class="text-sm text-gray-500 mb-2">Prestasi Keseluruhan</h2>
                  <div id="prestasiDonut" style="height: 200px; width: 200px; margin: auto;"></div>
                </div>
                <!-- Kanan: Bar Prestasi -->
                <div class="flex-1 space-y-4 w-full">
                  <?php
                  $indikator = [
                    ['id' => 'Ahli', 'label' => 'Ahli', 'icon' => 'fa-user-check'],
                    ['id' => 'Aktiviti', 'label' => 'Aktiviti', 'icon' => 'fa-calendar-alt'],
                    ['id' => 'Penyertaan', 'label' => 'Penyertaan', 'icon' => 'fa-users'],
                    ['id' => 'MaklumBalas', 'label' => 'Maklum Balas', 'icon' => 'fa-star']
                  ];

                  foreach ($indikator as $item):
                  ?>
                    <div>
                      <div class="flex justify-between text-sm mb-1">
                        <span class="text-black font-medium">
                          <i class="fas <?= $item['icon'] ?> mr-1"></i><?= $item['label'] ?>
                        </span>
                        <span id="peratus<?= $item['id'] ?>" class="text-gray-700 font-semibold">0%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="bar<?= $item['id'] ?>" class="bg-purple-300 h-3 rounded-full transition-all duration-700 ease-in-out" style="width: 0%;"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>

                  <!-- Penalti -->
                  <div>
                    <div class="flex justify-between text-sm mb-1">
                      <span class="text-red-600 font-medium">
                        <i class="fas fa-times-circle mr-1"></i>Penalti
                      </span>
                      <span id="peratusPenalti" class="text-gray-700 font-semibold">0 batal</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                      <div id="barPenalti" class="bg-red-300 h-3 rounded-full transition-all duration-700 ease-in-out" style="width: 0%;"></div>
                    </div>
                  </div>
                </div>

              </div>



              <div class="grid md:grid-cols-2 gap-6 mb-8 mt-8">
                <div class="bg-white p-6 rounded shadow">
                  <h3 class=" text-black mb-4">📊 Penyertaan Ahli Persatuan</h3>
                  <div id="ahliChart" class="h-[400px]"></div>
                </div>
                <div class="bg-white p-6 rounded shadow">
                  <h3 class="  text-black mb-4">📈 Bilangan Aktiviti Berjaya</h3>
                  <div id="aktivitiPenyertaanChart" class="h-[400px]"></div>
              </div>
      
              <div class="md:col-span-2 bg-white p-6 rounded shadow overflow-x-auto">
                <h3 class=" text-black mb-4">📈 Supply vs Demand Aktiviti</h3>
                <div id="supplyDemandChart" class="h-[500px]"></div>
              </div>
        </div>
      </div>
    </div>

      <?php include '../includes/fab.php'; ?>

</body>
</html>
