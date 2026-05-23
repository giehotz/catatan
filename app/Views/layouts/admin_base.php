<?php
/**
 * @var string|null $title
 */

$navItems = [
    ['url' => base_url('/'), 'label' => 'Kembali ke Web', 'active' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />'],
];

if (auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin')) {
    $navItems[] = [
        'url' => base_url('admin'),
        'label' => 'Manajemen Pengguna',
        'active' => strpos(current_url(), base_url('admin')) !== false && strpos(current_url(), base_url('admin/cooperative')) === false && strpos(current_url(), base_url('admin/audit-logs')) === false && strpos(current_url(), base_url('admin/active-users')) === false,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'
    ];
    $navItems[] = [
        'url' => base_url('admin/audit-logs'),
        'label' => 'Log Audit',
        'active' => strpos(current_url(), base_url('admin/audit-logs')) !== false,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
    ];
    $navItems[] = [
        'url' => base_url('admin/active-users'),
        'label' => 'Monitor Aktif',
        'active' => strpos(current_url(), base_url('admin/active-users')) !== false,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />'
    ];
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100 selection:bg-indigo-500 selection:text-white">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Panel Administrasi', 'forceDarkTheme' => true]) ?>
</head>
<body class="h-full bg-slate-950 antialiased overflow-x-hidden">
    
    <!-- Premium background glowing gradients -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-rose-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="min-h-full flex flex-col">
        <!-- Responsive Header / Navbar -->
        <header class="sticky top-0 z-40 backdrop-blur-md bg-slate-955/70 border-b border-slate-900">
            <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top Row: Logo & Profile -->
                <div class="flex items-center justify-between h-14 border-b border-slate-900/50">
                    <!-- Logo / Title with Admin Badge -->
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-linear-to-tr from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                            <span class="text-lg font-extrabold bg-linear-to-r from-white via-slate-200 to-indigo-400 bg-clip-text text-transparent">CatatanKeuangan</span>
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase tracking-widest text-center self-start sm:self-center">Admin Panel</span>
                        </div>
                    </div>

                    <!-- Admin Profile / Logout section & Premium KSP Shortcut -->
                    <div class="flex items-center gap-2.5">
                        <?php if (auth()->loggedIn()): ?>
                            <!-- PREMIUM SHORTCUT BUTTON TO KSP MANAGER PORTAL -->
                            <a href="<?= base_url('admin/cooperative') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all shadow-sm bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/20 shadow-emerald-500/5" title="Buka Panel Pengelola Koperasi">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="hidden sm:inline">Kelola Koperasi</span>
                            </a>
                            <div class="w-px h-4 bg-slate-800 hidden sm:block mx-1"></div>
                        <?php endif; ?>
                        
                        <?= view('partials/header_profile', ['portal' => 'admin', 'forceDarkTheme' => true]) ?>
                    </div>
                </div>

                <!-- Bottom Row: Navigation Links (Desktop Only) -->
                <?php if (auth()->loggedIn()): ?>
                    <div id="desktopNavWrapper" class="relative block">
                        <!-- Tombol panah kiri -->
                        <button id="navScrollLeft" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1 rounded-full bg-slate-950/80 backdrop-blur-sm border border-slate-800 text-slate-400 hover:text-indigo-400 transition-all opacity-0 pointer-events-none shadow-md" aria-label="Scroll kiri">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <div id="mainNavLinks" class="flex items-center h-14 overflow-x-auto no-scrollbar scroll-smooth relative">
                            <!-- Efek fade kiri -->
                            <div id="navFadeLeft" class="absolute left-0 top-0 bottom-0 w-8 bg-linear-to-r from-slate-950 to-transparent pointer-events-none opacity-0 transition-opacity duration-200"></div>
                            
                            <nav class="flex items-center gap-2 sm:gap-3 py-1.5 text-slate-400 font-medium text-xs sm:text-sm mx-auto">
                                <?php foreach ($navItems as $item): ?>
                                    <a href="<?= $item['url'] ?>" class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 <?= $item['active'] ? 'bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/30 shadow-sm shadow-indigo-500/10' : 'hover:text-slate-200 hover:bg-slate-800/60' ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <?= $item['icon'] ?>
                                        </svg>
                                        <span><?= $item['label'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                            
                            <!-- Efek fade kanan -->
                            <div id="navFadeRight" class="absolute right-0 top-0 bottom-0 w-8 bg-linear-to-l from-slate-950 to-transparent pointer-events-none opacity-0 transition-opacity duration-200"></div>
                        </div>
                        
                        <!-- Tombol panah kanan -->
                        <button id="navScrollRight" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 p-1 rounded-full bg-slate-950/80 backdrop-blur-sm border border-slate-800 text-slate-400 hover:text-indigo-400 transition-all opacity-0 pointer-events-none shadow-md" aria-label="Scroll kanan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="grow max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-900 bg-slate-950/30 backdrop-blur-md py-6 mt-auto">
            <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs sm:text-sm text-slate-500">© 2026 CatatanKeuangan Administrator Desk. Semua Hak Dilindungi.</p>
                <div class="flex gap-4">
                    <span class="text-xs text-slate-600 select-none">Secure Admin Session</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Navigation Drawer -->
    <?= view('partials/mobile_drawer', ['portal' => 'admin', 'navItems' => $navItems ?? null]) ?>

    <!-- Theme & Global Scripts -->
    <?= view('partials/global_scripts') ?>
</body>
</html>
