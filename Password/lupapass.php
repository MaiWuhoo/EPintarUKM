<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lupa Kata Laluan</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
     <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Fira Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Lupa Kata Laluan</h2>
        <form action="../controllers/semakReset.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700">Emel</label>
                <input type="email" name="email" placeholder="Masukkan emel"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-400" required>
            </div>
            <div>
                <label class="block text-gray-700">No Matrik</label>
                <input type="text" name="matrik" placeholder="Masukkan No Matrik"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-400" required>
            </div>
            <div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600 transition duration-200">
                    Seterusnya
                </button>
            </div>
        </form>
    </div>
</body>
</html>
