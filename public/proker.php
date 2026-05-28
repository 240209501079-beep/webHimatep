<?php
require_once '../private/php/config.php';

// Ambil Semua Proker
try {
    $stmt = $pdo->query("
        SELECT p.*, a.tanggal_event, a.waktu, a.lokasi 
        FROM proker p 
        LEFT JOIN agenda a ON p.id = a.proker_id 
        ORDER BY p.id ASC
    ");
    $all_proker = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_proker = [];
}

// Ambil Data Banner
try {
    $stmt = $pdo->prepare("SELECT * FROM halaman_banner WHERE halaman = 'proker'");
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
$proker_json = json_encode(array_map(function($p) {
    $date = $p['tanggal_event'] ? new DateTime($p['tanggal_event']) : null;
    return [
        'id' => $p['id'],
        'slug' => $p['slug'],
        'judul' => $p['judul'],
        'divisi' => $p['divisi'],
        'divisiColor' => $p['divisi_color'],
        'gambar' => $p['gambar'],
        'icon' => $p['icon'],
        'ringkasan' => $p['ringkasan'],
        'target' => $p['target'],
        'sasaran' => $p['sasaran'],
        'unggulan' => (bool)$p['is_unggulan'],
        'agenda' => $p['tanggal_event'] ? [
            'date' => $p['tanggal_event'],
            'bulan' => strtoupper($date->format('M')),
            'tanggal' => $date->format('d'),
            'waktu' => $p['waktu'],
            'lokasi' => $p['lokasi']
        ] : null
    ];
}, $all_proker));
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Kerja - HIMATEP FIP UNM</title>
    <?php include 'includes/meta_icons.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            important: true,
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
            },
            safelist: [
                'bg-emerald-100', 'bg-blue-100', 'bg-green-100', 'bg-orange-100', 'bg-purple-100', 'bg-red-100', 'bg-yellow-100', 'bg-pink-100', 'bg-indigo-100',
                'text-emerald-700', 'text-blue-700', 'text-green-700', 'text-orange-700', 'text-purple-700', 'text-red-700', 'text-yellow-700', 'text-pink-700', 'text-indigo-700',
                'border-emerald-200', 'border-blue-200', 'border-green-200', 'border-orange-200', 'border-purple-200', 'border-red-200', 'border-yellow-200', 'border-pink-200', 'border-indigo-200',
                'text-emerald-500', 'text-blue-500', 'text-green-500', 'text-orange-500', 'text-purple-500', 'text-red-500', 'text-yellow-500', 'text-pink-500', 'text-indigo-500',
                'text-emerald-400', 'text-blue-400', 'text-green-400', 'text-orange-400', 'text-purple-400', 'text-red-400', 'text-yellow-400', 'text-pink-400', 'text-indigo-400',
                'bg-emerald-600', 'bg-blue-600', 'bg-green-600', 'bg-orange-600', 'bg-purple-600', 'bg-red-600', 'bg-yellow-600', 'bg-pink-600', 'bg-indigo-600',
                'hover:text-emerald-300', 'hover:text-blue-300', 'hover:text-green-300', 'hover:text-orange-300', 'hover:text-purple-300',
                'hover:text-emerald-400', 'hover:text-blue-400', 'hover:text-green-400', 'hover:text-orange-400', 'hover:text-purple-400',
                'group-hover:text-emerald-400', 'group-hover:text-blue-400', 'group-hover:text-green-400', 'group-hover:text-orange-400', 'group-hover:text-purple-400'
            ]
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

<body class="font-sans theme-custom bg-[#1E2F4D] text-white overflow-x-hidden" x-data="{ mobileMenuOpen: false, prokers: dataProgram, groupBy(list, key) { return list.reduce((rv, x) => { (rv[x[key]] = rv[x[key]] || []).push(x); return rv; }, {}); } }">
    <section id="proker" style="scroll-margin-top:100px"></section>

    <!-- Navbar -->
    <?php 
    $root_path = '';
    include 'includes/navbar.php'; 
    ?>

    <!-- Header Section -->
    <section class="pt-40 pb-24 relative overflow-hidden min-h-[800px] flex items-center justify-center bg-himatep-dark">
        <img src="<?= ($banner && $banner['gambar']) ? (strpos($banner['gambar'], 'http') === 0 ? $banner['gambar'] : $banner['gambar']) : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&q=80' ?>" alt="Background Program Kerja" class="absolute inset-0 w-full h-full object-cover z-0 opacity-40 mix-blend-luminosity">
        <div class="absolute inset-0 bg-gradient-to-t from-himatep-green/40 to-transparent z-0"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-lg"><?= htmlspecialchars($banner['judul'] ?? 'Program Kerja') ?></h1>
            <p class="text-xl text-gray-100 max-w-2xl mx-auto font-medium drop-shadow-md"><?= htmlspecialchars($banner['subjudul'] ?? 'Dedikasi kami melalui program kerja nyata untuk memajukan mahasiswa Teknologi Pendidikan dan masyarakat luas.') ?></p>
        </div>
    </section>

    <!-- Katalog Program Kerja -->
    <section class="py-20 bg-batik-blue">
        <div class="max-w-7xl mx-auto px-4 gsap-fade-up">

            <!-- Katalog Berbasis Divisi (Dinamis dari Alpine.js) -->
            <template x-for="(group, division) in groupBy(prokers, 'divisi')" :key="division">
                <div class="mb-20">
                    <div class="flex items-center gap-4 mb-8">
                        <span class="w-12 h-1 rounded-full" :class="group[0].divisiColor === 'blue' ? 'bg-himatep-green' : 'bg-' + group[0].divisiColor + '-500'"></span>
                        <h2 class="text-3xl font-bold text-white" x-text="division"></h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <template x-for="item in group" :key="item.id">
                            <a :href="item.slug ? 'detail-program.php?slug=' + item.slug : 'detail-program.php?id=' + item.id"
                                class="bg-[#1E2F4D] rounded-3xl overflow-hidden border border-white/20 hover:shadow-xl transition-all duration-300 group flex flex-col cursor-pointer card-hover block">
                                
                                <!-- Card Image -->
                                <div class="w-full h-48 overflow-hidden relative shadow-sm">
                                    <img :src="item.gambar || 'images/logo-himatep.png'" :alt="item.judul"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold shadow-md uppercase tracking-wider border"
                                        :class="'text-' + (item.divisiColor || 'blue') + '-700 bg-' + (item.divisiColor || 'blue') + '-100 border-' + (item.divisiColor || 'blue') + '-200'" x-text="item.divisi"></div>
                                </div>

                                <div class="p-8 flex-1 flex flex-col">
                                    <h3 class="text-2xl font-bold mb-3 transition-colors" :class="'text-white group-hover:text-' + (item.divisiColor || 'blue') + '-400'" x-text="item.judul"></h3>
                                    <p class="text-gray-300 mb-6 leading-relaxed line-clamp-3 flex-1" x-text="item.ringkasan"></p>
                                    
                                    <div class="space-y-2 mb-6 mt-auto pt-4 border-t border-white/10/50">
                                        <div class="flex items-center text-sm font-medium text-gray-400">
                                            <svg class="w-5 h-5 mr-2" :class="'text-' + (item.divisiColor || 'blue') + '-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg> Target: <span class="ml-1" x-text="item.target"></span>
                                        </div>
                                        <div class="flex items-center text-sm font-medium text-gray-400">
                                            <svg class="w-5 h-5 mr-2" :class="'text-' + (item.divisiColor || 'blue') + '-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                                            </svg> Sasaran: <span class="ml-1" x-text="item.sasaran"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="inline-flex items-center font-semibold hover:gap-2 transition-all mt-auto"
                                        :class="'text-' + (item.divisiColor || 'blue') + '-400 hover:text-' + (item.divisiColor || 'blue') + '-300'">
                                        Detail Program <span class="ml-1">&rarr;</span>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </template>

        </div>
    </section>

    <!-- Upcoming Agenda -->
    <section class="py-20 bg-batik-blue border-white/10">
        <div class="max-w-7xl mx-auto px-4 gsap-fade-up">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Agenda Mendatang</h2>
                    <p class="text-gray-300">3 jadwal kegiatan terdekat HIMATEP</p>
                </div>
                <a href="index.php#kalender"
                    class="hidden md:inline-flex text-white font-semibold hover:underline">Lihat Kalender Penuh
                    &rarr;</a>
            </div>

            <!-- Grid 3 Agenda -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8"
                x-data="{ agendas: dataProgram.filter(p => p.agenda).sort((a,b) => new Date(a.agenda.date) - new Date(b.agenda.date)).slice(0, 3) }">
                <template x-for="item in agendas" :key="item.id">
                    <a :href="'detail-program.php?slug=' + item.slug"
                        class="flex flex-col bg-[#1E2F4D] rounded-3xl overflow-hidden shadow-sm border border-white/20 hover:shadow-xl transition-all duration-300 group cursor-pointer">
                        <div class="relative h-48 w-full overflow-hidden">
                            <img :src="item.gambar" :alt="item.judul"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div
                                :class="'absolute top-4 right-4 text-white rounded-2xl p-2 flex flex-col justify-center items-center shadow-lg min-w-[70px] bg-' + item.divisiColor + '-600'">
                                <span class="text-xs font-bold uppercase tracking-wider opacity-90"
                                    x-text="item.agenda.bulan"></span>
                                <span class="text-2xl font-black leading-none" x-text="item.agenda.tanggal"></span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-xl font-bold text-white mb-4 transition-colors"
                                :class="'group-hover:text-' + (item.divisiColor || 'blue') + '-400'" x-text="item.judul"></h3>
                            <div class="space-y-3 mt-auto">
                                <div class="flex items-center text-sm font-medium text-gray-400">
                                    <svg class="w-5 h-5 mr-3" :class="'text-' + (item.divisiColor || 'blue') + '-400'" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="item.agenda.waktu"></span>
                                </div>
                                <div class="flex items-center text-sm font-medium text-gray-400">
                                    <svg class="w-5 h-5 mr-3" :class="'text-' + (item.divisiColor || 'blue') + '-400'" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span x-text="item.agenda.lokasi"></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            <div class="mt-8 text-center md:hidden">
                <a href="index.php#kalender" class="inline-flex text-white font-semibold hover:underline">Lihat
                    Kalender Penuh &rarr;</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>


    <script>
        const dataProgram = <?php echo $proker_json; ?>;
    </script>
    <script src="js/animations.js?v=1.1"></script>
    <script src="js/main.js?v=<?= time() ?>"></script>
</body>

</html>


