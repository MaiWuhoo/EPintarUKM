<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akaun</title>
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
        <h2 class="text-3xl font-bold text-center text-gray-700 mb-6">Daftar Akaun</h2>

        <form id="registerForm" method="POST" action="controllers/registerPelajar.php">
            
            <!-- Nama Penuh -->
            <div class="mb-4">
                <label for="pelajar_nama" class="block text-sm font-medium text-gray-700">Nama Penuh</label>
                <input id="pelajar_nama" name="pelajar_nama" type="text" required
                       class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Nombor Matrik -->
            <div class="mb-4">
                <label for="pelajar_matrik" class="block text-sm font-medium text-gray-700">Nombor Matrik</label>
                <input id="pelajar_matrik" name="pelajar_matrik" type="text" required
                       class="block mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
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

            <!-- Submit Button -->
            <div class="mt-6 text-center">
                <button type="submit"
                        class="w-full py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Daftar Akaun
                </button>
            </div>
        </form>

        <!-- Register Persatuan -->
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Ingin mendaftar Persatuan?
                <a href="registerPengurus.php" class="text-blue-500 hover:underline">Daftar Persatuan</a>
            </p>
        </div>
    </div>
</div>

<!-- Toggle Password Visibility Script -->
<script>
    document.getElementById('toggle-password').addEventListener('click', function () {
        const field = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    document.getElementById('toggle-password-confirmation').addEventListener('click', function () {
        const field = document.getElementById('password_confirmation');
        const icon = document.getElementById('eye-icon-confirmation');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
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
