<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pengurus') {
    header("Location: ../login.php");
    exit;
}

$pman_emel = $_SESSION['emel'];

$stmt = $conn->prepare("SELECT persatuan_id FROM Persatuan WHERE pman_emel = ?");
$stmt->bind_param("s", $pman_emel);
$stmt->execute();
$result = $stmt->get_result();
$persatuan = $result->fetch_assoc();
$persatuan_id = $persatuan['persatuan_id'];

$sql = "SELECT p.*, u.*
        FROM Permohonan p 
        JOIN Pelajar_UKM u ON p.pelajar_emel = u.pelajar_emel
        WHERE p.persatuan_id = ? AND p.jenis_permohonan = 'khas'
        ORDER BY 
            CASE 
                WHEN p.status = 'menunggu' THEN 1
                WHEN p.status = 'disahkan' THEN 2
                ELSE 3
            END,
            p.permohonan_tarikh DESC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $persatuan_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Senarai Permohonan Kes Khas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">
    <?php include '../includes/headerPeng.php'; ?>
  <div class="max-w-6xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-700">Senarai Permohonan Kes Khas</h2>

    <div class="overflow-x-auto">
      <table class="w-full table-auto text-sm text-left text-gray-700 border border-gray-300">
        <thead class="bg-blue-100 text-blue-800 font-semibold uppercase">
          <tr>
            <th class="px-4 py-3 border">Nama</th>
            <th class="px-4 py-3 border">Email</th>
            <th class="px-4 py-3 border">Tarikh</th>
            <th class="px-4 py-3 border">Status</th>
            <th class="px-4 py-3 border text-center">Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $results->fetch_assoc()): ?>
          <tr class="hover:bg-gray-50 transition border-b">
            <td class="px-4 py-3"><?= htmlspecialchars($row['pelajar_nama']) ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['pelajar_emel']) ?></td>
            <td class="px-4 py-3"><?= date('Y-m-d', strtotime($row['permohonan_tarikh'])) ?></td>
            <td class="px-4 py-3">
              <?php if ($row['status'] === 'disahkan'): ?>
                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">Disahkan</span>
              <?php elseif ($row['status'] === 'ditolak'): ?>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-medium">Ditolak</span>
              <?php else: ?>
                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-medium">Menunggu</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
    <button class="text-blue-600 hover:underline" 
        data-details='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>' 
        onclick="handleClick(this)">
        Lihat
    </button>
</td>

          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function showDetails(data) {
  let tindakanHTML = '';

  if (data.status === 'menunggu') {
    tindakanHTML = `
      <div class="mt-5 flex justify-end gap-3">
        <form method="POST" action="../Controllers/sahkahkeskhas.php">
          <input type="hidden" name="permohonan_id" value="${data.permohonan_id}">
          <input type="hidden" name="status" value="disahkan">
          <button type="submit" class="bg-green-500 hover:bg-green-600 px-5 py-2 rounded-lg text-white font-semibold shadow">Sahkan</button>
        </form>
        <button onclick="tolakPermohonan(${data.permohonan_id})" class="bg-red-500 hover:bg-red-600 px-5 py-2 rounded-lg text-white font-semibold shadow">Tolak</button>
      </div>
    `;
  } else {
    const statusBadge = data.status === 'disahkan'
  ? `<span class="inline-block bg-green-500 text-white px-4 py-2 rounded-md text-sm font-semibold shadow">Permohonan Telah Disahkan</span>`
  : `<span class="inline-block bg-red-500 text-white px-4 py-2 rounded-md text-sm font-semibold shadow">Permohonan Telah Ditolak</span>`;


    tindakanHTML = `<div class="mt-6 text-center">${statusBadge}</div>`;
  }

Swal.fire({
  title: 'Permohonan Kes Khas',
  html: `
    <div class="text-left leading-relaxed text-[15px] mt-4 space-y-1">
      <p><strong>Nama:</strong> ${data.pelajar_nama}</p>
      <p><strong>Email:</strong> ${data.pelajar_emel}</p>
      <p><strong>No Matrik:</strong> ${data.pelajar_matrik}</p>
      <p><strong>Jantina:</strong> ${data.jantina}</p>
      <p><strong>No Kad Pengenalan:</strong> ${data.pelajar_kadpengenalan}</p>
      <p><strong>Telefon:</strong> ${data.pelajar_telefon}</p>
      <p><strong>Alamat:</strong> ${data.pelajar_alamat}</p>
      <p><strong>Fakulti:</strong> ${data.pelajar_fakulti}</p>
      <p><strong>Program:</strong> ${data.pelajar_program}</p>
      <p><strong>Tahun:</strong> ${data.pelajar_tahun}</p>
      <p><strong>Kolej:</strong> ${data.pelajar_kolej}</p>
      <p><strong>Alasan Permohonan:</strong> ${data.alasan ?? '-'}</p>
      <p><strong>Dokumen:</strong> 
        <a href="../uploads/dokumen/${data.dokumen_sokongan}" target="_blank" class="text-blue-600 underline hover:text-blue-800">Lihat Fail</a>
      </p>
      ${tindakanHTML}
    </div>
  `,
  showConfirmButton: false,
  customClass: {
    popup: 'rounded-xl'
  }
});

}


    function tolakPermohonan(id) {
      Swal.fire({
        title: 'Tolak Permohonan',
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Nyatakan sebab penolakan...',
        inputAttributes: {
          'aria-label': 'Alasan penolakan'
        },
        showCancelButton: true,
        confirmButtonText: 'Hantar',
        cancelButtonText: 'Batal',
        preConfirm: (alasan) => {
          if (!alasan) {
            Swal.showValidationMessage('Sila isi alasan');
          }
          return alasan;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '../Controllers/sahkahkeskhas.php';

          const idField = document.createElement('input');
          idField.type = 'hidden';
          idField.name = 'permohonan_id';
          idField.value = id;

          const statusField = document.createElement('input');
          statusField.type = 'hidden';
          statusField.name = 'status';
          statusField.value = 'ditolak';

          const alasanField = document.createElement('input');
          alasanField.type = 'hidden';
          alasanField.name = 'alasan_tolak';
          alasanField.value = result.value;

          form.appendChild(idField);
          form.appendChild(statusField);
          form.appendChild(alasanField);
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function handleClick(btn) {
  const data = JSON.parse(btn.getAttribute('data-details'));
  showDetails(data);
}

  </script>
</body>
</html>
