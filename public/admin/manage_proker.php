<?php
session_start();
require_once '../../private/php/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';
$upload_dir = '../images/proker/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$division_colors = [
    'Hubungan Masyarakat'       => 'emerald',
    'Penelitian & Pengembangan' => 'blue',
    'Pengabdian Masyarakat'     => 'purple',
    'PSDM'                      => 'orange',
];

$icons = [
    'Calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    'Users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    'Book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    'Lightbulb' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    'Globe' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
    'Lab' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
    'Trophy' => 'M20 7h-1.233c.125-.67.19-1.354.209-2.049C19.04 4.146 18.332 3 17 3H7c-1.332 0-2.04 1.146-1.976 1.951.019.695.084 1.379.209 2.049H4c-1.105 0-2 .895-2 2v2c0 1.105.895 2 2 2h2.82c1.017 2.025 2.84 3.513 5.18 3.862V19H9v2h6v-2h-3v-2.138c2.34-.349 4.163-1.837 5.18-3.862H20c1.105 0 2-.895 2-2V9c0-1.105-.895-2-2-2zM4 11V9h1.133c-.03.684-.047 1.365-.05 2H4zm14.867 0c-.003-.635-.02-1.316-.05-2H20v2h-1.133z',
    'Briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745V20a2 2 0 002 2h14a2 2 0 002-2v-6.745zM16 8V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v3H4a2 2 0 00-2 2v3a2 2 0 002 2h16a2 2 0 002-2v-3a2 2 0 00-2-2h-4zM10 8V5h4v3h-4z'
];

// Delete Proker
if (isset($_GET['delete']) && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM proker WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_proker.php?success=Proker berhasil dihapus");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus program kerja.";
    }
}

// Add/Update Proker
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $judul = $_POST['judul'];
    $slug = strtolower(str_replace(' ', '-', $judul));
    $divisi = $_POST['divisi'];
    $divisi_color = $_POST['divisi_color'];
    $icon = $_POST['icon'];
    $target = $_POST['target'];
    $sasaran = $_POST['sasaran'];
    $gambar = $_POST['gambar'];
    $ringkasan = $_POST['ringkasan'];
    $isi = $_POST['isi'];
    $is_unggulan = isset($_POST['is_unggulan']) ? 1 : 0;

    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar_file']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['gambar_file']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $gambar = 'images/proker/' . $file_name;
        }
    }

    if ($gambar === '') {
        $error = 'Pilih URL gambar atau upload file gambar.';
    }

    if (!$error) {
        try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE proker SET judul=?, slug=?, divisi=?, divisi_color=?, icon=?, target=?, sasaran=?, gambar=?, ringkasan=?, isi=?, is_unggulan=? WHERE id=?");
            $stmt->execute([$judul, $slug, $divisi, $divisi_color, $icon, $target, $sasaran, $gambar, $ringkasan, $isi, $is_unggulan, $id]);
            $message = "Program kerja berhasil diperbarui!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO proker (judul, slug, divisi, divisi_color, icon, target, sasaran, gambar, ringkasan, isi, is_unggulan) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$judul, $slug, $divisi, $divisi_color, $icon, $target, $sasaran, $gambar, $ringkasan, $isi, $is_unggulan]);
            $message = "Program kerja baru berhasil ditambahkan!";
        }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan program kerja: " . $e->getMessage();
        }
    }
}

// Fetch Proker
$proker_list = $pdo->query("SELECT * FROM proker ORDER BY id ASC")->fetchAll();

// Edit Mode
$edit_data = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM proker WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit_data = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Program Kerja - HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
    <style>
        #preview-content a {
            color: #60a5fa;
            text-decoration: underline;
            font-weight: 600;
        }

        #preview-content a:hover {
            color: #93c5fd;
        }

        #preview-content ul {
            list-style: disc;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        #preview-content ol {
            list-style: decimal;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        #preview-content li {
            margin: 0.35rem 0;
        }

        #preview-content h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #ffffff;
            line-height: 1.25;
        }

        #preview-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
            line-height: 1.25;
        }
    </style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-[#111A2C] text-white flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, showImageModal: false, imageUrlInput: '', imageCaptionInput: '' }">
    
    <!-- Image Insertion Modal -->
    <div x-show="showImageModal" 
         class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div @click.away="showImageModal = false" 
             class="bg-[#1E2F4D] rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="p-6 border-gray-100 flex justify-between items-center bg-[#1E2F4D]">
                <h3 class="text-xl font-bold text-white">Sisipkan Gambar</h3>
                <button @click="showImageModal = false" class="text-gray-400 hover:text-gray-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <!-- Option 1: Upload -->
                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-3 uppercase tracking-wider">Opsi 1: Unggah dari Perangkat</label>
                    <button type="button" @click="document.getElementById('content-image-upload').click()" class="w-full py-4 px-6 bg-blue-500/10 border-2 border-dashed border-blue-500/20 rounded-2xl text-blue-400 font-bold hover:bg-blue-500/20 hover:border-blue-500/30 transition flex flex-col items-center gap-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span>Pilih & Upload Gambar</span>
                    </button>
                </div>
                
                <div class="relative py-2 flex items-center justify-center">
                    <span class="absolute bg-[#1E2F4D] px-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Atau</span>
                    <hr class="w-full border-white/10">
                </div>

                <!-- Option 2: URL -->
                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-3 uppercase tracking-wider">Opsi 2: Masukkan Link URL</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="imageUrlInput" placeholder="https://contoh.com/gambar.jpg" class="flex-1 p-3 bg-[#1B2945] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>

                <!-- Caption Field -->
                <div class="pt-2">
                    <label class="block text-sm font-bold text-gray-200 mb-2 uppercase tracking-wider">Keterangan Gambar (Opsional)</label>
                    <input type="text" x-model="imageCaptionInput" placeholder="Tulis keterangan gambar di sini..." class="w-full p-3 bg-[#1B2945] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
            <div class="p-4 bg-[#1E2F4D] border-t border-white/10 flex justify-between items-center">
                <button @click="showImageModal = false" class="px-6 py-2 text-gray-400 font-bold hover:text-gray-200 transition">Batal</button>
                <button type="button" @click="insertImageFromUrl(imageUrlInput, imageCaptionInput)" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">Simpan Gambar</button>
            </div>
        </div>
    </div>
    
    <?php include "includes/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full">
        <header class="h-20 bg-[#1E2F4D] shadow-sm flex items-center justify-between px-4 lg:px-8 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-[#1B2945] hover:bg-white/10 text-white border border-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-xl lg:text-2xl font-bold text-white"><?= $edit_data ? 'Edit Proker' : 'Tambah Proker Baru' ?></h2>
            </div>
            <div class="flex items-center gap-3 lg:gap-4">
                <?php if($edit_data): ?>
                    <a href="manage_proker.php" class="bg-white/5 hover:bg-white/10 px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg text-[10px] lg:text-sm font-bold text-gray-200 transition border border-white/10">Batal</a>
                <?php endif; ?>
                <a href="../index.php" class="text-xs lg:text-sm text-white hover:underline font-medium">Lihat Website</a>
                <div class="flex items-center gap-2 lg:gap-4 border-l border-white/10 pl-3 lg:pl-4">
                    <span class="font-medium text-xs lg:text-base text-gray-300 truncate max-w-[100px] lg:max-w-none">
                        Halo, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="flex-1 p-8 overflow-y-auto grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="xl:col-span-1 bg-[#1E2F4D] p-6 rounded-2xl shadow-sm border border-white/20 h-fit">
                <?php if($message): ?> <div class="bg-blue-900/50 border border-blue-500/20 text-blue-200 p-4 rounded-xl mb-4"><?= $message ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="bg-red-900/50 border border-red-500/20 text-red-200 p-4 rounded-xl mb-4"><?= $error ?></div> <?php endif; ?>

                <form action="manage_proker.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-200">Judul Program Kerja</label>
                        <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul'] ?? '') ?>" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1 text-gray-200">Divisi Pelaksana</label>
                            <select name="divisi" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required onchange="updateDivisiColor(this.value)">
                                <option value="" class="bg-[#1E2F4D]">Pilih Divisi</option>
                                <?php foreach($division_colors as $div => $col): ?>
                                    <option value="<?= $div ?>" <?= ($edit_data['divisi'] ?? '') == $div ? 'selected' : '' ?> data-color="<?= $col ?>" class="bg-[#1E2F4D]"><?= $div ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1 text-gray-200">Warna Divisi</label>
                            <input type="text" name="divisi_color" value="<?= htmlspecialchars($edit_data['divisi_color'] ?? 'green') ?>" class="w-full p-2.5 rounded-lg bg-[#1B2945]/50 border border-white/10 text-white/50 cursor-not-allowed" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-200">Icon Program</label>
                        <div class="grid grid-cols-4 gap-2 border border-white/10 p-3 rounded-lg bg-[#1B2945]">
                            <?php foreach($icons as $name => $path): ?>
                                <label class="cursor-pointer group relative">
                                    <input type="radio" name="icon" value="<?= $path ?>" class="hidden peer" <?= ($edit_data['icon'] ?? '') == $path ? 'checked' : '' ?> required>
                                    <div class="p-2 border border-white/10 rounded-lg bg-[#1E2F4D] peer-checked:border-blue-500 peer-checked:bg-blue-500/10 hover:border-white/20 transition-all flex flex-col items-center gap-1">
                                        <svg class="w-6 h-6 text-gray-400 peer-checked:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $path ?>"></path>
                                        </svg>
                                        <span class="text-[10px] text-gray-400"><?= $name ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1 text-gray-200">Target Waktu</label>
                            <input type="text" name="target" value="<?= htmlspecialchars($edit_data['target'] ?? '') ?>" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: April 2026" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1 text-gray-200">Sasaran</label>
                            <input type="text" name="sasaran" value="<?= htmlspecialchars($edit_data['sasaran'] ?? '') ?>" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Mahasiswa Aktif" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-200">Gambar Banner</label>
                        <div class="space-y-3">
                            <input type="text" name="gambar" value="<?= htmlspecialchars($edit_data['gambar'] ?? '') ?>" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tempel URL gambar di sini">
                            <div class="text-xs text-gray-400">Atau upload file gambar JPG, PNG, atau WEBP.</div>
                            <input type="file" name="gambar_file" accept="image/*" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 py-2">
                        <input type="checkbox" name="is_unggulan" id="is_unggulan" <?= ($edit_data['is_unggulan'] ?? 0) ? 'checked' : '' ?> class="rounded bg-[#1B2945] border-white/10 text-blue-600 focus:ring-0 focus:ring-offset-0">
                        <label for="is_unggulan" class="text-sm font-bold text-gray-200">Tampilkan sebagai Program Unggulan</label>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-200">Ringkasan</label>
                        <textarea name="ringkasan" class="w-full p-2.5 rounded-lg bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" rows="2" required><?= htmlspecialchars($edit_data['ringkasan'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-200">Detail Konten (HTML Based)</label>
                        <div class="mb-2 flex flex-wrap gap-2 text-xs font-bold">
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="wrapSelection('strong')">Bold</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="wrapSelection('em')">Italic</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="wrapSelection('u')">Underline</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="wrapSelection('h2')">Judul 2</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="wrapSelection('h3')">Judul 3</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="insertList('ul')">Bullet List</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="insertList('ol')">Nomer List</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-gray-200 transition-colors" onclick="insertLink()">Link</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-white/10 bg-[#1B2945] hover:bg-white/5 text-blue-400 hover:text-blue-300 transition-colors" onclick="insertImage()">+ Gambar</button>
                        </div>
                        <textarea id="isi-editor" name="isi" class="w-full p-3 rounded-lg bg-[#1B2945] border border-white/10 text-white font-mono text-sm leading-6 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="8" required><?= htmlspecialchars($edit_data['isi'] ?? '') ?></textarea>
                        <input type="file" id="content-image-upload" class="hidden" accept="image/*">
                        <p class="text-xs text-gray-400 mt-2">Gunakan tombol di atas untuk menyisipkan tag HTML.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition">
                            <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Program' ?>
                        </button>
                        <?php if(!$edit_data): ?>
                            <button type="reset" onclick="setTimeout(updatePreview, 10)" class="px-6 py-3 border border-white/10 text-gray-300 font-bold rounded-xl hover:bg-white/5 transition">
                                Kosongkan
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Preview & List Container -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Live Preview -->
                <div class="bg-[#1E2F4D] rounded-2xl shadow-sm border border-white/20 overflow-hidden">
                    <div class="p-4 bg-[#1E2F4D] border-b border-white/10 font-bold flex justify-between items-center">
                        <span>Live Preview (Tampilan Detail Program)</span>
                        <span class="text-xs font-normal text-gray-400 italic">Pratinjau</span>
                    </div>
                    <div class="p-8 max-w-2xl mx-auto">
                        <div class="mb-6 flex justify-center">
                            <div id="preview-icon-container" class="w-24 h-24 rounded-3xl flex items-center justify-center shadow-lg bg-blue-500/10 border border-blue-500/20">
                                <svg id="preview-icon" class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div id="preview-divisi" class="inline-block px-3 py-1 rounded-full text-xs font-bold text-white bg-blue-600 mb-4 uppercase tracking-wider">BIDANG</div>
                        <h1 id="preview-title" class="text-3xl font-bold text-white mb-6 leading-tight">Nama Program Kerja</h1>
                        
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="bg-[#1E2F4D] p-4 rounded-xl border border-white/10">
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Target Waktu</div>
                                <div id="preview-target" class="font-bold text-gray-200">April 2026</div>
                            </div>
                            <div class="bg-[#1E2F4D] p-4 rounded-xl border border-white/10">
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Sasaran</div>
                                <div id="preview-sasaran" class="font-bold text-gray-200">Mahasiswa</div>
                            </div>
                        </div>

                        <img id="preview-image" src="https://via.placeholder.com/800x400" class="w-full h-64 object-cover rounded-2xl mb-8 shadow-lg">
                        
                        <div class="prose prose-green max-w-none text-gray-300 leading-relaxed">
                            <div id="preview-ringkasan" class="font-bold text-white mb-4 text-lg">Ringkasan singkat program akan tampil di sini.</div>
                            <div id="preview-content">Detail konten lengkap program...</div>
                        </div>
                    </div>
                </div>

                <!-- List -->
                <div class="bg-[#1E2F4D] rounded-2xl shadow-sm border border-white/20 overflow-hidden">
                <div class="p-4 bg-[#1E2F4D] border-b border-white/10 font-bold">Daftar Proker</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#1B2945] text-gray-400 uppercase text-xs border-b border-white/10">
                            <tr>
                                <th class="p-4">Banner</th>
                                <th class="p-4">Nama Program</th>
                                <th class="p-4">Divisi</th>
                                <th class="p-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <?php foreach($proker_list as $p): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <img src="<?= htmlspecialchars($p['gambar']) ?>" class="w-16 h-10 object-cover rounded shadow-sm" onerror="this.src='https://via.placeholder.com/100x60'">
                                </td>
                                <td class="p-4 font-medium text-white"><?= htmlspecialchars($p['judul']) ?></td>
                                <td class="p-4 text-xs text-gray-300"><?= htmlspecialchars($p['divisi']) ?></td>
                                <td class="p-4 space-x-2">
                                    <a href="../detail-program.php?slug=<?= $p['slug'] ?>" target="_blank" class="text-green-400 font-bold hover:text-green-300 hover:underline">Lihat</a>
                                    <a href="?edit=1&id=<?= $p['id'] ?>" class="text-blue-400 font-bold hover:text-blue-300 hover:underline">Edit</a>
                                    <a href="?delete=1&id=<?= $p['id'] ?>" class="text-red-400 font-bold hover:text-red-300 hover:underline" onclick="return confirm('Hapus proker ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        const fields = ['judul', 'divisi', 'gambar', 'target', 'sasaran', 'ringkasan', 'isi'];
        const previewMap = {
            judul: 'preview-title',
            divisi: 'preview-divisi',
            gambar: 'preview-image',
            target: 'preview-target',
            sasaran: 'preview-sasaran',
            ringkasan: 'preview-ringkasan',
            isi: 'preview-content'
        };

        function wrapSelection(tagName) {
            const textarea = document.getElementById('isi-editor');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end) || 'Teks';
            const replacement = `<${tagName}>${selected}</${tagName}>`;
            textarea.setRangeText(replacement, start, end, 'end');
            textarea.focus();
            updatePreview();
        }

        function insertList(listType) {
            const textarea = document.getElementById('isi-editor');
            if (!textarea) return;

            const selected = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd).trim();
            const items = selected ? selected.split('\n') : ['Item pertama', 'Item kedua'];
            const listItems = items.map(item => `    <li>${item.trim() || 'Item'}</li>`).join('\n');
            const replacement = `<${listType}>\n${listItems}\n</${listType}>`;
            textarea.setRangeText(replacement, textarea.selectionStart, textarea.selectionEnd, 'end');
            textarea.focus();
            updatePreview();
        }

        function insertLink() {
            const textarea = document.getElementById('isi-editor');
            if (!textarea) return;

            const url = window.prompt('Masukkan URL link');
            if (!url) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end) || 'Tautan';
            const replacement = `<a href="${url}" target="_blank" rel="noopener noreferrer">${selected}</a>`;
            textarea.setRangeText(replacement, start, end, 'end');
            textarea.focus();
            updatePreview();
        }

        function insertImage() {
            // Trigger Alpine.js Modal (v3 syntax)
            const body = document.querySelector('body');
            if (window.Alpine) {
                const alpineData = Alpine.$data(body);
                alpineData.imageUrlInput = '';
                alpineData.showImageModal = true;
            } else {
                console.error('Alpine.js not loaded');
                return;
            }
            
            // Set up upload handler
            const fileInput = document.getElementById('content-image-upload');
            const textarea = document.getElementById('isi-editor');
            
            fileInput.onchange = async () => {
                if (!fileInput.files || !fileInput.files[0]) return;
                
                const alpineData = Alpine.$data(body);
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                formData.append('type', 'proker');

                try {
                    alpineData.showImageModal = false; // Close modal
                    const response = await fetch('ajax_upload.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        const caption = alpineData.imageCaptionInput;
                        let replacement = `\n<figure class="my-6">\n    <img src="${result.path}" alt="${caption || 'Gambar Program'}" class="w-full rounded-2xl shadow-md">\n`;
                        if (caption) {
                            replacement += `    <figcaption class="text-center text-sm text-gray-400 mt-2 italic">${caption}</figcaption>\n`;
                        }
                        replacement += `</figure>\n`;
                        
                        textarea.setRangeText(replacement, textarea.selectionStart, textarea.selectionEnd, 'end');
                        textarea.focus();
                        updatePreview();
                    } else {
                        alert('Gagal upload: ' + (result.error || 'Terjadi kesalahan'));
                    }
                } catch (err) {
                    alert('Terjadi kesalahan saat upload.');
                }
                fileInput.value = ''; 
            };
        }

        function insertImageFromUrl(url, caption) {
            const textarea = document.getElementById('isi-editor');
            const alpineData = Alpine.$data(document.querySelector('body'));
            
            if (!url) {
                alert('Masukkan URL gambar terlebih dahulu.');
                return;
            }

            let replacement = `\n<figure class="my-6">\n    <img src="${url}" alt="${caption || 'Gambar Program'}" class="w-full rounded-2xl shadow-md">\n`;
            if (caption) {
                replacement += `    <figcaption class="text-center text-sm text-gray-400 mt-2 italic">${caption}</figcaption>\n`;
            }
            replacement += `</figure>\n`;

            textarea.setRangeText(replacement, textarea.selectionStart, textarea.selectionEnd, 'end');
            textarea.focus();
            
            alpineData.showImageModal = false;
            updatePreview();
        }

        function updateDivisiColor(divisiName) {
            const select = document.querySelector('select[name="divisi"]');
            const colorInput = document.querySelector('input[name="divisi_color"]');
            const selectedOption = select.options[select.selectedIndex];
            const color = selectedOption.getAttribute('data-color');
            
            if (color) {
                colorInput.value = color;
                updatePreview();
            }
        }

        function updatePreview() {
            fields.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                const preview = document.getElementById(previewMap[field]);
                
                if (input && preview) {
                    if (field === 'gambar') {
                        let path = input.value;
                        if (path && path.startsWith('images/')) path = '../' + path;
                        preview.src = path || 'https://via.placeholder.com/800x400';
                    } else if (field === 'isi' || field === 'ringkasan') {
                        let content = input.value || (field === 'ringkasan' ? 'Ringkasan...' : 'Konten...');
                        // Adjust relative image paths for admin preview
                        content = content.replace(/src="images\//g, 'src="../images/');
                        preview.innerHTML = content;
                    } else if (field === 'divisi') {
                        preview.innerText = input.value.toUpperCase() || 'BIDANG';
                        const colorInput = document.querySelector('[name="divisi_color"]');
                        const color = colorInput.value || 'green';
                        
                        // Remove existing color classes
                        preview.classList.forEach(cls => {
                            if (cls.startsWith('bg-')) preview.classList.remove(cls);
                        });
                        
                        // Add new color class
                        preview.classList.add(`bg-${color}-600`);
                    } else {
                        preview.innerText = input.value || '...';
                    }
                }
            });
            
            // Update icon preview
            const iconInput = document.querySelector('input[name="icon"]:checked');
            const iconPreview = document.getElementById('preview-icon');
            const iconContainer = document.getElementById('preview-icon-container');
            const colorInput = document.querySelector('[name="divisi_color"]');
            const color = colorInput?.value || 'blue';
            
            if (iconInput && iconPreview && iconContainer) {
                const iconPath = iconInput.value;
                iconPreview.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>`;
                
                // Update container color
                iconContainer.classList.forEach(cls => {
                    if (cls.startsWith('bg-')) iconContainer.classList.remove(cls);
                    if (cls.startsWith('text-')) iconPreview.classList.remove(cls);
                });
                iconContainer.classList.add(`bg-${color}-100`);
                iconPreview.classList.add(`text-${color}-600`);
            }
        }

        // Update preview when icon is selected
        document.querySelectorAll('input[name="icon"]').forEach(el => {
            el.addEventListener('change', updatePreview);
        });
        
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });

        const imageFileInput = document.querySelector('input[name="gambar_file"]');
        const imageUrlInput = document.querySelector('input[name="gambar"]');
        const imagePreview = document.getElementById('preview-image');

        if (imageFileInput && imagePreview) {
            imageFileInput.addEventListener('change', () => {
                const file = imageFileInput.files && imageFileInput.files[0];
                if (!file) {
                    updatePreview();
                    return;
                }

                const reader = new FileReader();
                reader.onload = event => {
                    imagePreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        if (imageUrlInput && imagePreview) {
            imageUrlInput.addEventListener('input', () => {
                if (!imageFileInput || !(imageFileInput.files && imageFileInput.files[0])) {
                    let path = imageUrlInput.value;
                    if (path && path.startsWith('images/')) path = '../' + path;
                    imagePreview.src = path || 'https://via.placeholder.com/800x400';
                }
            });
        }

        updatePreview();
    </script>
    <!-- Script Alpine.js untuk fitur interaktif -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
