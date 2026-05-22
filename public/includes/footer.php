    <?php
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $is_admin = ($current_dir == 'admin');
    $base_path = $is_admin ? '../' : '';
    ?>
    <footer class="bg-himatep-dark text-white py-16 relative overflow-hidden">
        <!-- Decoration -->
        <div
            class="absolute top-0 right-0 w-64 h-64 bg-himatep-green rounded-full blur-3xl opacity-20 transform translate-x-1/2 -translate-y-1/2">
        </div>

        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-4 gap-12 relative z-10">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <img src="<?= $base_path ?>images/logo-himatep.png" alt="Logo" class="h-14 w-14 rounded-full p-1"
                        onerror="this.src='https://via.placeholder.com/50x50.png?text=Logo'">
                    <span class="text-2xl text-white font-bold">HIMATEP FIP UNM</span>
                </div>
                <p class="text-gray-400 mb-6 max-w-md leading-relaxed">Wadah kreasi, inovasi, dan pengabdian mahasiswa
                    Teknologi Pendidikan menuju generasi unggul dan berkarakter.</p>
                <!-- Social Media -->
                <?php
                if (!isset($pdo)) {
                    require_once __DIR__ . '/../../private/php/config.php';
                }
                if (!function_exists('get_contact_svg')) {
                    require_once __DIR__ . '/icons.php';
                }
                try {
                    $stmt_footer = $pdo->query("SELECT * FROM contacts WHERE platform = 'Social Media' AND is_active = 1 ORDER BY sort_order ASC");
                    $footer_sosmed = $stmt_footer->fetchAll();
                } catch (PDOException $e) {
                    $footer_sosmed = [];
                }
                ?>
                <div class="flex space-x-4">
                    <?php foreach ($footer_sosmed as $sm): ?>
                        <a href="<?= $sm['value'] ?>" target="_blank"
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-himatep-green transition" title="<?= $sm['label'] ?>">
                            <?= get_contact_svg($sm['icon'], "w-5 h-5") ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-6 border-gray-800 pb-2 inline-block">Tautan Cepat</h3>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="<?= $base_path ?>index.php#hero" class="hover:text-himatep-light transition flex items-center"><span></span>&rsaquo; Beranda</a></li>
                    <li><a href="<?= $base_path ?>index.php#profile" class="hover:text-himatep-light transition flex items-center"><span
                                ></span>&rsaquo; Profil</a></li>
                    <li><a href="<?= $base_path ?>index.php#proker" class="hover:text-himatep-light transition flex items-center"><span
                                ></span>&rsaquo; Program Kerja</a></li>
                    <li><a href="<?= $base_path ?>index.php#berita" class="hover:text-himatep-light transition flex items-center"><span
                                ></span>&rsaquo; Berita</a></li>
                    <li><a href="<?= $base_path ?>admin/login.php" class="hover:text-himatep-light transition flex items-center"><span
                                ></span>&rsaquo; Admin Login</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-6 border-gray-800 pb-2 inline-block">Sekretariat</h3>
                <address class="text-gray-400 not-italic leading-relaxed">
                    Gedung PKM FIP UNM<br>
                    Kampus Tidung, Gn. Sari<br>
                    Makassar, Sulawesi Selatan<br>
                    Kode Pos 90222
                </address>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 mt-12 pt-8 border-gray-800 text-center text-gray-400 text-sm">
            &copy; 2026 HIMATEP FIP UNM. All rights reserved. Designed with ❤️
        </div>
    </footer>
