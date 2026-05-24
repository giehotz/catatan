<?php
/**
 * @var string|null $title
 */
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-base text-tx-primary selection:bg-brand selection:text-white">
<head>
    <?= view('partials/head', ['title' => $title ?? null, 'forceDarkTheme' => false]) ?>
</head>
<body class="h-full bg-base antialiased overflow-x-hidden">
    
    <!-- Premium background glowing gradients -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="min-h-full flex flex-col">
        <!-- Responsive Header / Navbar -->
        <header class="sticky top-0 z-40 backdrop-blur-md bg-base/70 border-b border-br-default">
            <!-- Impersonation Active Warning Banner -->
            <?php if (session()->has('impersonator_user_id')) : ?>
                <div class="bg-linear-to-r from-amber-600 to-orange-600 text-white py-2 shadow-lg flex items-center justify-between text-xs sm:text-sm font-semibold border-b border-orange-500/20">
                    <div class="max-w-7xl mx-auto w-full flex items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            <span>Mode Penyamaran: Anda sedang bertindak sebagai <strong class="underline"><?= (string) esc((string) auth()->user()->username) ?></strong>.</span>
                        </div>
                        <a href="<?= base_url('admin/stop-impersonate') ?>" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-all font-bold text-xs uppercase tracking-wider">Kembali ke Admin</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top Row: Logo & Profile -->
                <div class="flex items-center justify-between h-14 border-b border-br-default/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-linear-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold bg-linear-to-r from-tx-primary via-tx-secondary to-brand bg-clip-text text-transparent">CatatanKeuangan</span>
                    </div>
 
                    <!-- User Profile / Auth Actions next to dynamic KSP shortcut -->
                    <div class="flex items-center gap-2.5">
                        <?php if (auth()->loggedIn()): ?>
                            <!-- Cooperative Shortcut View Cell -->
                            <?= view_cell('App\Cells\CooperativeShortcutCell::render') ?>
                            <div class="w-px h-4 bg-br-default/70 mx-1"></div>
                        <?php endif; ?>
                        
                        <?= view('partials/header_profile', ['portal' => 'finance', 'forceDarkTheme' => false]) ?>
                    </div>
                </div>
 
                <!-- Bottom Row: Navigation Links (Desktop Only) -->
                <?php if (auth()->loggedIn()): ?>
                    <?php
                    $navItems = [
                        ['url' => base_url('/'), 'label' => 'Dashboard', 'active' => current_url() === base_url('/') || current_url() === base_url(), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
                        ['url' => base_url('transaction'), 'label' => 'Transaksi', 'active' => strpos(current_url(), base_url('transaction')) !== false && strpos(current_url(), base_url('wallets')) === false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                        ['url' => base_url('wallets'), 'label' => 'Rekening', 'active' => strpos(current_url(), base_url('wallets')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 033 3z" />'],
                        ['url' => base_url('savings'), 'label' => 'Tabungan', 'active' => strpos(current_url(), base_url('savings')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />'],
                        ['url' => base_url('category'), 'label' => 'Kategori', 'active' => strpos(current_url(), base_url('category')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />'],
                        ['url' => base_url('budgets'), 'label' => 'Anggaran', 'active' => strpos(current_url(), base_url('budgets')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />'],
                        ['url' => base_url('recurring'), 'label' => 'Berulang', 'active' => strpos(current_url(), base_url('recurring')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
                        ['url' => base_url('debt-receivable'), 'label' => 'Utang Piutang', 'active' => strpos(current_url(), base_url('debt-receivable')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />'],
                        ['url' => base_url('reports'), 'label' => 'Laporan', 'active' => strpos(current_url(), base_url('reports')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />']
                    ];
                    ?>
                    
                    <div id="desktopNavWrapper" class="relative block">
                        <!-- Tombol panah kiri -->
                        <button id="navScrollLeft" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1 rounded-full bg-base/80 backdrop-blur-sm border border-br-default text-tx-secondary hover:text-brand transition-all opacity-0 pointer-events-none shadow-md" aria-label="Scroll kiri">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <div id="mainNavLinks" class="flex items-center h-14 overflow-x-auto no-scrollbar scroll-smooth relative">
                            <!-- Efek fade kiri -->
                            <div id="navFadeLeft" class="absolute left-0 top-0 bottom-0 w-8 bg-linear-to-r from-base to-transparent pointer-events-none opacity-0 transition-opacity duration-200"></div>
                            
                            <nav class="flex items-center gap-2 sm:gap-3 py-1.5 text-tx-secondary font-medium text-xs sm:text-sm mx-auto">
                                <?php foreach ($navItems as $item): ?>
                                    <a href="<?= $item['url'] ?>" class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 <?= $item['active'] ? 'bg-brand/15 text-brand ring-1 ring-brand/30 shadow-sm shadow-brand/10' : 'hover:text-tx-primary hover:bg-elevated' ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <?= $item['icon'] ?>
                                        </svg>
                                        <span><?= $item['label'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                                
                                <?php if (auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin') || auth()->user()->inGroup('manager')): ?>
                                    <div class="h-6 w-px bg-br-default mx-1 shrink-0"></div>
                                    <a href="<?= auth()->user()->inGroup('manager') ? base_url('admin/cooperative') : base_url('admin') ?>" class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 bg-success/10 text-success ring-1 ring-success/30 hover:bg-success/20 shadow-sm shadow-success/10">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        <span>Panel Admin</span>
                                    </a>
                                <?php endif; ?>
                            </nav>
                            
                            <!-- Efek fade kanan -->
                            <div id="navFadeRight" class="absolute right-0 top-0 bottom-0 w-8 bg-linear-to-l from-base to-transparent pointer-events-none opacity-0 transition-opacity duration-200"></div>
                        </div>
                        
                        <!-- Tombol panah kanan -->
                        <button id="navScrollRight" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 p-1 rounded-full bg-base/80 backdrop-blur-sm border border-br-default text-tx-secondary hover:text-brand transition-all opacity-0 pointer-events-none shadow-md" aria-label="Scroll kanan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="border-t border-br-default bg-surface/30 backdrop-blur-md py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs sm:text-sm text-tx-secondary">© 2026 CatatanKeuangan CI4 + Tailwind v4. Semua Hak Dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-xs text-tx-secondary hover:text-brand transition-colors">Kebijakan Privasi</a>
                    <span class="text-xs text-tx-disabled/50 select-none">|</span>
                    <a href="#" class="text-xs text-tx-secondary hover:text-brand transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Navigation Drawer -->
    <?= view('partials/mobile_drawer', ['portal' => 'finance', 'navItems' => $navItems ?? null]) ?>

    <!-- Theme & Global Scripts -->
    <?= view('partials/global_scripts') ?>
</body>
</html>