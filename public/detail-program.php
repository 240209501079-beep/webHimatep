<?php
require_once '../private/php/config.php';

// Ambil Slug dari URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$proker = null;

if (!empty($slug)) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, a.tanggal_event, a.waktu, a.lokasi 
            FROM proker p 
            LEFT JOIN agenda a ON p.id = a.proker_id 
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        $proker = $stmt->fetch();
    } catch (PDOException $e) {
        $proker = null;
    }
}

// Fallback ID
if (!$proker && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT p.*, a.tanggal_event, a.waktu, a.lokasi FROM proker p LEFT JOIN agenda a ON p.id = a.proker_id WHERE p.id = ?");
    $stmt->execute([$id]);
    $proker = $stmt->fetch();
}

// Persiapkan data untuk Alpine.js
$proker_json = json_encode($proker ? [
    'id' => $proker['id'],
    'judul' => $proker['judul'],
    'divisi' => $proker['divisi'],
    'divisiColor' => $proker['divisi_color'],
    'gambar' => $proker['gambar'],
    'icon' => $proker['icon'],
    'ringkasan' => $proker['ringkasan'],
    'target' => $proker['target'],
    'sasaran' => $proker['sasaran'],
    'isi' => $proker['isi'],
    'agenda' => $proker['tanggal_event'] ? [
        'date' => $proker['tanggal_event'],
        'waktu' => $proker['waktu'],
        'lokasi' => $proker['lokasi']
    ] : null
] : null);

// SEO Meta Data
$page_title = $proker ? $proker['judul'] . " - Program Kerja HIMATEP" : "Program Tidak Ditemukan - HIMATEP FIP UNM";
$page_desc = $proker ? mb_strimwidth(strip_tags($proker['ringkasan']), 0, 160, "...") : "Detail Program Kerja HIMATEP FIP UNM.";
$page_img = $proker ? $proker['gambar'] : "http://localhost/webHimatep/public/images/logo-himatep.png";
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page_img) ?>">

    <title><?= htmlspecialchars($page_title) ?></title>
    <?php include 'includes/meta_icons.php'; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'himatep-green': '#1B2945',
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
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        .program-content a {
            color: #1B2945;
            text-decoration: underline;
            font-weight: 600;
        }

        .program-content a:hover {
            color: #1d4ed8;
        }

        .program-content ul {
            list-style: disc;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        .program-content ol {
            list-style: decimal;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        .program-content li {
            margin: 0.35rem 0;
        }

        .program-content h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #111;
            line-height: 1.2;
        }

        .program-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #111;
            line-height: 1.2;
        }

        .program-content img {
            width: 100%;
            height: auto;
            border-radius: 1rem;
            margin: 1.5rem 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .program-content figcaption {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: -0.75rem;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
    </style>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        const fetchedProker = <?php echo $proker_json; ?>;
    </script>

    <!-- Script Pembaca ID -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bacaProgram', () => ({
                program: null,
                loading: true,
                error: false,
                init() {
                    if (fetchedProker) {
                        this.program = fetchedProker;
                        document.title = fetchedProker.judul + " - Program Kerja HIMATEP FIP UNM";
                        this.error = false;
                    } else {
                        this.error = true;
                    }
                    this.loading = false;
                }
            }));
        });
    </script>

</head>

<body class="font-sans bg-gray-50 text-himatep-dark overflow-x-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Navbar -->
    <?php 
    $root_path = '';
    include 'includes/navbar.php'; 
    ?>

    <!-- Konten Pembaca Program -->
    <main x-data="bacaProgram" class="pt-32 pb-20 min-h-screen relative">
        <!-- Loader -->
        <div x-show="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-himatep-green"></div>
        </div>

        <!-- Pesan Error -->
        <div x-show="error" style="display: none;" class="max-w-3xl mx-auto px-4 text-center py-20">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Program Tidak Ditemukan</h1>
            <p class="text-gray-600 mb-8">Maaf, program kerja yang Anda cari mungkin telah dihapus atau ID tidak valid.
            </p>
            <a href="proker.php"
                class="bg-himatep-green hover:opacity-90 text-white font-bold py-3 px-8 rounded-full transition shadow-lg inline-block">Kembali
                ke Katalog Program</a>
        </div>

        <!-- Detail Program Utama -->
        <article x-show="!loading && !error && program" class="max-w-4xl mx-auto px-4">

            <header class="mb-10 text-center">
                <a href="proker.php"
                    class="inline-flex items-center text-gray-500 hover:text-himatep-green mb-8 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Program Kerja
                </a>

                <div class="mb-6 flex justify-center">
                    <div class="w-24 h-24 rounded-3xl flex items-center justify-center shadow-lg transform rotate-3"
                        :class="'bg-' + program?.divisiColor + '-100 text-' + program?.divisiColor + '-600'">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-bind:d="program?.icon"></path>
                        </svg>
                    </div>
                </div>

                <div class="mb-4">
                    <span
                        class="text-sm font-bold px-4 py-1 rounded-full shadow-sm uppercase tracking-wider bg-gray-100 text-gray-700"
                        x-text="'Divisi ' + program?.divisi"></span>
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight" x-text="program?.judul">
                </h1>

                <div class="flex justify-center gap-4 md:gap-8 flex-wrap">
                    <div
                        class="flex items-center text-sm font-medium text-gray-600 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-400">
                        <svg class="w-5 h-5 mr-2" :class="'text-' + program?.divisiColor + '-500'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>Target: <span x-text="program?.target"></span></span>
                    </div>
                    <div
                        class="flex items-center text-sm font-medium text-gray-600 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-400">
                        <svg class="w-5 h-5 mr-2" :class="'text-' + program?.divisiColor + '-500'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span>Sasaran: <span x-text="program?.sasaran"></span></span>
                    </div>
                </div>
            </header>

            <!-- Gambar Utama / Flyer -->
            <figure class="mb-12 mt-8" x-show="program?.gambar">
                <img :src="program?.gambar" :alt="program?.judul"
                    class="w-full h-[300px] md:h-[500px] object-cover rounded-3xl shadow-xl border border-gray-400">
            </figure>

            <!-- Isi Konten Artikel -->
            <div class="program-content prose prose-lg prose-blue max-w-none text-gray-700 leading-relaxed bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-gray-400 mt-10"
                x-html="program?.isi">
                <!-- Konten di-inject di sini -->
            </div>

        </article>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>

</html>


