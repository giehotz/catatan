<?php
/**
 * @var array $debts
 * @var array $receivables
 * @var float $totalDebt
 * @var float $totalReceivable
 * @var float $netExposure
 * @var string|null $filterSearch
 * @var string|null $filterStatus
 */
?>
<?= $this->extend('layouts/base') ?>
 
<?= $this->section('content') ?>
<div class="space-y-8 animate-fade-in">
    
    <!-- Welcome Header & Add Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-linear-to-r from-indigo-900 via-indigo-950 to-purple-950 p-6 rounded-2xl border border-indigo-500/20 shadow-xl shadow-indigo-950/20 relative overflow-hidden">
        <!-- Ambient decorative glow -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Utang Piutang</h1>
            <p class="text-indigo-200/70 dark:text-white text-sm sm:text-base">Kelola catatan pinjaman uang Anda dengan pihak lain secara terstruktur.</p>
        </div>
        <div class="flex gap-3 relative z-10 w-full sm:w-auto shrink-0">
            <button onclick="openModal('debt')" class="grow sm:grow-0 px-4 py-2.5 bg-linear-to-r from-rose-500 to-rose-600 hover:from-rose-400 hover:to-rose-500 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-md shadow-rose-500/15 flex items-center justify-center gap-2 cursor-pointer border border-rose-400/20">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Utang
            </button>
            <button onclick="openModal('receivable')" class="grow sm:grow-0 px-4 py-2.5 bg-linear-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-md shadow-emerald-500/15 flex items-center justify-center gap-2 cursor-pointer border border-emerald-400/20">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Piutang
            </button>
        </div>
    </div>
 
    <!-- Alert Messages -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-3 animate-fade-in shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>
    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-50/80 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-semibold flex items-center gap-3 animate-fade-in shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>
 
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Saldo Bersih Utang Piutang -->
        <div class="bg-surface p-6 rounded-2xl border border-br-default shadow-xs relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full pointer-events-none"></div>
            <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Net Posisi Utang Piutang</span>
            <h3 class="text-3xl font-extrabold mt-2 tracking-tight <?= $netExposure >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' ?>">
                <?= $netExposure >= 0 ? '+' : '' ?>Rp<?= number_format($netExposure, 0, ',', '.') ?>
            </h3>
        </div>
        <!-- Piutang (Receivables) -->
        <div class="bg-surface p-6 rounded-2xl border border-br-default shadow-xs relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Total Piutang Belum Lunas</span>
            <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2 tracking-tight">
                Rp<?= number_format($totalReceivable, 0, ',', '.') ?>
            </h3>
        </div>
        <!-- Utang (Debts) -->
        <div class="bg-surface p-6 rounded-2xl border border-br-default shadow-xs relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full pointer-events-none"></div>
            <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Total Utang Belum Lunas</span>
            <h3 class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 mt-2 tracking-tight">
                Rp<?= number_format($totalDebt, 0, ',', '.') ?>
            </h3>
        </div>
    </div>
 
    <!-- Filters Panel -->
    <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xs">
        <form method="get" action="<?= base_url('debt-receivable') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <!-- Search -->
            <div class="space-y-1.5 sm:col-span-2">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Cari Kreditur / Debitur</label>
                <input type="text" name="search" value="<?= (string) esc($filterSearch) ?>" placeholder="Contoh: Pak Budi, Andi" class="w-full px-4 py-2.5 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl transition-all outline-none text-sm">
            </div>
            <!-- Status / Buttons -->
            <div class="space-y-1.5 grid grid-cols-3 gap-3">
                <div class="space-y-1.5 col-span-2">
                    <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Status</label>
                    <div class="relative">
                        <select name="status" class="w-full px-4 py-2.5 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm cursor-pointer appearance-none">
                            <option value="">Semua Status</option>
                            <option value="unpaid" <?= $filterStatus === 'unpaid' ? 'selected' : '' ?>>Belum Lunas</option>
                            <option value="partial" <?= $filterStatus === 'partial' ? 'selected' : '' ?>>Cicilan (Sebagian)</option>
                            <option value="paid" <?= $filterStatus === 'paid' ? 'selected' : '' ?>>Lunas</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-tx-secondary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 items-end">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-600/10 cursor-pointer border-0">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
 
    <!-- Dual List Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- COLUMN 1: DEBTS (UTANG) -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 border-b border-br-subtle pb-3">
                <div class="w-2.5 h-6 bg-rose-500 rounded-full"></div>
                <h2 class="text-xl font-bold text-tx-primary">Catatan Utang</h2>
            </div>
 
            <!-- Koperasi Status Placeholder -->
            <?php
            $hasCoopDebt = false;
            $coopDebtStatus = '';
            foreach ($debts as $d) {
                if (stripos($d['creditor_name'], 'Koperasi') !== false) {
                    $hasCoopDebt = true;
                    $coopDebtStatus = $d['status'];
                    break;
                }
            }
            ?>
            
            <?php if ($hasCoopDebt): ?>
                <div class="bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl p-4 flex items-start gap-3 shadow-xs">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg text-indigo-600 dark:text-indigo-400 mt-0.5 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-800 dark:text-indigo-400">Terintegrasi dengan Koperasi</h4>
                        <p class="text-xs text-tx-secondary mt-1">Anda memiliki rekam jejak pinjaman aktif di Koperasi Simpan Pinjam. Status saat ini: 
                            <span class="font-bold text-indigo-900 dark:text-white uppercase"><?= $coopDebtStatus === 'paid' ? 'Lunas' : ($coopDebtStatus === 'partial' ? 'Mencicil' : 'Belum Lunas') ?></span>.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-surface border border-br-default rounded-xl p-4 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-bg-base flex items-center justify-center text-tx-secondary shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-tx-primary">Status Koperasi: Bersih</h4>
                            <p class="text-[10px] text-tx-secondary mt-0.5">Anda tidak memiliki tanggungan utang di Koperasi saat ini.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
 
            <div class="space-y-4">
                <?php if (empty($debts)) : ?>
                    <div class="p-6 text-center bg-surface border border-br-default rounded-2xl shadow-xs">
                        <p class="text-tx-secondary text-sm italic">Belum ada catatan utang.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($debts as $debt) : ?>
                        <div class="bg-surface border border-br-default hover:border-br-default/80 hover:shadow-md rounded-2xl p-5 shadow-xs space-y-4 transition-all duration-300">
                            <div class="flex justify-between items-start gap-4">
                                <div class="space-y-1">
                                    <h3 class="font-bold text-tx-primary text-lg leading-tight wrap-break-word"><?= (string) esc($debt['creditor_name']) ?></h3>
                                    <p class="text-xs text-tx-secondary"><?= (string) esc($debt['description'] ?: 'Tanpa keterangan') ?></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-lg font-extrabold text-rose-600 dark:text-rose-400">
                                        Rp<?= number_format($debt['total_amount'], 0, ',', '.') ?>
                                    </div>
                                    <!-- Due Date warning -->
                                    <?php if ($debt['due_date']) : ?>
                                        <?php 
                                            $isOverdue = strtotime($debt['due_date']) < time() && $debt['status'] !== 'paid';
                                        ?>
                                        <div class="text-xs font-semibold mt-1 <?= $isOverdue ? 'text-rose-600 dark:text-rose-500 animate-pulse' : 'text-tx-secondary' ?>">
                                            Jatuh Tempo: <?= date('d M Y', strtotime($debt['due_date'])) ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="text-xs text-tx-secondary mt-1 opacity-70">Tanpa Jatuh Tempo</div>
                                    <?php endif; ?>
                                </div>
                            </div>
 
                            <div class="flex items-center justify-between gap-3 border-t border-br-subtle pt-3">
                                <!-- Status Selector Form -->
                                <form action="<?= base_url('debt-receivable/update-status/debt/' . $debt['id']) ?>" method="post" class="flex items-center gap-2">
                                    <?= csrf_field() ?>
                                    <label class="text-xs text-tx-secondary font-semibold uppercase">Status:</label>
                                    <div class="relative">
                                        <?php
                                            $debtSelectClass = $debt['status'] === 'paid'
                                                ? 'text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
                                                : ($debt['status'] === 'partial'
                                                    ? 'text-amber-600 dark:text-amber-400 border-amber-500/20'
                                                    : 'text-rose-600 dark:text-rose-400 border-rose-500/20');
                                        ?>
                                        <select name="status" onchange="this.form.submit()" class="appearance-none pl-2.5 pr-7 py-1 bg-bg-base border border-br-default rounded-lg text-xs font-bold transition-all cursor-pointer outline-none <?= $debtSelectClass ?>">
                                            <option value="unpaid" <?= $debt['status'] === 'unpaid' ? 'selected' : '' ?>>Belum Lunas</option>
                                            <option value="partial" <?= $debt['status'] === 'partial' ? 'selected' : '' ?>>Cicilan (Sebagian)</option>
                                            <option value="paid" <?= $debt['status'] === 'paid' ? 'selected' : '' ?>>Lunas</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-tx-secondary">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </form>
 
                                <!-- Delete Action -->
                                <form action="<?= base_url('debt-receivable/delete/debt/' . $debt['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan utang ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/30 rounded-lg transition-all cursor-pointer" title="Hapus Catatan">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
 
        <!-- COLUMN 2: RECEIVABLES (PIUTANG) -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 border-b border-br-subtle pb-3">
                <div class="w-2.5 h-6 bg-emerald-500 rounded-full"></div>
                <h2 class="text-xl font-bold text-tx-primary">Catatan Piutang</h2>
            </div>
 
            <div class="space-y-4">
                <?php if (empty($receivables)) : ?>
                    <div class="p-6 text-center bg-surface border border-br-default rounded-2xl shadow-xs">
                        <p class="text-tx-secondary text-sm italic">Belum ada catatan piutang.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($receivables as $rec) : ?>
                        <div class="bg-surface border border-br-default hover:border-br-default/80 hover:shadow-md rounded-2xl p-5 shadow-xs space-y-4 transition-all duration-300">
                            <div class="flex justify-between items-start gap-4">
                                <div class="space-y-1">
                                    <h3 class="font-bold text-tx-primary text-lg leading-tight wrap-break-word"><?= (string) esc($rec['borrower_name']) ?></h3>
                                    <p class="text-xs text-tx-secondary"><?= (string) esc($rec['description'] ?: 'Tanpa keterangan') ?></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                                        Rp<?= number_format($rec['total_amount'], 0, ',', '.') ?>
                                    </div>
                                    <!-- Due Date warning -->
                                    <?php if ($rec['due_date']) : ?>
                                        <?php 
                                            $isOverdue = strtotime($rec['due_date']) < time() && $rec['status'] !== 'paid';
                                        ?>
                                        <div class="text-xs mt-1 <?= $isOverdue ? 'font-bold text-rose-600 dark:text-rose-500 animate-pulse' : 'font-semibold text-tx-secondary' ?>">
                                            Jatuh Tempo: <?= date('d M Y', strtotime($rec['due_date'])) ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="text-xs text-tx-secondary mt-1 opacity-70">Tanpa Jatuh Tempo</div>
                                    <?php endif; ?>
                                </div>
                            </div>
 
                            <div class="flex items-center justify-between gap-3 border-t border-br-subtle pt-3">
                                <!-- Status Selector Form -->
                                <form action="<?= base_url('debt-receivable/update-status/receivable/' . $rec['id']) ?>" method="post" class="flex items-center gap-2">
                                    <?= csrf_field() ?>
                                    <label class="text-xs text-tx-secondary font-semibold uppercase">Status:</label>
                                    <div class="relative">
                                        <?php
                                            $recSelectClass = $rec['status'] === 'paid'
                                                ? 'text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
                                                : ($rec['status'] === 'partial'
                                                    ? 'text-amber-600 dark:text-amber-400 border-amber-500/20'
                                                    : 'text-rose-600 dark:text-rose-400 border-rose-500/20');
                                        ?>
                                        <select name="status" onchange="this.form.submit()" class="appearance-none pl-2.5 pr-7 py-1 bg-bg-base border border-br-default rounded-lg text-xs font-bold transition-all cursor-pointer outline-none <?= $recSelectClass ?>">
                                            <option value="unpaid" <?= $rec['status'] === 'unpaid' ? 'selected' : '' ?>>Belum Lunas</option>
                                            <option value="partial" <?= $rec['status'] === 'partial' ? 'selected' : '' ?>>Cicilan (Sebagian)</option>
                                            <option value="paid" <?= $rec['status'] === 'paid' ? 'selected' : '' ?>>Lunas</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-tx-secondary">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </form>
 
                                <!-- Delete Action -->
                                <form action="<?= base_url('debt-receivable/delete/receivable/' . $rec['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan piutang ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-2 text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/30 rounded-lg transition-all cursor-pointer" title="Hapus Catatan">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142a2 2 0 01-1.724 1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
 
    </div>
</div>
 
<!-- Modal Tambah Catatan (Utang / Piutang) -->
<div id="addModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-950/40 backdrop-blur-md hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 relative" id="modalContainer">
        <!-- Top accent gradient line -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-linear-to-r from-indigo-500 to-purple-600"></div>

        <!-- Ambient background accents -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-500/5 rounded-full blur-xl pointer-events-none"></div>

        <!-- Close Button -->
        <button type="button" onclick="closeModal()" class="absolute right-4 top-4 text-tx-secondary hover:text-tx-primary p-1 hover:bg-bg-base rounded-lg transition-all cursor-pointer z-20">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
 
        <!-- Header -->
        <div class="px-6 py-5 border-b border-br-subtle relative z-10">
            <h3 id="modal_title" class="text-xl font-bold text-tx-primary">Tambah Catatan</h3>
            <p class="text-tx-secondary text-xs mt-1">Catat transaksi pinjam-meminjam uang.</p>
        </div>
 
        <!-- Body -->
        <form id="addForm" action="" method="post" class="p-6 space-y-4 relative z-10">
            <?= csrf_field() ?>
            
            <!-- Hidden type sync input -->
            <input type="hidden" name="type" id="record_type" value="debt" onchange="updateFormFields()">
 
            <!-- Person Name -->
            <div class="space-y-1.5">
                <label id="name_label" for="person_name" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Nama Orang / Lembaga</label>
                <input type="text" id="person_name" name="creditor_name" required placeholder="Contoh: Pak Budi, Bank Mandiri" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm transition-all">
            </div>
 
            <div class="grid grid-cols-2 gap-4">
                <!-- Amount -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Nominal Pinjaman</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tx-secondary font-semibold text-xs">
                            Rp
                        </div>
                        <input type="text" id="amount" name="total_amount" placeholder="0" required class="w-full pl-9 pr-3 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm font-semibold transition-all">
                    </div>
                </div>
 
                <!-- Due Date -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="due_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tgl Jatuh Tempo</label>
                    <input type="date" id="due_date" name="due_date" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm transition-all">
                </div>
            </div>
 
            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Deskripsi (Catatan Tambahan)</label>
                <input type="text" id="description" name="description" placeholder="Contoh: Pinjaman modal usaha, Bunga 0%" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm transition-all">
            </div>
 
            <!-- Submit -->
            <div class="flex gap-3 pt-4 border-t border-br-subtle mt-6">
                <button type="button" onclick="closeModal()" class="w-1/3 py-3 bg-bg-base hover:bg-bg-base/80 border border-br-default text-tx-secondary hover:text-tx-primary font-bold rounded-xl text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-indigo-500/10 cursor-pointer">
                    Simpan Catatan
                </button>
            </div>
        </form>
    </div>
</div>
 
<script>
    // Open Modal with targeted type
    function openModal(type) {
        const modal = document.getElementById('addModal');
        const container = document.getElementById('modalContainer');
        const typeInput = document.getElementById('record_type');
 
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 50);
 
        typeInput.value = type;
        updateFormFields();
    }
 
    // Close Modal
    function closeModal() {
        const modal = document.getElementById('addModal');
        const container = document.getElementById('modalContainer');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
 
    // Dynamically shift form action and names depending on record type
    function updateFormFields() {
        const type = document.getElementById('record_type').value;
        const modalTitle = document.getElementById('modal_title');
        const nameLabel = document.getElementById('name_label');
        const nameInput = document.getElementById('person_name');
        const formAction = document.getElementById('addForm');
 
        if (type === 'debt') {
            modalTitle.textContent = 'Tambah Catatan Utang';
            nameLabel.textContent = 'Nama Kreditur (Pemberi Pinjaman)';
            nameInput.placeholder = 'Contoh: Pak Budi, Bank BRI';
            nameInput.name = 'creditor_name';
            formAction.action = '<?= base_url('debt-receivable/create-debt') ?>';
        } else {
            modalTitle.textContent = 'Tambah Catatan Piutang';
            nameLabel.textContent = 'Nama Debitur (Peminjam Uang)';
            nameInput.placeholder = 'Contoh: Andi, Riko';
            nameInput.name = 'borrower_name';
            formAction.action = '<?= base_url('debt-receivable/create-receivable') ?>';
        }
    }
 
    // Auto-open modal based on URL query parameter action
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        if (action === 'add_debt') {
            openModal('debt');
        } else if (action === 'add_receivable') {
            openModal('receivable');
        }
 
        // Live dot formatting for amount input
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formattedValue;
                
                let newLength = formattedValue.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });
 
            // Strip dots on submit
            const form = amountInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    amountInput.value = amountInput.value.replace(/\D/g, "");
                });
            }
        }
    });
</script>
<?= $this->endSection() ?>
