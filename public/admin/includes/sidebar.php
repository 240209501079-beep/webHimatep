<?php
// Sidebar Admin standardized
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Overlay for Mobile -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-[90] lg:hidden" x-transition style="display:none;"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="fixed lg:static inset-y-0 left-0 w-64 bg-[#1B2945] text-white flex flex-col shadow-2xl z-[100] transition-transform duration-300 lg:translate-x-0">
    
    <!-- Header Sidebar -->
    <div class="p-6 bg-[#1B2945] flex items-center justify-between border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="p-1.5 bg-white rounded-lg">
                <img src="../images/logo-himatep.png" alt="Logo" class="h-7 w-7">
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold leading-tight">ADMIN PANEL</span>
                <span class="text-[10px] text-blue-200 font-medium tracking-widest uppercase">HIMATEP FIP UNM</span>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-white/50 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto custom-scrollbar">
        <p class="px-4 text-[10px] font-bold text-blue-300/50 uppercase tracking-widest mb-2">Utama</p>
        
        <a href="dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'dashboard.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-sm">Dashboard</span>
        </a>

        <p class="px-4 text-[10px] font-bold text-blue-300/50 uppercase tracking-widest mt-6 mb-2">Konten Website</p>

        <a href="manage_news.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'manage_news.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <span class="text-sm">Kelola Berita</span>
        </a>

        <a href="manage_proker.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'manage_proker.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <span class="text-sm">Program Kerja</span>
        </a>

        <a href="manage_pengurus.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'manage_pengurus.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-sm">Struktur Pengurus</span>
        </a>

        <a href="manage_banners.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'manage_banners.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-sm">Kelola Banner</span>
        </a>

        <p class="px-4 text-[10px] font-bold text-blue-300/50 uppercase tracking-widest mt-6 mb-2">Interaksi</p>

        <a href="manage_contacts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'manage_contacts.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            <span class="text-sm">Narahubung</span>
        </a>

        <a href="view_aspirasi.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-200 <?= $current_page == 'view_aspirasi.php' ? 'bg-white/10 text-white font-bold shadow-inner' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <span class="text-sm">Suara Mahasiswa</span>
        </a>
    </nav>

    <!-- Footer Sidebar -->
    <div class="p-4 border-t border-white/10 bg-[#1B2945]">
        <a href="../index.php" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 mb-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            Lihat Website
        </a>
        <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-red-500/80 hover:bg-red-600 text-white rounded-xl text-xs font-bold transition shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Logout
        </a>
    </div>
</aside>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
