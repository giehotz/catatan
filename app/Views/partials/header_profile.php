<?php
/**
 * @var string $portal
 * @var bool|null $forceDarkTheme
 */
helper('inbox');
$unreadCount = auth()->loggedIn() ? count_unread_messages() : 0;
$user = auth()->user();
$username = $user ? (string) esc($user->username) : '';
$avatar = $user ? (string) esc($user->avatar) : '';
$isAdminPortal = in_array($portal, ['admin', 'admin_cooperative']);
$isCoopPortal = in_array($portal, ['cooperative', 'admin_cooperative']);

// Button & badge styling based on portal
$btnStyle = 'bg-indigo-500/10 text-indigo-400 hover:text-indigo-300 border-indigo-500/20';
if ($portal === 'cooperative') {
    $btnStyle = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 border-emerald-500/20';
} elseif ($isAdminPortal) {
    $btnStyle = 'bg-indigo-500/10 text-indigo-400 hover:text-indigo-300 border-indigo-500/20';
}
?>
<div class="flex items-center gap-3">
    <!-- Theme Toggle (Hidden physically if forceDarkTheme is true) -->
    <?php if (!($forceDarkTheme ?? false)): ?>
        <button id="themeToggleBtn" onclick="cycleTheme('<?= base_url("profile/update-theme") ?>')" class="p-2 text-tx-secondary hover:text-brand hover:bg-elevated rounded-full transition-colors relative cursor-pointer" title="Ubah Tema (Terang/Gelap/Sistem)">
            <svg class="w-5 h-5 theme-icon-sun hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <svg class="w-5 h-5 theme-icon-moon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg class="w-5 h-5 theme-icon-system hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </button>
    <?php endif; ?>

    <?php if (auth()->loggedIn()): ?>
        <!-- Global unread messages check -->
        <a href="<?= base_url('inbox') ?>" class="relative p-2 text-tx-secondary hover:text-brand hover:bg-elevated rounded-full transition-colors" title="Pesan Masuk">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <?php if ($unreadCount > 0): ?>
                <span class="absolute top-1 right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-base">
                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- User Role Label -->
        <?php if ($isAdminPortal): ?>
            <span class="text-xs text-slate-400 hidden sm:inline">
                <?= $portal === 'admin_cooperative' ? 'Manager KSP' : 'Administrator' ?>: 
                <strong class="text-indigo-400 font-semibold"><?= $username ?></strong>
            </span>
        <?php else: ?>
            <span class="text-xs text-tx-secondary hidden sm:inline">
                Halo, <strong class="text-tx-primary font-semibold"><?= $username ?></strong>
            </span>
        <?php endif; ?>

        <!-- User Avatar -->
        <a href="<?= $isAdminPortal ? '#' : base_url('profile') ?>" class="w-7 h-7 rounded-full bg-elevated hover:bg-elevated/80 border border-br-default hover:border-brand/50 overflow-hidden flex items-center justify-center font-bold text-brand hover:text-brand/80 transition-all duration-205">
            <?php if (!empty($avatar) && file_exists(FCPATH . 'uploads/avatars/' . $avatar)) : ?>
                <img src="<?= base_url('uploads/avatars/' . $avatar) ?>" alt="<?= $username ?>" class="w-full h-full object-cover">
            <?php else : ?>
                <span class="text-xs"><?= strtoupper(substr($username, 0, 2)) ?></span>
            <?php endif; ?>
        </a>

        <!-- Logout Button -->
        <a href="<?= base_url('logout') ?>" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition-all duration-200 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border-rose-500/20 cursor-pointer">
            Keluar
        </a>

        <!-- Hamburger button for mobile menu -->
        <button onclick="toggleMobileMenu()" class="block md:hidden p-1.5 text-slate-400 hover:text-white bg-slate-900 hover:bg-slate-800/80 rounded-lg border border-slate-800 transition-all cursor-pointer" title="Buka Menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    <?php else: ?>
        <a href="<?= base_url('login') ?>" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition-all duration-200">
            Masuk
        </a>
    <?php endif; ?>
</div>
