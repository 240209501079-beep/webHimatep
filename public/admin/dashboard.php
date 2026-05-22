<?php
session_start();
require_once '../../private/php/config.php';

// Cek sesi login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Fetch Stats
try {
    $count_berita = $pdo->query("SELECT COUNT(*) FROM berita")->fetchColumn();
    $count_proker = $pdo->query("SELECT COUNT(*) FROM proker")->fetchColumn();
    $count_aspirasi = $pdo->query("SELECT COUNT(*) FROM aspirasi")->fetchColumn();
    
    // Fetch Recent Aspirasi
    $stmt = $pdo->query("SELECT * FROM aspirasi ORDER BY created_at DESC LIMIT 5");
    $recent_aspirasi = $stmt->fetchAll();
} catch (PDOException $e) {
    $count_berita = 0;
    $count_proker = 0;
    $count_aspirasi = 0;
    $recent_aspirasi = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-light': '#E2E8F0', /* Nuansa biru muda untuk tema baru */
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
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <?php include "includes/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full">
        <!-- Header -->
        <header class="h-20 bg-[#1E2F4D] shadow-sm flex items-center justify-between px-4 lg:px-8 z-10">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-gray-100 text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl lg:text-2xl font-bold text-white">Dashboard</h2>
            </div>
            <div class="flex items-center gap-3 lg:gap-4">
                <a href="../index.php" class="text-xs lg:text-sm text-white hover:underline font-medium">Lihat Website</a>
                <div class="flex items-center gap-2 lg:gap-4 border-l border-white/10 pl-3 lg:pl-4">
                    <span class="font-medium text-xs lg:text-base text-gray-300 truncate max-w-[100px] lg:max-w-none">
                        Halo, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- Dashboard Stats -->
        <div class="flex-1 p-8 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Stat Card 1 -->
                <div class="bg-[#1E2F4D] p-6 rounded-2xl shadow-sm border border-white/20 flex items-center gap-4">
                    <div class="w-16 h-16 bg-himatep-light text-white rounded-2xl flex items-center justify-center text-2xl">📰</div>
                    <div>
                        <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Total Berita</h3>
                        <p class="text-3xl font-bold text-white"><?= $count_berita ?></p>
                    </div>
                </div>
                <!-- Stat Card 2 -->
                <div class="bg-[#1E2F4D] p-6 rounded-2xl shadow-sm border border-white/20 flex items-center gap-4">
                    <div class="w-16 h-16 bg-himatep-light text-white rounded-2xl flex items-center justify-center text-2xl">📋</div>
                    <div>
                        <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Program Kerja</h3>
                        <p class="text-3xl font-bold text-white"><?= $count_proker ?></p>
                    </div>
                </div>
                <!-- Stat Card 3 -->
                <div class="bg-[#1E2F4D] p-6 rounded-2xl shadow-sm border border-white/20 flex items-center gap-4">
                    <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-2xl flex items-center justify-center text-2xl">🗣️</div>
                    <div>
                        <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Aspirasi Baru</h3>
                        <p class="text-3xl font-bold text-white"><?= $count_aspirasi ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Aspirasi -->
            <div class="bg-[#1E2F4D] rounded-2xl shadow-sm border border-white/20 overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold">Aspirasi Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-[#1E2F4D] text-gray-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Nama & Email</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Pesan</th>
                                <th class="px-6 py-4">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($recent_aspirasi as $item): ?>
                            <tr class="hover:bg-[#1E2F4D]">
                                <td class="px-6 py-4">
                                    <div class="font-medium"><?= htmlspecialchars($item['nama'] ?: 'Anonim') ?></div>
                                    <div class="text-[10px] text-gray-400 italic"><?= htmlspecialchars($item['email'] == '-' ? 'Tanpa Email' : $item['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-blue-600"><?= htmlspecialchars($item['jenis']) ?></td>
                                <td class="px-6 py-4 text-sm truncate max-w-[150px]"><?= htmlspecialchars($item['pesan']) ?></td>
                                <td class="px-6 py-4 text-xs text-gray-400"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_aspirasi)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Belum ada aspirasi masuk.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-[#1E2F4D] text-center border-t">
                    <a href="view_aspirasi.php" class="text-sm text-white font-bold hover:underline">Lihat Semua Aspirasi</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Tambahkan script Alpine jika belum ada (biasanya di head tapi aman jika di sini) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>


