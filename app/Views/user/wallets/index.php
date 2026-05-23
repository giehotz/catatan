<?php
/**
 * @var array $wallets
 * @var float $totalAssets
 */
?>
<?= $this->extend('layouts/base') ?>
 
<?= $this->section('content') ?>
<div class="space-y-8">
 
    <!-- Welcome Header & Quick Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Rekening & Dompet</h1>
            <p class="text-tx-secondary text-sm">Kelola semua sumber dana Anda, pantau saldo, dan lakukan transfer saldo antar dompet.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= base_url('wallets/transfer') ?>" class="px-5 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-brand/10 flex items-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Transfer Saldo
            </a>
            <button onclick="openAddWalletModal()" class="px-5 py-3 bg-linear-to-r from-success to-success/90 hover:from-success/90 hover:to-success text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-success/10 flex items-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Rekening
            </button>
        </div>
    </div>
 
    <!-- Alert Messages -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>
    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>
 
    <!-- Total Asset Card Summary -->
    <div class="bg-linear-to-r from-surface to-elevated p-6 sm:p-8 rounded-3xl border border-br-default shadow-2xl relative overflow-hidden">
        <!-- Glowing gradient backgrounds -->
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-brand/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -top-16 w-64 h-64 bg-success/5 rounded-full blur-3xl pointer-events-none"></div>
 
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2">
                <span class="text-xs sm:text-sm font-bold text-brand uppercase tracking-widest">Total Aset Keseluruhan</span>
                <h2 class="text-4xl sm:text-5xl font-black text-tx-primary tracking-tight">
                    Rp<?= number_format($totalAssets, 0, ',', '.') ?>
                </h2>
                <p class="text-tx-secondary text-xs sm:text-sm">Akumulasi bersih dari seluruh saldo dompet aktif terdaftar Anda.</p>
            </div>
            <div class="p-4 bg-surface/40 border border-br-default backdrop-blur-md rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand/10 text-brand flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-tx-primary"><?= count($wallets) ?> Rekening</h4>
                    <p class="text-xs text-tx-secondary">Tersedia untuk transaksi</p>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Wallet Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($wallets as $wallet) : ?>
            <?php
            // Assign gradient and icon based on wallet type
            $gradientClass = 'from-warning/5 to-surface';
            $iconBg = 'bg-warning/10 text-warning';
            $typeBadge = '';
            
            switch ($wallet['type']) {
                case 'cash':
                    $gradientClass = 'from-success/5 to-surface';
                    $iconBg = 'bg-success/10 text-success';
                    $typeBadge = 'Tunai';
                    $svgIcon = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>';
                    break;
                case 'bank':
                    $gradientClass = 'from-brand/5 to-surface';
                    $iconBg = 'bg-brand/10 text-brand';
                    $typeBadge = 'Bank / Rekening';
                    $svgIcon = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>';
                    break;
                case 'e-wallet':
                    $gradientClass = 'from-danger/5 to-surface';
                    $iconBg = 'bg-danger/10 text-danger';
                    $typeBadge = 'Dompet Digital';
                    $svgIcon = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>';
                    break;
                case 'investment':
                    $gradientClass = 'from-info/5 to-surface';
                    $iconBg = 'bg-info/10 text-info';
                    $typeBadge = 'Investasi';
                    $svgIcon = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>';
                    break;
                default:
                    $gradientClass = 'from-warning/5 to-surface';
                    $iconBg = 'bg-warning/10 text-warning';
                    $typeBadge = 'Lainnya';
                    $svgIcon = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>';
                    break;
            }
            ?>
            <div class="bg-linear-to-br <?= $gradientClass ?> p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden flex flex-col justify-between h-48 group hover:border-brand/40 hover:bg-elevated/40 transition-all duration-300">
                <!-- Top Row: Icon and Actions -->
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center <?= $iconBg ?> shadow-md">
                        <?= $svgIcon ?>
                    </div>
                    
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <button onclick='openEditWalletModal(<?= json_encode($wallet) ?>)' class="p-1.5 hover:bg-elevated text-tx-secondary hover:text-tx-primary rounded-lg transition-colors cursor-pointer" title="Edit Dompet">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <?php if ($wallet['name'] !== 'Dompet Utama'): ?>
                            <button onclick="openDeleteWalletModal(<?= $wallet['id'] ?>, '<?= (string) esc($wallet['name']) ?>')" class="p-1.5 hover:bg-rose-500/10 text-tx-secondary hover:text-danger rounded-lg transition-colors cursor-pointer" title="Hapus Dompet">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
 
                <!-- Middle Row: Name and Type -->
                <div class="mt-4">
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-tx-disabled"><?= $typeBadge ?></span>
                    <h3 class="text-xl font-bold text-tx-primary truncate leading-snug"><?= (string) esc($wallet['name']) ?></h3>
                </div>
 
                <!-- Bottom Row: Balance -->
                <div class="mt-2 pt-2 border-t border-br-subtle flex justify-between items-baseline">
                    <span class="text-xs text-tx-secondary font-medium">Saldo Dompet</span>
                    <span class="text-2xl font-black tracking-tight text-tx-primary">
                        Rp<?= number_format($wallet['balance'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
 
</div>
 
<!-- MODAL: ADD WALLET -->
<div id="addWalletModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div onclick="closeAddWalletModal()" class="absolute inset-0 bg-base/80 backdrop-blur-xs transition-opacity"></div>
 
    <div class="bg-surface border border-br-default rounded-3xl w-full max-w-md p-6 relative z-10 shadow-2xl transform scale-95 transition-all duration-300 ease-out overflow-hidden" id="addWalletContainer">
        <!-- Background accents -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-brand/5 rounded-full blur-xl pointer-events-none"></div>
        
        <!-- Header -->
        <div class="flex justify-between items-center pb-4 border-b border-br-subtle relative z-10">
            <h3 class="text-lg font-bold text-tx-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Rekening Baru
            </h3>
            <button onclick="closeAddWalletModal()" class="text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
 
        <!-- Form -->
        <form action="<?= base_url('wallets/create') ?>" method="post" class="space-y-4 pt-4 relative z-10">
            <?= csrf_field() ?>
 
            <!-- Name Input -->
            <div class="space-y-1.5">
                <label for="add_name" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Nama Rekening/Dompet</label>
                <input type="text" id="add_name" name="name" placeholder="misal: Bank Mandiri, GoPay, Tunai Dompet" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-medium">
            </div>
 
            <!-- Type Select -->
            <div class="space-y-1.5">
                <label for="add_type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe Rekening</label>
                <select id="add_type" name="type" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-medium">
                    <option value="cash" class="bg-surface">Uang Tunai (Cash)</option>
                    <option value="bank" class="bg-surface">Bank / Kartu Kredit</option>
                    <option value="e-wallet" class="bg-surface">Dompet Digital (e-Wallet)</option>
                    <option value="investment" class="bg-surface">Investasi</option>
                    <option value="other" class="bg-surface">Lainnya</option>
                </select>
            </div>
 
            <!-- Starting Balance -->
            <div class="space-y-1.5">
                <label for="add_starting_balance" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Saldo Awal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">
                        Rp
                    </div>
                    <input type="text" id="add_starting_balance" name="starting_balance" placeholder="0" class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                </div>
                <p class="text-[10px] text-tx-secondary/60">Saldo awal akan dicatat otomatis sebagai transaksi pemasukan.</p>
            </div>
 
            <!-- Submit buttons -->
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" onclick="closeAddWalletModal()" class="w-1/3 py-3 bg-elevated hover:bg-elevated/85 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-success to-success/90 hover:from-success/90 hover:to-success text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-success/10 cursor-pointer">
                    Simpan Dompet
                </button>
            </div>
        </form>
    </div>
</div>
 
<!-- MODAL: EDIT WALLET -->
<div id="editWalletModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div onclick="closeEditWalletModal()" class="absolute inset-0 bg-base/80 backdrop-blur-xs transition-opacity"></div>
 
    <div class="bg-surface border border-br-default rounded-3xl w-full max-w-md p-6 relative z-10 shadow-2xl transform scale-95 transition-all duration-300 ease-out overflow-hidden" id="editWalletContainer">
        <!-- Header -->
        <div class="flex justify-between items-center pb-4 border-b border-br-subtle relative z-10">
            <h3 class="text-lg font-bold text-tx-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Ubah Informasi Rekening
            </h3>
            <button onclick="closeEditWalletModal()" class="text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
 
        <!-- Form -->
        <form id="editWalletForm" action="" method="post" class="space-y-4 pt-4 relative z-10">
            <?= csrf_field() ?>
 
            <!-- Name Input -->
            <div class="space-y-1.5">
                <label for="edit_name" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Nama Rekening/Dompet</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-medium">
            </div>
 
            <!-- Type Select -->
            <div class="space-y-1.5">
                <label for="edit_type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe Rekening</label>
                <select id="edit_type" name="type" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-medium">
                    <option value="cash" class="bg-surface">Uang Tunai (Cash)</option>
                    <option value="bank" class="bg-surface">Bank / Kartu Kredit</option>
                    <option value="e-wallet" class="bg-surface">Dompet Digital (e-Wallet)</option>
                    <option value="investment" class="bg-surface">Investasi</option>
                    <option value="other" class="bg-surface">Lainnya</option>
                </select>
            </div>
 
            <!-- Submit buttons -->
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" onclick="closeEditWalletModal()" class="w-1/3 py-3 bg-elevated hover:bg-elevated/85 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
 
<!-- MODAL: DELETE WALLET CONFIRMATION -->
<div id="deleteWalletModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <div onclick="closeDeleteWalletModal()" class="absolute inset-0 bg-base/80 backdrop-blur-xs transition-opacity"></div>
 
    <div class="bg-surface border border-br-default rounded-3xl w-full max-w-sm p-6 relative z-10 shadow-2xl transform scale-95 transition-all duration-300 ease-out overflow-hidden" id="deleteWalletContainer">
        <!-- Accent Glow -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-danger/5 rounded-full blur-xl pointer-events-none"></div>
 
        <div class="text-center space-y-4 relative z-10">
            <!-- Warning Icon -->
            <div class="mx-auto w-16 h-16 rounded-full bg-danger/10 text-danger flex items-center justify-center shadow-lg shadow-danger/10">
                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
 
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-tx-primary">Hapus Rekening?</h3>
                <p class="text-xs text-tx-secondary px-2 leading-relaxed">
                    Menghapus rekening <strong class="text-tx-primary" id="deleteWalletName"></strong> akan menghapus secara permanen **seluruh riwayat mutasi transaksi** di dalamnya.
                </p>
                <p class="text-[10px] text-danger font-bold bg-danger/5 py-1 px-2 rounded-lg mt-2 inline-block border border-danger/10">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
 
            <!-- Delete form -->
            <form id="deleteWalletForm" action="" method="post" class="flex gap-3 pt-3 border-t border-br-subtle">
                <?= csrf_field() ?>
                <button type="button" onclick="closeDeleteWalletModal()" class="w-1/2 py-2.5 bg-elevated hover:bg-elevated/85 text-tx-primary border border-br-default font-bold rounded-lg text-xs transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-2.5 bg-danger hover:bg-danger/90 text-white font-bold rounded-lg text-xs transition-all shadow-lg shadow-danger/10 cursor-pointer">
                    Hapus Permanen
                </button>
            </form>
        </div>
    </div>
</div>
 
<script>
    // Open Add Wallet Modal
    function openAddWalletModal() {
        const modal = document.getElementById('addWalletModal');
        const container = document.getElementById('addWalletContainer');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 50);
    }
 
    // Close Add Wallet Modal
    function closeAddWalletModal() {
        const modal = document.getElementById('addWalletModal');
        const container = document.getElementById('addWalletContainer');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
 
    // Open Edit Wallet Modal
    function openEditWalletModal(wallet) {
        const modal = document.getElementById('editWalletModal');
        const container = document.getElementById('editWalletContainer');
        const form = document.getElementById('editWalletForm');
        
        // Fill fields
        document.getElementById('edit_name').value = wallet.name;
        document.getElementById('edit_type').value = wallet.type;
        
        // Set action URI dynamically
        form.action = `<?= base_url('wallets/update') ?>/${wallet.id}`;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 50);
    }
 
    // Close Edit Wallet Modal
    function closeEditWalletModal() {
        const modal = document.getElementById('editWalletModal');
        const container = document.getElementById('editWalletContainer');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
 
    // Open Delete Wallet Modal
    function openDeleteWalletModal(id, name) {
        const modal = document.getElementById('deleteWalletModal');
        const container = document.getElementById('deleteWalletContainer');
        const form = document.getElementById('deleteWalletForm');
        
        document.getElementById('deleteWalletName').textContent = `"${name}"`;
        form.action = `<?= base_url('wallets/delete') ?>/${id}`;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 50);
    }
 
    // Close Delete Wallet Modal
    function closeDeleteWalletModal() {
        const modal = document.getElementById('deleteWalletModal');
        const container = document.getElementById('deleteWalletContainer');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
 
    // Live Dot Formatting for starting balance input
    document.addEventListener('DOMContentLoaded', () => {
        const startingBalanceInput = document.getElementById('add_starting_balance');
        if (startingBalanceInput) {
            startingBalanceInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formattedValue;
                
                let newLength = formattedValue.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
 
            // Strip dots on form submit
            const form = startingBalanceInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    startingBalanceInput.value = startingBalanceInput.value.replace(/\D/g, "");
                });
            }
        }
    });
</script>
<?= $this->endSection() ?>
