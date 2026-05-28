<?php
session_start();
require_once '../../private/php/config.php';
require_once '../includes/icons.php';

// Cek sesi login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';

// Proses Hapus
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "Kontak berhasil dihapus.";
    }
    header("Location: manage_contacts.php");
    exit;
}

// Proses Tambah / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $platform = $_POST['platform'];
    $label = trim($_POST['label']);
    $value = trim($_POST['value']);
    $icon = $_POST['icon'] ?? 'whatsapp';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)$_POST['sort_order'];

    if ($platform === 'WhatsApp') {
        $icon = 'whatsapp';
        // Bersihkan karakter non-numerik (seperti +, -, spasi)
        $clean_value = preg_replace('/[^0-9]/', '', $value);
        
        if (!empty($clean_value)) {
            // Jika diawali '08', ganti '0' dengan '62'
            if (strpos($clean_value, '0') === 0) {
                $clean_value = '62' . substr($clean_value, 1);
            } 
            // Jika diawali '8', tambahkan '62'
            elseif (strpos($clean_value, '8') === 0) {
                $clean_value = '62' . $clean_value;
            }
            // Jika sudah 62, biarkan
            
            $value = "https://wa.me/" . $clean_value;
        }
    } elseif ($platform === 'Email') {
        $icon = 'email';
        if (strpos($value, 'mailto:') === false && strpos($value, '@') !== false) {
            $value = "mailto:" . $value;
        }
    }

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO contacts (platform, label, value, icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$platform, $label, $value, $icon, $is_active, $sort_order])) {
            $_SESSION['message'] = "Kontak berhasil ditambahkan.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE contacts SET platform=?, label=?, value=?, icon=?, is_active=?, sort_order=? WHERE id=?");
        if ($stmt->execute([$platform, $label, $value, $icon, $is_active, $sort_order, $id])) {
            $_SESSION['message'] = "Kontak berhasil diperbarui.";
        }
    }
    header("Location: manage_contacts.php");
    exit;
}

// Menangkap Flash Message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Ambil semua data kontak
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY platform, sort_order ASC");
$contacts = $stmt->fetchAll();

// List icons available for Social Media
$available_icons = ['instagram', 'facebook', 'twitter', 'youtube', 'tiktok', 'linkedin'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Narahubung - Admin HIMATEP</title>
    <?php include '../includes/meta_icons.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
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
<body class="bg-[#111A2C] text-white flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, modalOpen: false, modalMode: 'add', form: { id: '', platform: 'WhatsApp', label: '', value: '', icon: 'whatsapp', is_active: 1, sort_order: 0 } }">
    
    <!-- Sidebar -->
    <?php include "includes/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-20 bg-[#1E2F4D] shadow-sm flex items-center justify-between px-8 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-[#1B2945] hover:bg-white/10 text-white border border-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-2xl font-bold text-white">Kelola Narahubung</h2>
            </div>
            <button @click="modalMode = 'add'; form = { id: '', platform: 'WhatsApp', label: '', value: '', icon: 'whatsapp', is_active: 1, sort_order: 0 }; modalOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-bold transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kontak
            </button>
        </header>

        <div class="flex-1 p-8 overflow-y-auto">
            <?php if ($message): ?>
                <div class="mb-6 p-4 bg-green-900/50 border border-green-500/20 text-green-200 shadow-sm rounded-r-lg">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="bg-[#1E2F4D] rounded-2xl shadow-sm border border-white/10 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-[#1E2F4D] text-gray-400 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Platform</th>
                            <th class="px-6 py-4">Label</th>
                            <th class="px-6 py-4">Link / Value</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <?php foreach ($contacts as $contact): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-500/10 border border-blue-500/20 rounded-lg flex items-center justify-center text-blue-400">
                                            <?= get_contact_svg($contact['icon'], "w-5 h-5") ?>
                                        </div>
                                        <span class="font-bold text-white"><?= $contact['platform'] ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-white"><?= htmlspecialchars($contact['label']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400 truncate max-w-xs"><?= htmlspecialchars($contact['value']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($contact['is_active']): ?>
                                        <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-xs font-bold">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-white/5 text-gray-400 border border-white/10 rounded-full text-xs font-bold">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button @click="modalMode = 'edit'; form = <?= htmlspecialchars(json_encode($contact)) ?>; modalOpen = true" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-white/5 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <a href="?delete=<?= $contact['id'] ?>" onclick="return confirm('Hapus kontak ini?')" class="p-2 text-red-400 hover:text-red-300 hover:bg-white/5 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($contacts)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">Belum ada data kontak.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="modalOpen = false" class="bg-[#1E2F4D] rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-[#1E2F4D]">
                <h3 class="text-xl font-bold text-white" x-text="modalMode === 'add' ? 'Tambah Kontak Baru' : 'Edit Kontak'"></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="manage_contacts.php" method="POST" class="p-8 space-y-4">
                <input type="hidden" name="action" :value="modalMode">
                <input type="hidden" name="id" :value="form.id">

                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-1">Platform</label>
                    <select name="platform" x-model="form.platform" class="w-full px-4 py-2 rounded-xl bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="WhatsApp">WhatsApp</option>
                        <option value="Email">Email</option>
                        <option value="Social Media">Social Media</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-1">Label / Nama</label>
                    <input type="text" name="label" x-model="form.label" :placeholder="form.platform === 'WhatsApp' ? 'Contoh: Humas 1' : (form.platform === 'Email' ? 'Contoh: Email Resmi' : 'Contoh: Instagram @himatep')" required class="w-full px-4 py-2 rounded-xl bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-200 mb-1">Nilai / Link</label>
                    <input type="text" name="value" x-model="form.value" :placeholder="form.platform === 'WhatsApp' ? '628...' : (form.platform === 'Email' ? 'himatep@...' : 'https://...')" required class="w-full px-4 py-2 rounded-xl bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[10px] text-gray-400 mt-1" x-show="form.platform === 'WhatsApp'">*Bisa masukkan 08..., 8..., atau 62..., sistem akan otomatis menyesuaikan.</p>
                </div>

                <div x-show="form.platform === 'Social Media'">
                    <label class="block text-sm font-bold text-gray-200 mb-1">Pilih Icon</label>
                    <div class="grid grid-cols-6 gap-2 mt-2">
                        <?php foreach ($available_icons as $icon): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="icon" value="<?= $icon ?>" x-model="form.icon" class="hidden peer">
                                <div class="w-10 h-10 flex items-center justify-center border border-white/10 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 rounded-lg bg-[#1B2945] text-gray-400 peer-checked:text-blue-400 transition-all">
                                    <?= get_contact_svg($icon, "w-6 h-6") ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center gap-4 py-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" :checked="form.is_active == 1" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 bg-[#1B2945] border border-white/10">
                        <span class="text-sm font-bold text-gray-200">Aktifkan Kontak</span>
                    </label>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-400">Urutan Tampil</label>
                        <input type="number" name="sort_order" x-model="form.sort_order" class="w-20 px-2 py-1 rounded bg-[#1B2945] border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="pt-6 flex gap-4">
                    <button type="button" @click="modalOpen = false" class="flex-1 px-6 py-3 border border-white/10 text-gray-200 font-bold rounded-xl hover:bg-white/5 transition">Batal</button>
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

