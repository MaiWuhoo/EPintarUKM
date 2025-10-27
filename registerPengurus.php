<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akaun Persatuan</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">

<?php include 'includes/header.php'; ?>

<div class="flex justify-center mt-20 mb-20">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center text-gray-700 mb-6">Daftar Akaun Persatuan</h2>

        <!-- Handle error messages -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'confirm'): ?>
            <p class="text-red-500 text-sm text-center mb-4">Kata laluan dan pengesahan tidak sepadan.</p>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'server'): ?>
            <p class="text-red-500 text-sm text-center mb-4">Ralat semasa pendaftaran. Sila cuba lagi.</p>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="controllers/registerPengurus.php">
            <!-- Nama Persatuan -->
            <div class="mb-4">
                <label for="persatuan_nama" class="block text-sm font-medium text-gray-700">Nama Persatuan</label>
                <input id="persatuan_nama" name="persatuan_nama" type="text" required
                       class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email Persatuan -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Persatuan</label>
                <input id="email" name="email" type="email" required
                       class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Kata Laluan -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Laluan</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required
                           class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="toggle-password" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                        <i class="fa-solid fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Sahkan Kata Laluan -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Sahkan Kata Laluan</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <button type="button" id="toggle-password-confirmation" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                        <i class="fa-solid fa-eye" id="eye-icon-confirmation"></i>
                    </button>
                </div>
            </div>

            <!-- Register Button -->
            <div class="mt-6 text-center">
                <button type="submit"
                        class="w-full py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Daftar Akaun Persatuan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
    document.getElementById('toggle-password').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const isHidden = passwordField.type === 'password';

        passwordField.type = isHidden ? 'text' : 'password';
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggle-password-confirmation').addEventListener('click', function () {
        const passwordField = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('eye-icon-confirmation');
        const isHidden = passwordField.type === 'password';

        passwordField.type = isHidden ? 'text' : 'password';
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
    // Validasi panjang kata laluan
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        const password = document.getElementById('password').value;

        if (password.length <= 6) {
            e.preventDefault(); // Halang borang dari dihantar
            Swal.fire({
                icon: 'error',
                title: 'Kata Laluan Lemah',
                text: 'Kata laluan mesti lebih daripada 6 aksara.',
            });
        }
    });
</script>

</body>
</html>
