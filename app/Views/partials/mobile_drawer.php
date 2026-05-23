<?php
/**
 * @var string $portal
 * @var array|null $navItems
 */
$user = auth()->user();
$username = $user ? (string) esc($user->username) : '';
$avatar = $user ? (string) esc($user->avatar) : '';
$isAdminPortal = in_array($portal, ['admin', 'admin_cooperative']);
$isEmerald = in_array($portal, ['cooperative', 'admin_cooperative']);
$accentGlow = $isEmerald ? 'bg-emerald-500/5' : 'bg-indigo-500/5';
$accentText = $isEmerald ? 'text-emerald-450' : 'text-indigo-400';
$titleGradient = $isEmerald ? 'from-white to-emerald-400' : 'from-white to-indigo-400';
$activeClass = $isEmerald 
    ? 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30 shadow-sm shadow-emerald-500/10' 
    : 'bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/30 shadow-sm shadow-indigo-500/10';
?>
<?php if (auth()->loggedIn()): ?>
    <!-- Mobile Menu Backdrop -->
    <div id="mobileMenuBackdrop" onclick="toggleMobileMenu()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 transition-opacity duration-300 opacity-0 pointer-events-none"></div>

    <!-- Mobile Menu Drawer -->
    <div id="mobileMenuDrawer" class="fixed inset-y-0 right-0 max-w-xs w-full bg-slate-950/95 border-l border-slate-900 z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl">
        <div class="absolute top-0 right-0 w-32 h-32 <?= $accentGlow ?> rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex items-center justify-between h-14 px-5 border-b border-slate-900 relative z-10">
            <span class="text-md font-extrabold bg-linear-to-r <?= $titleGradient ?> bg-clip-text text-transparent">Menu Navigasi</span>
            <button onclick="toggleMobileMenu()" class="p-1.5 text-slate-400 hover:text-white bg-slate-900 hover:bg-slate-800 rounded-lg border border-slate-800 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-5 py-4 border-b border-slate-900 bg-slate-950/40 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center font-bold <?= $accentText ?> shrink-0">
                    <?php if (!empty($avatar) && file_exists(FCPATH . 'uploads/avatars/' . $avatar)) : ?>
                        <img src="<?= base_url('uploads/avatars/' . $avatar) ?>" alt="<?= $username ?>" class="w-full h-full object-cover">
                    <?php else : ?>
                        <span class="text-xs"><?= strtoupper(substr($username, 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs text-slate-500 font-semibold"><?= $isAdminPortal ? ($portal === 'admin_cooperative' ? 'Manager KSP' : 'Administrator') : 'Pengguna Aktif' ?></span>
                    <span class="text-sm font-semibold text-white truncate"><?= $username ?></span>
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto no-scrollbar py-3 px-3 space-y-1 relative z-10">
            <?php if (isset($navItems) && !empty($navItems)): ?>
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-all duration-205 <?= $item['active'] ? $activeClass : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60' ?>">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <?= $item['icon'] ?>
                        </svg>
                        <span><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Contextual Portal switch buttons in mobile menu -->
            <?php if ($portal === 'finance'): ?>
                <?php if ($user->inGroup('admin') || $user->inGroup('superadmin') || $user->inGroup('manager')): ?>
                    <div class="h-px bg-slate-900 my-2"></div>
                    <a href="<?= $user->inGroup('manager') ? base_url('admin/cooperative') : base_url('admin') ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-all duration-205 bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/30 hover:bg-emerald-500/20 shadow-sm shadow-emerald-500/10">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Panel Admin</span>
                    </a>
                <?php endif; ?>
            <?php elseif ($portal === 'cooperative'): ?>
                <div class="h-px bg-slate-900 my-2"></div>
                <a href="<?= base_url('/') ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs font-semibold transition-all duration-205 bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/30 hover:bg-indigo-500/20 shadow-sm shadow-indigo-500/10">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>← Catatan Keuangan</span>
                </a>
            <?php endif; ?>
        </div>
        <div class="p-4 border-t border-slate-900 bg-slate-950/60 relative z-10">
            <a href="<?= base_url('logout') ?>" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 font-bold text-xs rounded-xl border border-rose-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar dari Aplikasi</span>
            </a>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const backdrop = document.getElementById('mobileMenuBackdrop');
            const drawer = document.getElementById('mobileMenuDrawer');
            const isOpen = !drawer.classList.contains('translate-x-full');
            if (isOpen) {
                drawer.classList.add('translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = '';
            } else {
                drawer.classList.remove('translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                document.body.style.overflow = 'hidden';
            }
        }
    </script>
<?php endif; ?>
