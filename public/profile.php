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

// Ambil Data Banner
try {
    $stmt = $pdo->prepare("SELECT * FROM halaman_banner WHERE halaman = 'profile'");
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

// Ambil Data Pengurus (BPH, Ketua Divisi, & Anggota)
try {
    $stmt = $pdo->query("SELECT * FROM pengurus ORDER BY urutan ASC");
    $semua_pengurus = $stmt->fetchAll();
    
    $bph_list = [];
    $ketua_divisi_list = [];
    $anggota_list = [];
    
    foreach ($semua_pengurus as $p) {
        $divisi_upper = strtoupper(trim($p['divisi']));
        $jabatan_trim = trim($p['jabatan']);
        
        if ($divisi_upper === 'BPH') {
            $bph_list[] = $p;
        } elseif ($jabatan_trim === 'Ketua Divisi') {
            $ketua_divisi_list[] = $p;
        } else {
            $anggota_list[] = $p;
        }
    }
} catch (PDOException $e) {
    $bph_list = [];
    $ketua_divisi_list = [];
    $anggota_list = [];
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
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=2.7">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- GSAP & ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <style>
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
            max-width: 210px;
            height: 315px;
            background: white;
            overflow: hidden;
            transform: skewX(-12deg); 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid #1B2945; 
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
            background: linear-gradient(to top, #1B2945 0%, #1B2945 25%, rgba(27, 41, 69, 0.6) 40%, transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.1rem 1.1rem;
        }

        .ksb-content {
            transform: skewX(12deg); 
            text-align: left;
        }

        .ksb-nama {
            font-size: 1.4rem;
            font-weight: 900;
            color: white;
            font-style: italic; 
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .ksb-jabatan {
            font-size: 0.7rem;
            font-weight: 700;
            color: white;
            font-style: italic; 
            opacity: 0.95;
            text-transform: capitalize;
        }
    </style>
    <style>
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
    </style>
</head>

<body class="font-sans theme-custom bg-[#1E2F4D] text-white overflow-x-hidden" 
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
    <section id="profile" style="scroll-margin-top:100px"></section>

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
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-lg"><?= htmlspecialchars($banner['judul'] ?? 'Profil Organisasi') ?></h1>
            <p class="text-xl text-gray-100 max-w-2xl mx-auto font-medium drop-shadow-md"><?= htmlspecialchars($banner['subjudul'] ?? 'Mengenal lebih dekat struktur kepengurusan dan divisi-divisi di HIMATEP FIP UNM.') ?></p>
        </div>
    </section>

    <!-- Struktur Organisasi -->
    <section id="pengurus" class="py-24 bg-batik-blue relative z-10 scroll-mt-24">
        <div class="max-w-7xl mx-auto px-4">
            <!-- BPH INTI -->
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white mb-4 uppercase tracking-tighter">BPH Inti <span class="text-amber-500">HIMATEP</span></h2>
                <div class="flex items-center justify-center gap-4">
                    <span class="h-px w-12 bg-gray-300"></span>
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">KSB</p>
                    <span class="h-px w-12 bg-gray-300"></span>
                </div>
            </div>

            <div class="ksb-wrapper" style="opacity: 1 !important; visibility: visible !important;">
                <?php 
                if (!empty($bph_list)): 
                    foreach ($bph_list as $p): 
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
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12 text-gray-400 italic">Belum ada data pengurus BPH.</div>
                <?php endif; ?>
            </div>

            <?php if (!empty($ketua_divisi_list)): ?>
            <!-- BIDANG Section -->
            <div id="divisi" class="text-center mt-24 mb-16 scroll-mt-24">
                <h2 class="text-4xl font-black text-white mb-4 uppercase tracking-tighter">Divisi <span class="text-amber-500">Himatep</span></h2>
                <div class="flex items-center justify-center gap-4">
                    <span class="h-px w-12 bg-gray-300"></span>
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Ketua Divisi</p>
                    <span class="h-px w-12 bg-gray-300"></span>
                </div>
            </div>

            <div class="ksb-wrapper" style="opacity: 1 !important; visibility: visible !important;">
                <?php 
                foreach ($ketua_divisi_list as $p): 
                    $foto_path = (strpos($p['foto'], 'http') === 0) ? $p['foto'] : 'images/pengurus/' . ($p['foto'] ?? 'default.png');
                    // Format Jabatan + Divisi (Ketua Divisi -> Ketua)
                    $jab_clean = str_replace('Ketua Divisi', 'Ketua', $p['jabatan']);
                    $jabatan_display = $jab_clean . ' ' . $p['divisi'];
                ?>
                    <div class="ksb-card group" @click="openModal('<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($jabatan_display, ENT_QUOTES) ?>', '<?= $foto_path ?>', '<?= htmlspecialchars($p['deskripsi'] ?? 'Tidak ada deskripsi', ENT_QUOTES) ?>')">
                        <div class="ksb-image-container">
                            <img src="<?= $foto_path ?>" onerror="this.src='images/logo-himatep.png'" alt="<?= htmlspecialchars($p['nama']) ?>">
                        </div>
                        <div class="ksb-overlay">
                            <div class="ksb-content transition-transform duration-500 group-hover:translate-x-2">
                                <p class="ksb-jabatan"><?= htmlspecialchars($jabatan_display) ?></p>
                                <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($anggota_list)): ?>
            <!-- ANGGOTA Section -->
            <div class="text-center mt-24 mb-16">
                <h2 class="text-4xl font-black text-white mb-4 uppercase tracking-tighter">Anggota <span class="text-amber-500">Himatep</span></h2>
                <div class="flex items-center justify-center gap-4">
                    <span class="h-px w-12 bg-gray-300"></span>
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Seluruh Anggota</p>
                    <span class="h-px w-12 bg-gray-300"></span>
                </div>
            </div>

            <div class="ksb-wrapper" style="opacity: 1 !important; visibility: visible !important;">
                <?php 
                foreach ($anggota_list as $p): 
                    $foto_path = (strpos($p['foto'], 'http') === 0) ? $p['foto'] : 'images/pengurus/' . ($p['foto'] ?? 'default.png');
                    // Format Jabatan + Divisi
                    $jabatan_display = $p['jabatan'] . ' ' . $p['divisi'];
                ?>
                    <div class="ksb-card group" @click="openModal('<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>', '<?= htmlspecialchars($jabatan_display, ENT_QUOTES) ?>', '<?= $foto_path ?>', '<?= htmlspecialchars($p['deskripsi'] ?? 'Tidak ada deskripsi', ENT_QUOTES) ?>')">
                        <div class="ksb-image-container">
                            <img src="<?= $foto_path ?>" onerror="this.src='images/logo-himatep.png'" alt="<?= htmlspecialchars($p['nama']) ?>">
                        </div>
                        <div class="ksb-overlay">
                            <div class="ksb-content transition-transform duration-500 group-hover:translate-x-2">
                                <p class="ksb-jabatan"><?= htmlspecialchars($jabatan_display) ?></p>
                                <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Agenda Section -->
    <section class="py-20 bg-batik-blue border-gray-100">
        <div class="max-w-7xl mx-auto px-4 gsap-fade-up">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 text-center md:text-left">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Agenda Mendatang</h2>
                    <p class="text-gray-300">Jadwal kegiatan terdekat yang tidak boleh Anda lewatkan</p>
                </div>
                <a href="index.php#kalender"
                    class="mt-4 md:mt-0 inline-flex items-center text-white font-semibold hover:underline">
                    Lihat Kalender Penuh <span class="ml-2">&rarr;</span>
                </a>
            </div>

            <!-- Dynamic Agenda Grid (Sync with Proker.php) -->
            <div class="flex flex-wrap justify-center gap-8" 
                 x-data="{ agendas: dataProgram.filter(p => p.agenda).sort((a,b) => new Date(a.agenda.date) - new Date(b.agenda.date)).slice(0, 3) }">
                <template x-for="item in agendas" :key="item.id">
                    <a :href="'detail-program.php?slug=' + item.slug" 
                       class="flex flex-col bg-[#1E2F4D] rounded-3xl overflow-hidden shadow-sm border border-white/20 hover:shadow-xl transition-all duration-300 group cursor-pointer w-full md:w-[calc(33.333%-2rem)] min-w-[300px]">
                        <div class="relative h-48 w-full overflow-hidden">
                            <img :src="item.gambar" :alt="item.judul" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            <div class="absolute top-4 right-4 text-white rounded-2xl p-2 flex flex-col justify-center items-center shadow-lg min-w-[70px]"
                                 :style="'background-color: ' + (item.divisiColor === 'blue' ? '#1B2945' : item.divisiColor === 'green' ? '#16A34A' : item.divisiColor === 'purple' ? '#9333EA' : '#059669')">
                                <span class="text-xs font-bold uppercase tracking-wider opacity-90" x-text="item.agenda.bulan"></span>
                                <span class="text-2xl font-black leading-none" x-text="item.agenda.tanggal"></span>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col text-left">
                            <h3 class="text-xl font-bold text-white mb-4 transition-colors"
                                :style="item.divisiColor === 'blue' ? '--hover-color: #1B2945' : item.divisiColor === 'green' ? '--hover-color: #16A34A' : '--hover-color: #9333EA'"
                                :class="'group-hover:text-[var(--hover-color)]'" x-text="item.judul"></h3>
                            <div class="space-y-3 mt-auto">
                                <div class="flex items-center text-sm font-medium text-gray-400">
                                    <svg class="w-5 h-5 mr-3" :style="'color: ' + (item.divisiColor === 'blue' ? '#1B2945' : item.divisiColor === 'green' ? '#16A34A' : '#9333EA')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="item.agenda.waktu"></span>
                                </div>
                                <div class="flex items-center text-sm font-medium text-gray-400">
                                    <svg class="w-5 h-5 mr-3" :style="'color: ' + (item.divisiColor === 'blue' ? '#1B2945' : item.divisiColor === 'green' ? '#16A34A' : '#9333EA')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <?php include 'includes/footer.php'; ?>

    <!-- MODAL POPUP (PREVIEW PENGURUS) -->
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[110] flex items-center justify-center perspective-1000 p-4">
        <!-- Blurred Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
             x-show="isModalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="closeModal()"></div>
        
        <!-- Card Content with 3D Flip Animation -->
        <div class="relative w-full max-w-3xl bg-[#1E2F4D] rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden z-10"
             x-show="isModalOpen"
             x-transition:enter="flip-enter"
             x-transition:leave="flip-leave"
             @click.stop>
             
            <!-- Close Button -->
            <button @click="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black z-20 bg-[#1E2F4D]/80 p-2 rounded-full shadow-sm backdrop-blur">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <!-- Photo Side (Kiri) -->
            <div class="md:w-2/5 bg-[#1E2F4D] flex flex-col items-center justify-end relative overflow-hidden min-h-[300px]">
                <img :src="modalData.foto" :alt="modalData.nama" onerror="this.src='images/logo-himatep.png'" class="w-full h-full object-cover relative z-10 drop-shadow-lg object-center">
            </div>
            
            <!-- Info Side (Kanan) -->
            <div class="md:w-3/5 p-8 md:p-10 flex flex-col justify-center bg-[#1E2F4D] relative z-10">
                <span class="text-xs font-bold text-yellow-600 bg-yellow-50 px-4 py-1.5 rounded-full w-max mb-4 uppercase tracking-wider border border-yellow-200" x-text="modalData.jabatan"></span>
                
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6 leading-tight" x-text="modalData.nama"></h2>
                
                <div class="mt-2">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 border-gray-100 pb-2">Deskripsi</h3>
                    <p class="text-gray-300 text-sm leading-relaxed" x-text="modalData.deskripsi || 'Tidak ada deskripsi yang ditambahkan untuk pengurus ini.'"></p>
                </div>

                <div class="mt-8 flex items-center opacity-30 grayscale">
                    <img src="images/logo-himatep.png" alt="Logo" class="w-8 h-8">
                </div>
            </div>
        </div>
    </div>

    <script>
        const dataProgram = <?php echo $data_program_json; ?>;
    </script>
    <script src="js/animations.js?v=1.1"></script>
    <script src="js/main.js?v=<?= time() ?>"></script>
</body>
</html>
