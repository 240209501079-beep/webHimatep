<?php
session_start();
require_once '../../private/php/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';
$upload_dir = '../images/berita/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function normalizeNewsContent(string $content)
{
    $content = trim($content);

    if ($content === '') {
        return '';
    }

    $content = preg_replace('/<(\/?)(ul|ol|li|p|h2|h3|blockquote|div|section|article|table|thead|tbody|tr|td|th)\b([^>]*)>/i', "\n<$1$2$3>\n", $content);
    $lines = preg_split('/\R/', $content);
    $blocks = [];
    $paragraphLines = [];
    $listItems = [];
    $listType = null;

    $flushParagraph = function () use (&$blocks, &$paragraphLines) {
        if (!$paragraphLines) {
            return;
        }

        $paragraph = implode('<br>', array_map('htmlspecialchars', $paragraphLines));
        $blocks[] = '<p>' . $paragraph . '</p>';
        $paragraphLines = [];
    };

    $flushList = function () use (&$blocks, &$listItems, &$listType) {
        if (!$listItems || !$listType) {
            return;
        }

        $tag = $listType === 'ol' ? 'ol' : 'ul';
        $items = array_map(function ($item) {
            return '<li>' . htmlspecialchars($item) . '</li>';
        }, $listItems);

        $blocks[] = '<' . $tag . '>' . implode('', $items) . '</' . $tag . '>';
        $listItems = [];
        $listType = null;
    };

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '') {
            $flushParagraph();
            $flushList();
            continue;
        }

        if (preg_match('/^<\/?(?:ul|ol|li|p|h2|h3|blockquote|div|section|article|table|thead|tbody|tr|td|th)\b[^>]*>$/i', $trimmedLine)) {
            $flushParagraph();
            $flushList();
            $blocks[] = $trimmedLine;
            continue;
        }

        if (preg_match('/^(-|\*|•)\s+(.*)$/', $trimmedLine, $matches)) {
            $flushParagraph();
            if ($listType !== 'ul') {
                $flushList();
                $listType = 'ul';
            }
            $listItems[] = $matches[2];
            continue;
        }

        if (preg_match('/^\d+\.\s+(.*)$/', $trimmedLine, $matches)) {
            $flushParagraph();
            if ($listType !== 'ol') {
                $flushList();
                $listType = 'ol';
            }
            $listItems[] = $matches[1];
            continue;
        }

        $flushList();
        $paragraphLines[] = $trimmedLine;
    }

    $flushParagraph();
    $flushList();

    return implode("\n", $blocks);
}

// Delete News
if (isset($_GET['delete']) && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM berita WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_news.php?success=Berita berhasil dihapus");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus berita.";
    }
}

// Add/Update News
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $judul = $_POST['judul'];
    $slug = strtolower(str_replace(' ', '-', $judul));
    $kategori = $_POST['kategori'];
    $kategori_color = $_POST['kategori_color'];
    $penulis = $_POST['penulis'];
    $gambar = trim($_POST['gambar'] ?? '');
    $ringkasan = $_POST['ringkasan'];
    $isi = normalizeNewsContent($_POST['isi']);
    $tanggal = $_POST['tanggal_posting'];

    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar_file']['tmp_name'];
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['gambar_file']['name']);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $gambar = 'images/berita/' . $file_name;
        }
    }

    if ($gambar === '') {
        $error = 'Pilih URL gambar atau upload file gambar.';
    }

    if (!$error) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE berita SET judul=?, slug=?, kategori=?, kategori_color=?, penulis=?, gambar=?, ringkasan=?, isi=?, tanggal_posting=? WHERE id=?");
                $stmt->execute([$judul, $slug, $kategori, $kategori_color, $penulis, $gambar, $ringkasan, $isi, $tanggal, $id]);
                $message = "Berita berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO berita (judul, slug, kategori, kategori_color, penulis, gambar, ringkasan, isi, tanggal_posting) VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$judul, $slug, $kategori, $kategori_color, $penulis, $gambar, $ringkasan, $isi, $tanggal]);
                $message = "Berita baru berhasil ditambahkan!";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan berita: " . $e->getMessage();
        }
    }
}

// Fetch News
$berita_list = $pdo->query("SELECT * FROM berita ORDER BY tanggal_posting DESC")->fetchAll();

// Edit Mode
$edit_data = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit_data = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - HIMATEP</title>
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
    <style>
        #preview-content a {
            color: #1B2945;
            text-decoration: underline;
            font-weight: 600;
        }

        #preview-content a:hover {
            color: #1d4ed8;
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
            color: #111;
            line-height: 1.25;
        }

        #preview-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #111;
            line-height: 1.25;
        }
    </style>
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, showImageModal: false, imageUrlInput: '', imageCaptionInput: '' }">
    
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
                    <button type="button" @click="document.getElementById('content-image-upload').click()" class="w-full py-4 px-6 bg-blue-50 border-2 border-dashed border-blue-300 rounded-2xl text-blue-600 font-bold hover:bg-blue-100 hover:border-blue-400 transition flex flex-col items-center gap-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span>Pilih & Upload Gambar</span>
                    </button>
                </div>
                
                <div class="relative py-2 flex items-center justify-center">
                    <span class="absolute bg-[#1E2F4D] px-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Atau</span>
                    <hr class="w-full border-gray-100">
                </div>

                <!-- Option 2: URL -->
                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-3 uppercase tracking-wider">Opsi 2: Masukkan Link URL</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="imageUrlInput" placeholder="https://contoh.com/gambar.jpg" class="flex-1 p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <!-- Caption Field -->
                <div class="pt-2">
                    <label class="block text-sm font-bold text-gray-200 mb-2 uppercase tracking-wider">Keterangan Gambar (Opsional)</label>
                    <input type="text" x-model="imageCaptionInput" placeholder="Tulis keterangan gambar di sini..." class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
            </div>
            <div class="p-4 bg-[#1E2F4D] border-gray-100 flex justify-between items-center">
                <button @click="showImageModal = false" class="px-6 py-2 text-gray-400 font-bold hover:text-gray-200 transition">Batal</button>
                <button type="button" @click="insertImageFromUrl(imageUrlInput, imageCaptionInput)" class="bg-himatep-green text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-800 transition shadow-lg shadow-blue-200">Konfirmasi</button>
            </div>
        </div>
    </div>
    
    <?php include "includes/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-screen overflow-hidden w-full">
        <header class="h-20 bg-[#1E2F4D] shadow-sm flex items-center justify-between px-4 lg:px-8 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-gray-100 text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-xl lg:text-2xl font-bold text-white"><?= $edit_data ? 'Edit Berita' : 'Tambah Berita Baru' ?></h2>
            </div>
            <div class="flex items-center gap-3 lg:gap-4">
                <?php if($edit_data): ?>
                    <a href="manage_news.php" class="bg-gray-100 hover:bg-gray-200 px-3 py-1.5 lg:px-4 lg:py-2 rounded-lg text-[10px] lg:text-sm font-bold text-gray-300 transition border border-white/10">Batal</a>
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
                <?php if($message): ?> <div class="bg-blue-100 text-green-700 p-4 rounded-xl mb-4"><?= $message ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4"><?= $error ?></div> <?php endif; ?>

                <form action="manage_news.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                    <div>
                        <label class="block text-sm font-bold mb-1">Judul Berita</label>
                        <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul'] ?? '') ?>" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Kategori</label>
                            <input type="text" name="kategori" value="<?= htmlspecialchars($edit_data['kategori'] ?? '') ?>" class="w-full p-2 border rounded-lg" placeholder="Contoh: Kegiatan" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Warna Kategori</label>
                            <select name="kategori_color" class="w-full p-2 border rounded-lg">
                                <option value="green" <?= ($edit_data['kategori_color'] ?? '') == 'green' ? 'selected' : '' ?>>Hijau</option>
                                <option value="blue" <?= ($edit_data['kategori_color'] ?? '') == 'blue' ? 'selected' : '' ?>>Biru</option>
                                <option value="purple" <?= ($edit_data['kategori_color'] ?? '') == 'purple' ? 'selected' : '' ?>>Ungu</option>
                                <option value="yellow" <?= ($edit_data['kategori_color'] ?? '') == 'yellow' ? 'selected' : '' ?>>Kuning</option>
                                <option value="red" <?= ($edit_data['kategori_color'] ?? '') == 'red' ? 'selected' : '' ?>>Merah</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Penulis</label>
                            <input type="text" name="penulis" value="<?= htmlspecialchars($edit_data['penulis'] ?? 'Admin') ?>" class="w-full p-2 border rounded-lg" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Tanggal Posting</label>
                            <input type="date" name="tanggal_posting" value="<?= $edit_data['tanggal_posting'] ?? date('Y-m-d') ?>" class="w-full p-2 border rounded-lg" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Gambar Berita</label>
                        <div class="space-y-3">
                            <input type="text" name="gambar" value="<?= htmlspecialchars($edit_data['gambar'] ?? '') ?>" class="w-full p-2 border rounded-lg" placeholder="Tempel URL gambar di sini">
                            <div class="text-xs text-gray-400">Atau upload file gambar JPG, PNG, atau WEBP.</div>
                            <input type="file" name="gambar_file" accept="image/*" class="w-full p-2 border rounded-lg bg-[#1E2F4D]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Ringkasan (Pendek)</label>
                        <textarea name="ringkasan" class="w-full p-2 border rounded-lg" rows="2" required><?= htmlspecialchars($edit_data['ringkasan'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Isi Berita (HTML Based)</label>
                        <div class="mb-2 flex flex-wrap gap-2 text-xs font-bold">
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="wrapSelection('strong')">Bold</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="wrapSelection('em')">Italic</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="wrapSelection('u')">Underline</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="wrapSelection('h2')">Judul 2</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="wrapSelection('h3')">Judul 3</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="insertList('ul')">Bullet List</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="insertList('ol')">Nomer List</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100" onclick="insertLink()">Link</button>
                            <button type="button" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-[#1E2F4D] hover:bg-gray-100 text-blue-600" onclick="insertImage()">+ Gambar</button>
                        </div>
                        <textarea id="isi-editor" name="isi" class="w-full p-2 border rounded-lg font-mono text-sm leading-6" rows="10" required><?= htmlspecialchars($edit_data['isi'] ?? '') ?></textarea>
                        <input type="file" id="content-image-upload" class="hidden" accept="image/*">
                        <p class="text-xs text-gray-400 mt-2">Gunakan tombol di atas untuk menyisipkan tag HTML seperti <strong>&lt;strong&gt;</strong>, <strong>&lt;em&gt;</strong>, bullet list, dan list bernomor agar lebih mudah ditulis.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-himatep-green text-white font-bold py-3 rounded-xl hover:bg-blue-800 transition">
                            <?= $edit_data ? 'Simpan Perubahan' : 'Terbitkan Berita' ?>
                        </button>
                        <?php if(!$edit_data): ?>
                            <button type="reset" onclick="setTimeout(updatePreview, 10)" class="px-6 py-3 border border-gray-300 text-gray-400 font-bold rounded-xl active:bg-gray-200 hover:bg-gray-200 transition">
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
                    <div class="p-4 bg-[#1E2F4D] font-bold flex justify-between items-center">
                        <span>Live Preview (Tampilan Berita)</span>
                        <span class="text-xs font-normal text-gray-400 italic">Pratinjau</span>
                    </div>
                    <div class="p-8 max-w-2xl mx-auto">
                        <div id="preview-category" class="inline-block px-3 py-1 rounded-full text-xs font-bold text-white bg-blue-600 mb-4 uppercase tracking-wider">KATEGORI</div>
                        <h1 id="preview-title" class="text-3xl font-bold text-gray-900 mb-4 leading-tight">Judul Berita Akan Muncul Di Sini</h1>
                        <div class="flex items-center gap-3 mb-6 text-sm text-gray-400 pb-6 border-b">
                            <span id="preview-author" class="font-semibold text-white">Penulis</span>
                            <span>•</span>
                            <span id="preview-date"><?= date('d M Y') ?></span>
                        </div>
                        <img id="preview-image" src="https://via.placeholder.com/800x400" class="w-full h-64 object-cover rounded-2xl mb-8 shadow-lg">
                        <div id="preview-content" class="prose prose-blue max-w-none text-gray-300 leading-relaxed">
                            Konten berita Anda akan tampil di sini secara real-time...
                        </div>
                    </div>
                </div>

                <!-- List -->
                <div class="bg-[#1E2F4D] rounded-2xl shadow-sm border border-white/20 overflow-hidden">
                <div class="p-4 bg-[#1E2F4D] font-bold">Daftar Berita</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-100 text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="p-4">Preview</th>
                                <th class="p-4">Judul</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($berita_list as $b): ?>
                            <tr>
                                <td class="p-4">
                                    <img src="<?= htmlspecialchars($b['gambar']) ?>" class="w-16 h-10 object-cover rounded shadow-sm" onerror="this.src='https://via.placeholder.com/100x60'">
                                </td>
                                <td class="p-4 font-medium"><?= htmlspecialchars($b['judul']) ?></td>
                                <td class="p-4 text-xs"><?= htmlspecialchars($b['kategori']) ?></td>
                                <td class="p-4 space-x-2">
                                    <a href="../detail-berita.php?slug=<?= $b['slug'] ?>" target="_blank" class="text-green-600 font-bold hover:underline">Lihat</a>
                                    <a href="?edit=1&id=<?= $b['id'] ?>" class="text-blue-600 font-bold">Edit</a>
                                    <a href="?delete=1&id=<?= $b['id'] ?>" class="text-red-600 font-bold" onclick="return confirm('Hapus berita ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <script>
        // Live Preview Logic
        const fields = ['judul', 'kategori', 'penulis', 'gambar', 'isi', 'tanggal_posting'];
        const previewMap = {
            judul: 'preview-title',
            kategori: 'preview-category',
            penulis: 'preview-author',
            gambar: 'preview-image',
            isi: 'preview-content',
            tanggal_posting: 'preview-date'
        };

        function getTextSelectionRange(textarea) {
            return [textarea.selectionStart, textarea.selectionEnd];
        }

        function replaceSelection(textarea, before, after = '') {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end) || 'Teks';
            const replacement = before + selected + after;
            textarea.setRangeText(replacement, start, end, 'end');
            textarea.focus();
            updatePreview();
        }

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
                // Fallback jika Alpine belum siap
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
                formData.append('type', 'berita');

                try {
                    alpineData.showImageModal = false; // Close modal
                    const response = await fetch('ajax_upload.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        const caption = alpineData.imageCaptionInput;
                        let replacement = `\n<figure class="my-6">\n    <img src="${result.path}" alt="${caption || 'Gambar Berita'}" class="w-full rounded-2xl shadow-md">\n`;
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

            let replacement = `\n<figure class="my-6">\n    <img src="${url}" alt="${caption || 'Gambar Berita'}" class="w-full rounded-2xl shadow-md">\n`;
            if (caption) {
                replacement += `    <figcaption class="text-center text-sm text-gray-400 mt-2 italic">${caption}</figcaption>\n`;
            }
            replacement += `</figure>\n`;

            textarea.setRangeText(replacement, textarea.selectionStart, textarea.selectionEnd, 'end');
            textarea.focus();
            
            alpineData.showImageModal = false;
            updatePreview();
        }

        function normalizePreviewContent(content) {
            const rawContent = (content || '').trim();

            if (!rawContent) {
                return '';
            }

            const expanded = rawContent.replace(/<(\/?)(ul|ol|li|p|h2|h3|blockquote|div|section|article|table|thead|tbody|tr|td|th)\b([^>]*)>/gi, '\n<$1$2$3>\n'.replace(/\\n/g, '\n'));
            const lines = expanded.split(/\r?\n/);
            const blocks = [];
            let paragraphLines = [];
            let listItems = [];
            let listType = null;

            const flushParagraph = () => {
                if (!paragraphLines.length) return;
                blocks.push(`<p>${paragraphLines.map(line => line.trim()).join('<br>')}</p>`);
                paragraphLines = [];
            };

            const flushList = () => {
                if (!listType || !listItems.length) return;
                const tag = listType === 'ol' ? 'ol' : 'ul';
                blocks.push(`<${tag}>${listItems.map(item => `<li>${item}</li>`).join('')}</${tag}>`);
                listType = null;
                listItems = [];
            };

            lines.forEach(line => {
                const trimmed = line.trim();
                if (!trimmed) {
                    flushParagraph();
                    flushList();
                    return;
                }

                if (/^<\/?(?:ul|ol|li|p|h2|h3|blockquote|div|section|article|table|thead|tbody|tr|td|th)\b[^>]*>$/i.test(trimmed)) {
                    flushParagraph();
                    flushList();
                    blocks.push(trimmed);
                    return;
                }

                if (/^(-|\*|•)\s+/.test(trimmed)) {
                    flushParagraph();
                    if (listType !== 'ul') {
                        flushList();
                        listType = 'ul';
                    }
                    listItems.push(trimmed.replace(/^(-|\*|•)\s+/, ''));
                    return;
                }

                if (/^\d+\.\s+/.test(trimmed)) {
                    flushParagraph();
                    if (listType !== 'ol') {
                        flushList();
                        listType = 'ol';
                    }
                    listItems.push(trimmed.replace(/^\d+\.\s+/, ''));
                    return;
                }

                flushList();
                paragraphLines.push(trimmed);
            });

            flushParagraph();
            flushList();
            return blocks.join('\n');
        }

        function renderPreviewContent(rawContent) {
            let content = normalizePreviewContent(rawContent);

            if (!content) {
                return 'Konten berita Anda akan tampil di sini secara real-time...';
            }

            // Adjust relative image paths for admin preview
            return content.replace(/src="images\//g, 'src="../images/');
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
                    } else if (field === 'isi') {
                        preview.innerHTML = renderPreviewContent(input.value);
                    } else if (field === 'kategori') {
                        preview.innerText = input.value.toUpperCase() || 'KATEGORI';
                        const colorInput = document.querySelector('[name="kategori_color"]');
                        const colors = {
                            green: 'bg-green-600',
                            blue: 'bg-blue-600',
                            purple: 'bg-purple-600',
                            yellow: 'bg-yellow-500',
                            red: 'bg-red-600'
                        };
                        preview.className = `inline-block px-3 py-1 rounded-full text-xs font-bold text-white mb-4 uppercase tracking-wider ${colors[colorInput.value]}`;
                    } else {
                        preview.innerText = input.value || (field === 'judul' ? 'Judul Berita Akan Muncul' : '...');
                    }
                }
            });
        }

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

        // Init
        updatePreview();
    </script>
    <!-- Script Alpine.js untuk fitur interaktif -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
