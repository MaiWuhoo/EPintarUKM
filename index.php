<?php
include 'includes/db.php'; // pastikan path betul

// Kira pelajar
$pelajarResult = $conn->query("SELECT COUNT(*) AS total FROM Pelajar_UKM");
$pelajarCount = $pelajarResult->fetch_assoc()['total'];

// Kira persatuan
$persatuanResult = $conn->query("SELECT COUNT(*) AS total FROM Persatuan");
$persatuanCount = $persatuanResult->fetch_assoc()['total'];

// Kira aktiviti
$aktivitiResult = $conn->query("SELECT COUNT(*) AS total FROM Aktiviti");
$aktivitiCount = $aktivitiResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-PINTAR UKM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
<?php include 'includes/headerLP.php'; ?>

<!-- Hero Section with Overlay & Hover -->
<section class="relative h-screen bg-cover bg-center group" style="background-image: url('images/UKM.png');">
    <div class="absolute inset-0 bg-black bg-opacity-60 group-hover:bg-opacity-70 transition duration-300 flex items-center justify-center">
        <div class="text-center text-white px-6">
            <h1 class="text-5xl font-extrabold mb-4" data-aos="fade-down">Sistem Pengurusan Persatuan Mahasiswa Anak Negeri</h1>
            <p class="text-lg mb-6" data-aos="fade-up">Platform rasmi untuk mengurus aktiviti, keahlian dan jaringan komuniti pelajar di UKM.</p>
        </div>
    </div>
</section>

<!-- Kelebihan Sistem -->
<section class="py-16 bg-white text-center">
    <h2 class="text-3xl font-bold mb-10" data-aos="fade-up">Kenapa Guna Sistem Ini?</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="100">
            <div class="text-4xl mb-4">⏱</div>
            <h3 class="text-xl font-semibold mb-2">Jimat Masa</h3>
            <p class="text-sm">Penyertaan aktiviti dan permohonan ahli boleh dibuat secara online.</p>
        </div>
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="200">
            <div class="text-4xl mb-4">🤝</div>
            <h3 class="text-xl font-semibold mb-2">Mudah Berhubung</h3>
            <p class="text-sm">Hubungi persatuan dengan mudah dan pantas melalui platform ini.</p>
        </div>
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="300">
            <div class="text-4xl mb-4">📱</div>
            <h3 class="text-xl font-semibold mb-2">Akses Di Mana-mana</h3>
            <p class="text-sm">Boleh diakses pada bila-bila masa melalui telefon atau komputer.</p>
        </div>
    </div>
</section>

<!-- Our Services Section -->
<section class="py-16 bg-white text-center">
    <h2 class="text-3xl font-bold mb-10" data-aos="fade-up">Modul Sistem</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto px-4">
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="100">
            <div class="text-4xl mb-4">📝</div>
            <h3 class="text-xl font-semibold mb-2">Pendaftaran Aktiviti</h3>
            <p class="text-sm">Pelajar boleh melihat, menyertai dan membatalkan aktiviti dengan mudah.</p>
        </div>
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="200">
            <div class="text-4xl mb-4">👥</div>
            <h3 class="text-xl font-semibold mb-2">Pengurusan Keahlian</h3>
            <p class="text-sm">Permohonan keahlian pelajar dalam persatuan anak negeri dikendalikan secara digital.</p>
        </div>
        <div class="bg-gray-50 p-6 rounded shadow" data-aos="fade-up" data-aos-delay="300">
            <div class="text-4xl mb-4">🔔</div>
            <h3 class="text-xl font-semibold mb-2">Notifikasi & Pengumuman</h3>
            <p class="text-sm">Sistem akan menghantar notifikasi apabila terdapat aktiviti atau perubahan penting.</p>
        </div>
    </div>
</section>

<!-- Statistik Section -->
<section class="py-16 bg-gray-50 text-center">
    <h2 class="text-3xl font-bold mb-10" data-aos="fade-up">Statistik Sistem</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-5xl mx-auto">
        <div class="shadow p-6 rounded bg-white" data-aos="zoom-in">
            <p class="text-5xl font-bold text-blue-600"><?= $pelajarCount ?></p>
            <p class="mt-2 text-lg">Pelajar Berdaftar</p>
        </div>
        <div class="shadow p-6 rounded bg-white" data-aos="zoom-in" data-aos-delay="100">
            <p class="text-5xl font-bold text-green-600"><?= $persatuanCount ?></p>
            <p class="mt-2 text-lg">Persatuan Aktif</p>
        </div>
        <div class="shadow p-6 rounded bg-white" data-aos="zoom-in" data-aos-delay="200">
            <p class="text-5xl font-bold text-red-500"><?= $aktivitiCount ?></p>
            <p class="mt-2 text-lg">Aktiviti Dianjurkan</p>
        </div>
    </div>
</section>


<!-- Galeri Gambar -->
<section class="py-16 bg-white text-center">
    <h2 class="text-3xl font-bold mb-10" data-aos="fade-up">Galeri Aktiviti</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-6xl mx-auto px-4">
        <img src="images/galeri1.jpg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in">
        <img src="images/gal2.jpg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in" data-aos-delay="100">
        <img src="images/gal3.jpg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in" data-aos-delay="200">
        <img src="images/gal4.jpg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in" data-aos-delay="300">
        <img src="images/galeri5.jpeg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in" data-aos-delay="400">
        <img src="images/galeri6.jpg" class="w-full h-60 object-cover rounded shadow" data-aos="zoom-in" data-aos-delay="500">
    </div>
</section>


<!-- Testimoni -->
<section class="py-16 bg-gray-100 text-center">
    <h2 class="text-3xl font-bold mb-10" data-aos="fade-up">Apa Kata Pelajar?</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto px-4">
        <div class="bg-white p-6 rounded shadow" data-aos="fade-up">
            <p class="italic">"Sistem ni sangat mudah digunakan. Saya tak perlu isi borang manual dah!"</p>
            <p class="mt-4 font-semibold">– Aina, FST</p>
        </div>
        <div class="bg-white p-6 rounded shadow" data-aos="fade-up" data-aos-delay="100">
            <p class="italic">"Saya suka feature notifikasi, senang tahu bila ada aktiviti baru dari persatuan saya."</p>
            <p class="mt-4 font-semibold">– Amir, FEP</p>
        </div>
        <div class="bg-white p-6 rounded shadow" data-aos="fade-up" data-aos-delay="200">
            <p class="italic">"Sistem ni nampak profesional, senang nak browse dan cepat loading."</p>
            <p class="mt-4 font-semibold">– Harith, FKAB</p>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-white text-center py-4 shadow-inner">
    <p class="text-sm text-gray-500">&copy; 2025 E-PINTAR UKM. Semua Hak Terpelihara.</p>
</footer>

<script>
  AOS.init();
</script>

</body>
</html>

