<?php
require_once '../private/php/config.php';

// Ambil Semua Berita
try {
    $stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggal_posting DESC");
    $all_berita = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_berita = [];
}

// Ambil Data Banner
try {
    $stmt = $pdo->prepare("SELECT * FROM halaman_banner WHERE halaman = 'berita'");
    $stmt->execute();
    $banner = $stmt->fetch();
} catch (PDOException $e) {
    $banner = null;
}

// Ambil Data Narahubung
try {
    $stmt = $pdo->query("SELECT * FROM contacts WHERE is_active = 1 ORDER BY platform DESC, sort_order ASC");
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    $contacts = [];
}
$wa_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'WhatsApp');
$email_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'Email');
$sosmed_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'Social Media');
require_once 'includes/icons.php';

// Persiapkan data untuk Alpine.js
$berita_json = json_encode(array_map(function($b) {
    return [
        'id' => $b['id'],
        'slug' => $b['slug'],
        'judul' => $b['judul'],
        'kategori' => $b['kategori'],
        'kategoriColor' => $b['kategori_color'],
        'tanggal' => $b['tanggal_posting'],
        'penulis' => $b['penulis'],
        'gambar' => $b['gambar'],
        'ringkasan' => $b['ringkasan']
    ];
}, $all_berita));
?>
<section id="berita" style="scroll-margin-top:100px"></section>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Berita Resmi HIMATEP FIP UNM. Temukan kabar terbaru mengenai kegiatan mahasiswa, prestasi, dan pengumuman penting.">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Portal Berita - HIMATEP FIP UNM">
    <meta property="og:description" content="Kabar terbaru mengenai kegiatan, prestasi, dan pengumuman HIMATEP FIP UNM.">

    <title>Portal Berita - HIMATEP FIP UNM</title>
    <?php include 'includes/meta_icons.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-light': '#E2E8F0',
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

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=2.7">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>



    <!-- GSAP & ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
</head>

<body class="font-sans theme-custom bg-gray-50 text-himatep-dark overflow-x-hidden"
    x-data="{ mobileMenuOpen: false, daftarBerita: dataBerita }">

    <!-- Navbar -->
    <?php 
    $root_path = '';
    include 'includes/navbar.php'; 
    ?>

    <!-- Header Section -->
    <section class="pt-40 pb-24 relative overflow-hidden min-h-[800px] flex items-center justify-center bg-himatep-dark">
        <img src="<?= ($banner && $banner['gambar']) ? (strpos($banner['gambar'], 'http') === 0 ? $banner['gambar'] : $banner['gambar']) : 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80' ?>" alt="Background Profil Organisasi" class="absolute inset-0 w-full h-full object-cover z-0 opacity-40 mix-blend-luminosity">
        <div class="absolute inset-0 bg-gradient-to-t from-himatep-green/40 to-transparent z-0"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-lg"><?= htmlspecialchars($banner['judul'] ?? 'Portal Berita') ?></h1>
            <p class="text-xl text-gray-100 max-w-2xl mx-auto font-medium drop-shadow-md"><?= htmlspecialchars($banner['subjudul'] ?? 'Ikuti berita terbaru, kegiatan, dan prestasi dari HIMATEP FIP UNM.') ?></p>
        </div>
    </section>

    <!-- Daftar Berita -->
    <section class="py-20 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Headline Berita Terbaru (Ambil berita pertama) -->
            <template x-if="daftarBerita.length > 0">
                <div class="mb-16">
                    <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-400 flex flex-col md:flex-row hover:shadow-xl transition-shadow group cursor-pointer"
                        @click="window.location.href='detail-berita.php?slug=' + daftarBerita[0].slug">
                        <div class="md:w-1/2 relative overflow-hidden">
                            <img :src="daftarBerita[0].gambar" :alt="daftarBerita[0].judul"
                                class="w-full h-64 md:h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 left-4">
                                <span class="text-xs font-bold text-white px-3 py-1 rounded-full shadow-md"
                                    :class="daftarBerita[0].kategoriColor === 'blue' ? 'bg-himatep-green' : 'bg-' + daftarBerita[0].kategoriColor + '-600'"
                                    x-text="daftarBerita[0].kategori"></span>
                            </div>
                        </div>
                        <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center relative">
                            <div
                                class="absolute top-8 right-8 text-sm font-semibold text-himatep-green bg-himatep-light px-3 py-1 rounded-full border border-gray-200">
                                BERITA UTAMA
                            </div>
                            <div class="flex items-center text-sm text-gray-500 mb-4 mt-8 md:mt-0">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span x-text="daftarBerita[0].tanggal"></span>
                                <span class="mx-2">•</span>
                                <span x-text="daftarBerita[0].penulis"></span>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-4 group-hover:text-himatep-green transition-colors"
                                x-text="daftarBerita[0].judul"></h2>
                            <p class="text-gray-600 mb-6 text-lg" x-text="daftarBerita[0].ringkasan"></p>
                            <a :href="'detail-berita.php?slug=' + daftarBerita[0].slug"
                                class="inline-flex items-center font-bold text-himatep-green hover:gap-2 transition-all">
                                Baca Artikel Lengkap <span class="ml-1">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Grid Berita Lainnya -->
            <h3 class="text-2xl font-bold text-gray-800 mb-8 border-b-2 border-gray-200 pb-4">Berita Lainnya</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Loop dari berita index ke-1 sampai habis -->
                <template x-for="(berita, index) in daftarBerita" :key="berita.id">
                    <div x-show="index > 0"
                        class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-400 flex flex-col cursor-pointer card-hover"
                        @click="window.location.href='detail-berita.php?slug=' + berita.slug">
                        <div class="relative h-48 overflow-hidden">
                            <img :src="berita.gambar" :alt="berita.judul" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="mb-3">
                                <span class="text-xs font-bold px-3 py-1 rounded-full"
                                    :class="'text-' + berita.kategoriColor + '-600 bg-' + berita.kategoriColor + '-100'"
                                    x-text="berita.kategori"></span>
                            </div>
                            <h3 class="text-xl font-bold mb-3 text-gray-800 line-clamp-2 hover:text-himatep-green transition-colors"
                                x-text="berita.judul"></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-1" x-text="berita.ringkasan"></p>

                            <div class="flex items-center justify-between mt-auto pt-4 border-gray-100">
                                <div class="text-xs text-gray-500 font-medium" x-text="berita.tanggal"></div>
                                <a :href="'detail-berita.php?slug=' + berita.slug"
                                    class="text-sm text-himatep-green font-bold hover:underline">Baca &rarr;</a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        const dataBerita = <?php echo $berita_json; ?>;
    </script>
    <script src="js/animations.js"></script>
    <script src="js/main.js?v=<?= time() ?>"></script>
</body>

</html>


