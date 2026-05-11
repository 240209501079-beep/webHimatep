<?php
session_start();
require_once '../../private/php/config.php';

// Cek sesi login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$upload_dir = '../images/pengurus/';

// Pastikan folder upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Proses Hapus
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cari foto lama
    $stmt = $pdo->prepare("SELECT foto FROM pengurus WHERE id = ?");
    $stmt->execute([$id]);
    $foto_lama = $stmt->fetchColumn();
    
    if ($foto_lama && $foto_lama !== 'default.png' && file_exists($upload_dir . $foto_lama)) {
        unlink($upload_dir . $foto_lama);
    }
    
    $stmt = $pdo->prepare("DELETE FROM pengurus WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "Data pengurus berhasil dihapus.";
    }
    header("Location: manage_pengurus.php");
    exit;
}

// Proses Tambah / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $nama = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $divisi = trim($_POST['divisi']);
    $urutan = (int)$_POST['urutan'];
    $periode = trim($_POST['periode']);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $foto = 'default.png';

    // Handle Upload File Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['foto']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $foto = $file_name;
        }
    }

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO pengurus (nama, jabatan, divisi, urutan, periode, deskripsi, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nama, $jabatan, $divisi, $urutan, $periode, $deskripsi, $foto])) {
            $_SESSION['message'] = "Data pengurus berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Hapus foto lama jika ada
            $stmt = $pdo->prepare("SELECT foto FROM pengurus WHERE id = ?");
            $stmt->execute([$id]);
            $foto_lama = $stmt->fetchColumn();
            if ($foto_lama && $foto_lama !== 'default.png' && file_exists($upload_dir . $foto_lama)) {
                unlink($upload_dir . $foto_lama);
            }
            
            $stmt = $pdo->prepare("UPDATE pengurus SET nama=?, jabatan=?, divisi=?, urutan=?, periode=?, deskripsi=?, foto=? WHERE id=?");
            $stmt->execute([$nama, $jabatan, $divisi, $urutan, $periode, $deskripsi, $foto, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE pengurus SET nama=?, jabatan=?, divisi=?, urutan=?, periode=?, deskripsi=? WHERE id=?");
            $stmt->execute([$nama, $jabatan, $divisi, $urutan, $periode, $deskripsi, $id]);
        }
        $_SESSION['message'] = "Data pengurus berhasil diperbarui.";
    }
    header("Location: manage_pengurus.php");
    exit;
}

// Menangkap Flash Message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Ambil semua data pengurus
$stmt = $pdo->query("SELECT * FROM pengurus ORDER BY urutan ASC");
$pengurus_list = $stmt->fetchAll();

// Pisahkan BPH, Ketua Divisi, dan Anggota
$bph_list = [];
$ketua_divisi_list = [];
$anggota_list = [];
$taken_bph_roles = [];

foreach ($pengurus_list as $p) {
    $divisi_upper = strtoupper(trim($p['divisi']));
    $jabatan_trim = trim($p['jabatan']);
    
    if ($divisi_upper === 'BPH') {
        $bph_list[] = $p;
        $taken_bph_roles[$p['jabatan']] = $p['id'];
    } elseif ($jabatan_trim === 'Ketua Divisi') {
        $ketua_divisi_list[] = $p;
    } else {
        $anggota_list[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengurus - Admin HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
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
    <style> 
        body { font-family: 'Poppins', sans-serif; } 
        /* KSB Modern Cards - Preview Sync */
        .ksb-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            padding: 2rem 0;
        }

        @media (min-width: 768px) {
            .ksb-wrapper {
                gap: 1.5rem;
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
            border: 3px solid #1B2945;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
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
        }

        .ksb-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, #1B2945 0%, #1B2945 25%, rgba(27, 41, 69, 0.6) 40%, transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.1rem;
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-light': '#E2E8F0',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden" 
      x-data="{ 
          sidebarOpen: false, 
          modalOpen: false, 
          modalMode: 'add', 
          takenRoles: <?= htmlspecialchars(json_encode($taken_bph_roles), ENT_QUOTES, 'UTF-8') ?>,
          form: { id: '', nama: '', jabatan: '', divisi: 'BPH', urutan: '', periode: '2026/2027', deskripsi: '' },
          openModal(mode, data = null) {
              this.modalMode = mode;
              if (mode === 'edit' && data) {
                  this.form = { ...data };
              } else {
                  this.form = { id: '', nama: '', jabatan: '', divisi: 'BPH', urutan: '', periode: '2026/2027', deskripsi: '' };
              }
              this.modalOpen = true;
          },
          isRoleTaken(role) {
              if (!this.takenRoles[role]) return false;
              if (this.modalMode === 'edit' && this.form.id == this.takenRoles[role]) return false;
              return true;
          },
          isBphRole(role) {
              return ['Ketua Umum', 'Wakil Ketua Umum', 'Sekretaris Umum', 'Wakil Sekretaris Umum', 'Bendahara Umum'].includes(role);
          }
      }">
    
    <?php include "includes/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full">
        <!-- Header (Format Dashboard) -->
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-4 lg:px-8 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-gray-100 text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl lg:text-2xl font-bold text-gray-800">Struktur Pengurus</h2>
            </div>
            <div class="flex items-center gap-3 lg:gap-4">
                <a href="../index.php" class="text-xs lg:text-sm text-[#1B2945] hover:underline font-medium">Lihat Website</a>
                <div class="flex items-center gap-2 lg:gap-4 border-l border-gray-200 pl-3 lg:pl-4">
                    <span class="font-medium text-xs lg:text-base text-gray-600 truncate max-w-[100px] lg:max-w-none">
                        Halo, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            <?php if ($message): ?>
            <div class="bg-blue-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Pengurus</h3>
                    <button @click="openModal('add')" class="bg-himatep-green hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                        + Tambah Pengurus
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 text-gray-500 text-sm uppercase tracking-wider">
                                <th class="py-4 px-4 w-16 text-center">No</th>
                                <th class="py-4 px-2 border-b">Pengurus</th>
                                <th class="py-4 px-2 border-b">Bidang</th>
                                <th class="py-4 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <?php foreach ($pengurus_list as $p): ?>
                            <tr class="hover:bg-gray-50 transition border-gray-100/50">
                                <td class="py-4 px-4 font-bold text-gray-900 text-center"><?= $p['urutan'] ?></td>
                                <td class="py-4 px-2 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 border border-gray-200">
                                        <img src="../images/pengurus/<?= htmlspecialchars($p['foto']) ?>" onerror="this.src='../images/logo-himatep.png'" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 leading-tight"><?= htmlspecialchars($p['nama']) ?></p>
                                        <?php 
                                            $is_bph = strtoupper(trim($p['divisi'])) === 'BPH';
                                            $jab_clean = str_replace('Ketua Divisi', 'Ketua', $p['jabatan']);
                                            $jab_display = $is_bph ? $p['jabatan'] : ($jab_clean . ' ' . $p['divisi']);
                                        ?>
                                        <p class="text-[11px] text-gray-500 mt-0.5"><?= htmlspecialchars($jab_display) ?></p>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <span class="bg-blue-100 text-[#1B2945] px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-blue-200"><?= htmlspecialchars($p['divisi']) ?></span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <?php
                                            // Escaping khusus untuk pass data JSON di Alpine x-on:click
                                            $p_json = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button @click="openModal('edit', <?= $p_json ?>)" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider hover:underline">Edit</button>
                                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Yakin hapus?')" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wider hover:underline">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($pengurus_list)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data pengurus.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex mt-8">
                <span class="text-[11px] text-gray-400 italic font-medium px-4 py-1.5 bg-white rounded-full border border-gray-200 shadow-sm">
                    <span>Catatan: Pengurus dengan divisi <strong>BPH</strong> dan pengurus dengan jabatan <strong>Ketua Divisi</strong> akan ditampilkan pada kartu trapesium di halaman Profil.
                </span>
            </div>

            <!-- Preview Struktur Organisasi (KSB Highlight) -->
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-12">
                <div class="p-6 border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Preview Tampilan KSB (Live)</h3>
                    <span class="text-xs bg-himatep-light text-himatep-green px-2 py-1 rounded font-medium">Visual Card Only</span>
                </div>
                <div class="p-8 border-gray-100">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-4 bg-himatep-green rounded-full"></div>
                        <h4 class="font-bold text-gray-700 uppercase text-[10px] tracking-widest">BPH Inti</h4>
                    </div>
                    <div class="ksb-wrapper">
                        <?php if (!empty($bph_list)): ?>
                            <?php foreach ($bph_list as $p): 
                                $foto_path = '../images/pengurus/' . ($p['foto'] ?? 'default.png');
                            ?>
                                <div class="ksb-card">
                                    <div class="ksb-image-container">
                                        <img src="<?= htmlspecialchars($foto_path) ?>" 
                                             onerror="this.src='../images/logo-himatep.png'" 
                                             alt="<?= htmlspecialchars($p['nama']) ?>">
                                    </div>
                                    <div class="ksb-overlay">
                                        <div class="ksb-content">
                                            <p class="ksb-jabatan"><?= htmlspecialchars($p['jabatan']) ?></p>
                                            <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center py-6 text-gray-400 italic text-xs">Belum ada data BPH.</div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($ketua_divisi_list)): ?>
                    <div class="flex items-center gap-2 mt-12 mb-6 border-gray-100 pt-8">
                        <div class="w-1 h-4 bg-himatep-green rounded-full"></div>
                        <h4 class="font-bold text-gray-700 uppercase text-[10px] tracking-widest">Ketua</h4>
                    </div>
                    <div class="ksb-wrapper">
                        <?php foreach ($ketua_divisi_list as $p): 
                            $foto_path = '../images/pengurus/' . ($p['foto'] ?? 'default.png');
                            // Jabatan + Divisi (Ketua Divisi -> Ketua)
                            $jab_clean = str_replace('Ketua Divisi', 'Ketua', $p['jabatan']);
                            $jabatan_display = $jab_clean . ' ' . $p['divisi'];
                        ?>
                            <div class="ksb-card">
                                <div class="ksb-image-container">
                                    <img src="<?= htmlspecialchars($foto_path) ?>" 
                                         onerror="this.src='../images/logo-himatep.png'" 
                                         alt="<?= htmlspecialchars($p['nama']) ?>">
                                </div>
                                <div class="ksb-overlay">
                                    <div class="ksb-content">
                                        <p class="ksb-jabatan"><?= htmlspecialchars($jabatan_display) ?></p>
                                        <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($anggota_list)): ?>
                    <div class="flex items-center gap-2 mt-12 mb-6 border-gray-100 pt-8">
                        <div class="w-1 h-4 bg-himatep-green rounded-full"></div>
                        <h4 class="font-bold text-gray-700 uppercase text-[10px] tracking-widest">Anggota</h4>
                    </div>
                    <div class="ksb-wrapper">
                        <?php foreach ($anggota_list as $p): 
                            $foto_path = '../images/pengurus/' . ($p['foto'] ?? 'default.png');
                            // Jabatan + Divisi
                            $jabatan_display = $p['jabatan'] . ' ' . $p['divisi'];
                        ?>
                            <div class="ksb-card">
                                <div class="ksb-image-container">
                                    <img src="<?= htmlspecialchars($foto_path) ?>" 
                                         onerror="this.src='../images/logo-himatep.png'" 
                                         alt="<?= htmlspecialchars($p['nama']) ?>">
                                </div>
                                <div class="ksb-overlay">
                                    <div class="ksb-content">
                                        <p class="ksb-jabatan"><?= htmlspecialchars($jabatan_display) ?></p>
                                        <h3 class="ksb-nama"><?= htmlspecialchars($p['nama']) ?></h3>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Preview Deskripsi (Sesuai Format Profile.php) -->
                <div class="bg-gray-100 p-8">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-1.5 h-6 bg-yellow-500 rounded-full"></div>
                        <h4 class="font-bold text-gray-800 uppercase text-sm tracking-widest">Live Preview Detail Profil</h4>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <?php 
                        $highlight_list = array_merge($bph_list, $ketua_divisi_list, $anggota_list);
                        if (!empty($highlight_list)): 
                            foreach ($highlight_list as $p): 
                                $foto_path = '../images/pengurus/' . ($p['foto'] ?? 'default.png');
                                $is_bph = strtoupper(trim($p['divisi'])) === 'BPH';
                                $jab_clean = str_replace('Ketua Divisi', 'Ketua', $p['jabatan']);
                                $jabatan_display = $is_bph ? $p['jabatan'] : ($jab_clean . ' ' . $p['divisi']);
                        ?>
                                <div class="bg-white rounded-3xl shadow-md border border-gray-100 flex flex-col md:flex-row overflow-hidden group min-h-[300px]">
                                    <!-- Photo Side (Kiri) -->
                                    <div class="md:w-2/5 bg-gray-50 relative overflow-hidden border-r border-gray-100">
                                        <div class="absolute w-32 h-32 bg-blue-50 rounded-full -top-10 -left-10 z-0 opacity-50"></div>
                                        <img src="<?= htmlspecialchars($foto_path) ?>" 
                                             onerror="this.src='../images/logo-himatep.png'" 
                                             alt="<?= htmlspecialchars($p['nama']) ?>"
                                             class="w-full h-full object-cover relative z-10 group-hover:scale-105 transition-transform duration-500">
                                    </div>

                                    <!-- Info Side (Kanan) -->
                                    <div class="md:w-3/5 p-8 flex flex-col justify-center bg-white relative z-10">
                                        <!-- Jabatan Badge -->
                                        <span class="text-[10px] font-bold text-yellow-600 bg-yellow-50 px-3 py-1.5 rounded-full w-max mb-4 uppercase tracking-wider border border-yellow-200">
                                            <?= htmlspecialchars($jabatan_display) ?>
                                        </span>
                                        
                                        <!-- Nama -->
                                        <h5 class="text-xl font-bold text-gray-800 mb-6 leading-tight">
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </h5>
                                        
                                        <!-- Deskripsi Section -->
                                        <div class="mt-2">
                                            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 border-gray-100 pb-1">Deskripsi</h3>
                                            <p class="text-gray-600 text-xs leading-relaxed line-clamp-4">
                                                <?= htmlspecialchars($p['deskripsi'] ?: 'Tidak ada deskripsi yang ditambahkan untuk pengurus ini.') ?>
                                            </p>
                                        </div>

                                        <!-- Mini Logo Decoration -->
                                        <div class="mt-6 flex items-center opacity-20 grayscale">
                                            <img src="../images/logo-himatep.png" alt="Logo" class="w-6 h-6">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal Form Tambah/Edit Pengurus -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="modalOpen = false"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg z-50 transform transition-all"
                 x-show="modalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                 
                <div class="px-6 py-4 border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800" x-text="modalMode === 'add' ? 'Tambah Pengurus' : 'Edit Pengurus'"></h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-red-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form action="manage_pengurus.php" method="POST" enctype="multipart/form-data">
                    <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                        <input type="hidden" name="action" :value="modalMode">
                        <input type="hidden" name="id" x-model="form.id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" x-model="form.nama" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                            <select name="jabatan" x-model="form.jabatan" 
                                    @change="if(isBphRole(form.jabatan)) { form.divisi = 'BPH'; } else if(form.divisi === 'BPH') { form.divisi = ''; }" 
                                    required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none bg-white">
                                <option value="" disabled>-- Pilih Jabatan --</option>
                                <optgroup label="Pengurus Inti (BPH) - Pengurus Harian">
                                    <option value="Ketua Umum" x-bind:disabled="isRoleTaken('Ketua Umum')">Ketua Umum</option>
                                    <option value="Wakil Ketua Umum" x-bind:disabled="isRoleTaken('Wakil Ketua Umum')">Wakil Ketua Umum</option>
                                    <option value="Sekretaris Umum" x-bind:disabled="isRoleTaken('Sekretaris Umum')">Sekretaris Umum</option>
                                    <option value="Wakil Sekretaris Umum" x-bind:disabled="isRoleTaken('Wakil Sekretaris Umum')">Wakil Sekretaris Umum</option>
                                    <option value="Bendahara Umum" x-bind:disabled="isRoleTaken('Bendahara Umum')">Bendahara Umum</option>
                                </optgroup>
                                <optgroup label="Jabatan Bidang (Bisa Banyak Orang)">
                                    <option value="Ketua Divisi">Ketua</option>
                                    <option value="Anggota">Anggota</option>
                                </optgroup>
                            </select>
                            <span x-show="isBphRole(form.jabatan)" class="text-xs text-orange-600 mt-1 block" style="display:none;">
                                *Jabatan Inti mengatur Bidang terkunci sebagai "BPH".
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bidang</label>
                            <select name="divisi" x-model="form.divisi" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none transition-colors"
                                    :class="isBphRole(form.jabatan) ? 'bg-gray-100 text-gray-500' : 'bg-white'">
                                <option value="" disabled>-- Pilih Bidang --</option>
                                <option value="BPH" :disabled="!isBphRole(form.jabatan)">BPH (Badan Pengurus Harian)</option>
                                <option value="Bidang I Pendidikan dan Pelatihan" :disabled="isBphRole(form.jabatan)">Bidang I Pendidikan dan Pelatihan</option>
                                <option value="Bidang II Sosial dan Politik" :disabled="isBphRole(form.jabatan)">Bidang II Sosial dan Politik</option>
                                <option value="Bidang III Bakat dan Minat" :disabled="isBphRole(form.jabatan)">Bidang III Bakat dan Minat</option>
                                <option value="Bidang IV Media dan Propaganda" :disabled="isBphRole(form.jabatan)">Bidang IV Media dan Propaganda</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Urutan</label>
                                <input type="number" name="urutan" x-model="form.urutan" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none">
                                <span class="text-xs text-gray-500 mt-1 block">Logika Tree berdasar urutan</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                                <input type="text" name="periode" x-model="form.periode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" x-model="form.deskripsi" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none" placeholder="Tulis deskripsi singkat pengurus..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Pengurus</label>
                            <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none text-sm">
                            <div x-show="modalMode === 'edit'" class="mt-2 text-xs text-gray-500 bg-blue-50 p-2 rounded inline-block">
                                *Biarkan kosong jika tidak ingin mengubah foto.
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-gray-200 rounded-b-xl flex justify-end gap-3">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-himatep-green hover:bg-blue-700 text-white rounded-lg font-medium transition" x-text="modalMode === 'add' ? 'Simpan Data' : 'Update Data'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


