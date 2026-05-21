// Inisialisasi tambahan jika diperlukan
// Sebagian besar state management sudah ditangani Alpine.js langsung di HTML

// Cek URL parameter untuk notifikasi sukses (dari submit_aspirasi.php)
function init() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        alert('Terima kasih! Aspirasi Anda telah berhasil dikirim.');
        // Membersihkan URL
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (urlParams.get('login') === 'failed') {
        alert('Login Gagal! Username atau Password salah.');
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Scroll Spy untuk Active Menu
    const sections = document.querySelectorAll("section[id]");
    const navLinks = document.querySelectorAll(".nav-link");

    // Hanya jalankan scroll spy jika berada di halaman utama (index.php atau /)
    const isHomepage = window.location.pathname.endsWith("index.php") || 
                       window.location.pathname.endsWith("/") || 
                       window.location.pathname.split("/").pop() === "";

    function updateActiveMenu() {
        if (!isHomepage) return;

        let current = "";
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= (sectionTop - sectionHeight / 3)) {
                current = section.getAttribute("id");
            }
        });

        navLinks.forEach(link => {
            link.classList.remove("text-himatep-green", "font-bold");
            link.classList.add("text-gray-600", "font-medium");

            // Menggunakan endsWith agar cocok dengan format 'index.php#section' maupun '#section'
            if (link.getAttribute("href") && link.getAttribute("href").endsWith(`#${current}`)) {
                link.classList.remove("text-gray-600", "font-medium");
                link.classList.add("text-himatep-green", "font-bold");
            }
        });
    }

    window.addEventListener("scroll", updateActiveMenu);
    // Jalankan sekali saat load untuk sinkronisasi awal
    updateActiveMenu();

    // 1. Tangani Scroll Lintas Halaman menggunakan Local Storage
    // Ini membuat perpindahan dari file html lain (seperti proker.php) ke index.php aman dari bentrok native hash dan GSAP
    const pendingScroll = localStorage.getItem('himatep_pending_scroll');
    if (pendingScroll) {
        // Hapus segera agar tidak berulang saat refresh
        localStorage.removeItem('himatep_pending_scroll');

        // Jeda agak lama (300ms) untuk memastikan GSAP selesai merender elemen
        setTimeout(() => {
            const targetElement = document.querySelector(pendingScroll);
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }
        }, 300);
    } else if (window.location.hash) {
        // Fallback jika URL memiliki hash secara langsung
        setTimeout(() => {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                window.scrollTo({ top: offsetPosition, behavior: "smooth" });
            }
        }, 300);
    }

    // 2. Cegah lompatan native browser dan gunakan localStorage untuk link lintas halaman
    document.querySelectorAll('a[href]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            // Jika href mengandung '#' (baik di halaman yang sama maupun beda halaman)
            if (href.includes('#')) {
                const parts = href.split('#');
                const page = parts[0];
                const hash = '#' + parts[1];

                // Jika itu mengarah ke halaman ini sendiri (misal href="#kalender" atau href="index.php#kalender" saat di index.php)
                const isSamePage = page === '' || page === window.location.pathname.split('/').pop();

                if (isSamePage) {
                    e.preventDefault();
                    const targetElement = document.querySelector(hash);
                    if (targetElement) {
                        const headerOffset = 80;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({ top: offsetPosition, behavior: "smooth" });

                        // Update URL tanpa reload
                        window.history.pushState(null, null, hash);
                    }
                } else if (page) {
                    // Jika mengarah ke halaman lain (misal dari proker.php ke index.php#kalender)
                    e.preventDefault();
                    // Simpan tujuannya di local storage
                    localStorage.setItem('himatep_pending_scroll', hash);
                    // Pindah ke halaman tersebut tanpa hash di URL
                    window.location.href = page;
                }
            }
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}
