-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Bulan Mei 2026 pada 07.01
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `himatep`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `proker_id` int(11) NOT NULL,
  `tanggal_event` date NOT NULL,
  `waktu` varchar(100) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `agenda`
--

INSERT INTO `agenda` (`id`, `proker_id`, `tanggal_event`, `waktu`, `lokasi`, `created_at`) VALUES
(1, 1, '2026-11-15', '08:00 - Selesai', 'Pelataran Pinisi UNM', '2026-04-25 15:37:20'),
(2, 2, '2026-05-20', '08:00 (Kumpul)', 'Desa Pattalassang', '2026-04-25 15:37:20'),
(3, 3, '2026-05-12', '09:00 - 15:00 WITA', 'Lab Komputer FIP', '2026-04-25 15:37:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT '-',
  `jenis` enum('Aspirasi','Kritik','Saran','Laporan') NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('Baru','Dibaca','Selesai') DEFAULT 'Baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `berita`
--

CREATE TABLE `berita` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `kategori_color` varchar(20) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `ringkasan` text NOT NULL,
  `isi` longtext NOT NULL,
  `tanggal_posting` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `berita`
--

INSERT INTO `berita` (`id`, `judul`, `slug`, `kategori`, `kategori_color`, `penulis`, `gambar`, `ringkasan`, `isi`, `tanggal_posting`, `created_at`) VALUES
(1, 'Seminar Nasional Teknologi Pendidikan 2026', 'seminar-nasional-teknologi-pendidikan-2026', 'Kegiatan', 'green', 'Divisi Humas', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80', 'Membahas masa depan AI dalam dunia pendidikan tinggi di Indonesia dengan pembicara ahli.', '<p class=\"mb-4\">\n<p>Himpunan Mahasiswa Teknologi Pendidikan (HIMATEP) FIP UNM sukses menyelenggarakan Seminar Nasional Teknologi Pendidikan 2026 dengan tema &quot;Masa Depan AI dalam Transformasi Pendidikan Tinggi di Indonesia&quot;. Acara ini berlangsung meriah di Auditorium Ammanagappa dan dihadiri oleh lebih dari 500 peserta dari berbagai perguruan tinggi.</p>\n</p>\n<p class=\"mb-4\">\n<p>Seminar ini menghadirkan tiga pembicara pakar di bidang kecerdasan buatan dan teknologi pembelajaran. Mereka menyoroti urgensi adaptasi dosen dan mahasiswa terhadap perangkat AI generatif, bukan sebagai ancaman, melainkan sebagai alat bantu kognitif yang kuat.</p>\n</p>\n<h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">\n<p>Poin Penting Seminar</p>\n</h3>\n<ul class=\"list-disc pl-5 mb-4 text-gray-700 space-y-2\">\n<li>\n<p>Integrasi AI dalam kurikulum merdeka belajar.</p>\n</li>\n<li>\n<p>Etika penggunaan ChatGPT dan AI lainnya dalam penulisan karya ilmiah.</p>\n</li>\n<li>\n<p>Bagaimana lulusan Teknologi Pendidikan memposisikan diri di era otomatisasi cerdas.</p>\n</li>\n</ul>\n<p class=\"mb-4\">\n<p>Ketua Umum HIMATEP, Andi Syahrul, dalam sambutannya menyampaikan harapannya agar kegiatan ini menjadi katalisator inovasi mahasiswa FIP UNM. &quot;Kita tidak boleh tertinggal. Teknologi Pendidikan harus menjadi garda terdepan yang mendikte arah pendidikan masa depan, bukan sekadar penonton,&quot; tegasnya.</p>\n</p>', '2026-04-12', '2026-04-25 15:37:20'),
(2, 'Penerimaan Pengurus Baru HIMATEP Periode 2026/2027', 'oprec-himatep-2026', 'Pengumuman', 'blue', 'Sekretaris Umum', 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=800&q=80', 'Open recruitment pengurus HIMATEP periode 2026/2027 telah dibuka. Segera daftarkan diri Anda!', '<p class=\"mb-4\">Kabar gembira bagi seluruh mahasiswa aktif program studi Teknologi Pendidikan FIP UNM! HIMATEP secara resmi membuka <strong>Open Recruitment (Oprec)</strong> untuk kepengurusan periode 2026/2027.</p>\r\n            <p class=\"mb-4\">Kami mencari individu-individu berdedikasi tinggi, kreatif, dan inovatif yang siap berkontribusi memajukan himpunan dan mengharumkan nama program studi.</p>\r\n            \r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Syarat & Ketentuan:</h3>\r\n            <ul class=\"list-disc pl-5 mb-4 text-gray-700 space-y-2\">\r\n                <li>Mahasiswa aktif Teknologi Pendidikan UNM Angkatan 2024 & 2025.</li>\r\n                <li>Telah mengikuti dan lulus Latihan Dasar Kepemimpinan (LDK) HIMATEP.</li>\r\n                <li>Memiliki komitmen dan integritas yang tinggi.</li>\r\n                <li>Mengisi formulir pendaftaran secara online.</li>\r\n            </ul>\r\n\r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Jadwal Seleksi:</h3>\r\n            <ul class=\"list-none mb-4 text-gray-700 space-y-2\">\r\n                <li><strong>📅 Pendaftaran Online:</strong> 5 - 15 April 2026</li>\r\n                <li><strong>📄 Berkas Fisik:</strong> 16 April 2026 (Sekretariat HIMATEP)</li>\r\n                <li><strong>🗣️ Wawancara:</strong> 18 - 19 April 2026</li>\r\n                <li><strong>📢 Pengumuman:</strong> 22 April 2026</li>\r\n            </ul>\r\n            <p class=\"mb-4 text-himatep-green font-bold\">Jangan lewatkan kesempatan berharga ini untuk mengembangkan kapasitas kepemimpinan Anda. Mari bergabung dan buat sejarah bersama HIMATEP!</p>', '2026-04-05', '2026-04-25 15:37:20'),
(3, 'Juara 1 Lomba Media Pembelajaran Interaktif Nasional', 'juara-1-media-interaktif', 'Prestasi', 'purple', 'Divisi PSDM', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80', 'Delegasi HIMATEP berhasil meraih juara pertama pada ajang nasional pengembangan media interaktif di Universitas Negeri Yogyakarta.', '<p class=\"mb-4\">Kabar membanggakan datang dari delegasi mahasiswa Teknologi Pendidikan FIP UNM! Tim \"EduNesia\" yang terdiri dari 3 mahasiswa berhasil menyabet <strong>Juara 1 Tingkat Nasional</strong> dalam ajang bergengsi <em>Edutech Innovation Competition 2026</em> yang diselenggarakan di Universitas Negeri Yogyakarta (UNY).</p>\r\n            <p class=\"mb-4\">Tim EduNesia mengembangkan sebuah aplikasi media pembelajaran berbasis <em>Augmented Reality (AR)</em> bernama <strong>\"Jelajah Nusantara\"</strong>. Aplikasi ini ditujukan untuk mempermudah siswa Sekolah Dasar (SD) dalam mempelajari sejarah kebudayaan dan peninggalan kerajaan-kerajaan di Indonesia secara interaktif dan menyenangkan.</p>\r\n            \r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Tentang Aplikasi Jelajah Nusantara</h3>\r\n            <p class=\"mb-4\">Aplikasi ini mendapat pujian tinggi dari dewan juri karena desain UX-nya yang ramah anak dan kemampuannya untuk berjalan lancar di *smartphone* berspesifikasi rendah, sehingga sangat inklusif untuk sekolah-sekolah daerah terpencil.</p>\r\n            <blockquote class=\"border-l-4 border-himatep-green pl-4 my-6 italic text-gray-600\">\r\n                \"Kami mendedikasikan kemenangan ini untuk HIMATEP dan prodi Teknologi Pendidikan UNM. Harapan kami, aplikasi ini bisa segera diujicobakan secara gratis ke desa-desa binaan HIMATEP,\" ujar Ketua Tim EduNesia.\r\n            </blockquote>\r\n            <p class=\"mb-4\">Selamat kepada tim yang bertugas! Prestasi ini semakin mengukuhkan eksistensi mahasiswa Teknologi Pendidikan UNM sebagai kreator andal di bidang inovasi pembelajaran digital.</p>', '2026-03-28', '2026-04-25 15:37:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `platform` enum('WhatsApp','Email','Social Media') DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `contacts`
--

INSERT INTO `contacts` (`id`, `platform`, `label`, `value`, `icon`, `is_active`, `sort_order`) VALUES
(1, 'WhatsApp', 'Humas 1', '6281234567890', 'whatsapp', 1, 0),
(2, 'Email', 'Official Email', 'mailto:himatepfipunm01@gmail.com', 'email', 1, 0),
(3, 'Social Media', 'Instagram', 'https://instagram.com/himatepfipunm', 'instagram', 1, 0),
(4, 'Social Media', 'X / Twitter', 'https://twitter.com/himatepfipunm', 'twitter', 1, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `halaman_banner`
--

CREATE TABLE `halaman_banner` (
  `id` int(11) NOT NULL,
  `halaman` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `subjudul` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `halaman_banner`
--

INSERT INTO `halaman_banner` (`id`, `halaman`, `gambar`, `judul`, `subjudul`, `updated_at`) VALUES
(1, 'profile', 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80', 'Profil Organisasi', 'Mengenal lebih dekat struktur kepengurusan dan divisi-divisi di HIMATEP FIP UNM.', '2026-05-11 02:20:45'),
(2, 'proker', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&q=80', 'Program Kerja', 'Dedikasi kami melalui program kerja nyata untuk memajukan mahasiswa Teknologi Pendidikan dan masyarakat luas.', '2026-05-11 02:20:45'),
(3, 'berita', 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80', 'Portal Berita', 'Ikuti berita terbaru, kegiatan, dan prestasi dari HIMATEP FIP UNM.', '2026-05-11 02:20:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengurus`
--

CREATE TABLE `pengurus` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `divisi` varchar(50) NOT NULL,
  `foto` varchar(255) DEFAULT 'default.png',
  `urutan` int(11) DEFAULT 0,
  `periode` varchar(20) NOT NULL DEFAULT '2026/2027',
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengurus`
--

INSERT INTO `pengurus` (`id`, `nama`, `jabatan`, `divisi`, `foto`, `urutan`, `periode`, `deskripsi`, `created_at`) VALUES
(2, 'Kafka', 'Ketua Umum', 'BPH', '1778463717_kafka.webp', 1, '2026/2027', 'Seorang anggota Stellaron Hunters yang tenang, terkendali, dan cantik. Catatannya dalam daftar buronan Interastral Peace Corporation hanya mencantumkan namanya dan hobinya. Orang-orang selalu membayangkannya sebagai sosok yang elegan, terhormat, dan selalu mengejar keindahan bahkan dalam pertempuran.', '2026-05-08 03:07:02'),
(3, 'Silver Wolf', 'Sekretaris Umum', 'BPH', '1778463778_sw.webp', 3, '2026/2027', 'Sebagai anggota Stellaron Hunters dan seorang peretas jenius, Silver Wolf telah menguasai keterampilan yang dikenal sebagai \"pengeditan aether,\" yang dapat digunakan untuk memanipulasi data realitas. Oleh karena itu, dia selalu memandang alam semesta sebagai permainan simulasi imersif yang besar dan bersemangat untuk menyelesaikan tahapan yang menanti di depannya.', '2026-05-08 03:20:49'),
(4, 'Firefly', 'Bendahara Umum', 'BPH', '1778463822_ff.webp', 5, '2026/2027', 'Terlahir sebagai senjata, ia menderita sindrom kehilangan entropi akibat modifikasi genetik. Ia bergabung dengan Stellaron Hunters untuk mencari makna hidup, tanpa henti mengejar cara untuk menentang takdir.', '2026-05-08 03:22:08'),
(5, 'Rover', 'Ketua Divisi', 'Bidang I Pendidikan dan Pelatihan', '1778464644_rover_m.webp', 6, '2026/2027', 'Terbangun dengan masa lalu yang tidak diketahui oleh entitas misterius, Rover adalah seorang Arbiter yang menderita amnesia dari dunia lain yang memulai perjalanan untuk mengungkap kebenaran dan mendapatkan kembali ingatannya yang hilang. Saat rahasia terungkap, mereka menjalin hubungan yang lebih dalam dengan Solaris-3 dan bangsa-bangsanya.', '2026-05-11 01:33:25'),
(6, 'Sensei', 'Ketua Divisi', 'Bidang II Sosial dan Politik', '1778465082_sensei2.webp', 7, '2026/2027', 'Seorang guru dengan ketertarikan yang aneh pada taktik militer, Sensei adalah penasihat untuk SCHALE. Dengan bantuan Arona, Plana (setelah Volume F), dan siswa dari berbagai akademi, kehadiran mereka menjaga perdamaian di Kivotos sambil terlibat dalam berbagai kekonyolan bersama para siswa kota, membimbing mereka untuk menjadi lebih baik sambil semakin dekat dengan mereka.', '2026-05-11 01:35:23'),
(7, 'Ishigami Senku', 'Ketua Divisi', 'Bidang III Bakat dan Minat', '1778465312_senku2.png', 8, '2026/2027', 'Senku Ishigami adalah protagonis utama dari seri Dr. Stone. Ia adalah seorang remaja jenius dengan kecerdasan tingkat tinggi yang terbangun di \"Dunia Batu\" (Stone World) sekitar 3.700 tahun setelah fenomena misterius mengubah seluruh umat manusia menjadi patung.', '2026-05-11 01:38:23'),
(8, 'Trailblazer', 'Wakil Ketua Umum', 'BPH', '1778464734_Character_Trailblazer_29_Destruction_Splash_Art.webp', 2, '2026/2027', 'Terbangun selama peristiwa pembukaan permainan oleh Kafka dan Silver Wolf, mereka ditemukan oleh March 7th dan Dan Heng di Stasiun Luar Angkasa Herta selama invasi Legiun Antimateri.', '2026-05-11 01:58:43'),
(9, 'Blade', 'Wakil Sekretaris Umum', 'BPH', '1778464883_blade.webp', 4, '2026/2027', 'Seorang anggota Stellaron Hunters dan pendekar pedang yang meninggalkan tubuhnya untuk menjadi pedang. Dia berjanji setia kepada Budak Takdir dan memiliki kemampuan penyembuhan diri yang menakutkan.', '2026-05-11 02:01:23'),
(10, 'Charlotte', 'Ketua Divisi', 'Bidang IV Media dan Propaganda', '1778465569_Charlotte.webp', 9, '2026/2027', 'Charlotte, seorang jurnalis dari The Steambird, selalu berburu berita eksklusif. Dengan kegigihan dan daya tahan yang luar biasa, dia tidak akan berhenti sampai menemukan kebenaran, bahkan jika itu membuatnya semakin dekat dengan bahaya.', '2026-05-11 02:12:49'),
(11, 'Phoebe', 'Anggota', 'Bidang I Pendidikan dan Pelatihan', '1778475188_Screenshot__849_.png', 9, '2026/2027', 'yayaay', '2026-05-11 04:53:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `proker`
--

CREATE TABLE `proker` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `divisi` varchar(100) NOT NULL,
  `divisi_color` varchar(20) NOT NULL,
  `icon` text NOT NULL,
  `target` varchar(100) NOT NULL,
  `sasaran` varchar(255) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `ringkasan` text NOT NULL,
  `isi` longtext NOT NULL,
  `is_unggulan` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `proker`
--

INSERT INTO `proker` (`id`, `judul`, `slug`, `divisi`, `divisi_color`, `icon`, `target`, `sasaran`, `gambar`, `ringkasan`, `isi`, `is_unggulan`, `created_at`) VALUES
(1, 'TechnoFest 2026', 'technofest-2026', 'Hubungan Masyarakat', 'blue', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'November 2026', 'Mahasiswa Umum (Nasional)', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=80', 'Festival teknologi pendidikan terbesar tingkat nasional yang menghadirkan perlombaan, seminar nasional, dan pameran inovasi.', '<p class=\"mb-4\"><strong>TechnoFest</strong> adalah program kerja terbesar dan paling prestisius dari HIMATEP FIP UNM yang diselenggarakan oleh Divisi Hubungan Masyarakat. Acara tahunan ini bertujuan untuk menjadi wadah kreativitas dan inovasi di bidang teknologi pendidikan berskala nasional.</p>\r\n            \r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Rangkaian Kegiatan</h3>\r\n            <ul class=\"list-disc pl-5 mb-4 text-gray-700 space-y-2\">\r\n                <li><strong>Lomba Media Pembelajaran Interaktif:</strong> Kompetisi pengembangan media ajar digital tingkat mahasiswa se-Indonesia.</li>\r\n                <li><strong>Seminar Nasional:</strong> Menghadirkan pakar teknologi pendidikan dan praktisi EdTech terkemuka.</li>\r\n                <li><strong>Pameran Inovasi (EdTech Expo):</strong> *Showcase* karya-karya terbaik mahasiswa Teknologi Pendidikan UNM.</li>\r\n            </ul>\r\n\r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Tujuan Program</h3>\r\n            <p class=\"mb-4\">Meningkatkan kesadaran masyarakat akan pentingnya pemanfaatan teknologi dalam pendidikan serta memfasilitasi mahasiswa untuk berkompetisi dan berjejaring di tingkat nasional.</p>', 1, '2026-04-25 15:37:20'),
(2, 'Desa Binaan (Edu-Village)', 'desa-binaan', 'Pengabdian Masyarakat', 'purple', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'Sepanjang Tahun Kepengurusan', 'Desa Tertinggal di Sulsel', 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=1200&q=80', 'Program pengabdian jangka panjang. Kami membangun pusat literasi digital dan membantu sekolah setempat dalam membuat media ajar.', '<p class=\"mb-4\"><strong>Desa Binaan (Edu-Village)</strong> merupakan wujud nyata Tridharma Perguruan Tinggi mahasiswa Teknologi Pendidikan. Program pengabdian ini difokuskan pada daerah-daerah atau desa tertinggal di Sulawesi Selatan yang masih memiliki keterbatasan dalam akses pendidikan digital.</p>\r\n            \r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Fokus Kegiatan</h3>\r\n            <ul class=\"list-disc pl-5 mb-4 text-gray-700 space-y-2\">\r\n                <li>Pembangunan perpustakaan mini dan pusat literasi digital desa.</li>\r\n                <li>Pelatihan dasar komputer dan internet sehat bagi pemuda desa.</li>\r\n                <li>Pendampingan guru-guru sekolah dasar setempat dalam pembuatan media pembelajaran sederhana berbasis lingkungan.</li>\r\n            </ul>\r\n\r\n            <blockquote class=\"border-l-4 border-himatep-green pl-4 my-6 italic text-gray-600\">\r\n                \"Pendidikan yang baik bukan hanya milik mereka yang di kota, tapi hak seluruh anak bangsa hingga ke pelosok negeri.\"\r\n            </blockquote>', 1, '2026-04-25 15:37:20'),
(3, 'Laboratorium Inovasi', 'laboratorium-inovasi', 'Penelitian & Pengembangan', 'blue', 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', 'Rutin Mingguan', 'Mahasiswa Internal', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&q=80', 'Wadah inkubasi bagi mahasiswa untuk mengembangkan startup, aplikasi pendidikan, dan media interaktif terbaru.', '<p class=\"mb-4\"><strong>Laboratorium Inovasi</strong> adalah program kerja internal yang dirancang seperti inkubator *startup* bagi mahasiswa HIMATEP. Di sini, mahasiswa yang memiliki ide-ide liar seputar teknologi pendidikan akan difasilitasi untuk mewujudkannya menjadi produk nyata.</p>\r\n            \r\n            <h3 class=\"text-xl font-bold mt-6 mb-3 text-himatep-dark\">Apa Saja yang Dikerjakan?</h3>\r\n            <p class=\"mb-4\">Mahasiswa akan dibagi menjadi kelompok-kelompok kecil lintas angkatan untuk mengembangkan proyek seperti:</p>\r\n            <ul class=\"list-disc pl-5 mb-4 text-gray-700 space-y-2\">\r\n                <li>Aplikasi mobile edukasi (Android/iOS).</li>\r\n                <li>Media pembelajaran berbasis *Virtual Reality* (VR) dan *Augmented Reality* (AR).</li>\r\n                <li>Sistem Informasi Akademik Sekolah.</li>\r\n            </ul>\r\n\r\n            <p class=\"mb-4\">Nantinya, produk-produk unggulan dari Laboratorium Inovasi ini akan diikutsertakan dalam berbagai kompetisi nasional seperti PKM (Program Kreativitas Mahasiswa) atau Gemastik.</p>', 1, '2026-04-25 15:37:20'),
(4, 'Latihan Kepemimpinan (LDK)', 'latihan-kepemimpinan-(ldk)', 'PSDM', 'blue', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'Agustus 2026', 'Mahasiswa Baru', 'https://images.unsplash.com/photo-1777014908321-322645585bb9?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', 'Program wajib tahunan bagi mahasiswa baru untuk membentuk jiwa kepemimpinan, tanggung jawab, dan soliditas angkatan.', '<p>LDK atau Latihan Dasar Kepemimpinan adalah gerbang pertama bagi mahasiswa baru Teknologi Pendidikan UNM untuk berproses di himpunan.</p>', 0, '2026-04-25 16:46:51'),
(5, 'Upgrading Pengurus', 'upgrading-pengurus', 'PSDM', 'blue', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'Setiap Triwulan', 'Pengurus Inti & Anggota', 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&q=80', 'Pelatihan berkala khusus untuk pengurus HIMATEP guna meningkatkan soft skill seperti manajemen waktu dan komunikasi publik.', '<p>Program ini bertujuan untuk menjaga performa dan harmonisasi internal pengurus HIMATEP selama satu periode kepengurusan.</p>', 0, '2026-04-25 16:46:51'),
(6, 'HIMATEP Menyapa (Kunjungan)', 'himatep-menyapa', 'Hubungan Masyarakat', 'blue', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'Semester Genap', 'HIMA Eksternal', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800&q=80', 'Program kunjungan silaturahmi ke himpunan mahasiswa sejenis di universitas lain untuk bertukar informasi dan studi banding.', '<p>HIMATEP Menyapa adalah wadah kolaborasi antar himpunan untuk memperluas jaringan dan perspektif mahasiswa.</p>', 0, '2026-04-25 16:46:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','superadmin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator HIMATEP', 'superadmin', '2026-04-25 15:37:20');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proker_id` (`proker_id`);

--
-- Indeks untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `halaman_banner`
--
ALTER TABLE `halaman_banner`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `halaman` (`halaman`);

--
-- Indeks untuk tabel `pengurus`
--
ALTER TABLE `pengurus`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `proker`
--
ALTER TABLE `proker`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `halaman_banner`
--
ALTER TABLE `halaman_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pengurus`
--
ALTER TABLE `pengurus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `proker`
--
ALTER TABLE `proker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`proker_id`) REFERENCES `proker` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
