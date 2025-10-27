<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>

</head>
<body class="bg-gray-100">

    <!-- HEADER (now in body, not inside <head>) -->
    <?php include 'includes/header.php'; ?>

    <!-- Login Form Container -->
    <div class="flex justify-center mt-20 mb-20">
        <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-center text-gray-700 mb-6">Log Masuk</h2>

            <form method="POST" action="auth.php">
                <!-- Email -->
                <div class="mb-6">
                    <label for="emel" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="emel" name="emel" type="email" required autofocus
                           class="block mt-2 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="kataLaluan" class="block text-sm font-medium text-gray-700">Kata Laluan</label>
                    <div class="relative">
                        <input id="kataLaluan" name="kataLaluan" type="password" required
                               class="block mt-2 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" id="toggle-password" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                

                <!-- Login Button -->
                <div class="mt-6 text-center">
                    <button type="submit"
                            class="w-full py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Log Masuk
                    </button>
                </div>

                <!-- Forgot Password -->
                <div class="flex justify-between items-center mt-4">
                    <a href="password/lupapass.php" class="text-sm text-blue-500 hover:underline">Lupa Kata Laluan?</a>
                </div>

                <!-- Register Link -->
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Tiada Akaun? <a href="registerPelajar.php" class="text-blue-500 hover:underline">Daftar Akaun</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('kataLaluan');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>

</body>
</html>
