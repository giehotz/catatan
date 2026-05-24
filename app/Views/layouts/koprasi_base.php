<?php

/**
 * @var string|null $title
 */
$memberActiveTab = 'flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 bg-emerald-500/15 text-emerald-500 dark:text-emerald-450 ring-1 ring-emerald-500/30 shadow-sm shadow-emerald-500/10 font-bold';
$memberInactiveTab = 'flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 font-semibold';

// Robust active detection for KSP main hub pages (including join flows)
$isHubActive = current_url() === base_url('cooperative')
    || strpos(current_url(), base_url('cooperative/join')) !== false
    || strpos(current_url(), base_url('cooperative/magic-join')) !== false;

// Self-contained KSP active member check using global shared cache with database fallback
$isKspActiveMember = false;
if (auth()->loggedIn()) {
    $userId = auth()->id();
    $cacheKey = "coop_member_active_{$userId}";
    $cachedStatus = cache($cacheKey);
    if ($cachedStatus !== null) {
        $isKspActiveMember = (bool) $cachedStatus;
    } else {
        $anggotaModel = new \App\Models\KopAnggotaModel();
        $member = $anggotaModel->where('user_id', $userId)
            ->where('status_keaktifan', 'aktif')
            ->first();
        $isKspActiveMember = !empty($member);
        cache()->save($cacheKey, $isKspActiveMember ? 1 : 0, 300);
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-base text-tx-primary selection:bg-emerald-500 selection:text-white">

<head>
    <?= view('partials/head', ['title' => $title ?? null, 'forceDarkTheme' => false]) ?>
</head>

<body class="h-full bg-base antialiased overflow-x-hidden">

    <!-- Premium background glowing gradients in Emerald/Teal -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-teal-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="min-h-full flex flex-col">
        <!-- Responsive Header / Navbar -->
        <header class="sticky top-0 z-40 backdrop-blur-md bg-base/70 border-b border-br-default">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top Row: Logo & Profile -->
                <div class="flex items-center justify-between h-14 border-b border-br-default/50">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-linear-to-tr from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="text-lg font-extrabold text-emerald-600">
                                PortalKSP
                            </span>
                        </div>
                    </div>

                    <!-- User Profile / Auth Actions -->
                    <div class="flex items-center gap-2.5">
                        <?= view('partials/header_profile', ['portal' => 'cooperative', 'forceDarkTheme' => false]) ?>
                    </div>
                </div>

                <!-- Bottom Row: Navigation Links (Desktop Only) -->
                <?php if (auth()->loggedIn()): ?>
                    <div id="desktopNavWrapper" class="relative hidden md:block">
                        <div id="mainNavLinks" class="flex items-center h-14 overflow-x-auto no-scrollbar scroll-smooth relative">
                            <nav class="flex items-center gap-2 sm:gap-3 py-1.5 text-slate-400 font-medium text-xs sm:text-sm mx-auto">
                                <a href="<?= base_url('/') ?>" class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/30 hover:bg-indigo-500/20 shadow-sm shadow-indigo-500/10" title="Kembali ke Catatan Keuangan Pribadi">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Catatan Keuangan</span>
                                </a>
                                <div class="h-6 w-px bg-slate-700 mx-1 shrink-0"></div>

                                <a href="<?= base_url('cooperative') ?>" class="<?= $isHubActive ? $memberActiveTab : $memberInactiveTab ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>Hub Koperasi Saya</span>
                                </a>
                                <?php if ($isKspActiveMember): ?>
                                    <a href="<?= base_url('cooperative/savings') ?>" class="<?= strpos(current_url(), base_url('cooperative/savings')) !== false ? $memberActiveTab : $memberInactiveTab ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Simpanan Saya</span>
                                    </a>
                                    <a href="<?= base_url('cooperative/loans') ?>" class="<?= strpos(current_url(), base_url('cooperative/loans')) !== false ? $memberActiveTab : $memberInactiveTab ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Pinjaman Saya</span>
                                    </a>
                                    <a href="<?= base_url('cooperative/shu') ?>" class="<?= strpos(current_url(), base_url('cooperative/shu')) !== false ? $memberActiveTab : $memberInactiveTab ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                        </svg>
                                        <span>SHU Saya</span>
                                    </a>
                                    <a href="<?= base_url('cooperative/bills') ?>" class="<?= strpos(current_url(), base_url('cooperative/bills')) !== false ? $memberActiveTab : $memberInactiveTab ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>Tagihan Saya</span>
                                    </a>
                                <?php endif; ?>

                                <!-- Shortcut back to admin for managers/admins -->
                                <?php if (auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin') || auth()->user()->inGroup('manager')): ?>
                                    <div class="h-6 w-px bg-slate-700 mx-1 shrink-0"></div>
                                    <a href="<?= base_url('admin/cooperative') ?>" class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg transition-all duration-200 shrink-0 bg-emerald-500/10 text-emerald-450 ring-1 ring-emerald-500/30 hover:bg-emerald-500/20 shadow-sm shadow-emerald-500/10" title="Kembali ke Panel Pengelola Koperasi">
                                        <span>Mode Pengelola</span>
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
            <div class="space-y-6">
                <!-- Cooperative Tab Navigation Header -->
                <div class="bg-surface border border-br-default rounded-3xl p-5 sm:p-6 shadow-xl relative">
                    <!-- Accent glowing corner glow -->
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-[60px] pointer-events-none overflow-hidden"></div>



                    <!-- Render Content Specific to KSP Sub-Views -->
                    <div class="relative z-10">
                        <?= $this->renderSection('koprasi_content') ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-br-default bg-surface/30 backdrop-blur-md py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs sm:text-sm text-tx-secondary">© 2026 CatatanKeuangan KSP Hub. Semua Hak Dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-xs text-tx-secondary hover:text-brand transition-colors">Kebijakan Privasi</a>
                    <span class="text-xs text-tx-disabled/50 select-none">|</span>
                    <a href="<?= base_url('cooperative/terms') ?>" class="text-xs text-slate-400 hover:text-indigo-400 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Navigation Drawer -->
    <?php
    $navItems = [
        ['url' => base_url('/'), 'label' => 'Catatan Keuangan', 'active' => false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />'],
        ['url' => base_url('cooperative'), 'label' => 'Hub Koperasi Saya', 'active' => $isHubActive, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
    ];
    if ($isKspActiveMember) {
        $navItems[] = ['url' => base_url('cooperative/savings'), 'label' => 'Simpanan Saya', 'active' => strpos(current_url(), base_url('cooperative/savings')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />'];
        $navItems[] = ['url' => base_url('cooperative/loans'), 'label' => 'Pinjaman Saya', 'active' => strpos(current_url(), base_url('cooperative/loans')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'];
        $navItems[] = ['url' => base_url('cooperative/shu'), 'label' => 'SHU Saya', 'active' => strpos(current_url(), base_url('cooperative/shu')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />'];
        $navItems[] = ['url' => base_url('cooperative/bills'), 'label' => 'Tagihan Saya', 'active' => strpos(current_url(), base_url('cooperative/bills')) !== false, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />'];
    }
    ?>
    <?= view('partials/mobile_drawer', ['portal' => 'cooperative', 'navItems' => $navItems]) ?>

    <!-- Theme & Global Scripts -->
    <?= view('partials/global_scripts') ?>
</body>

</html>