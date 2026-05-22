<?php
require_once '../private/php/config.php';
// Ambil Data Narahubung
try {
    $stmt = $pdo->query("SELECT * FROM contacts WHERE is_active = 1 ORDER BY platform DESC, sort_order ASC");
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    $contacts = [];
}

// Pisahkan kontak berdasarkan platform
$wa_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'WhatsApp');
$email_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'Email');
$sosmed_contacts = array_filter($contacts, fn($c) => $c['platform'] === 'Social Media');
require_once 'includes/icons.php';

// Ambil Berita (Terbaru 3)
try {
    $stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggal_posting DESC LIMIT 3");
    $berita_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $berita_list = [];
}

// Ambil Proker dengan Agenda
try {
    $stmt = $pdo->query("
        SELECT p.*, a.tanggal_event, a.waktu, a.lokasi 
        FROM proker p 
        LEFT JOIN agenda a ON p.id = a.proker_id 
        ORDER BY p.id ASC
    ");
    $proker_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $proker_list = [];
}

// Ambil Jumlah Divisi, Pengurus, dan Proker secara dinamis
try {
    $stmt_divisi = $pdo->query("SELECT COUNT(DISTINCT divisi) as total FROM pengurus WHERE TRIM(UPPER(divisi)) != 'BPH'");
    $count_divisi = $stmt_divisi->fetch()['total'];
    if ($count_divisi == 0) $count_divisi = 4;
} catch (PDOException $e) {
    $count_divisi = 4;
}

try {
    $stmt_pengurus = $pdo->query("SELECT COUNT(*) as total FROM pengurus");
    $count_pengurus = $stmt_pengurus->fetch()['total'];
    if ($count_pengurus == 0) $count_pengurus = 48;
} catch (PDOException $e) {
    $count_pengurus = 48;
}

try {
    $stmt_proker = $pdo->query("SELECT COUNT(*) as total FROM proker");
    $count_proker = $stmt_proker->fetch()['total'];
    if ($count_proker == 0) $count_proker = 20;
} catch (PDOException $e) {
    $count_proker = 20;
}

// Persiapkan data untuk Alpine.js
$data_program_json = json_encode(array_map(function($p) {
    $date = $p['tanggal_event'] ? new DateTime($p['tanggal_event']) : null;
    return [
        'id' => $p['id'],
        'judul' => $p['judul'],
        'slug' => $p['slug'],
        'divisi' => $p['divisi'],
        'divisiColor' => $p['divisi_color'],
        'gambar' => $p['gambar'],
        'icon' => $p['icon'],
        'ringkasan' => $p['ringkasan'],
        'unggulan' => (bool)$p['is_unggulan'],
        'agenda' => $p['tanggal_event'] ? [
            'date' => $p['tanggal_event'],
            'bulan' => strtoupper($date->format('M')),
            'tanggal' => $date->format('d'),
            'waktu' => $p['waktu'],
            'lokasi' => $p['lokasi']
        ] : null
    ];
}, $proker_list));
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth scroll-pt-5 md:scroll-pt-5">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Website Resmi HIMATEP FIP UNM (Himpunan Mahasiswa Teknologi Pendidikan). Wadah kreasi, inovasi, dan pengabdian mahasiswa Teknologi Pendidikan UNM.">
    <meta name="keywords" content="HIMATEP, FIP UNM, Teknologi Pendidikan, UNM, Mahasiswa TP, Makassar, Pendidikan Digital">
    <meta name="author" content="HIMATEP FIP UNM">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost/webHimatep/">
    <meta property="og:title" content="HIMATEP FIP UNM - Kisahmu Tak Pernah Usai">
    <meta property="og:description" content="Wadah kreasi, inovasi, dan pengabdian mahasiswa Teknologi Pendidikan UNM.">
    <meta property="og:image" content="http://localhost/webHimatep/public/images/logo-himatep.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="HIMATEP FIP UNM - Kisahmu Tak Pernah Usai">
    <meta property="twitter:description" content="Wadah kreasi, inovasi, dan pengabdian mahasiswa Teknologi Pendidikan UNM.">

    <title>HIMATEP FIP UNM - Kisahmu Tak Pernah Usai</title>

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

<body class="font-sans theme-custom bg-[#1E2F4D] text-white overflow-x-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section id="hero" class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden bg-batik-blue">
        <div class="relative z-10 text-center px-4 max-w-10xl mx-auto flex flex-col items-center">
            <img src="images/logo-himatep.png" alt="HIMATEP Logo"
                class="w-[200px] h-[200px] md:w-80 md:h-80 mb-16 rounded-full p-4 mt-3 hero-logo"
                onerror="this.src='https://via.placeholder.com/150x150.png?text=Logo'">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-2 tracking-tight hero-text">HIMATEP FIP UNM
            </h1>
            <h2 class="text-6xl md:text-8xl font-cursive text-himatep-light mb-8 hero-text transform -rotate-2">Kisahmu
                Tak Pernah Usai</h2>

            <p class="text-lg md:text-xl text-gray-300 mb-16 mt-4 hero-text max-w-12xl mx-auto font-medium">Wadah
                kreasi,
                inovasi, dan pengabdian mahasiswa Teknologi Pendidikan menuju generasi unggul.</p>
            <a href="#profile"
                class="btn-profile-kami mt-4 inline-block bg-[#D4AF37] hover:bg-[#B8860B] text-white px-10 py-4 rounded-full font-bold shadow-2xl transition-all duration-300 text-lg tracking-wide">
                Profile Kami
            </a>
        </div>
    </section>

    <!-- Profile Section -->
    <section id="profile" class="py-24 bg-batik-blue min-h-screen flex flex-col justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 gsap-fade-up">
            <h2 class="text-3xl font-bold text-center mb-16 text-white">Profil Organisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-bold mb-4 border-l-4 border-himatep-green pl-4">Sejarah Singkat</h3>
                    <p class="text-gray-300 leading-relaxed mb-8 text-justify">HIMATEP FIP UNM didirikan sebagai wadah
                        aspirasi dan pengembangan diri mahasiswa Teknologi Pendidikan. Kami berkomitmen untuk terus
                        berinovasi dalam bidang pendidikan dan teknologi serta menjunjung tinggi asas kekeluargaan.</p>

                    <h3 class="text-2xl font-bold mb-4 border-l-4 border-himatep-green pl-4">Visi & Misi</h3>
                    <ul class="list-none text-gray-300 space-y-3">
                        <li class="flex items-start"><svg class="w-6 h-6 text-white mr-2 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg> Mewujudkan mahasiswa yang kreatif dan inovatif.</li>
                        <li class="flex items-start"><svg class="w-6 h-6 text-white mr-2 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg> Meningkatkan solidaritas antar mahasiswa Teknologi Pendidikan.</li>
                        <li class="flex items-start"><svg class="w-6 h-6 text-white mr-2 flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg> Berkontribusi aktif dalam pengembangan pendidikan di Indonesia.</li>
                    </ul>

                    <a href="profile.php"
                        class="mt-12 justify-center text-center items-center bg-himatep-green hover:bg-himatep-green/80 text-white  px-8 py-3 rounded-full font-bold  shadow-xl inline-block">Profile
                        Lengkap</a>

                </div>
                <div class="grid grid-cols-2 gap-6">
                    <a href="profile.php#divisi" class="bg-[#1E2F4D] p-8 rounded-2xl shadow-sm border border-white/20 text-center card-hover block group hover:bg-[#D4AF37] hover:border-[#D4AF37] transition-all duration-300">
                        <div class="text-5xl font-bold text-white mb-2 group-hover:scale-110 group-hover:text-white transition-all duration-300"><?= $count_divisi ?></div>
                        <div class="text-sm text-gray-400 font-medium uppercase tracking-wider group-hover:text-white transition-colors duration-300">Divisi</div>
                    </a>
                    <a href="profile.php#pengurus" class="bg-[#1E2F4D] p-8 rounded-2xl shadow-sm border border-white/20 text-center card-hover block group hover:bg-[#D4AF37] hover:border-[#D4AF37] transition-all duration-300">
                        <div class="text-5xl font-bold text-white mb-2 group-hover:scale-110 group-hover:text-white transition-all duration-300"><?= $count_pengurus ?></div>
                        <div class="text-sm text-gray-400 font-medium uppercase tracking-wider group-hover:text-white transition-colors duration-300">Pengurus</div>
                    </a>
                    <a href="proker.php" class="bg-[#1E2F4D] p-8 rounded-2xl shadow-sm border border-white/20 text-center col-span-2 card-hover block group hover:bg-[#D4AF37] hover:border-[#D4AF37] transition-all duration-300">
                        <div class="text-5xl font-bold text-white mb-2 group-hover:scale-110 group-hover:text-white transition-all duration-300"><?= $count_proker ?></div>
                        <div class="text-sm text-gray-400 font-medium uppercase tracking-wider group-hover:text-white transition-colors duration-300">Program Kerja Aktif</div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Kerja Section -->
    <section id="proker" class="py-24 bg-batik-blue min-h-screen flex flex-col justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 gsap-fade-up">
            <h2 class="text-3xl font-bold text-center mb-4 text-white">Program Kerja Unggulan</h2>
            <p class="text-center text-gray-300 mb-16 max-w-2xl mx-auto">Dedikasi kami melalui program kerja nyata untuk
                memajukan mahasiswa Teknologi Pendidikan dan masyarakat luas.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8"
                x-data="{ prokers: (typeof dataProgram !== 'undefined' ? dataProgram : []).filter(p => p.unggulan).slice(0, 3) }">
                <template x-for="item in prokers" :key="item.id">
                    <a :href="item.slug ? 'detail-program.php?slug=' + item.slug : 'detail-program.php?id=' + item.id"
                        class="bg-[#1E2F4D] rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-white/20 group card-hover flex flex-col cursor-pointer block">
                        <div
                            class="w-full h-48 mb-6 overflow-hidden relative group-hover:shadow-md transition-shadow">
                            <img :src="item.gambar" :alt="item.judul"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 right-3 px-3 py-1 bg-[#1E2F4D]/90 backdrop-blur-sm rounded-full text-xs font-bold shadow-sm uppercase tracking-wider"
                                :class="'text-' + item.divisiColor + '-600'" x-text="item.divisi"></div>
                        </div>
                        <h3 class="p-6 pt-0 pb-0 text-xl font-bold mb-3 text-white" x-text="item.judul"></h3>
                        <p class="p-6 pt-0 pb-0 text-gray-300 mb-4 line-clamp-3 flex-1" x-text="item.ringkasan"></p>
                        <div
                            class="p-6 inline-flex items-center font-semibold hover:gap-2 transition-all mt-auto"
                            :class="item.divisiColor === 'blue' ? 'text-white' : 'text-' + item.divisiColor + '-600'">
                            Detail Program <span class="ml-1">&rarr;</span>
                        </div>
                    </a>
                </template>
            </div>

            <div class="mt-12 text-center">
                <a href="proker.php"
                    class="bg-himatep-green hover:bg-himatep-green/80 text-white px-8 py-3 rounded-full font-bold shadow-xl inline-flex items-center justify-center transition-colors duration-300">
                    Lihat Semua Program Kerja
                </a>
            </div>
        </div>
    </section>

    <!-- Kalender Section -->
    <section id="kalender" class="py-24 bg-batik-blue min-h-screen flex flex-col justify-center">
        <div class="max-w-6xl mx-auto px-4 w-full gsap-fade-up" x-data="calendarApp">
            <h2 class="text-3xl font-bold text-center mb-12 text-white">Agenda Kegiatan</h2>
            <div class="bg-[#1E2F4D] rounded-3xl shadow-xl border border-white/20 p-4 md:p-8">
                <div class="flex justify-between items-center mb-6 md:mb-8">
                    <button @click="prevMonth()" class="p-2 md:p-3 bg-[#1E2F4D] rounded-full hover:bg-himatep-light transition text-gray-200 hover:text-white"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg></button>
                    <h3 class="text-xl md:text-2xl font-bold text-white" x-text="monthNames[month] + ' ' + year"></h3>
                    <button @click="nextMonth()" class="p-2 md:p-3 bg-[#1E2F4D] rounded-full hover:bg-himatep-light transition text-gray-200 hover:text-white"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></button>
                </div>
                <div class="grid grid-cols-7 gap-1.5 md:gap-3 text-center mb-4 font-bold text-gray-400 uppercase text-xs md:text-sm">
                    <div>Min</div>
                    <div>Sen</div>
                    <div>Sel</div>
                    <div>Rab</div>
                    <div>Kam</div>
                    <div>Jum</div>
                    <div>Sab</div>
                </div>
                <div class="grid grid-cols-7 gap-1.5 md:gap-3">
                    <template x-for="(day, index) in days" :key="index">
                        <div class="h-12 md:h-32 border rounded-xl p-1.5 md:p-3 flex flex-col justify-between transition-all"
                            :class="{
                                'bg-[#1E2F4D]/50 border-transparent opacity-40': day.empty, 
                                'bg-[#1E2F4D] border-white/10 hover:border-himatep-green cursor-pointer': !day.empty && !day.event, 
                                'bg-blue-50 border-himatep-green/40 hover:border-himatep-green cursor-pointer shadow-sm transform hover:-translate-y-1': !day.empty && day.event
                            }"
                            @click="!day.empty && day.event ? showEvent(day.event) : null">
                            <span x-show="!day.empty" class="text-xs md:text-sm font-bold block text-right"
                                :class="{'text-white font-extrabold': day.event, 'text-gray-300': !day.event}"
                                x-text="day.date"></span>
                            
                            <!-- On Desktop: Show Title Text -->
                            <span x-show="day.event"
                                class="hidden md:block text-[11px] bg-himatep-green text-white rounded-lg p-1.5 truncate mt-1 font-medium text-left leading-none"
                                x-text="day.event ? day.event.title : ''"></span>
                            
                            <!-- On Mobile: Show Centered Dot Indicator -->
                            <div x-show="day.event" class="md:hidden flex justify-center pb-1 mt-auto">
                                <span class="w-2.5 h-2.5 rounded-full bg-himatep-green animate-pulse"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Detail Event (Premium Card Style) -->
            <template x-teleport="body">
                <div x-show="modalOpen"
                    class="fixed inset-0 z-[120] flex items-center justify-center p-4" x-cloak
                    style="display: none;">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="modalOpen = false"></div>
                    
                    <!-- Card Modal -->
                    <div class="event-modal-container bg-[#1E2F4D] rounded-[2rem] shadow-2xl max-w-2xl w-full relative overflow-hidden flex flex-col md:flex-row transform transition-all z-10"
                        x-show="modalOpen"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                        
                        <!-- Close Button -->
                        <button @click="modalOpen = false" class="absolute top-4 right-4 z-30 bg-[#1E2F4D]/95 backdrop-blur p-2 rounded-full shadow-md hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>

                        <!-- Image Side (Left on Desktop, Top on Mobile) -->
                        <div class="event-modal-image-side md:w-2/5 h-48 md:h-auto relative overflow-hidden bg-gray-100 flex-shrink-0">
                            <img :src="selectedEvent?.gambar" 
                                 class="w-full h-full object-cover">
                            <!-- Date Badge Overlay -->
                            <div x-show="selectedEvent?.agenda" 
                                 class="absolute top-4 left-4 text-white rounded-2xl p-2.5 flex flex-col justify-center items-center shadow-lg min-w-[65px]"
                                 :class="selectedEvent?.divisiColor ? 'bg-' + selectedEvent.divisiColor + '-600' : 'bg-himatep-green'">
                                <span class="text-[10px] font-bold uppercase tracking-wider opacity-90" x-text="selectedEvent?.agenda?.bulan"></span>
                                <span class="text-2xl font-black leading-none" x-text="selectedEvent?.agenda?.tanggal"></span>
                            </div>
                        </div>

                        <!-- Content Side (Right on Desktop, Bottom on Mobile) -->
                        <div class="event-modal-content-side md:w-3/5 p-6 md:p-8 flex flex-col justify-between">
                            <div>
                                <!-- Division Badge -->
                                <div class="mb-3">
                                    <span class="text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider bg-gray-100 text-gray-300 border border-white/10"
                                          x-text="selectedEvent?.divisi ? 'Divisi ' + selectedEvent.divisi : 'HIMATEP'"></span>
                                </div>
                                <h3 class="text-xl md:text-2xl font-bold mb-4 text-white leading-snug" x-text="selectedEvent?.title"></h3>
                                <p class="text-gray-300 text-sm leading-relaxed mb-6" x-text="selectedEvent?.desc || 'Tidak ada ringkasan tersedia untuk kegiatan ini.'"></p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row gap-3 mt-auto">
                                <a x-show="selectedEvent?.slug" :href="'detail-program.php?slug=' + selectedEvent?.slug"
                                    class="flex-1 bg-himatep-green hover:opacity-90 text-white text-center font-bold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-sm">
                                    <span>Buka Informasi</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                                <button @click="modalOpen = false"
                                    class="sm:w-24 bg-gray-100 hover:bg-gray-200 text-gray-400 font-bold py-2.5 rounded-xl transition-colors text-sm">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Agenda Mendatang (Bawah Kalender) -->
            <div class="mt-16">
                <div class="mb-8 text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">Agenda Mendatang</h3>
                    <p class="text-gray-300">Jadwal kegiatan terdekat yang tidak boleh Anda lewatkan</p>
                </div>

                <!-- Grid 3 Agenda -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8"
                    x-data="{ agendas: (typeof dataProgram !== 'undefined' ? dataProgram : []).filter(p => p.agenda).sort((a,b) => new Date(a.agenda.date) - new Date(b.agenda.date)).slice(0, 3) }">
                    <template x-for="item in agendas" :key="item.id">
                        <a :href="'detail-program.php?slug=' + item.slug"
                            class="card-hover flex flex-col bg-[#1E2F4D] rounded-3xl overflow-hidden shadow-sm border border-white/20 hover:shadow-xl hover:border-[#D4AF37] transition-all duration-300 group cursor-pointer">
                            <div class="relative h-48 w-full overflow-hidden">
                                <img :src="item.gambar" :alt="item.judul"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <!-- Date Badge (Gold theme) -->
                                <div class="absolute top-4 right-4 text-white rounded-2xl p-2 flex flex-col justify-center items-center shadow-lg min-w-[70px] bg-[#D4AF37]">
                                    <span class="text-xs font-bold uppercase tracking-wider opacity-90"
                                        x-text="item.agenda.bulan"></span>
                                    <span class="text-2xl font-black leading-none" x-text="item.agenda.tanggal"></span>
                                </div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold text-white mb-4 group-hover:text-[#D4AF37] transition-colors"
                                    x-text="item.judul"></h3>
                                <div class="space-y-3 mt-auto">
                                    <div class="flex items-center text-sm font-medium text-gray-400">
                                        <svg class="w-5 h-5 mr-3 text-[#D4AF37]"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span x-text="item.agenda.waktu"></span>
                                    </div>
                                    <div class="flex items-center text-sm font-medium text-gray-400">
                                        <svg class="w-5 h-5 mr-3 text-[#D4AF37]"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            </div>

        </div>
    </section>

    <!-- Berita Section -->
    <section id="berita" class="py-24 bg-batik-blue min-h-screen flex flex-col justify-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 gsap-fade-up">
            <h2 class="text-3xl font-bold text-center mb-16 text-white">Berita Terkini</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (empty($berita_list)): ?>
                    <div class="col-span-full text-center py-10 text-gray-400">
                        Belum ada berita yang dipublikasikan.
                    </div>
                <?php else: ?>
                    <?php foreach ($berita_list as $berita): ?>
                        <a href="detail-berita.php?slug=<?php echo htmlspecialchars($berita['slug']); ?>"
                            class="block border border-white/20 bg-[#1E2F4D] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 card-hover flex flex-col cursor-pointer">
                            <img src="<?php echo htmlspecialchars($berita['gambar']); ?>"
                                alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full h-48 object-cover">
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="mb-3">
                                    <span
                                        class="text-xs font-bold text-<?php echo $berita['kategori_color']; ?>-600 bg-<?php echo $berita['kategori_color']; ?>-100 px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($berita['kategori']); ?>
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold mb-2 text-white line-clamp-2"><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                <p class="text-gray-300 text-sm mb-4 line-clamp-3 flex-1">
                                    <?php echo htmlspecialchars($berita['ringkasan']); ?>
                                </p>
                                <div class="text-white font-semibold hover:underline mt-auto pt-2">
                                    Baca selengkapnya &rarr;
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-12 text-center">
                <a href="berita.php"
                    class="bg-himatep-green hover:bg-himatep-green/80 text-white px-8 py-3 rounded-full font-bold shadow-xl inline-flex items-center justify-center transition-colors duration-300">
                    Lihat Semua Berita
                </a>
            </div>
        </div>
    </section>

    <!-- Suara Mahasiswa -->
    <section id="aspirasi" class="py-24 bg-batik-blue min-h-screen flex flex-col justify-center">
        <div class="max-w-3xl mx-auto px-4 gsap-fade-up">
            <h2 class="text-3xl font-bold text-center mb-4 text-white">Suara Mahasiswa</h2>
            <p class="text-center text-gray-300 mb-12">Sampaikan aspirasi, kritik, dan saran Anda untuk kemajuan
                bersama.</p>

            <?php if(isset($_GET['status'])): ?>
                <div id="notif-aspirasi">
                    <?php if($_GET['status'] == 'success'): ?>
                        <div class="bg-himatep-light border border-himatep-green text-white px-4 py-3 rounded-2xl mb-8 text-center shadow-lg">
                            <strong>Berhasil!</strong> Aspirasi Anda telah kami terima. Terima kasih!
                        </div>
                    <?php elseif($_GET['status'] == 'error'): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl mb-8 text-center shadow-lg">
                            <strong>Gagal!</strong> Terjadi kesalahan saat mengirim. Silakan coba lagi.
                            <?php if(isset($_GET['msg'])): ?>
                                <br><span class="text-xs opacity-75 italic">(Error: <?= htmlspecialchars($_GET['msg']) ?>)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                    // Hapus parameter URL agar tidak muncul lagi saat di-refresh
                    if (window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.delete('status');
                        url.searchParams.delete('msg');
                        window.history.replaceState({}, document.title, url.pathname + url.hash);
                    }
                    
                    // Hilangkan notifikasi secara halus setelah 5 detik
                    setTimeout(() => {
                        const notif = document.getElementById('notif-aspirasi');
                        if (notif) {
                            notif.style.transition = 'opacity 1s ease';
                            notif.style.opacity = '0';
                            setTimeout(() => notif.remove(), 1000);
                        }
                    }, 5000);
                </script>
            <?php endif; ?>

            <form action="php/submit_aspirasi.php" method="POST"
                class="bg-[#1E2F4D] p-8 rounded-3xl shadow-lg border border-white/20">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-200 text-sm font-bold mb-2">Nama Lengkap (Opsional)</label>
                        <input type="text" name="nama"
                            class="w-full px-4 py-3 bg-[#1E2F4D] border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green transition"
                            placeholder="Samaran dibolehkan">
                    </div>
                    <div>
                        <label class="block text-gray-200 text-sm font-bold mb-2">Gmail (Opsional)</label>
                        <input type="email" name="email"
                            class="w-full px-4 py-3 bg-[#1E2F4D] border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green transition"
                            placeholder="Untuk balasan">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-200 text-sm font-bold mb-2">Jenis Suara</label>
                    <select name="jenis"
                        class="w-full px-4 py-3 bg-[#1E2F4D] border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green transition">
                        <option value="Aspirasi">Aspirasi Program</option>
                        <option value="Kritik">Kritik Membangun</option>
                        <option value="Saran">Saran Inovasi</option>
                        <option value="Laporan">Laporan Fasilitas</option>
                    </select>
                </div>
                <div class="mb-8">
                    <label class="block text-gray-200 text-sm font-bold mb-2">Pesan Anda *</label>
                    <textarea name="pesan" rows="5"
                        class="w-full px-4 py-3 bg-[#1E2F4D] border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-himatep-green transition"
                        required placeholder="Tuliskan pesan Anda di sini..."></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-himatep-green hover:opacity-90 text-white font-bold py-4 px-6 rounded-xl transition shadow-lg transform hover:-translate-y-1">Kirim
                    Aspirasi</button>
            </form>
        </div>
    </section>

    <!-- Narahubung -->
    <section id="kontak" class="py-24 mb-16 bg-batik-blue flex flex-col justify-center">
        <div class="max-w-7xl mx-auto px-4 w-full text-center gsap-fade-up">
            <h2 class="text-3xl font-bold mb-4 text-white">Narahubung</h2>
            <p class="text-gray-400 mb-16 max-w-2xl mx-auto">Butuh informasi lebih lanjut? Silakan hubungi kami melalui platform di bawah ini. Tim kami siap membantu Anda.</p>
            
            <div class="flex flex-wrap justify-center gap-12">
                <?php foreach ($contacts as $contact): 
                    $link = $contact['value'];
                    if ($contact['platform'] === 'WhatsApp' && strpos($link, 'http') !== 0) {
                        $link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $link);
                    }
                ?>
                    <!-- Flip Card GSAP -->
                    <div class="w-52 h-80 flip-card cursor-pointer group">
                        <div class="flip-card-inner shadow-xl rounded-2xl">
                            <!-- Front -->
                            <div class="rounded-2xl border border-white/20 flip-card-front flex flex-col items-center justify-center p-6 bg-gradient-to-br from-white to-himatep-light">
                                <div class="w-20 h-20 bg-[#111A2C] rounded-full mb-4 flex items-center justify-center shadow-inner group-hover:bg-[#D4AF37] transition-colors">
                                    <?= get_contact_svg($contact['icon'], 'w-10 h-10 text-white') ?>
                                </div>
                                <h3 class="text-xl font-bold text-white text-center"><?= htmlspecialchars($contact['label']) ?></h3>
                                <p class="text-white mt-2 font-medium bg-himatep-green px-3 py-0.5 rounded-full text-[10px]">
                                    <?= $contact['platform'] ?>
                                </p>
                            </div>
                            <!-- Back -->
                            <div class="flip-card-back p-6 rounded-2xl bg-himatep-dark flex flex-col items-center text-center justify-center">
                                <div class="space-y-4 mb-8 text-green-50 w-full">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-wider text-green-300"><?= $contact['platform'] ?></p>
                                        <?php 
                                            $val = str_replace(['https://wa.me/', 'mailto:'], '', $contact['value']);
                                            if ($contact['platform'] === 'WhatsApp') {
                                                $display = '+' . preg_replace('/[^0-9]/', '', $val);
                                            } else {
                                                $display = htmlspecialchars($contact['label']);
                                            }
                                        ?>
                                        <p class="font-bold text-lg break-all leading-tight"><?= $display ?></p>
                                        <?php if ($contact['platform'] !== 'WhatsApp'): ?>
                                            <p class="text-[10px] text-green-200 opacity-60 truncate w-full"><?= htmlspecialchars($val) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?= $link ?>" target="_blank"
                                    class="bg-[#1E2F4D] text-white px-4 py-2 rounded-full text-xs font-bold hover:bg-gray-100 transition shadow-lg w-full">
                                    <?= $contact['platform'] === 'Social Media' ? 'Kunjungi' : 'Hubungi' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>


    <!-- Scripts External -->
    <script>
        const dataProgram = <?php echo $data_program_json; ?>;
    </script>
    <script src="js/calendar.js?v=<?= time() ?>"></script>
    <script src="js/animations.js"></script>
    <script src="js/main.js?v=<?= time() ?>"></script>
</body>

</html>

