<?php
session_start();
require_once '../../private/php/config.php';

// Cek sesi login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$upload_dir = '../images/banners/';

// Pastikan folder upload ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $judul = trim($_POST['judul']);
    $subjudul = trim($_POST['subjudul']);
    $halaman = $_POST['halaman'];

    // Handle Upload File Gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['gambar']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $gambar_path = 'images/banners/' . $file_name;
            
            // Hapus gambar lama jika bukan URL eksternal (Unsplash)
            $stmt = $pdo->prepare("SELECT gambar FROM halaman_banner WHERE id = ?");
            $stmt->execute([$id]);
            $gambar_lama = $stmt->fetchColumn();
            
            if ($gambar_lama && strpos($gambar_lama, 'http') === false && file_exists('../' . $gambar_lama)) {
                unlink('../' . $gambar_lama);
            }
            
            $stmt = $pdo->prepare("UPDATE halaman_banner SET judul=?, subjudul=?, gambar=? WHERE id=?");
            $stmt->execute([$judul, $subjudul, $gambar_path, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE halaman_banner SET judul=?, subjudul=? WHERE id=?");
        $stmt->execute([$judul, $subjudul, $id]);
    }
    
    $_SESSION['message'] = "Banner halaman " . ucfirst($halaman) . " berhasil diperbarui.";
    header("Location: manage_banners.php");
    exit;
}

// Menangkap Flash Message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Ambil semua data banner
$stmt = $pdo->query("SELECT * FROM halaman_banner");
$banners = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Banner - Admin HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .banner-preview {
            aspect-ratio: 1920 / 800;
            background-size: cover;
            background-position: center;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
                        'himatep-dark': '#111111',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, modalOpen: false, editData: {} }">
    
    <!-- Sidebar -->
    <?php include "includes/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-gray-100 text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-2xl font-bold text-gray-800">Kelola Banner Halaman</h2>
            </div>
        </header>

        <div class="flex-1 p-8 overflow-y-auto">
            <?php if ($message): ?>
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-8">
                <?php foreach ($banners as $banner): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden group">
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-1/3 relative">
                                <div class="banner-preview bg-gray-200" style="background-image: url('<?= strpos($banner['gambar'], 'http') === 0 ? $banner['gambar'] : '../' . $banner['gambar'] ?>')"></div>
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button @click="editData = <?= htmlspecialchars(json_encode($banner)) ?>; modalOpen = true" class="bg-white text-blue-600 px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all">
                                        Edit Banner
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 md:w-2/3 flex flex-col justify-center">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-1 block">Halaman <?= ucfirst($banner['halaman']) ?></span>
                                        <h3 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($banner['judul']) ?></h3>
                                    </div>
                                    <button @click="editData = <?= htmlspecialchars(json_encode($banner)) ?>; modalOpen = true" class="text-blue-600 hover:text-blue-800 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-gray-500 line-clamp-2"><?= htmlspecialchars($banner['subjudul']) ?></p>
                                <div class="mt-4 pt-4 border-gray-100 text-xs text-gray-400">
                                    Terakhir diperbarui: <?= date('d M Y, H:i', strtotime($banner['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Modal Edit Banner -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="modalOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
            <div class="p-6 border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-xl font-bold text-gray-800">Edit Banner: <span class="text-blue-600" x-text="editData.halaman?.toUpperCase()"></span></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="manage_banners.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" :value="editData.id">
                <input type="hidden" name="halaman" :value="editData.halaman">

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul Banner</label>
                        <input type="text" name="judul" :value="editData.judul" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sub-judul (Deskripsi Singkat)</label>
                        <textarea name="subjudul" rows="3" x-text="editData.subjudul" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Gambar Banner</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-2xl hover:border-blue-400 transition-colors bg-gray-50 group cursor-pointer relative">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <span class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">Unggah file</span>
                                    <p class="pl-1">atau seret dan lepas</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 5MB (Rekomendasi: 1920x800)</p>
                            </div>
                            <input type="file" name="gambar" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <p class="mt-2 text-xs text-gray-400 italic">*Biarkan kosong jika tidak ingin mengganti gambar.</p>
                    </div>
                </div>

                <div class="pt-6 flex gap-4">
                    <button type="button" @click="modalOpen = false" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

