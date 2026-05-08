<?php
require_once '../private/php/config.php';

// Ambil Proker (untuk Agenda di Profile)
try {
    $stmt = $pdo->query("
        SELECT p.*, a.tanggal_event, a.waktu, a.lokasi 
        FROM proker p 
        LEFT JOIN agenda a ON p.id = a.proker_id 
        WHERE a.tanggal_event IS NOT NULL
        ORDER BY a.tanggal_event ASC
    ");
    $agenda_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $agenda_list = [];
}

// Persiapkan data untuk Alpine.js
$data_program_json = json_encode(array_map(function($p) {
    $date = $p['tanggal_event'] ? new DateTime($p['tanggal_event']) : null;
    return [
        'id' => $p['id'],
        'slug' => $p['slug'],
        'judul' => $p['judul'],
        'divisiColor' => $p['divisi_color'] ?? 'blue',
        'gambar' => $p['gambar'] ?: 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800&q=80',
        'agenda' => $date ? [
            'date' => $p['tanggal_event'],
            'bulan' => strtoupper($date->format('M')),
            'tanggal' => $date->format('d'),
            'waktu' => $p['waktu'] ?? '09:00',
            'lokasi' => $p['lokasi'] ?? 'Kampus UNM'
        ] : null
    ];
}, $agenda_list));

// TAMBAHKAN DUMMY DATA JIKA KOSONG (AGENDA) - RESTORE FALLBACK
if (empty($agenda_list)) {
    $data_program_json = json_encode([
        [
            'id' => 1, 'slug' => 'dummy-1', 'judul' => 'Workshop Pengembangan Diri', 'divisiColor' => 'blue', 'gambar' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800&q=80',
            'agenda' => ['date' => '2026-06-15', 'bulan' => 'JUN', 'tanggal' => '15', 'waktu' => '09:00 - 12:00', 'lokasi' => 'Aula FIP UNM']
        ],
        [
            'id' => 2, 'slug' => 'dummy-2', 'judul' => 'Seminar Teknologi Pendidikan', 'divisiColor' => 'green', 'gambar' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=800&q=80',
            'agenda' => ['date' => '2026-07-20', 'bulan' => 'JUL', 'tanggal' => '20', 'waktu' => '13:00 - 16:00', 'lokasi' => 'Ruang Seminar Lt.3']
        ],
        [
            'id' => 3, 'slug' => 'dummy-3', 'judul' => 'Pelatihan Media Pembelajaran', 'divisiColor' => 'purple', 'gambar' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&q=80',
            'agenda' => ['date' => '2026-08-10', 'bulan' => 'AGT', 'tanggal' => '10', 'waktu' => '08:00 - 15:00', 'lokasi' => 'Lab Komputer FIP']
        ]
    ]);
}

// Ambil Data Pengurus (BPH & Divisi)
try {
    $stmt = $pdo->query("SELECT * FROM pengurus ORDER BY urutan ASC");
    $semua_pengurus = $stmt->fetchAll();
    
    $bph_list = [];
    $divisi_list = [];
    
    foreach ($semua_pengurus as $p) {
        if (strtoupper($p['divisi']) === 'BPH') {
            $bph_list[] = $p;
        } else {
            $divisi_list[$p['divisi']][] = $p;
        }
    }
} catch (PDOException $e) {
    $bph_list = [];
    $divisi_list = [];
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profil Lengkap HIMATEP FIP UNM. Kenali visi, misi, struktur organisasi, dan sejarah Himpunan Mahasiswa Teknologi Pendidikan UNM.">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Profil Organisasi - HIMATEP FIP UNM">
    <meta property="og:description" content="Kenali visi, misi, dan struktur organisasi HIMATEP FIP UNM.">

    <title>Profil Organisasi - HIMATEP FIP UNM</title>
    <?php include 'includes/meta_icons.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#2563EB',
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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- GSAP & ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <style>
        /* CSS untuk Org Chart */
        .org-tree {
            display: flex;
            justify-content: center;
            overflow-x: auto;
            padding-bottom: 20px;
        }
        .org-tree ul {
            padding-top: 40px;
            position: relative;
            transition: all 0.5s;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .org-tree li {
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 40px 5px 0 5px;
            transition: all 0.5s;
        }
        .org-tree li::before, .org-tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #ccc;
            width: 50%;
            height: 40px;
        }
        .org-tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #ccc;
        }
        .org-tree li:only-child::after, .org-tree li:only-child::before {
            display: none;
        }
        .org-tree li:only-child {
            padding-top: 0;
        }
        /* KSB Modern Cards - Exact Match to Image */
        .ksb-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 3rem;
            justify-content: center;
            padding: 3rem 0;
        }

        @media (min-width: 768px) {
            .ksb-wrapper {
                gap: 2rem;
            }
        }

        .ksb-card {
            position: relative;
            width: 100%;
            max-width: 350px;
            height: 520px;
            background: white;
            overflow: hidden;
            transform: skewX(-12deg); 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid #2563EB; 
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.1);
            cursor: pointer;
        }

        .ksb-card:hover {
            transform: skewX(-12deg) translateY(-10px);
            box-shadow: 0 25px 50px rgba(37, 99, 235, 0.25);
        }

        .ksb-image-container {
            position: absolute;
            inset: 0;
            width: 140%; 
            left: -20%;
            transform: skewX(12deg); 
        }

        .ksb-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }

        .ksb-card:hover .ksb-image-container img {
            scale: 1.05;
        }

        .ksb-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, #2563EB 0%, #2563EB 25%, rgba(37, 99, 235, 0.6) 40%, transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 2rem 2rem;
        }

        .ksb-content {
            transform: skewX(12deg); 
            text-align: left;
        }

        .ksb-nama {
            font-size: 2.25rem;
            font-weight: 900;
            color: white;
            font-style: italic; 
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .ksb-jabatan {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            font-style: italic; 
            opacity: 0.95;
            text-transform: capitalize;
        }
    </style>
    <style>
        .org-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px auto;
            border: 2px solid #2563EB;
            padding: 2px;
            background: white;
        }

        /* 3D Flip Animation for Modal */
        .perspective-1000 {
            perspective: 1000px;
        }
        .flip-enter {
            animation: flipIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .flip-leave {
            animation: flipOut 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes flipIn {
            0% { transform: rotateY(-90deg) scale(0.9); opacity: 0; }
            100% { transform: rotateY(0deg) scale(1); opacity: 1; }
        }
        @keyframes flipOut {
            0% { transform: rotateY(0deg) scale(1); opacity: 1; }
            100% { transform: rotateY(90deg) scale(0.9); opacity: 0; }
        }

        /* Mobile overrides */
        @media (max-width: 768px) {
            .org-tree ul {
                flex-direction: column;
                align-items: center;
                padding-top: 0;
            }
            .org-tree li {
                padding: 10px 0;
                width: 100%;
            }
            .org-tree li::before, .org-tree li::after, .org-tree ul ul::before {
                display: none;
            }
            .org-tree li:not(:last-child) {
                border-bottom: 2px solid #ccc;
                padding-bottom: 20px;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<!-- Alpine State untuk Modal ada di tag body -->
<body class="font-sans bg-gray-50 text-himatep-dark overflow-x-hidden" 
      x-data="{ 
          mobileMenuOpen: false,
          isModalOpen: false,
          modalData: {nama: '', jabatan: '', foto: '', deskripsi: ''},
          openModal(nama, jabatan, foto, deskripsi) {
              this.modalData = {nama, jabatan, foto, deskripsi};
              this.isModalOpen = true;
          },
          closeModal() {
              this.isModalOpen = false;
              setTimeout(() => {
                  this.modalData = {nama: '', jabatan: '', foto: '', deskripsi: ''};
              }, 400);
          }
      }">

    <!-- Navbar -->
    <nav class="fixed w-full z-[100] pt-4" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20 bg-white backdrop-blur-xl rounded-full px-6 shadow-md border border-gray-400 transition-all duration-300">
                <div class="flex items-center gap-3">
                    <img src="images/logo-himatep.png" alt="Logo" class="h-10 w-10 rounded-full bg-gray-100" onerror="this.src='https://via.placeholder.com/50x50.png?text=Logo'">
                    <span class="font-bold text-xs md:text-sm leading-tight text-himatep-dark">HIMATEP<br>FIP<br>UNM</span>
                </div>
                <div class="hidden md:flex space-x-4 lg:space-x-8 text-sm lg:text-base">
                    <a href="index.php#hero" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Beranda</a>
                    <a href="#" class="nav-link whitespace-nowrap text-himatep-green font-medium hover:text-himatep-green transition">Profile</a>
                    <a href="index.php#proker" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Program Kerja</a>
                    <a href="index.php#kalender" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Agenda</a>
                    <a href="index.php#berita" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Berita</a>
                    <a href="index.php#aspirasi" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Suara Mahasiswa</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <a href="admin/login.php" class="text-gray-400 hover:text-himatep-green transition-all p-2 rounded-full hover:bg-blue-50" title="Admin Panel">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </a>
                    <div class="relative" x-data="{ dropdownOpen: false }" @mouseenter="dropdownOpen = true" @mouseleave="dropdownOpen = false">
                        <a href="#kontak" class="bg-blue-400 hover:bg-blue-500 text-himatep-dark px-6 py-2 rounded-full font-medium transition shadow-md flex items-center gap-2 focus:outline-none">
                            Narahubung <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </a>
                        <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 border border-gray-400 z-50" style="display: none;">
                            <a href="index.php#kontak" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-himatep-green transition flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4light-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> WhatsApp</a>
                        </div>
                    </div>
                </div>
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display:none;" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="mobileMenuOpen" class="md:hidden bg-white shadow-lg absolute w-full mt-2 rounded-b-2xl" x-transition style="display:none;">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a @click="mobileMenuOpen = false" href="index.php#hero" class="nav-link block px-3 py-2 text-gray-600 font-medium">Beranda</a>
                <a @click="mobileMenuOpen = false" href="#" class="nav-link block px-3 py-2 text-himatep-green font-medium">Profile</a>
                <a @click="mobileMenuOpen = false" href="index.php#proker" class="nav-link block px-3 py-2 text-gray-600 font-medium">Program Kerja</a>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <section class="pt-40 pb-24 relative overflow-hidden min-h-[800px] flex items-center justify-center bg-himatep-dark">
        <img src="https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80" alt="Background Profil Organisasi" class="absolute inset-0 w-full h-full object-cover z-0 opacity-40 mix-blend-luminosity">
        <div class="absolute inset-0 bg-gradient-to-t from-himatep-green/40 to-transparent z-0"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-lg">Profil Organisasi</h1>
            <p class="text-xl text-gray-100 max-w-2xl mx-auto font-medium drop-shadow-md">Mengenal lebih dekat struktur kepengurusan dan divisi-divisi di HIMATEP FIP UNM.</p>
        </div>
    </section>

    <!-- Struktur Organisasi (KSB Highlight) -->
    <section class="py-24 bg-white relative z-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-gray-900 mb-4 uppercase tracking-tighter">BPH Inti <span class="text-himatep-green">HIMATEP</span></h2>
                <div class="flex items-center justify-center gap-4">
                    <span class="h-px w-12 bg-gray-300"></span>
                    <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">KSB</p>
                    <span class="h-px w-12 bg-gray-300"></span>
                </div>
            </div>

            <div class="ksb-wrapper" style="opacity: 1 !important; visibility: visible !important;">
                <?php 
                if (!empty($bph_list)): 
                    foreach ($bph_list as $p): 
                        // Deteksi jika path adalah URL eksternal atau file lokal
                        $foto_path = (strpos($p['foto'], 'http') === 0) ? $p['foto'] : 'images/pengurus/' . ($p['foto'] ?? 'default.png');
                ?>
                    <div class="ksb-card group" @click="openModal('<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['jabatan'], ENT_QUOTES) ?>', '<?= $foto_path ?>', '<?= htmlspecialchars($p['deskripsi'] ?? 'Tidak ada deskripsi', ENT_QUOTES) ?>')">
                        <div class="ksb-image-container">
                            <img src="<?= $foto_path ?>" onerror="this.src='images/logo-himatep.png'" alt="<?= htmlspecialchars($p['nama']) ?>">
                        </div>
                        <div class="ksb-overlay">
                            <div class="ksb-content transition-transform duration-500 group-hover:translate-x-2">
                                <p class="ksb-jabatan"><?= htmlspecialchars($p['jabatan']) ?></p>
                                <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else: 
                ?>
                    <div class="col-span-full text-center py-12 text-gray-400 italic">Belum ada data pengurus BPH.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Agenda Section -->
    <section class="py-20 bg-gray-50 gsap-fade-up border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 text-center md:text-left">
                <div>
                    <h2 class="text-3xl font-bold text-himatep-green mb-2">Agenda Mendatang</h2>
                    <p class="text-gray-600">Jadwal kegiatan terdekat yang tidak boleh Anda lewatkan</p>
                </div>
                <a href="index.php#kalender"
                    class="mt-4 md:mt-0 inline-flex items-center text-himatep-green font-semibold hover:underline">
                    Lihat Kalender Penuh <span class="ml-2">&rarr;</span>
                </a>
            </div>

            <!-- Dynamic Agenda Grid (Sync with Proker.php) -->
            <div class="flex flex-wrap justify-center gap-8" 
                 x-data="{ agendas: dataProgram.filter(p => p.agenda).sort((a,b) => new Date(a.agenda.date) - new Date(b.agenda.date)).slice(0, 3) }">
                <template x-for="item in agendas" :key="item.id">
                    <a :href="'detail-program.php?slug=' + item.slug" 
                       class="flex flex-col bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-400 hover:shadow-xl transition-all duration-300 group cursor-pointer w-full md:w-[calc(33.333%-2rem)] min-w-[300px]">
                        <div class="relative h-48 w-full overflow-hidden">
                            <img :src="item.gambar" :alt="item.judul" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute top-4 right-4 text-white rounded-2xl p-2 flex flex-col justify-center items-center shadow-lg min-w-[70px]"
                                 :style="'background-color: ' + (item.divisiColor === 'blue' ? '#2563EB' : item.divisiColor === 'green' ? '#16A34A' : item.divisiColor === 'purple' ? '#9333EA' : '#059669')">
                                <span class="text-xs font-bold uppercase tracking-wider opacity-90" x-text="item.agenda.bulan"></span>
                                <span class="text-2xl font-black leading-none" x-text="item.agenda.tanggal"></span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col text-left">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 transition-colors"
                                :style="item.divisiColor === 'blue' ? '--hover-color: #2563EB' : item.divisiColor === 'green' ? '--hover-color: #16A34A' : '--hover-color: #9333EA'"
                                :class="'group-hover:text-[var(--hover-color)]'" x-text="item.judul"></h3>
                            <div class="space-y-3 mt-auto">
                                <div class="flex items-center text-sm font-medium text-gray-500">
                                    <svg class="w-5 h-5 mr-3" :style="'color: ' + (item.divisiColor === 'blue' ? '#2563EB' : item.divisiColor === 'green' ? '#16A34A' : '#9333EA')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="item.agenda.waktu"></span>
                                </div>
                                <div class="flex items-center text-sm font-medium text-gray-500">
                                    <svg class="w-5 h-5 mr-3" :style="'color: ' + (item.divisiColor === 'blue' ? '#2563EB' : item.divisiColor === 'green' ? '#16A34A' : '#9333EA')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span x-text="item.agenda.lokasi"></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-himatep-dark text-white py-16 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 mt-12 pt-8 border-t border-gray-800 text-center text-gray-500 text-sm">
            &copy; 2026 HIMATEP FIP UNM. All rights reserved. Designed with ❤️
        </div>
    </footer>

    <!-- MODAL POPUP (PREVIEW PENGURUS) -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[110] flex items-center justify-center perspective-1000 p-4">
        <!-- Blurred Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
             x-show="isModalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="closeModal()"></div>
        
        <!-- Card Content with 3D Flip Animation -->
        <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden z-10"
             x-show="isModalOpen"
             x-transition:enter="flip-enter"
             x-transition:leave="flip-leave"
             @click.stop>
             
            <!-- Close Button -->
            <button @click="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-black z-20 bg-white/80 p-2 rounded-full shadow-sm backdrop-blur">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <!-- Photo Side (Kiri) -->
            <div class="md:w-2/5 bg-gray-100 flex flex-col items-center justify-end relative overflow-hidden min-h-[300px]">
                <!-- Decorative Circle -->
                <div class="absolute w-64 h-64 bg-gray-200 rounded-full -top-10 left-10 z-0"></div>
                <img :src="modalData.foto" :alt="modalData.nama" onerror="this.src='images/logo-himatep.png'" class="w-full h-full object-cover relative z-10 drop-shadow-lg max-h-[400px] object-center">
            </div>
            
            <!-- Info Side (Kanan) -->
            <div class="md:w-3/5 p-8 md:p-10 flex flex-col justify-center bg-white relative z-10">
                <span class="text-xs font-bold text-yellow-600 bg-yellow-50 px-4 py-1.5 rounded-full w-max mb-4 uppercase tracking-wider border border-yellow-200" x-text="modalData.jabatan"></span>
                
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6 leading-tight" x-text="modalData.nama"></h2>
                
                <div class="mt-2">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Deskripsi</h3>
                    <p class="text-gray-600 text-sm leading-relaxed" x-text="modalData.deskripsi || 'Tidak ada deskripsi yang ditambahkan untuk pengurus ini.'"></p>
                </div>

                <div class="mt-8 flex items-center opacity-30 grayscale">
                    <img src="images/logo-himatep.png" alt="Logo" class="w-8 h-8">
                </div>
            </div>
        </div>
    </div>

    <script>
        const dataProgram = <?php echo $data_program_json; ?>;
        console.log('Data Program loaded:', dataProgram);
    </script>
    <script src="js/animations.js?v=1.1"></script>
    <script src="js/main.js"></script>
</body>
</html>