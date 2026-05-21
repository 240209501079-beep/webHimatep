<?php
require_once '../private/php/config.php';

// Ambil Slug dari URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$berita = null;

if (!empty($slug)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM berita WHERE slug = ?");
        $stmt->execute([$slug]);
        $berita = $stmt->fetch();
    } catch (PDOException $e) {
        $berita = null;
    }
}

// Jika berita tidak ditemukan, ID fallback (opsional)
if (!$berita && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
    $stmt->execute([$id]);
    $berita = $stmt->fetch();
}

// Persiapkan data untuk Alpine.js
$berita_json = json_encode($berita ? [
    'id' => $berita['id'],
    'judul' => $berita['judul'],
    'kategori' => $berita['kategori'],
    'kategoriColor' => $berita['kategori_color'],
    'tanggal' => $berita['tanggal_posting'],
    'penulis' => $berita['penulis'],
    'gambar' => $berita['gambar'],
    'isi' => $berita['isi']
] : null);

// SEO Meta Data
$page_title = $berita ? $berita['judul'] . " - HIMATEP FIP UNM" : "Berita Tidak Ditemukan - HIMATEP FIP UNM";
$page_desc = $berita ? mb_strimwidth(strip_tags($berita['ringkasan']), 0, 160, "...") : "Membaca berita terbaru dari HIMATEP FIP UNM.";
$page_img = $berita ? $berita['gambar'] : "http://localhost/webHimatep/public/images/logo-himatep.png";
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta name="author" content="<?= htmlspecialchars($berita['penulis'] ?? 'HIMATEP FIP UNM') ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page_img) ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">

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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=1.4">

    <style>
        .news-content a {
            color: #1B2945;
            text-decoration: underline;
            font-weight: 600;
        }

        .news-content a:hover {
            color: #1d4ed8;
        }

        .news-content ul {
            list-style: disc;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        .news-content ol {
            list-style: decimal;
            padding-left: 1.5rem;
            margin: 0.75rem 0;
        }

        .news-content li {
            margin: 0.35rem 0;
        }

        .news-content h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #111;
            line-height: 1.2;
        }

        .news-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #111;
            line-height: 1.2;
        }

        .news-content img {
            width: 100%;
            height: auto;
            border-radius: 1rem;
            margin: 1.5rem 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .news-content figcaption {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: -0.75rem;
            margin-bottom: 1.5rem;
            font-style: italic;
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        const fetchedBerita = <?php echo $berita_json; ?>;
    </script>



</head>

<body class="font-sans bg-gray-50 text-himatep-dark overflow-x-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Navbar -->
    <?php 
    $root_path = '';
    include 'includes/navbar.php'; 
    ?>

    <!-- Konten Pembaca Berita -->
    <main x-data="{
        berita: null,
        loading: true,
        error: false,
        toastOpen: false,
        toastMessage: '',
        init() {
            if (fetchedBerita) {
                this.berita = fetchedBerita;
                this.error = false;
            } else {
                this.error = true;
            }
            this.loading = false;
        },
        showToast(message) {
            this.toastMessage = message;
            this.toastOpen = true;
            setTimeout(() => {
                this.toastOpen = false;
            }, 3000);
        },
        shareTo(platform) {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(this.berita ? this.berita.judul : document.title);
            if (platform === 'x') {
                window.open(`https://x.com/intent/tweet?text=${title}&url=${url}`, '_blank');
            } else if (platform === 'wa') {
                window.open(`https://api.whatsapp.com/send?text=${title}%20${url}`, '_blank');
            } else if (platform === 'ig') {
                navigator.clipboard.writeText(window.location.href);
                this.showToast('Link disalin! Membuka Instagram...');
                window.open('https://www.instagram.com/', '_blank');
            } else if (platform === 'copy') {
                navigator.clipboard.writeText(window.location.href);
                this.showToast('Link disalin ke clipboard!');
            }
        }
    }" class="pt-32 pb-20 min-h-screen relative">
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
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Berita Tidak Ditemukan</h1>
            <p class="text-gray-600 mb-8">Maaf, berita yang Anda cari mungkin telah dihapus atau ID tidak valid.</p>
            <a href="berita.php"
                class="bg-himatep-green hover:bg-blue-900 text-white font-bold py-3 px-8 rounded-full transition shadow-lg inline-block">Kembali
                ke Portal Berita</a>
        </div>

        <!-- Artikel Utama -->
        <article x-show="!loading && !error && berita" style="display: none;" class="max-w-4xl mx-auto px-4">

            <!-- Header Artikel -->
            <header class="mb-10 text-center">
                <a href="berita.php"
                    class="inline-flex items-center text-gray-500 hover:text-himatep-green mb-8 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Arsip
                </a>

                <div class="mb-4">
                    <span class="text-sm font-bold px-4 py-1 rounded-full shadow-sm uppercase tracking-wider"
                        :class="'text-' + berita?.kategoriColor + '-700 bg-' + berita?.kategoriColor + '-100 border border-' + berita?.kategoriColor + '-200'"
                        x-text="berita?.kategori"></span>
                </div>

                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight" x-text="berita?.judul"></h1>

                <div class="flex items-center justify-center text-sm text-gray-500 gap-4 md:gap-8 flex-wrap">
                    <span class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg> <span x-text="berita?.tanggal"></span></span>
                    <span class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg> Oleh: <span class="font-medium ml-1" x-text="berita?.penulis"></span></span>
                </div>
            </header>

            <!-- Gambar Utama -->
            <figure class="mb-12">
                <img :src="berita?.gambar" :alt="berita?.judul"
                    class="w-full h-[300px] md:h-[500px] object-cover rounded-3xl shadow-xl border border-gray-400">
            </figure>

            <!-- Isi Konten Artikel -->
            <div class="news-content prose prose-lg prose-blue max-w-none text-gray-700 leading-relaxed bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-gray-400"
                x-html="berita?.isi">
                <!-- Konten akan di-inject di sini oleh Alpine -->
            </div>

            <!-- Share Box (Premium Interactive) -->
            <div
                class="mt-12 bg-himatep-light p-6 rounded-2xl border border-green-100 flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h4 class="font-bold text-gray-800">Bagikan artikel ini:</h4>
                </div>
                <div class="flex space-x-3">
                    <!-- WhatsApp -->
                    <button @click="shareTo('wa')"
                        class="w-10 h-10 bg-white text-green-600 rounded-full flex items-center justify-center hover:bg-green-50 shadow-sm hover:shadow-md transition"
                        title="Bagikan ke WhatsApp">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.18 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.63-1.023-5.101-2.885-6.968C16.632 2.006 14.162.979 11.999.979 6.562.979 2.137 5.348 2.133 10.781c-.001 1.776.471 3.51 1.363 5.062l-.988 3.61 3.738-.98c1.513.824 3.018 1.258 4.401 1.258h.001zm10.725-7.37c-.3-.15-1.772-.874-2.047-.973-.275-.1-.475-.15-.675.15-.2.3-.775.973-.95 1.173-.175.2-.35.225-.65.075-.3-.15-1.267-.467-2.415-1.492-.893-.797-1.496-1.782-1.671-2.081-.175-.3-.018-.462.13-.61.135-.133.3-.35.45-.525.15-.175.2-.3.3-.5s.05-.375-.025-.525c-.075-.15-.675-1.624-.925-2.225-.244-.582-.491-.504-.675-.513-.175-.008-.375-.01-.575-.01-.2 0-.525.075-.8 0.375-.275.3-1.05 1.025-1.05 2.5s1.075 2.9 1.225 3.1c.15.2 2.11 3.224 5.112 4.521.714.308 1.272.493 1.707.632.717.228 1.369.196 1.885.119.574-.086 1.772-.724 2.022-1.424.25-.7.25-1.3.175-1.424-.075-.125-.275-.2-.575-.35z"/>
                        </svg>
                    </button>
                    
                    <!-- X (Twitter) -->
                    <button @click="shareTo('x')"
                        class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-gray-50 shadow-sm hover:shadow-md transition"
                        title="Bagikan ke X">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </button>
                    
                    <!-- Instagram -->
                    <button @click="shareTo('ig')"
                        class="w-10 h-10 bg-white text-pink-600 rounded-full flex items-center justify-center hover:bg-pink-50 shadow-sm hover:shadow-md transition"
                        title="Bagikan ke Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204 0.013-3.583 0.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </button>
                    
                    <!-- Copy Link -->
                    <button @click="shareTo('copy')"
                        class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-50 shadow-sm hover:shadow-md transition"
                        title="Salin Tautan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
                </article>
        <!-- Floating Toast Notification -->
        <div x-show="toastOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="fixed bottom-8 right-8 z-[150] bg-himatep-green text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-blue-400/20"
             x-cloak
             style="display: none;">
            <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-semibold" x-text="toastMessage"></span>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>

</html>


