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

// Pisahkan BPH dan Divisi
$bph_list = [];
$divisi_list = [];
$taken_bph_roles = [];

foreach ($pengurus_list as $p) {
    if (strtoupper(trim($p['divisi'])) === 'BPH') {
        $bph_list[] = $p;
        $taken_bph_roles[$p['jabatan']] = $p['id'];
    } else {
        $divisi_list[$p['divisi']][] = $p;
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
            max-width: 280px;
            height: 420px;
            background: white;
            overflow: hidden;
            transform: skewX(-12deg);
            border: 3px solid #2563EB;
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
            background: linear-gradient(to top, #2563EB 0%, #2563EB 25%, rgba(37, 99, 235, 0.6) 40%, transparent 60%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.5rem;
        }

        .ksb-content {
            transform: skewX(12deg);
            text-align: left;
        }

        .ksb-nama {
            font-size: 1.5rem;
            font-weight: 900;
            color: white;
            font-style: italic;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .ksb-jabatan {
            font-size: 0.9rem;
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
                        'himatep-green': '#2563EB',
                        'himatep-light': '#DBEAFE',
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
              return ['Ketua Umum', 'Sekretaris Umum', 'Bendahara Umum'].includes(role);
          }
<body class="bg-gray-100 flex h-screen overflow-hidden" x-data="{ 
    sidebarOpen: false, 
    modalOpen: false, 
    modalMode: 'add', 
    takenRoles: <?= htmlspecialchars(json_encode($taken_bph_roles), ENT_QUOTES, 'UTF-8') ?>,
    formData: { id: '', nama: '', jabatan: '', divisi: 'BPH', urutan: '', periode: '2026/2027', deskripsi: '' },
    openModal(mode, data = null) {
        this.modalMode = mode;
        if (mode === 'edit' && data) {
            this.formData = { ...data };
        } else {
            this.formData = { id: '', nama: '', jabatan: '', divisi: 'BPH', urutan: '', periode: '2026/2027', deskripsi: '' };
        }
        this.modalOpen = true;
    },
    isRoleTaken(role) {
        if (!this.takenRoles[role]) return false;
        if (this.modalMode === 'edit' && this.formData.id == this.takenRoles[role]) return false;
        return true;
    },
    isBphRole(role) {
        return ['Ketua Umum', 'Sekretaris Umum', 'Bendahara Umum'].includes(role);
    }
}">
    
    <!-- Overlay for Mobile -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition opacity-0 ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition opacity-100 ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/50 z-30 lg:hidden" style="display:none;"></div>

    <!-- Sidebar (Format Dashboard) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed lg:static inset-y-0 left-0 w-64 bg-[#2563EB] text-white flex flex-col shadow-xl z-40 transition-transform duration-300 lg:translate-x-0">
        <div class="p-6 border-b border-blue-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="../images/logo-himatep.png" alt="Logo" class="h-8 w-8 bg-white rounded-full p-1">
                <span class="text-xl font-bold text-white">Admin Panel</span>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="block py-3 px-4 rounded-lg hover:bg-blue-800 transition text-green-100">Dashboard</a>
            <a href="manage_news.php" class="block py-3 px-4 rounded-lg hover:bg-blue-800 transition text-green-100">Kelola Berita</a>
            <a href="manage_proker.php" class="block py-3 px-4 rounded-lg hover:bg-blue-800 transition text-green-100">Program Kerja</a>
            <a href="manage_pengurus.php" class="block py-3 px-4 rounded-lg bg-blue-800 font-medium">Struktur Pengurus</a>
            <a href="view_aspirasi.php" class="block py-3 px-4 rounded-lg hover:bg-blue-800 transition text-green-100">Suara Mahasiswa</a>
        </nav>
        <div class="p-4 border-t border-green-800">
            <a href="logout.php" class="block w-full py-2 px-4 bg-red-500 hover:bg-red-600 rounded text-center font-bold transition shadow">Logout</a>
        </div>
    </aside>

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
                <a href="../index.php" class="text-xs lg:text-sm text-[#2563EB] hover:underline font-medium">Lihat Website</a>
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
                <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Pengurus</h3>
                    <button @click="openModal('add')" class="bg-himatep-green hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                        + Tambah Pengurus
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 text-gray-500 text-sm uppercase tracking-wider">
                                <th class="py-4 px-4 border-b w-16 text-center">No</th>
                                <th class="py-4 px-2 border-b">Pengurus</th>
                                <th class="py-4 px-2 border-b">Divisi</th>
                                <th class="py-4 px-4 border-b text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-sm">
                            <?php foreach ($pengurus_list as $p): ?>
                            <tr class="hover:bg-gray-50 transition border-b border-gray-100/50">
                                <td class="py-4 px-4 font-bold text-gray-900 text-center"><?= $p['urutan'] ?></td>
                                <td class="py-4 px-2 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 border border-gray-200">
                                        <img src="../images/pengurus/<?= htmlspecialchars($p['foto']) ?>" onerror="this.src='../images/logo-himatep.png'" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 leading-tight"><?= htmlspecialchars($p['nama']) ?></p>
                                        <p class="text-[11px] text-gray-500 mt-0.5"><?= htmlspecialchars($p['jabatan']) ?></p>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <span class="bg-blue-100 text-[#2563EB] px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-blue-200"><?= htmlspecialchars($p['divisi']) ?></span>
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
                    <span>Catatan: Hanya pengurus dengan divisi <strong>BPH</strong> yang akan ditampilkan pada kartu trapesium di halaman Profil.
                </span>
            </div>

            <!-- Preview Struktur Organisasi (KSB Highlight) -->
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-12">
                <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Preview Tampilan KSB (Live)</h3>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded font-medium">Visual Card Only</span>
                </div>
                <div class="p-8 border-b border-gray-100">
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
                            <div class="col-span-full text-center py-12 text-gray-400 italic">Belum ada data BPH.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview Deskripsi (Sesuai Format Profile.php) -->
                <div class="bg-gray-100 p-8">
                    <div class="flex items-center gap-2 mb-8">
                        <div class="w-1.5 h-6 bg-yellow-500 rounded-full"></div>
                        <h4 class="font-bold text-gray-800 uppercase text-sm tracking-widest">Live Preview Detail Profil</h4>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <?php if (!empty($bph_list)): ?>
                            <?php foreach ($bph_list as $p): 
                                $foto_path = '../images/pengurus/' . ($p['foto'] ?? 'default.png');
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
                                            <?= htmlspecialchars($p['jabatan']) ?>
                                        </span>
                                        
                                        <!-- Nama -->
                                        <h5 class="text-xl font-bold text-gray-800 mb-6 leading-tight">
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </h5>
                                        
                                        <!-- Deskripsi Section -->
                                        <div class="mt-2">
                                            <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 border-b border-gray-100 pb-1">Deskripsi</h3>
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
                 
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
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
                            <select name="jabatan" x-model="form.jabatan" @change="if(isBphRole(form.jabatan)) { form.divisi = 'BPH'; }" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none bg-white">
                                <option value="" disabled>-- Pilih Jabatan --</option>
                                <optgroup label="Pengurus Inti (BPH) - Hanya 1 Orang per Peran">
                                    <option value="Ketua Umum" x-bind:disabled="isRoleTaken('Ketua Umum')">Ketua Umum</option>
                                    <option value="Sekretaris Umum" x-bind:disabled="isRoleTaken('Sekretaris Umum')">Sekretaris Umum</option>
                                    <option value="Bendahara Umum" x-bind:disabled="isRoleTaken('Bendahara Umum')">Bendahara Umum</option>
                                </optgroup>
                                <optgroup label="Jabatan Divisi (Bisa Banyak Orang)">
                                    <option value="Koordinator">Koordinator</option>
                                    <option value="Ketua Divisi">Ketua Divisi</option>
                                    <option value="Anggota">Anggota</option>
                                </optgroup>
                            </select>
                            <span x-show="isBphRole(form.jabatan)" class="text-xs text-orange-600 mt-1 block" style="display:none;">
                                *Jabatan Inti mengatur Divisi terkunci sebagai "BPH (Non-Divisi)".
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Divisi</label>
                            <select name="divisi" x-model="form.divisi" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-himatep-green focus:border-himatep-green outline-none transition-colors"
                                    :class="isBphRole(form.jabatan) ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                    :style="isBphRole(form.jabatan) ? 'pointer-events: none;' : ''">
                                <option value="BPH">BPH (Badan Pengurus Harian - Non Divisi)</option>
                                <option value="PSDM">PSDM</option>
                                <option value="Penelitian & Pengembangan">Penelitian & Pengembangan</option>
                                <option value="Hubungan Masyarakat">Hubungan Masyarakat</option>
                                <option value="Pengabdian Masyarakat">Pengabdian Masyarakat</option>
                                <option value="Minat & Bakat">Minat & Bakat</option>
                                <option value="Kesejahteraan Mahasiswa">Kesejahteraan Mahasiswa</option>
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
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end gap-3">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-himatep-green hover:bg-blue-700 text-white rounded-lg font-medium transition" x-text="modalMode === 'add' ? 'Simpan Data' : 'Update Data'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>