<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - HIMATEP</title>
    <?php include 'includes/meta_icons.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
        <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-light': '#DBEAFE',
                        'himatep-dark': '#111111',
                    },
                    fontFamily: {
                        'sans': ['Poppins', 'sans-serif'],
                        'cursive': ['"Great Vibes"', 'cursive'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=2.6">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="font-sans theme-custom bg-[#1E2F4D] text-white overflow-hidden min-h-screen flex items-center justify-center relative">

    <div class="relative z-10 max-w-2xl mx-auto px-4 text-center">
        <!-- Ilustrasi Error -->
        <div class="mb-8 relative inline-block">
            <span class="text-9xl font-black text-white/10">404</span>

        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">Waduh! Halamannya <span class="text-white italic font-cursive text-5xl">Menghilang...</span></h1>
        <p class="text-gray-300 text-lg mb-10 max-w-md mx-auto">Sepertinya link yang Anda tuju sudah tidak ada atau berpindah tempat ke divisi lain.</p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="/webHimatep/public/index.php" class="btn-error-primary px-8 py-4 font-bold rounded-full shadow-lg transition transform hover:-translate-y-1 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Kembali ke Beranda
            </a>
            <button onclick="history.back()" class="btn-error-secondary px-8 py-4 border-2 font-bold rounded-full transition transform hover:-translate-y-1">
                Halaman Sebelumnya
            </button>
        </div>

        <p class="mt-16 text-xs text-gray-400 font-medium tracking-widest uppercase italic">Kisahmu Tak Pernah Usai • HIMATEP FIP UNM</p>
    </div>
</body>
</html>

