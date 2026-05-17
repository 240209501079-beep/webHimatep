<?php
// Tentukan base path untuk menghindari error path
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_admin = ($current_dir == 'admin');
$base_path = $is_admin ? '../' : '';

$is_home = ($current_page == 'index.php');
$prefix = $is_home ? '' : $base_path . 'index.php';

// Fungsi helper untuk active state (cek jika belum ada)
if (!function_exists('is_nav_active')) {
    /**
     * Cek apakah halaman aktif untuk navbar
     * @param array|string $pages
     */
    function is_nav_active(array|string $pages): bool {
        $cp = basename($_SERVER['PHP_SELF']);
        if (is_array($pages)) {
            return in_array($cp, $pages);
        }
        return $cp == $pages;
    }
}
?>

<!-- Navbar -->
<nav class="fixed w-full z-[100] pt-4" id="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20 bg-white backdrop-blur-xl rounded-full px-6 shadow-md border border-gray-400 transition-all duration-300">
            <div class="flex items-center gap-3">
                <img src="<?= $base_path ?>images/logo-himatep.png" alt="Logo" class="h-10 w-10 rounded-full bg-gray-100"
                    onerror="this.src='https://via.placeholder.com/50x50.png?text=Logo'">
                <span class="font-bold text-xs md:text-sm leading-tight text-himatep-dark">HIMATEP<br>FIP<br>UNM</span>
            </div>
            <div class="hidden md:flex space-x-4 lg:space-x-8 text-sm lg:text-base">
                <a href="<?= $prefix ?>#hero" class="nav-link whitespace-nowrap <?= $is_home ? 'text-himatep-green font-bold' : 'text-gray-600' ?> font-medium hover:text-himatep-green transition">Beranda</a>
                <a href="<?= $prefix ?>#profile" class="nav-link whitespace-nowrap <?= is_nav_active('profile.php') ? 'text-himatep-green font-bold' : 'text-gray-600' ?> font-medium hover:text-himatep-green transition">Profile</a>
                <a href="<?= $prefix ?>#proker" class="nav-link whitespace-nowrap <?= is_nav_active(['proker.php', 'detail-program.php']) ? 'text-himatep-green font-bold' : 'text-gray-600' ?> font-medium hover:text-himatep-green transition">Program Kerja</a>
                <a href="<?= $prefix ?>#kalender" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Agenda</a>
                <a href="<?= $prefix ?>#berita" class="nav-link whitespace-nowrap <?= is_nav_active(['berita.php', 'detail-berita.php']) ? 'text-himatep-green font-bold' : 'text-gray-600' ?> font-medium hover:text-himatep-green transition">Berita</a>
                <a href="<?= $prefix ?>#aspirasi" class="nav-link whitespace-nowrap text-gray-600 font-medium hover:text-himatep-green transition">Suara Mahasiswa</a>
            </div>
            <div class="hidden md:flex items-center gap-4">
                <a href="<?= $base_path ?>admin/login.php" class="text-gray-400 hover:text-himatep-green transition-all p-2 rounded-full hover:bg-himatep-light" title="Admin Panel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </a>
                <div class="relative" x-data="{ dropdownOpen: false }" @mouseenter="dropdownOpen = true" @mouseleave="dropdownOpen = false">
                    <a href="<?= $prefix ?>#kontak" class="bg-himatep-green hover:opacity-90 text-white px-6 py-2 rounded-full font-medium transition shadow-md flex items-center gap-2 focus:outline-none">
                        Narahubung <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 border border-gray-400 z-50" style="display: none;">
                        <?php
                        // Pastikan koneksi database tersedia
                        if (!isset($pdo)) {
                            $config_path = dirname(__DIR__, 2) . '/private/php/config.php';
                            if (file_exists($config_path)) {
                                require_once $config_path;
                            }
                        }
                        
                        // Cek fungsi icon
                        if (!function_exists('get_contact_svg')) {
                            $icons_path = __DIR__ . '/icons.php';
                            if (file_exists($icons_path)) {
                                require_once $icons_path;
                            }
                        }

                        $nav_contacts = [];
                        if (isset($pdo) && $pdo instanceof PDO) {
                            try {
                                $stmt_nav = $pdo->query("SELECT * FROM contacts WHERE is_active = 1 ORDER BY sort_order ASC");
                                $nav_contacts = $stmt_nav->fetchAll();
                            } catch (PDOException $e) {
                                // Fallback jika query gagal
                            }
                        }
                        
                        if (!empty($nav_contacts)):
                            foreach ($nav_contacts as $nc): 
                                $link = $nc['value'];
                                // Pastikan link WA valid
                                if ($nc['platform'] === 'WhatsApp' && strpos($link, 'http') !== 0) {
                                    $link = "https://wa.me/" . preg_replace('/[^0-9]/', '', $link);
                                }
                        ?>
                            <a href="<?= $link ?>" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-himatep-light hover:text-himatep-green transition flex items-center gap-2">
                                <?= function_exists('get_contact_svg') ? get_contact_svg($nc['icon'], 'w-4 h-4') : '' ?> <?= htmlspecialchars($nc['label']) ?>
                            </a>
                        <?php endforeach; 
                        else: ?>
                            <div class="px-4 py-2 text-sm text-gray-400 italic">Kontak tidak tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Mobile menu button -->
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
    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="md:hidden absolute top-24 left-4 right-4 bg-white rounded-3xl shadow-2xl border border-gray-100 py-6 px-6 z-[90]"
         style="display: none;">
        <div class="flex flex-col space-y-4">
            <a href="<?= $prefix ?>#hero" @click="mobileMenuOpen = false" class="font-bold text-lg <?= $is_home ? 'text-himatep-green' : 'text-gray-600' ?> hover:text-himatep-green flex items-center justify-between">
                Beranda <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="<?= $prefix ?>#profile" @click="mobileMenuOpen = false" class="font-bold text-lg <?= is_nav_active('profile.php') ? 'text-himatep-green' : 'text-gray-600' ?> hover:text-himatep-green flex items-center justify-between">
                Profile <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="<?= $prefix ?>#proker" @click="mobileMenuOpen = false" class="font-bold text-lg <?= is_nav_active(['proker.php', 'detail-program.php']) ? 'text-himatep-green' : 'text-gray-600' ?> hover:text-himatep-green flex items-center justify-between">
                Program Kerja <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="<?= $prefix ?>#kalender" @click="mobileMenuOpen = false" class="text-gray-600 font-bold text-lg hover:text-himatep-green flex items-center justify-between">
                Agenda <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="<?= $prefix ?>#berita" @click="mobileMenuOpen = false" class="font-bold text-lg <?= is_nav_active(['berita.php', 'detail-berita.php']) ? 'text-himatep-green' : 'text-gray-600' ?> hover:text-himatep-green flex items-center justify-between">
                Berita <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="<?= $prefix ?>#aspirasi" @click="mobileMenuOpen = false" class="text-gray-600 font-bold text-lg hover:text-himatep-green flex items-center justify-between">
                Suara Mahasiswa <svg class="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <hr class="border-gray-100">
            <div class="grid grid-cols-2 gap-3">
                <a href="<?= $base_path ?>admin/login.php" class="bg-gray-100 text-gray-600 py-3 rounded-2xl font-bold text-center text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Admin
                </a>
                <a href="<?= $prefix ?>#kontak" @click="mobileMenuOpen = false" class="bg-himatep-green text-white py-3 rounded-2xl font-bold text-center text-sm">
                    Narahubung
                </a>
            </div>
        </div>
    </div>
</nav>
