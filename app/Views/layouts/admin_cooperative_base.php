<?php
/**
 * @var string|null $title
 */

// Unified styling classes to prevent IDE tailwind CSS property conflicts
$adminActiveTab = 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30 shadow-sm shadow-emerald-500/10 font-bold';
$adminInactiveTab = 'text-tx-secondary hover:text-tx-primary hover:bg-elevated font-semibold';
$isAdmin = auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin');
$directLoanEnabled = \App\Models\KopSettingModel::getSetting('direct_loan_enabled', '0') === '1';
$canDirectLoan = $isAdmin || $directLoanEnabled;

$navItems = [
    ['url' => base_url('admin/cooperative'), 'label' => 'Ringkasan', 'active' => current_url() === base_url('admin/cooperative'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />'],
    ['url' => base_url('admin/cooperative/members'), 'label' => 'Keanggotaan', 'active' => strpos(current_url(), base_url('admin/cooperative/members')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'],
    ['url' => base_url('admin/cooperative/savings'), 'label' => 'Kelola Simpanan', 'active' => strpos(current_url(), base_url('admin/cooperative/savings')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />'],
    ['url' => base_url('admin/cooperative/loans'), 'label' => 'Kelola Pinjaman', 'active' => (strpos(current_url(), base_url('admin/cooperative/loans')) !== false && current_url() !== base_url('admin/cooperative/loans/direct')), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
];

if ($canDirectLoan) {
    $navItems[] = ['url' => base_url('admin/cooperative/loans/direct'), 'label' => 'Pinjaman Langsung', 'active' => current_url() === base_url('admin/cooperative/loans/direct'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />'];
}

$navItems[] = ['url' => base_url('admin/cooperative/funds'), 'label' => 'Kelola Kas Koperasi', 'active' => strpos(current_url(), base_url('admin/cooperative/funds')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />'];
$navItems[] = ['url' => base_url('admin/cooperative/installments'), 'label' => 'Terima Angsuran', 'active' => strpos(current_url(), base_url('admin/cooperative/installments')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />'];
$navItems[] = ['url' => base_url('admin/cooperative/invitations'), 'label' => 'Kode Undangan', 'active' => strpos(current_url(), base_url('admin/cooperative/invitations')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />'];
$navItems[] = ['url' => base_url('admin/cooperative/messages'), 'label' => 'Pesan & Undangan', 'active' => strpos(current_url(), base_url('admin/cooperative/messages')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />'];
$navItems[] = ['url' => base_url('admin/cooperative/shu'), 'label' => 'Pembagian SHU', 'active' => strpos(current_url(), base_url('admin/cooperative/shu')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />'];
$navItems[] = ['url' => base_url('admin/cooperative/reports/arrears'), 'label' => 'Laporan Tunggakan', 'active' => strpos(current_url(), base_url('admin/cooperative/reports/arrears')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'];
$navItems[] = ['url' => base_url('admin/cooperative/loans/directory'), 'label' => 'Daftar Pinjaman', 'active' => strpos(current_url(), base_url('admin/cooperative/loans/directory')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'];

if ($isAdmin) {
    $navItems[] = ['url' => base_url('admin/cooperative/settings'), 'label' => 'Pengaturan KSP', 'active' => strpos(current_url(), base_url('admin/cooperative/settings')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />'];
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-base text-tx-primary selection:bg-emerald-500 selection:text-white">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Pengelola Koperasi', 'forceDarkTheme' => true]) ?>
</head>
<body class="h-full bg-slate-950 antialiased overflow-x-clip">
    
    <!-- Premium background glowing gradients in Emerald/Teal -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-teal-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-rose-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="min-h-full flex flex-col">
        <!-- Responsive Header / Navbar -->
        <header class="sticky top-0 z-40 backdrop-blur-md bg-slate-950/70 border-b border-slate-900">
            <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo / Title & Premium Back Shortcut -->
                    <div class="flex items-center gap-3">
                        <!-- PREMIUM BACK SHORTCUT TO MAIN ADMIN (Protected by $isAdmin) -->
                        <?php if ($isAdmin): ?>
                            <a href="<?= base_url('admin') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all shadow-sm bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/25 hover:bg-indigo-500/20 shadow-indigo-500/5" title="Kembali ke Admin Utama">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="hidden sm:inline">Admin Utama</span>
                            </a>
                            <div class="w-px h-4 bg-slate-800 hidden sm:block mx-1"></div>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-linear-to-tr from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                <span class="text-xl font-extrabold bg-linear-to-r from-white via-slate-200 to-emerald-400 bg-clip-text text-transparent">KSP Pengelola</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest text-center self-start sm:self-center">Manager Portal</span>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile / Auth Actions -->
                    <div class="flex items-center gap-2.5">
                        <?= view('partials/header_profile', ['portal' => 'admin_cooperative', 'forceDarkTheme' => true]) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Layout with Collapsible Sidebar -->
        <main class="grow max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
            <div id="coop-layout-wrapper" class="flex flex-col lg:flex-row gap-8 w-full">
                
                <!-- Sidebar Navigation - Hidden on Mobile -->
                <aside id="coop-sidebar" class="hidden lg:block lg:w-[280px] shrink-0 transition-all duration-300 ease-in-out sticky top-24 self-start z-30">
                    <div id="coop-sidebar-card" class="bg-slate-900/40 border border-slate-900 rounded-2xl p-5 backdrop-blur-md shadow-2xl overflow-y-auto max-h-[calc(100vh-8rem)] transition-all duration-300" style="scrollbar-width: none;">
                        <!-- Glow background -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>

                        <!-- Minimize Button (Hidden on Mobile) -->
                        <button onclick="toggleCoopSidebar()" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-all cursor-pointer hidden lg:flex items-center justify-center p-1 rounded-lg border border-slate-800 bg-slate-950/80 hover:bg-slate-900 shadow-md z-20" id="sidebar-toggle-btn" title="Minimize/Maximize Sidebar">
                            <svg id="toggle-chevron" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div id="sidebar-header-section" class="border-b border-slate-800/60 pb-4 mb-4 relative z-10 pr-8 transition-all duration-300">
                            <div class="flex items-center gap-2 text-emerald-400 font-bold text-[10px] tracking-wider uppercase mb-1">
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Pengelola Koperasi
                            </div>
                            <h2 class="text-sm font-extrabold text-white tracking-wide">Navigasi Utama KSP</h2>
                        </div>

                        <!-- Vertical Menu Links -->
                        <nav id="coop-sidebar-nav" class="space-y-1 relative z-10 flex flex-col w-full">
                            <?php foreach ($navItems as $item): ?>
                                <a href="<?= $item['url'] ?>" class="sidebar-menu-item flex items-center gap-3 px-4 py-3 rounded-xl text-xs transition-all duration-200 w-full <?= $item['active'] ? $adminActiveTab : $adminInactiveTab ?>">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <?= $item['icon'] ?>
                                    </svg>
                                    <span class="sidebar-text transition-all duration-300"><?= $item['label'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Body -->
                <div class="flex-1 min-w-0 space-y-6">
                    
                    <?php
                    // Find active label for current page to display on the mobile button
                    $currentActiveLabel = 'Ringkasan';
                    foreach ($navItems as $item) {
                        if ($item['active']) {
                            $currentActiveLabel = $item['label'];
                            break;
                        }
                    }
                    ?>

                    <!-- Mobile Collapsible Dropdown Selector (Visible below lg) -->
                    <div class="block lg:hidden w-full relative z-30 mb-2">
                        <button onclick="toggleCoopDropdown()" class="w-full flex items-center justify-between px-4 py-3.5 bg-slate-900/60 border border-slate-805 rounded-xl text-xs font-bold text-white shadow-lg cursor-pointer hover:bg-slate-900 transition-all select-none">
                            <div class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Menu KSP: <?= $currentActiveLabel ?></span>
                            </div>
                            <svg id="coop-dropdown-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu items -->
                        <div id="coop-dropdown-menu" class="hidden absolute left-0 right-0 mt-2 bg-slate-950/95 border border-slate-900 rounded-xl p-2 shadow-2xl z-40 backdrop-blur-lg">
                            <nav class="flex flex-col gap-1 text-[11px] font-semibold">
                                <?php foreach ($navItems as $item): ?>
                                    <a href="<?= $item['url'] ?>" class="px-4 py-3 rounded-lg transition-all <?= $item['active'] ? $adminActiveTab : $adminInactiveTab ?>">
                                        <?= $item['label'] ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        </div>
                    </div>
                    
                    <script>
                        function toggleCoopDropdown() {
                            const menu = document.getElementById('coop-dropdown-menu');
                            const chevron = document.getElementById('coop-dropdown-chevron');
                            const isOpen = !menu.classList.contains('hidden');
                            if (isOpen) {
                                menu.classList.add('hidden');
                                chevron.classList.remove('rotate-180');
                            } else {
                                menu.classList.remove('hidden');
                                chevron.classList.add('rotate-180');
                            }
                        }
                        
                        // Auto close when clicking outside
                        document.addEventListener('click', function(e) {
                            const menu = document.getElementById('coop-dropdown-menu');
                            const button = menu ? menu.previousElementSibling : null;
                            if (menu && button && !button.contains(e.target) && !menu.contains(e.target)) {
                                menu.classList.add('hidden');
                                const chevron = document.getElementById('coop-dropdown-chevron');
                                if (chevron) chevron.classList.remove('rotate-180');
                            }
                        });
                    </script>

                    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 sm:p-8 backdrop-blur-md shadow-2xl relative overflow-hidden">
                        <!-- Glow background -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px] pointer-events-none"></div>

                        <div class="border-b border-slate-800/60 pb-5 mb-5 relative z-10">
                            <h1 class="text-xl sm:text-2xl font-extrabold bg-linear-to-r from-white via-slate-100 to-emerald-300 bg-clip-text text-transparent">
                                <?= (string) esc($title ?? 'Panel Koperasi') ?>
                            </h1>
                        </div>

                        <!-- Content Slot -->
                        <div class="relative z-10">
                            <?= $this->renderSection('koprasi_content') ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-900 bg-slate-950/30 backdrop-blur-md py-6 mt-auto">
            <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs sm:text-sm text-slate-500">© 2026 CatatanKeuangan KSP Manager Desk. Semua Hak Dilindungi.</p>
                <div class="flex gap-4">
                    <span class="text-xs text-slate-600 select-none">Secure Manager Session</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Navigation Drawer -->
    <?= view('partials/mobile_drawer', ['portal' => 'admin_cooperative', 'navItems' => $navItems ?? null]) ?>

    <!-- Theme & Global Scripts -->
    <?= view('partials/global_scripts') ?>

    <script>
        // Initialize sidebar state on DOM load
        document.addEventListener("DOMContentLoaded", function() {
            const isMinimized = localStorage.getItem("coop_sidebar_minimized") === "true";
            if (isMinimized) {
                applySidebarState(true);
            }
        });

        function toggleCoopSidebar() {
            const sidebar = document.getElementById("coop-sidebar");
            const isMinimized = sidebar.classList.contains("lg:w-[82px]");
            applySidebarState(!isMinimized);
        }


        function applySidebarState(minimize) {
            const sidebar = document.getElementById("coop-sidebar");
            const sidebarCard = document.getElementById("coop-sidebar-card");
            const chevron = document.getElementById("toggle-chevron");
            const textElements = document.querySelectorAll(".sidebar-text");
            const headerSection = document.getElementById("sidebar-header-section");
            const menuItems = document.querySelectorAll(".sidebar-menu-item");

            if (minimize) {
                // Shrink sidebar width
                sidebar.classList.remove("lg:w-[280px]");
                sidebar.classList.add("lg:w-[82px]");
                
                // Adjust card padding
                sidebarCard.classList.remove("p-5");
                sidebarCard.classList.add("p-3.5");

                // Hide text elements
                textElements.forEach(el => el.classList.add("lg:hidden"));
                headerSection.classList.add("lg:hidden");

                // Adjust menu item alignment to center the icons
                menuItems.forEach(el => {
                    el.classList.remove("px-4", "gap-3");
                    el.classList.add("px-0", "justify-center", "w-full", "h-11");
                    el.setAttribute("title", el.querySelector(".sidebar-text").innerText);
                });

                // Rotate or change chevron to point right
                chevron.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />';

                // Persist state
                localStorage.setItem("coop_sidebar_minimized", "true");
            } else {
                // Restore sidebar width
                sidebar.classList.remove("lg:w-[82px]");
                sidebar.classList.add("lg:w-[280px]");
                
                // Restore card padding
                sidebarCard.classList.remove("p-3.5");
                sidebarCard.classList.add("p-5");

                // Show text elements
                textElements.forEach(el => el.classList.remove("lg:hidden"));
                headerSection.classList.remove("lg:hidden");

                // Restore menu item alignment
                menuItems.forEach(el => {
                    el.classList.remove("px-0", "justify-center", "w-full", "h-11");
                    el.classList.add("px-4", "gap-3");
                    el.removeAttribute("title");
                });

                // Restore chevron to point left
                chevron.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />';

                // Persist state
                localStorage.setItem("coop_sidebar_minimized", "false");
            }
        }
    </script>
</body>
</html>
