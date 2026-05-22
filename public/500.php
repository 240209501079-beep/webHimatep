<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - HIMATEP</title>
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
<body class="font-sans theme-custom bg-gray-50 text-himatep-dark overflow-hidden min-h-screen flex items-center justify-center relative">
    
    <div class="relative z-10 max-w-2xl mx-auto px-4 text-center">
        <!-- Ikon Server Error -->
        <div class="mb-8 inline-flex items-center justify-center p-6 bg-white rounded-3xl shadow-xl border border-gray-100">
            <svg class="w-16 h-16 text-red-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">Server Sedang <span class="text-red-600 italic font-cursive text-5xl">Kelelahan...</span></h1>
        <p class="text-gray-600 text-lg mb-10 max-w-md mx-auto">Terjadi kesalahan pada sistem internal kami. Tim IT HIMATEP sedang berusaha memperbaikinya secepat mungkin.</p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="/webHimatep/public/index.php" class="btn-error-primary px-8 py-4 font-bold rounded-full shadow-lg transition transform hover:-translate-y-1 flex items-center justify-center">
                Coba Segarkan Halaman
            </a>
            <a href="https://wa.me/628123456789" class="btn-error-secondary px-8 py-4 border-2 font-bold rounded-full transition transform hover:-translate-y-1 flex items-center justify-center">
                Laporkan Masalah
            </a>
        </div>

        <p class="mt-16 text-xs text-gray-400 font-medium tracking-widest uppercase italic">Error Code: 500 • Internal Server Error</p>
    </div>
</body>
</html>

