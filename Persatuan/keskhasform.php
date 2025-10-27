<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['emel']) || $_SESSION['peranan'] !== 'pelajar') {
    header("Location: ../login.php");
    exit;
}

$pelajar_emel = $_SESSION['emel'];
$persatuan_id = $_GET['persatuan_id'] ?? null;

if (!$persatuan_id) {
    echo "<script>alert('ID persatuan tidak sah.'); window.history.back();</script>";
    exit;
}

// Dapatkan maklumat pelajar
$stmt = $conn->prepare("SELECT pelajar_nama, pelajar_matrik, pelajar_kadpengenalan, pelajar_emel, pelajar_telefon, pelajar_alamat FROM Pelajar_UKM WHERE pelajar_emel = ?");
$stmt->bind_param("s", $pelajar_emel);
$stmt->execute();
$pelajar = $stmt->get_result()->fetch_assoc();

// Dapatkan nama persatuan
$stmt2 = $conn->prepare("SELECT persatuan_nama FROM Persatuan WHERE persatuan_id = ?");
$stmt2->bind_param("i", $persatuan_id);
$stmt2->execute();
$persatuan = $stmt2->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Permohonan Kes Khas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->

    <style>
        body { font-family: 'Fira Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../includes/headerPel.php'; ?>
    <div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4 text-center text-purple-700">Permohonan Kes Khas</h2>

        <form id="permohonanForm" action="../Controllers/permohonankeskhas.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="persatuan_id" value="<?= $persatuan_id ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Persatuan</label>
                <input type="text" value="<?= htmlspecialchars($persatuan['persatuan_nama']) ?>" readonly
                    class="w-full mt-1 px-3 py-2 border border-gray-300 bg-gray-100 text-gray-600 rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Pelajar</label>
                <input type="text" value="<?= htmlspecialchars($pelajar['pelajar_nama']) ?>" readonly class="w-full mt-1 px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">No Matrik</label>
                <input type="text" value="<?= htmlspecialchars($pelajar['pelajar_matrik']) ?>" readonly class="w-full mt-1 px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Alasan Permohonan</label>
                <textarea name="alasan" rows="4" required class="w-full mt-1 px-3 py-2 border rounded"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Muat Naik Dokumen Sokongan</label>
                <input type="file" id="dokumenInput" name="dokumen_sokongan" accept=".pdf,.jpg,.jpeg,.png" required class="mt-1">
            </div>

            <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700">
                Hantar Permohonan
            </button>
        </form>
    </div>

<script>
// Client-side validation
document.getElementById("permohonanForm").addEventListener("submit", function(e) {
    const inputFile = document.getElementById("dokumenInput");
    const file = inputFile.files[0];
    if (file) {
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            e.preventDefault(); // prevent form submit
            Swal.fire({
                icon: 'error',
                title: 'Format fail tidak dibenarkan!',
                text: 'Sila muat naik fail dalam format PDF, JPG atau PNG sahaja.',
            });
        }
    }
});
</script>

</body>
</html>
