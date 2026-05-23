<?php
/**
 * @var string|null $title
 */
helper('inbox');
$unreadCount = auth()->loggedIn() ? count_unread_messages() : 0;

// Menentukan status aktif navigasi bawah
$currentUrl = current_url();
$isDashboardActive = $currentUrl === base_url('/') || $currentUrl === base_url();
$isTransactionActive = strpos($currentUrl, base_url('transaction')) !== false && strpos($currentUrl, base_url('wallets')) === false;
$isReportsActive = strpos($currentUrl, base_url('reports')) !== false;
$isLainnyaActive = !$isDashboardActive && !$isTransactionActive && !$isReportsActive;
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-base text-tx-primary selection:bg-brand selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'Catatan Keuangan' ?> (Mobile)</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        /* Hide scrollbars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Glassmorphic active dot */
        .mobile-nav-dot {
            box-shadow: 0 0 8px var(--color-brand);
        }
    </style>
    <script>
        (function() {
            // Prioritas: DB (via PHP inject) > localStorage > OS
            const serverTheme = '<?= (auth()->loggedIn() && isset(auth()->user()->theme_preference)) ? esc((string) auth()->user()->theme_preference) : '' ?>';
            const saved = serverTheme || localStorage.getItem('theme') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            let activeTheme = 'dark';
            if (saved === 'light' || saved === 'dark') {
                activeTheme = saved;
            } else {
                activeTheme = prefersDark ? 'dark' : 'light';
            }
            
            document.documentElement.className = 'h-full theme-' + activeTheme;
            
            // Dynamic & Defensive Meta Theme-Color
            let meta = document.querySelector('meta[name="theme-color"]');
            if (!meta) {
                meta = document.createElement('meta');
                meta.name = 'theme-color';
                document.head.appendChild(meta);
            }
            meta.content = activeTheme === 'dark' ? '#020617' : '#f8fafc';
        })();
    </script>
</head>
<body class="h-full bg-base antialiased overflow-x-hidden">
    
    <!-- Centered Smartphone Viewport Container for Desktop -->
    <div class="min-h-screen max-w-md mx-auto bg-base shadow-2xl border-x border-br-default/60 flex flex-col relative">
        
        <!-- Premium glowing gradients background -->
        <div class="absolute top-0 left-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/4 right-5 w-64 h-64 bg-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-20 left-5 w-64 h-64 bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="min-h-full flex flex-col">
        <!-- Compact Mobile Top Header -->
        <header class="sticky top-0 z-30 backdrop-blur-md bg-base/80 border-b border-br-default/60 px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-linear-to-tr from-brand to-purple-500 flex items-center justify-center shadow-lg shadow-brand/20">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-base font-extrabold bg-linear-to-r from-tx-primary via-tx-secondary to-brand bg-clip-text text-transparent">CatatanKeuangan</span>
            </div>

            <!-- Profile & Inbox Icons -->
            <div class="flex items-center gap-3">
                <a href="<?= base_url('inbox') ?>" class="relative p-2 text-slate-400 hover:text-indigo-400 active:scale-95 transition-all" title="Pesan Masuk">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <?php if ($unreadCount > 0): ?>
                        <span class="absolute top-1.5 right-1.5 flex h-3 w-3 items-center justify-center rounded-full bg-rose-500 text-[8px] font-bold text-white ring-2 ring-slate-950">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?= base_url('profile') ?>" class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center font-bold text-indigo-400 active:scale-90 transition-all">
                    <?php if (auth()->loggedIn() && !empty(auth()->user()->avatar) && file_exists(FCPATH . 'uploads/avatars/' . auth()->user()->avatar)) : ?>
                        <img src="<?= base_url('uploads/avatars/' . auth()->user()->avatar) ?>" alt="avatar" class="w-full h-full object-cover">
                    <?php else : ?>
                        <span class="text-xs"><?= auth()->loggedIn() ? (string) esc(strtoupper(substr((string) auth()->user()->username, 0, 2))) : 'U' ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>

        <!-- Main Content Area with padding bottom to avoid Bottom Nav overlap -->
        <main class="grow px-4 pt-6 pb-28 relative">
            <!-- Impersonation Warning Banner if active -->
            <?php if (session()->has('impersonator_user_id')) : ?>
                <div class="mb-4 bg-linear-to-r from-amber-600 to-orange-600 text-white p-3 rounded-xl shadow-lg text-xs font-semibold border border-orange-500/20 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-2 w-2 relative shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                        <span class="leading-tight">Mode Penyamaran: <strong><?= (string) esc((string) auth()->user()->username) ?></strong>.</span>
                    </div>
                    <a href="<?= base_url('admin/stop-impersonate') ?>" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg text-[10px] font-bold shrink-0">Kembali</a>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Sticky Glassmorphic Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 md:max-w-md md:left-1/2 md:-translate-x-1/2 z-40 backdrop-blur-md bg-base/80 border-t border-br-default/60 pb-safe h-16 flex items-center justify-around px-4">
        <!-- Dashboard / Beranda -->
        <a href="<?= base_url('/') ?>" class="relative flex flex-col items-center justify-center w-12 h-12 text-tx-secondary hover:text-brand transition-colors <?= $isDashboardActive ? 'text-brand font-bold' : '' ?>">
            <svg class="w-5 h-5 <?= $isDashboardActive ? 'scale-110 -translate-y-0.5' : '' ?> transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[10px] mt-0.5">Dashboard</span>
            <?php if ($isDashboardActive): ?>
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-brand mobile-nav-dot"></span>
            <?php endif; ?>
        </a>

        <!-- Transaksi -->
        <a href="<?= base_url('transaction') ?>" class="relative flex flex-col items-center justify-center w-12 h-12 text-tx-secondary hover:text-brand transition-colors <?= $isTransactionActive ? 'text-brand font-bold' : '' ?>">
            <svg class="w-5 h-5 <?= $isTransactionActive ? 'scale-110 -translate-y-0.5' : '' ?> transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[10px] mt-0.5">Transaksi</span>
            <?php if ($isTransactionActive): ?>
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-brand mobile-nav-dot"></span>
            <?php endif; ?>
        </a>

        <!-- Floating Quick Action FAB Button -->
        <div class="relative w-14 h-14 -mt-6">
            <button onclick="toggleQuickAddSheet()" class="w-14 h-14 rounded-full bg-linear-to-tr from-brand to-brand-hover flex items-center justify-center text-white shadow-lg shadow-brand/35 hover:scale-105 active:scale-95 transition-all focus:outline-hidden cursor-pointer" id="quickAddBtn" aria-label="Catat Transaksi Cepat">
                <svg class="w-7 h-7 transition-transform duration-300" id="quickAddIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <!-- Laporan -->
        <a href="<?= base_url('reports') ?>" class="relative flex flex-col items-center justify-center w-12 h-12 text-tx-secondary hover:text-brand transition-colors <?= $isReportsActive ? 'text-brand font-bold' : '' ?>">
            <svg class="w-5 h-5 <?= $isReportsActive ? 'scale-110 -translate-y-0.5' : '' ?> transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            <span class="text-[10px] mt-0.5">Laporan</span>
            <?php if ($isReportsActive): ?>
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-brand mobile-nav-dot"></span>
            <?php endif; ?>
        </a>

        <!-- Menu Lainnya (Drawer Toggle) -->
        <button onclick="toggleMoreMenuSheet()" class="relative flex flex-col items-center justify-center w-12 h-12 text-tx-secondary hover:text-brand transition-colors cursor-pointer outline-none <?= $isLainnyaActive ? 'text-brand font-bold' : '' ?>">
            <svg class="w-5 h-5 <?= $isLainnyaActive ? 'scale-110 -translate-y-0.5' : '' ?> transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="text-[10px] mt-0.5">Lainnya</span>
            <?php if ($isLainnyaActive): ?>
                <span class="absolute bottom-1 w-1 h-1 rounded-full bg-brand mobile-nav-dot"></span>
            <?php endif; ?>
        </button>
    </nav>

    <!-- MOBILE SYSTEM SHEETS (BACKDRUP & BOTTOM SHEETS) -->

    <!-- Sheet Backdrop -->
    <div id="sheetBackdrop" onclick="closeAllSheets()" class="fixed inset-0 md:max-w-md md:left-1/2 md:-translate-x-1/2 bg-base/80 backdrop-blur-xs z-45 transition-opacity duration-300 opacity-0 pointer-events-none"></div>

    <!-- Quick Add Bottom Sheet -->
    <div id="quickAddSheet" role="dialog" aria-modal="true" aria-label="Menu Aksi Cepat" class="fixed bottom-0 left-0 right-0 md:max-w-md md:left-1/2 md:-translate-x-1/2 max-h-[85vh] bg-surface border-t border-br-default rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 ease-out pb-8 flex flex-col overflow-hidden shadow-2xl">
        <!-- Glow accent inside modal -->
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Pull Handle -->
        <div class="w-12 h-1.5 bg-br-default rounded-full mx-auto my-3 shrink-0"></div>
        
        <div class="px-6 pb-2 shrink-0">
            <h3 class="text-base font-extrabold text-tx-primary">Catat Transaksi Cepat</h3>
            <p class="text-xs text-tx-secondary mt-0.5">Pilih jenis transaksi finansial yang ingin segera dicatat</p>
        </div>

        <div class="p-6 space-y-3 grow overflow-y-auto no-scrollbar">
            <!-- Pemasukan -->
            <a href="<?= base_url('transaction?action=add_income') ?>" class="flex items-center gap-4 p-4 rounded-2xl bg-success/10 border border-success/30 active:bg-success/20 active:scale-98 transition-all">
                <div class="w-10 h-10 rounded-xl bg-success/20 text-success flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="text-left">
                    <span class="block text-sm font-bold text-success">Catat Pemasukan</span>
                    <span class="block text-[11px] text-tx-secondary leading-tight">Catat uang masuk, gaji, piutang cair, dll.</span>
                </div>
            </a>

            <!-- Pengeluaran -->
            <a href="<?= base_url('transaction?action=add_expense') ?>" class="flex items-center gap-4 p-4 rounded-2xl bg-danger/10 border border-danger/30 active:bg-danger/20 active:scale-98 transition-all">
                <div class="w-10 h-10 rounded-xl bg-danger/20 text-danger flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                    </svg>
                </div>
                <div class="text-left">
                    <span class="block text-sm font-bold text-danger">Catat Pengeluaran</span>
                    <span class="block text-[11px] text-tx-secondary leading-tight">Belanja makanan, utilitas, cicilan utang, dll.</span>
                </div>
            </a>

            <!-- Transfer -->
            <a href="<?= base_url('wallets/transfer') ?>" class="flex items-center gap-4 p-4 rounded-2xl bg-brand/10 border border-brand/30 active:bg-brand/20 active:scale-98 transition-all">
                <div class="w-10 h-10 rounded-xl bg-brand/20 text-brand flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div class="text-left">
                    <span class="block text-sm font-bold text-brand">Transfer Saldo Rekening</span>
                    <span class="block text-[11px] text-tx-secondary leading-tight">Pindah dana antar kas, bank, atau dompet digital.</span>
                </div>
            </a>
        </div>
    </div>

    <!-- More Menu Bottom Sheet -->
    <div id="moreMenuSheet" role="dialog" aria-modal="true" aria-label="Menu Tambahan" class="fixed bottom-0 left-0 right-0 md:max-w-md md:left-1/2 md:-translate-x-1/2 max-h-[85vh] bg-surface border-t border-br-default rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 ease-out pb-8 flex flex-col overflow-hidden shadow-2xl">
        <!-- Glow accent inside modal -->
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Pull Handle -->
        <div class="w-12 h-1.5 bg-br-default rounded-full mx-auto my-3 shrink-0"></div>

        <div class="px-6 pb-2 shrink-0">
            <h3 class="text-base font-extrabold text-tx-primary">Menu Navigasi</h3>
            <p class="text-xs text-tx-secondary mt-0.5">Kelola aspek keuangan lainnya dan pengaturan aplikasi</p>
        </div>

        <div class="p-5 grow overflow-y-auto no-scrollbar">
            <!-- Theme Preference Segmented Control (Mobile) -->
            <div class="mb-6 p-4 rounded-2xl bg-base border border-br-default/60">
                <span class="block text-xs font-bold text-tx-secondary uppercase tracking-wider mb-2">Tema Aplikasi</span>
                <div class="flex p-1 bg-surface border border-br-default rounded-2xl gap-1">
                    <button id="mobile-theme-btn-light" onclick="updateThemeSelection('light')" class="flex-1 py-2 text-center text-xs font-semibold text-tx-secondary hover:text-tx-primary transition-all cursor-pointer">
                        ☀️ Terang
                    </button>
                    <button id="mobile-theme-btn-dark" onclick="updateThemeSelection('dark')" class="flex-1 py-2 text-center text-xs font-semibold text-tx-secondary hover:text-tx-primary transition-all cursor-pointer">
                        🌙 Gelap
                    </button>
                    <button id="mobile-theme-btn-system" onclick="updateThemeSelection('system')" class="flex-1 py-2 text-center text-xs font-semibold text-tx-secondary hover:text-tx-primary transition-all cursor-pointer">
                        💻 Sistem
                    </button>
                </div>
            </div>

            <!-- Navigation Links Grid -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <!-- Rekening -->
                <a href="<?= base_url('wallets') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Rekening</span>
                </a>

                <!-- Tabungan -->
                <a href="<?= base_url('savings') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Tabungan</span>
                </a>

                <!-- Kategori -->
                <a href="<?= base_url('category') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Kategori</span>
                </a>

                <!-- Anggaran -->
                <a href="<?= base_url('budgets') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Anggaran</span>
                </a>

                <!-- Berulang -->
                <a href="<?= base_url('recurring') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Berulang</span>
                </a>

                <!-- Utang Piutang -->
                <a href="<?= base_url('debt-receivable') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Utang Piutang</span>
                </a>

                <!-- Koperasi -->
                <a href="<?= base_url('cooperative') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Portal Koperasi</span>
                </a>

                <!-- Profil Saya -->
                <a href="<?= base_url('profile') ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-base border border-br-default hover:border-br-subtle active:scale-95 transition-all text-center">
                    <div class="w-8 h-8 rounded-lg bg-surface text-brand flex items-center justify-center mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-tx-primary">Profil Saya</span>
                </a>
            </div>

            <!-- Admin Shortcut Panel if allowed -->
            <?php if (auth()->loggedIn() && (auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin') || auth()->user()->inGroup('manager'))): ?>
                <a href="<?= auth()->user()->inGroup('manager') ? base_url('admin/cooperative') : base_url('admin') ?>" class="flex items-center justify-between gap-3 p-4 mb-4 rounded-2xl bg-success/10 border border-success/20 active:scale-98 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-success/20 text-success flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-success">Masuk Panel Admin</span>
                    </div>
                    <svg class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            <?php endif; ?>

            <!-- Logout Button -->
            <a href="<?= base_url('logout') ?>" class="w-full flex items-center justify-center gap-2 p-3 bg-danger/10 hover:bg-danger/20 active:scale-95 text-danger hover:text-danger/80 font-extrabold text-xs rounded-2xl border border-danger/25 transition-all text-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar Aplikasi</span>
            </a>
        </div>
    </div>

    <!-- MOBILE SHEETS LOGIC (Pure JS & Lightweight) -->
    <script>
        function toggleQuickAddSheet() {
            const sheet = document.getElementById('quickAddSheet');
            const backdrop = document.getElementById('sheetBackdrop');
            const btnIcon = document.getElementById('quickAddIcon');
            const isOpen = !sheet.classList.contains('translate-y-full');

            // Close other sheets
            document.getElementById('moreMenuSheet').classList.add('translate-y-full');

            if (isOpen) {
                sheet.classList.add('translate-y-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                btnIcon.classList.remove('rotate-45');
                document.body.style.overflow = '';
            } else {
                sheet.classList.remove('translate-y-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                btnIcon.classList.add('rotate-45');
                document.body.style.overflow = 'hidden';
            }
        }

        function toggleMoreMenuSheet() {
            const sheet = document.getElementById('moreMenuSheet');
            const backdrop = document.getElementById('sheetBackdrop');
            const quickBtnIcon = document.getElementById('quickAddIcon');
            const isOpen = !sheet.classList.contains('translate-y-full');

            // Close other sheets
            document.getElementById('quickAddSheet').classList.add('translate-y-full');
            quickBtnIcon.classList.remove('rotate-45');

            if (isOpen) {
                sheet.classList.add('translate-y-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = '';
            } else {
                sheet.classList.remove('translate-y-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAllSheets() {
            document.getElementById('quickAddSheet').classList.add('translate-y-full');
            document.getElementById('moreMenuSheet').classList.add('translate-y-full');
            document.getElementById('sheetBackdrop').classList.add('opacity-0', 'pointer-events-none');
            document.getElementById('sheetBackdrop').classList.remove('opacity-100', 'pointer-events-auto');
            document.getElementById('quickAddIcon').classList.remove('rotate-45');
            document.body.style.overflow = '';
        }
    </script>
    
    <!-- Theme System Global Scripts (Mobile) -->
    <script>
        // Global Theme Management System
        const Toast = {
            show(message, type = 'success') {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 max-w-sm w-full px-4 pointer-events-none';
                    document.body.appendChild(container);
                }
                
                const toast = document.createElement('div');
                toast.className = `p-4 rounded-2xl border shadow-2xl flex items-center gap-3 text-xs font-semibold transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto ${
                    type === 'success' 
                    ? 'bg-success/15 border-success/30 text-success' 
                    : 'bg-danger/15 border-danger/30 text-danger'
                }`;
                
                const icon = type === 'success' 
                    ? '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                    : '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
                    
                toast.innerHTML = `${icon}<span class="leading-tight">${message}</span>`;
                container.appendChild(toast);
                
                setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
                
                setTimeout(() => {
                    toast.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }
        };

        let currentThemePref = localStorage.getItem('theme') || 'system';
        const serverTheme = '<?= (auth()->loggedIn() && isset(auth()->user()->theme_preference)) ? esc((string) auth()->user()->theme_preference) : '' ?>';
        if (serverTheme) {
            currentThemePref = serverTheme;
        }

        let themeTransitionTimer = null;
        let saveThemeDebounceTimer = null;

        function applyThemeClass(themeVal) {
            if (themeTransitionTimer) {
                clearTimeout(themeTransitionTimer);
            }
            
            // Smooth transition
            document.body.classList.add('theme-transitioned');
            
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            let targetClass = 'dark';
            if (themeVal === 'light' || themeVal === 'dark') {
                targetClass = themeVal;
            } else {
                targetClass = prefersDark ? 'dark' : 'light';
            }
            
            document.documentElement.className = 'h-full theme-' + targetClass;
            
            // Defensive Meta Theme-Color
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.setAttribute('content', targetClass === 'dark' ? '#020617' : '#f8fafc');
            }
            
            themeTransitionTimer = setTimeout(() => {
                document.body.classList.remove('theme-transitioned');
                themeTransitionTimer = null;
            }, 200);
        }

        function updateThemeSelection(themeVal, syncToServer = true) {
            const oldThemePref = currentThemePref;
            currentThemePref = themeVal;
            localStorage.setItem('theme', themeVal);
            
            applyThemeClass(themeVal);
            
            // Update active state in mobile bottom sheet if it exists
            updateMobileSheetControls();
            // Update active state in profile page dropdown if it exists
            updateProfileDropdownControls();
            
            // Auto close mobile more drawer after 300ms
            if (document.getElementById('moreMenuSheet') && !document.getElementById('moreMenuSheet').classList.contains('translate-y-full')) {
                setTimeout(() => {
                    closeAllSheets();
                }, 300);
            }
            
            if (syncToServer && '<?= auth()->loggedIn() ? "true" : "false" ?>' === 'true') {
                if (saveThemeDebounceTimer) {
                    clearTimeout(saveThemeDebounceTimer);
                }
                
                saveThemeDebounceTimer = setTimeout(() => {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers = {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };
                    if (csrfMeta) {
                        headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
                    }
                    
                    fetch('<?= base_url("profile/update-theme") ?>', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ theme_preference: themeVal })
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Gagal memperbarui tema di server.');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            Toast.show('Preferensi tema berhasil disimpan.', 'success');
                        } else {
                            throw new Error(data.message || 'Gagal menyimpan preferensi.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Toast.show('Gagal menyelaraskan tema dengan server. Pilihan dikembalikan.', 'danger');
                        // Rollback
                        updateThemeSelection(oldThemePref, false);
                    });
                }, 300); // 300ms debounce
            }
        }

        // OS system preference listener
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (currentThemePref === 'system') {
                applyThemeClass('system');
            }
        });

        // Mobile Bottom Sheet control update helper
        function updateMobileSheetControls() {
            ['light', 'dark', 'system'].forEach(t => {
                const btn = document.getElementById(`mobile-theme-btn-${t}`);
                if (btn) {
                    if (currentThemePref === t) {
                        btn.className = "flex-1 py-2 text-center text-xs font-bold bg-brand text-white rounded-xl shadow-sm transition-all cursor-pointer";
                    } else {
                        btn.className = "flex-1 py-2 text-center text-xs font-semibold text-tx-secondary hover:text-tx-primary transition-all cursor-pointer";
                    }
                }
            });
        }

        // Profile dropdown update helper
        function updateProfileDropdownControls() {
            const select = document.getElementById('theme_preference_select');
            if (select) {
                select.value = currentThemePref;
            }
        }

        // Init toggle controls on load
        updateMobileSheetControls();

        // Guest-to-User State Alignment Sync
        (function() {
            const loggedIn = '<?= auth()->loggedIn() ? "true" : "false" ?>' === 'true';
            if (loggedIn) {
                const userId = '<?= auth()->id() ?>';
                const syncKey = `theme_synced_${userId}`;
                const synced = localStorage.getItem(syncKey);
                
                if (!synced) {
                    const localPref = localStorage.getItem('theme') || 'system';
                    const sPref = '<?= (auth()->loggedIn() && isset(auth()->user()->theme_preference)) ? esc((string) auth()->user()->theme_preference) : "system" ?>';
                    
                    if (sPref === 'system' && localPref !== 'system') {
                        // local preference is more specific, update server
                        updateThemeSelection(localPref, true);
                    } else if (sPref !== 'system') {
                        // server is more specific, overwrite local
                        localStorage.setItem('theme', sPref);
                        currentThemePref = sPref;
                        applyThemeClass(sPref);
                    }
                    localStorage.setItem(syncKey, 'true');
                }
            }
        })();
    </script>
    </div>
</body>
</html>
