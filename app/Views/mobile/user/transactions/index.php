<?php
/**
 * @var array $transactions
 * @var array $incomeCategories
 * @var array $expenseCategories
 * @var array $wallets
 * @var float $totalIncome
 * @var float $totalExpense
 * @var float $netBalance
 * @var string|null $filterType
 * @var string|null $filterStartDate
 * @var string|null $filterEndDate
 * @var string|null $filterSearch
 * @var string|null $filterWallet
 */
?>
<?= $this->extend('layouts/mobile_base') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Welcome Header & Summary Stats -->
    <div class="space-y-1">
        <h1 class="text-xl font-extrabold text-tx-primary tracking-tight">Kelola Transaksi</h1>
        <p class="text-xs text-tx-secondary">Catat semua pemasukan dan pengeluaran finansial Anda</p>
    </div>

    <!-- Alert Messages -->
    <?php if (session('message') !== null) : ?>
        <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>
    <?php if (session('error') !== null) : ?>
        <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <!-- Summary Cards -->
    <div class="space-y-3">
        <!-- Saldo Bersih -->
        <div class="bg-surface/40 p-4.5 rounded-2xl border border-br-default/60 shadow-xl relative overflow-hidden flex justify-between items-center">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-brand/5 rounded-full pointer-events-none"></div>
            <div>
                <span class="text-[10px] font-semibold text-tx-secondary block">Saldo Bersih Periode Ini</span>
                <h3 class="text-lg font-extrabold mt-1 tracking-tight <?= $netBalance >= 0 ? 'text-brand' : 'text-danger' ?>">
                    Rp<?= number_format($netBalance, 0, ',', '.') ?>
                </h3>
            </div>
            <button onclick="openAdjustModal()" class="w-10 h-10 flex items-center justify-center text-brand/90 active:scale-90 bg-brand/10 hover:bg-brand/20 border border-brand/20 rounded-xl transition-all cursor-pointer shadow-sm focus:outline-hidden" title="Sesuaikan Saldo Bersih Rekening">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        </div>

        <!-- Pemasukan & Pengeluaran Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-surface/40 p-4 rounded-2xl border border-br-default/60 shadow-xl relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-success/5 rounded-full pointer-events-none"></div>
                <span class="text-[10px] font-semibold text-tx-secondary block">Total Pemasukan</span>
                <h3 class="text-sm font-extrabold text-success mt-1">
                    Rp<?= number_format($totalIncome, 0, ',', '.') ?>
                </h3>
            </div>
            <div class="bg-surface/40 p-4 rounded-2xl border border-br-default/60 shadow-xl relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-danger/5 rounded-full pointer-events-none"></div>
                <span class="text-[10px] font-semibold text-tx-secondary block">Total Pengeluaran</span>
                <h3 class="text-sm font-extrabold text-danger mt-1">
                    Rp<?= number_format($totalExpense, 0, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-surface/30 border border-br-default/70 rounded-2xl p-4 space-y-3">
        <form method="get" action="<?= url_to('transaction') ?>" id="filterForm">
            <div class="flex gap-2">
                <div class="relative grow">
                    <input type="text" name="search" value="<?= (string) esc($filterSearch) ?>" placeholder="Cari deskripsi..." class="w-full pl-9 pr-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tx-disabled">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <button type="button" onclick="toggleFilterDrawer()" class="px-3.5 py-2.5 bg-base/60 border border-br-default rounded-xl text-tx-secondary hover:text-tx-primary active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer relative focus:outline-hidden">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                    </svg>
                    <?php if (!empty($filterType) || !empty($filterWallet) || !empty($filterStartDate) || !empty($filterEndDate)): ?>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-brand shadow-xs shadow-brand/50"></span>
                    <?php endif; ?>
                </button>
                <button type="submit" class="px-4 py-2.5 bg-brand hover:bg-brand-hover text-white font-bold text-xs rounded-xl active:scale-95 transition-all shadow-md shadow-brand/10 cursor-pointer">
                    Filter
                </button>
            </div>

            <!-- Drawer Advanced Filters -->
            <div id="advancedFilters" class="hidden mt-4 pt-4 border-t border-br-default/60 space-y-4">
                <!-- Type -->
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Tipe Transaksi</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="" class="sr-only peer" <?= empty($filterType) ? 'checked' : '' ?>>
                            <span class="block text-center py-2 px-2 text-xs bg-base/60 border border-br-default rounded-xl text-tx-secondary peer-checked:bg-brand/10 peer-checked:border-brand/30 peer-checked:text-brand font-bold transition-all">Semua</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="income" class="sr-only peer" <?= $filterType === 'income' ? 'checked' : '' ?>>
                            <span class="block text-center py-2 px-2 text-xs bg-base/60 border border-br-default rounded-xl text-tx-secondary peer-checked:bg-success/10 peer-checked:border-success/30 peer-checked:text-success font-bold transition-all">Masuk</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" class="sr-only peer" <?= $filterType === 'expense' ? 'checked' : '' ?>>
                            <span class="block text-center py-2 px-2 text-xs bg-base/60 border border-br-default rounded-xl text-tx-secondary peer-checked:bg-danger/10 peer-checked:border-danger/30 peer-checked:text-danger font-bold transition-all">Keluar</span>
                        </label>
                    </div>
                </div>

                <!-- Wallet Filter -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Rekening/Dompet</label>
                    <select name="wallet_id" class="w-full px-3 py-2.5 bg-base/60 border border-br-default rounded-xl text-tx-primary outline-none text-xs cursor-pointer">
                        <option value="" class="bg-surface">Semua Rekening</option>
                        <?php foreach ($wallets as $w) : ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface" <?= $filterWallet == $w['id'] ? 'selected' : '' ?>><?= (string) esc($w['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="<?= (string) esc($filterStartDate) ?>" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-xl text-tx-primary outline-none text-xs">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="<?= (string) esc($filterEndDate) ?>" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-xl text-tx-primary outline-none text-xs">
                    </div>
                </div>

                <!-- Action buttons inside drawer -->
                <div class="flex gap-2 pt-2">
                    <a href="<?= url_to('transaction') ?>" class="w-1/3 py-2.5 bg-elevated hover:bg-surface text-tx-primary border border-br-default font-bold text-xs rounded-xl transition-all text-center flex items-center justify-center gap-1.5">
                        Reset
                    </a>
                    <button type="submit" class="w-2/3 py-2.5 bg-brand hover:bg-brand-hover text-white font-bold text-xs rounded-xl active:scale-95 transition-all shadow-md shadow-brand/10 cursor-pointer">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions List Section -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-tx-primary tracking-tight flex items-center gap-1.5">
            <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Daftar Transaksi (<?= count($transactions) ?>)
        </h2>

        <div class="space-y-2.5">
            <?php if (empty($transactions)) : ?>
                <!-- Empty State -->
                <div class="p-10 text-center space-y-4 bg-surface/20 border border-br-default rounded-2xl">
                    <div class="w-12 h-12 bg-elevated/40 rounded-xl border border-br-subtle flex items-center justify-center mx-auto text-tx-disabled">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-tx-primary">Tidak Ada Transaksi</h4>
                        <p class="text-tx-secondary text-[11px] leading-normal max-w-xs mx-auto">Kami tidak menemukan transaksi untuk pencarian atau filter yang Anda terapkan.</p>
                    </div>
                </div>
            <?php else : ?>
                <?php foreach ($transactions as $tx) : ?>
                    <?php 
                    $badgeClass = 'bg-elevated text-tx-secondary';
                    $amountClass = 'text-tx-primary';
                    $sign = '';
                    $initial = 'T';

                    if ($tx['type'] === 'income') {
                        $badgeClass = 'bg-success/10 text-success';
                        $amountClass = 'text-success';
                        $sign = '+ ';
                        $initial = !empty($tx['category_name']) ? strtoupper(substr((string)$tx['category_name'], 0, 1)) : 'I';
                    } elseif ($tx['type'] === 'expense') {
                        $badgeClass = 'bg-danger/10 text-danger';
                        $amountClass = 'text-danger';
                        $sign = '- ';
                        $initial = !empty($tx['category_name']) ? strtoupper(substr((string)$tx['category_name'], 0, 1)) : 'E';
                    } else {
                        // Transfer
                        $badgeClass = 'bg-brand/10 text-brand';
                        $amountClass = 'text-tx-secondary';
                        $initial = 'TF';
                    }
                    ?>
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-surface/30 border border-br-default active:bg-surface/50 transition-all gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Category Avatar Circle -->
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 <?= $badgeClass ?>">
                                <?= $initial ?>
                            </div>
                            
                            <!-- Description and Metadata -->
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-tx-primary truncate max-w-[160px] leading-tight">
                                    <?= (string) esc($tx['description'] ?: '-') ?>
                                </h4>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="text-[9px] text-tx-secondary font-medium whitespace-nowrap">
                                        <?= date('d M Y', strtotime($tx['transaction_date'])) ?>
                                    </span>
                                    <span class="w-1 h-1 rounded-full bg-br-default shrink-0"></span>
                                    
                                    <?php if ($tx['type'] === 'transfer') : ?>
                                        <span class="text-[8px] font-bold text-tx-secondary bg-base border border-br-default px-1 rounded-md truncate max-w-[100px]">
                                            <?= (string) esc($tx['wallet_name']) ?> ➔ <?= (string) esc($tx['to_wallet_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[8px] font-bold text-brand bg-brand/5 border border-brand/10 px-1 rounded-md truncate max-w-[80px]">
                                            <?= (string) esc($tx['wallet_name']) ?>
                                        </span>
                                        <?php if (!empty($tx['category_name'])) : ?>
                                            <span class="w-1 h-1 rounded-full bg-br-default shrink-0"></span>
                                            <span class="text-[8px] font-semibold text-tx-secondary bg-surface px-1.5 py-0.5 rounded-md truncate max-w-[80px]">
                                                <?= (string) esc($tx['category_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formatted Amount & Action button -->
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="text-right">
                                <span class="text-xs font-extrabold <?= $amountClass ?> block leading-none">
                                    <?= $sign ?>Rp<?= number_format($tx['amount'], 0, ',', '.') ?>
                                </span>
                            </div>
                            
                            <!-- Action Trigger button -->
                            <button onclick="openTxActionSheet(<?= (int)$tx['id'] ?>, '<?= (string)$tx['type'] ?>', '<?= date('Y-m-d', strtotime((string)$tx['transaction_date'])) ?>', <?= (int)$tx['wallet_id'] ?>, <?= (int)$tx['category_id'] ?>, <?= floatval($tx['amount']) ?>, '<?= (string) esc((string)$tx['description'], 'js') ?>')" class="w-7 h-7 flex items-center justify-center text-tx-disabled active:text-tx-primary active:bg-elevated rounded-lg transition-colors cursor-pointer focus:outline-hidden">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- ================= MOBILE SYSTEM SHEETS ================= -->

<!-- Transaction Action Bottom Sheet -->
<div id="txActionSheet" role="dialog" aria-modal="true" aria-label="Aksi Transaksi" class="fixed bottom-0 left-0 right-0 max-h-[80vh] bg-base border-t border-br-default rounded-t-3xl z-50 transform translate-y-full transition-transform duration-350 ease-out pb-8 flex flex-col overflow-hidden shadow-2xl">
    <div class="w-12 h-1.5 bg-elevated rounded-full mx-auto my-3 shrink-0"></div>
    <div class="px-6 pb-2 shrink-0">
        <h3 class="text-sm font-extrabold text-tx-primary">Kelola Transaksi</h3>
        <p class="text-[11px] text-tx-secondary mt-0.5 id-desc">Aksi penyesuaian atau penghapusan data terpilih</p>
    </div>
    <div class="p-6 space-y-3">
        <!-- Edit Action -->
        <button onclick="triggerEditFromAction()" class="w-full flex items-center gap-4 p-4 rounded-2xl bg-surface/40 border border-br-default active:bg-surface/70 active:scale-98 transition-all text-left cursor-pointer focus:outline-hidden">
            <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-tx-primary">Ubah Data Transaksi</span>
                <span class="block text-[10px] text-tx-secondary">Edit nominal, rekening, kategori, atau tanggal</span>
            </div>
        </button>

        <!-- Delete Action -->
        <button onclick="triggerDeleteFromAction()" class="w-full flex items-center gap-4 p-4 rounded-2xl bg-danger/10 border border-danger/20 active:bg-danger/20 active:scale-98 transition-all text-left cursor-pointer focus:outline-hidden">
            <div class="w-10 h-10 rounded-xl bg-danger/20 text-danger flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-danger">Hapus Permanen</span>
                <span class="block text-[10px] text-tx-secondary">Hapus catatan ini secara permanen dari basis data</span>
            </div>
        </button>
    </div>
</div>

<!-- Edit Transaction Bottom Sheet -->
<div id="editTxSheet" role="dialog" aria-modal="true" aria-label="Edit Transaksi" class="fixed bottom-0 left-0 right-0 max-h-[90vh] bg-base border-t border-br-default rounded-t-3xl z-55 transform translate-y-full transition-transform duration-350 ease-out pb-8 flex flex-col overflow-hidden shadow-2xl">
    <div class="w-12 h-1.5 bg-elevated rounded-full mx-auto my-3 shrink-0"></div>
    <div class="px-6 pb-2 shrink-0">
        <h3 class="text-sm font-extrabold text-tx-primary">Ubah Catatan Keuangan</h3>
        <p class="text-[11px] text-tx-secondary mt-0.5">Perbarui rincian informasi transaksi terpilih</p>
    </div>
    
    <div class="grow overflow-y-auto no-scrollbar px-6 py-2">
        <form id="editFormMobile" method="post" class="space-y-4">
            <?= csrf_field() ?>
            
            <input type="hidden" name="category_id" id="edit_category_id" value="">

            <div class="grid grid-cols-2 gap-3">
                <!-- Type -->
                <div class="space-y-1.5">
                    <label for="edit_type" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Tipe</label>
                    <select id="edit_type" name="type" onchange="toggleEditCategoryOptions()" class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs cursor-pointer">
                        <option value="expense" class="bg-surface">Pengeluaran</option>
                        <option value="income" class="bg-surface">Pemasukan</option>
                    </select>
                </div>

                <!-- Transaction Date -->
                <div class="space-y-1.5">
                    <label for="edit_transaction_date" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Tanggal</label>
                    <input type="date" id="edit_transaction_date" name="transaction_date" required class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Wallet Selection -->
                <div class="space-y-1.5">
                    <label for="edit_wallet_id" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Rekening/Dompet</label>
                    <select id="edit_wallet_id" name="wallet_id" required class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs cursor-pointer">
                        <?php foreach ($wallets as $w) : ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface"><?= (string) esc($w['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Amount -->
                <div class="space-y-1.5">
                    <label for="edit_amount" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Jumlah (Nominal)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tx-disabled font-semibold text-xs">
                            Rp
                        </div>
                        <input type="text" id="edit_amount" name="amount" required class="w-full pl-8 pr-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs font-semibold">
                    </div>
                </div>
            </div>

            <!-- Income Category Container -->
            <div id="edit_category_income_container" class="space-y-1.5 hidden">
                <label for="edit_income_category_select" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Kategori Pemasukan</label>
                <select id="edit_income_category_select" onchange="syncEditCategory()" class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs cursor-pointer">
                    <?php foreach ($incomeCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>" class="bg-surface"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Expense Category Container -->
            <div id="edit_category_expense_container" class="space-y-1.5 hidden">
                <label for="edit_expense_category_select" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Kategori Pengeluaran</label>
                <select id="edit_expense_category_select" onchange="syncEditCategory()" class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs cursor-pointer">
                    <?php foreach ($expenseCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>" class="bg-surface"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="edit_description" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider">Keterangan Deskripsi</label>
                <input type="text" id="edit_description" name="description" placeholder="Contoh: Makan malam di warung" class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs">
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3 pt-3 border-t border-br-default/60">
                <button type="button" onclick="closeEditTxSheet()" class="w-1/3 py-3 bg-surface hover:bg-elevated text-tx-primary border border-br-default font-bold rounded-xl text-xs transition-all active:scale-95 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-xs transition-all active:scale-95 shadow-md shadow-brand/10 cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Balance Bottom Sheet -->
<div id="adjustTxSheet" role="dialog" aria-modal="true" aria-label="Sesuaikan Saldo" class="fixed bottom-0 left-0 right-0 max-h-[85vh] bg-base border-t border-br-default rounded-t-3xl z-55 transform translate-y-full transition-transform duration-350 ease-out pb-8 flex flex-col overflow-hidden shadow-2xl">
    <div class="w-12 h-1.5 bg-elevated rounded-full mx-auto my-3 shrink-0"></div>
    <div class="px-6 pb-2 shrink-0">
        <h3 class="text-sm font-extrabold text-tx-primary">Sesuaikan Saldo Dompet</h3>
        <p class="text-[11px] text-tx-secondary mt-0.5">Sistem akan membuat transaksi penyesuaian otomatis di rekening terpilih agar saldo sesuai keinginan Anda.</p>
    </div>

    <div class="p-6 grow overflow-y-auto no-scrollbar">
        <form action="<?= base_url('transaction/adjust-balance') ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Wallet Select for Adjustment -->
            <div class="space-y-1.5">
                <label for="adjust_wallet_id" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Pilih Rekening Terkait</label>
                <select id="adjust_wallet_id" name="wallet_id" required class="w-full px-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs font-semibold cursor-pointer">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= $w['id'] ?>" data-balance="<?= floatval($w['balance']) ?>" class="bg-surface">
                            <?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Target Balance Input -->
            <div class="space-y-1.5">
                <label for="target_balance" class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Target Saldo Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tx-disabled font-semibold text-xs">
                        Rp
                    </div>
                    <input type="text" id="target_balance" name="target_balance" placeholder="0" required class="w-full pl-8 pr-3 py-2.5 bg-surface border border-br-subtle rounded-xl text-tx-primary outline-none text-xs font-semibold">
                </div>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-3 border-t border-br-default/60">
                <button type="button" onclick="closeAdjustModal()" class="w-1/3 py-3 bg-surface hover:bg-elevated text-tx-primary border border-br-default font-bold rounded-xl text-xs transition-all active:scale-95 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-xs transition-all active:scale-95 shadow-md shadow-brand/10 cursor-pointer">
                    Sesuaikan Saldo
                </button>
            </div>
        </form>
    </div>
</div>v>

<!-- Hidden form for transaction deletion -->
<form id="hiddenDeleteForm" method="post" class="hidden">
    <?= csrf_field() ?>
</form>

<!-- JS FOR INTERACTIVITY -->
<script>
    // Variables to store current active transaction context
    let activeTxId = null;
    let activeTxType = '';
    let activeTxDate = '';
    let activeTxWalletId = null;
    let activeTxCategoryId = null;
    let activeTxAmount = 0;
    let activeTxDescription = '';

    // Advanced Filter Drawer Toggle
    function toggleFilterDrawer() {
        const drawer = document.getElementById('advancedFilters');
        if (drawer.classList.contains('hidden')) {
            drawer.classList.remove('hidden');
        } else {
            drawer.classList.add('hidden');
        }
    }

    // Open Action Sheet
    function openTxActionSheet(id, type, date, wallet_id, category_id, amount, description) {
        activeTxId = id;
        activeTxType = type;
        activeTxDate = date;
        activeTxWalletId = wallet_id;
        activeTxCategoryId = category_id;
        activeTxAmount = amount;
        activeTxDescription = description;

        // Update description preview in modal subtitle
        const subtitle = document.querySelector('#txActionSheet .id-desc');
        if (subtitle) {
            subtitle.textContent = `Pencatatan "${description || '-'}" senilai Rp` + Math.abs(amount).toLocaleString('id-ID');
        }

        const sheet = document.getElementById('txActionSheet');
        const backdrop = document.getElementById('sheetBackdrop');

        sheet.classList.remove('translate-y-full');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = 'hidden';
    }

    function closeTxActionSheet() {
        const sheet = document.getElementById('txActionSheet');
        const backdrop = document.getElementById('sheetBackdrop');
        sheet.classList.add('translate-y-full');
        // Do not close backdrop if we're chaining to edit modal
        if (document.getElementById('editTxSheet').classList.contains('translate-y-full') && 
            document.getElementById('adjustTxSheet').classList.contains('translate-y-full')) {
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            document.body.style.overflow = '';
        }
    }

    // Action Trigger: Edit
    function triggerEditFromAction() {
        closeTxActionSheet();
        setTimeout(() => {
            openEditTxSheet();
        }, 150);
    }

    // Action Trigger: Delete
    function triggerDeleteFromAction() {
        closeTxActionSheet();
        if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
            const form = document.getElementById('hiddenDeleteForm');
            form.action = '<?= base_url('transaction/delete/') ?>' + activeTxId;
            form.submit();
        }
    }

    // Open Edit Bottom Sheet
    function openEditTxSheet() {
        document.getElementById('editFormMobile').action = '<?= base_url('transaction/update/') ?>' + activeTxId;
        document.getElementById('edit_type').value = activeTxType;
        document.getElementById('edit_transaction_date').value = activeTxDate;
        document.getElementById('edit_wallet_id').value = activeTxWalletId;
        document.getElementById('edit_description').value = activeTxDescription;
        
        let cleanAmount = Math.abs(activeTxAmount).toString();
        document.getElementById('edit_amount').value = cleanAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        toggleEditCategoryOptions();
        
        if (activeTxType === 'income') {
            document.getElementById('edit_income_category_select').value = activeTxCategoryId;
        } else {
            document.getElementById('edit_expense_category_select').value = activeTxCategoryId;
        }
        syncEditCategory();

        const sheet = document.getElementById('editTxSheet');
        const backdrop = document.getElementById('sheetBackdrop');
        sheet.classList.remove('translate-y-full');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = 'hidden';
    }

    function closeEditTxSheet() {
        const sheet = document.getElementById('editTxSheet');
        const backdrop = document.getElementById('sheetBackdrop');
        sheet.classList.add('translate-y-full');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = '';
    }

    function toggleEditCategoryOptions() {
        const type = document.getElementById('edit_type').value;
        const incomeSelect = document.getElementById('edit_category_income_container');
        const expenseSelect = document.getElementById('edit_category_expense_container');
 
        if (type === 'income') {
            incomeSelect.classList.remove('hidden');
            expenseSelect.classList.add('hidden');
        } else {
            incomeSelect.classList.add('hidden');
            expenseSelect.classList.remove('hidden');
        }
        syncEditCategory();
    }
 
    function syncEditCategory() {
        const type = document.getElementById('edit_type').value;
        const realCategoryInput = document.getElementById('edit_category_id');
 
        if (type === 'income') {
            const select = document.getElementById('edit_income_category_select');
            realCategoryInput.value = select ? select.value : '';
        } else {
            const select = document.getElementById('edit_expense_category_select');
            realCategoryInput.value = select ? select.value : '';
        }
    }

    // Adjust Balance Bottom Sheet
    function openAdjustModal() {
        const sheet = document.getElementById('adjustTxSheet');
        const backdrop = document.getElementById('sheetBackdrop');
        sheet.classList.remove('translate-y-full');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = 'hidden';
        
        updateAdjustTargetBalance();
    }

    function closeAdjustModal() {
        const sheet = document.getElementById('adjustTxSheet');
        const backdrop = document.getElementById('sheetBackdrop');
        sheet.classList.add('translate-y-full');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        document.body.style.overflow = '';
    }

    function updateAdjustTargetBalance() {
        const select = document.getElementById('adjust_wallet_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;
        const currentBalance = parseInt(selectedOption.getAttribute('data-balance') || 0);
        
        const targetInput = document.getElementById('target_balance');
        if (targetInput) {
            let isNeg = currentBalance < 0;
            let cleanVal = Math.abs(currentBalance).toString();
            let formatted = cleanVal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            targetInput.value = (isNeg ? '-' : '') + formatted;
        }
    }

    // Override background click to close sheets
    document.addEventListener('DOMContentLoaded', () => {
        const backdrop = document.getElementById('sheetBackdrop');
        if (backdrop) {
            backdrop.removeAttribute('onclick'); // remove inline to handle chain safely
            backdrop.addEventListener('click', () => {
                closeTxActionSheet();
                closeEditTxSheet();
                closeAdjustModal();
                // Also close layouts base sheets if loaded
                if (typeof closeAllSheets === 'function') {
                    closeAllSheets();
                }
            });
        }

        // Live dot formatting for amount in edit form
        const editAmountInput = document.getElementById('edit_amount');
        if (editAmountInput) {
            editAmountInput.addEventListener('input', (e) => {
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formattedValue;
            });
        }

        // Live dot formatting for adjustment input
        const adjustAmountInput = document.getElementById('target_balance');
        if (adjustAmountInput) {
            adjustAmountInput.addEventListener('input', (e) => {
                // allow negative sign prefix
                let hasNeg = e.target.value.startsWith('-');
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = (hasNeg ? '-' : '') + formattedValue;
            });
        }

        // Bind wallet adjust select change listener
        const adjustSelect = document.getElementById('adjust_wallet_id');
        if (adjustSelect) {
            adjustSelect.addEventListener('change', updateAdjustTargetBalance);
        }
    });
</script>
<?= $this->endSection() ?>
