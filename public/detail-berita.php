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
    <link rel="stylesheet" href="css/style.css">

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

    <!-- Script Pembaca -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bacaBerita', () => ({
                berita: null,
                loading: true,
                error: false,
                init() {
                    if (fetchedBerita) {
                        this.berita = fetchedBerita;
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

    <!-- Konten Pembaca Berita -->
    <main x-data="bacaBerita" class="pt-32 pb-20 min-h-screen relative">
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

            <!-- Share Box (Statik simulasi) -->
            <div
                class="mt-12 bg-himatep-light p-6 rounded-2xl border border-green-100 flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h4 class="font-bold text-gray-800">Bagikan artikel ini:</h4>
                </div>
                <div class="flex space-x-3">
                    <button
                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-green-600 shadow-sm hover:shadow-md transition"><svg
                            class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                        </svg></button>
                    <button
                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-green-600 shadow-sm hover:shadow-md transition"><svg
                            class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg></button>
                    <button
                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-green-600 shadow-sm hover:shadow-md transition"><svg
                            class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg></button>
                </div>
            </div>
        </article>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>

</html>


