<?php
session_start();
include '../includes/db.php';

$pelajarNama = 'Pelajar';
if (isset($_SESSION['emel']) && $_SESSION['peranan'] === 'pelajar') {
    $emel = $_SESSION['emel'];
    $stmt = $conn->prepare("SELECT pelajar_nama FROM Pelajar_UKM WHERE pelajar_emel = ?");
    $stmt->bind_param("s", $emel);
    $stmt->execute();
    $stmt->bind_result($pelajarNama);
    $stmt->fetch();
    $stmt->close();
}

// Ambil pengumuman dari persatuan yang pelajar telah sertai
$pengumumanStmt = $conn->prepare("
    SELECT pn.*, ps.persatuan_nama 
    FROM Pengumuman pn
    JOIN Persatuan ps ON pn.persatuan_id = ps.persatuan_id
    WHERE pn.persatuan_id IN (
        SELECT persatuan_id 
        FROM Permohonan 
        WHERE pelajar_emel = ? AND status = 'disahkan'
    )
    AND pn.tamat_tayang >= CURDATE()
    ORDER BY pn.tarikh_umum DESC
");

$pengumumanStmt->bind_param("s", $emel);
$pengumumanStmt->execute();
$senaraiPengumuman = $pengumumanStmt->get_result();



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>E-Pintar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../assets/js/calendarComponent.js"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-gray-900">
<?php include '../includes/headerPel.php'; ?>
<div class="max-w-6xl mx-auto  py-6">
  <h1 class="text-2xl font-bold text-blue-700 mb-4 flex items-center gap-2">
Selamat datang, <span class="uppercase"><?= htmlspecialchars($pelajarNama) ?></span>!
  </h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 px-6">
    <div class="lg:col-span-3" x-data="{
        openActivity: false,
        openPersatuan: false,
        selectedActivity: { title: '', tarikh: '', masa: '', tempat: '', anjuran: '' },
        selectedPersatuan: { title: '', pengerusi: '', ahli: '', benefit: '' }
    }">
        <section id="aktiviti" class="mb-4 px-6">
            <h2 class="text-xl font-bold mb-4 pt-2">Aktiviti Akan Datang</h2>
            <div class="flex overflow-x-auto scroll-container space-x-4 pb-2">
                <?php
                $now = date('Y-m-d H:i:s');
                $end = date('Y-m-d 23:59:59', strtotime('+2 days'));
                
                $sql = "SELECT a.*, p.persatuan_nama 
                        FROM Aktiviti a 
                        LEFT JOIN Persatuan p ON a.persatuan_id = p.persatuan_id 
                        WHERE a.tarikh_mula BETWEEN ? AND ? 
                        AND a.status = 'aktif'
                        ORDER BY a.tarikh_mula ASC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $now, $end);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($a = $result->fetch_assoc()):
                    $gambar = !empty($a['aktiviti_gambar']) ? '../uploads/' . $a['aktiviti_gambar'] : '../images/default.jpg';
                ?>
               <div class="flex-shrink-0 w-64 bg-blue-50 p-3 rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transition cursor-pointer"
                    @click="openActivity = true; selectedActivity = {
                    title: '<?= addslashes($a['aktiviti_nama']) ?>',
                    tarikh: '<?= addslashes(date('d M Y', strtotime($a['tarikh_mula'])) ." - " . date('d M Y', strtotime($a['tarikh_tamat'])))?>',
                    masa: '<?= addslashes(date("g:i A", strtotime($a["aktiviti_mula"])) . " - " . date("g:i A", strtotime($a["aktiviti_tamat"]))) ?>',
                    tempat: '<?= addslashes($a['aktiviti_tempat']) ?>',
                    anjuran: '<?= addslashes($a['persatuan_nama'] ?? '-') ?>'
                    }">
                    <img src="<?= $gambar ?>" alt="..." class="w-full h-64 object-cover rounded-lg">
                    <h3 class="mt-2 text-md font-semibold text-gray-800"><?= htmlspecialchars($a['aktiviti_nama']) ?></h3>
                    <div class="text-sm text-gray-600 mt-1 flex items-center gap-1">
                        <i class="fa fa-calendar-alt text-[12px]"></i>
                        <?= date('d M Y', strtotime($a['tarikh_mula'])) . ' - ' . date('d M Y', strtotime($a['tarikh_tamat'])) ?>
                    </div>
                    <div class="text-sm text-gray-600 flex items-center gap-1">
                        <i class="fa fa-clock text-[12px]"></i>
                        <?= date('g:i A', strtotime($a['aktiviti_mula'])) . ' - ' . date('g:i A', strtotime($a['aktiviti_tamat'])) ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section id="persatuan" class="mb-6 px-6">
            <h2 class="text-xl font-bold mb-4">Persatuan Aktif</h2>
            <div class="flex overflow-x-auto space-x-4 pb-2">
                <?php
                $stmt = $conn->prepare("SELECT DISTINCT ps.persatuan_nama, ps.persatuan_logo, ps.pengerusi_nama, ps.jumlah_ahli, ps.benefit 
                                        FROM Persatuan ps
                                        JOIN Aktiviti a ON a.persatuan_id = ps.persatuan_id
                                        WHERE a.tarikh_mula BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY
                                        AND a.status = 'aktif'");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()):
                    $logo = !empty($row['persatuan_logo']) ? '../uploads/' . $row['persatuan_logo'] : '../images/default-logo.png';
                ?>
                <div class="flex-shrink-0 w-64 bg-blue-50 p-3 rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transition cursor-pointer"
                    @click="openPersatuan = true; selectedPersatuan = {
                    title: '<?= addslashes($row['persatuan_nama']) ?>',
                    pengerusi: '<?= addslashes($row['pengerusi_nama']) ?>',
                    ahli: '<?= $row['jumlah_ahli'] ?>',
                    benefit: `<?= nl2br(addslashes($row['benefit'])) ?>`
                    }">
                    <img src="<?= $logo ?>" alt="<?= htmlspecialchars($row['persatuan_nama']) ?>" class="w-full h-64 object-contain rounded">
                    <h3 class="mt-2 font-semibold text-center text-sm text-gray-800"><?= htmlspecialchars($row['persatuan_nama']) ?></h3>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <!-- Modal Aktiviti -->
        <template x-if="openActivity">
            <div class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50">
                <div class="bg-white p-6 rounded-lg w-[90%] md:w-[400px] text-left">
                    <h2 class="text-xl font-bold mb-3 text-center" x-text="selectedActivity.title"></h2>
                    <p class="text-sm"><b>Tarikh:</b> <span x-text="selectedActivity.tarikh"></span></p>
                    <p class="text-sm"><b>Masa:</b> <span x-text="selectedActivity.masa"></span></p>
                    <p class="text-sm"><b>Tempat:</b> <span x-text="selectedActivity.tempat"></span></p>
                    <p class="text-sm"><b>Anjuran:</b> <span x-text="selectedActivity.anjuran"></span></p>
                    <div class="mt-4 text-center">
                        <button class="px-4 py-2 bg-blue-500 text-white rounded" @click="openActivity = false">Tutup</button>
                    </div>
                </div>
            </div>
        </template>
        <!-- Modal Persatuan -->
        <template x-if="openPersatuan">
            <div class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50">
                <div class="bg-white p-6 rounded-lg w-[90%] md:w-[400px] text-left">
                    <h2 class="text-xl font-bold mb-3 text-center" x-text="selectedPersatuan.title"></h2>
                    <p class="text-sm"><b>Pengerusi:</b> <span x-text="selectedPersatuan.pengerusi"></span></p>
                    <p class="text-sm"><b>Jumlah Ahli:</b> <span x-text="selectedPersatuan.ahli"></span></p>
                    <p class="text-sm mt-3"><b>Kelebihan Menyertai:</b></p>
                    <p class="text-sm" x-html="selectedPersatuan.benefit"></p>
                    <div class="mt-4 text-center">
                        <button class="px-4 py-2 bg-blue-500 text-white rounded" @click="openPersatuan = false">Tutup</button>
                    </div>
                </div>
            </div>
        </template>
    </div>


    

     <div class="flex flex-col space-y-6">
        <?php include '../Calendar/calendarPelajar.php'; ?>

        

        <section class="bg-white rounded-lg p-4 shadow">
    <h2 class="text-xl font-bold mb-4">📢 Pengumuman Persatuan</h2>

    <?php if ($senaraiPengumuman->num_rows > 0): ?>
        <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            <?php while ($row = $senaraiPengumuman->fetch_assoc()): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow">
                    <h3 class="font-semibold text-yellow-800"><?= htmlspecialchars($row['tajuk']) ?> <span class="text-sm text-gray-500">[<?= htmlspecialchars($row['persatuan_nama']) ?>]</span></h3>
                   
                    <p class="text-xs text-gray-500 mt-2"><?= date('d M Y', strtotime($row['tarikh_umum'])) ?></p>
                    <div class="mt-2 text-right">
                        <a href="../Penguguman/pengugumanDetails.php?id=<?= $row['id'] ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1 rounded">
                            Lihat Maklumat
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-sm text-gray-500">Tiada pengumuman buat masa ini.</p>
    <?php endif; ?>
</section>
    </div>
</div>

</div>

<footer class="bg-white text-center py-4 shadow-inner mt-4">
    <p class="text-sm text-gray-500">&copy; 2025 E-PINTAR UKM. Semua Hak Terpelihara.</p>
</footer>
<?php include '../chatbot/chatbot.php'; ?>
</body>
</html>
