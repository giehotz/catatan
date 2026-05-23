<?php
/**
 * @var float $totalIncome
 * @var float $totalExpense
 * @var float $totalDebt
 * @var float $totalReceivable
 * @var array $recentTransactions
 * @var array $activeDebts
 * @var array $activeReceivables
 * @var array $smartAlerts
 * @var array $wallets
 */
?>
<?= $this->extend('layouts/base') ?>
 
<?= $this->section('content') ?>
<div class="space-y-8">
    
    <!-- Welcome Banner with quick actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-linear-to-r from-indigo-900 via-indigo-950 to-purple-950 p-6 rounded-2xl border border-indigo-500/20 shadow-xl shadow-indigo-950/20 relative overflow-hidden">
        <!-- Ambient decorative glows -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Ringkasan Keuangan Anda</h1>
            <p class="text-indigo-200/70 dark:text-white text-sm sm:text-base">Pantau semua pemasukan, pengeluaran, utang, dan piutang Anda dalam satu dasbor.</p>
        </div>
        <!-- Quick Actions -->
        <div class="flex flex-wrap gap-3 relative z-10">
            <a href="<?= base_url('transaction') ?>" class="px-4 py-2.5 bg-linear-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-indigo-500/20 flex items-center gap-2 cursor-pointer border border-indigo-400/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Kelola Keuangan
            </a>
        </div>
    </div>
 
    <!-- Smart Alerts for Budgets -->
    <?php if (!empty($smartAlerts)) : ?>
        <div class="space-y-3">
            <?php foreach ($smartAlerts as $alert) : ?>
                <?php 
                    $alertBg = $alert['percent'] > 100 ? 'bg-danger/10 border-danger/20 text-danger' : 'bg-warning/10 border-warning/20 text-warning';
                    $alertBadgeColor = $alert['percent'] > 100 ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning';
                ?>
                <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-200 <?= $alertBg ?>">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 <?= $alertBadgeColor ?>">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-bold text-tx-primary leading-tight">
                                <?= $alert['percent'] > 100 ? 'Anggaran Terlampaui!' : 'Peringatan Anggaran Kritis!' ?>
                            </h4>
                            <p class="text-xs text-tx-secondary">
                                Pengeluaran untuk kategori <strong class="text-tx-primary"><?= (string) esc($alert['name']) ?></strong> telah mencapai <strong class="text-tx-primary"><?= $alert['percent'] ?>%</strong> dari limit bulanan (Rp<?= number_format($alert['spending'], 0, ',', '.') ?> / Rp<?= number_format($alert['limit_amount'], 0, ',', '.') ?>).
                            </p>
                        </div>
                    </div>
                    <a href="<?= base_url('budgets') ?>" class="px-3.5 py-1.5 bg-surface hover:bg-elevated text-tx-primary font-semibold text-xs border border-br-subtle rounded-lg transition-colors inline-block text-center shrink-0">
                        Sesuaikan Anggaran
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
 
    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Income / Pemasukan -->
        <a href="<?= base_url('transaction?action=add_income') ?>" class="group bg-surface p-6 rounded-2xl border border-br-default shadow-lg hover:border-success/30 hover:bg-elevated transition-all duration-300 relative overflow-hidden block">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-success/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-tx-secondary group-hover:text-success transition-colors">Pemasukan Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-success/10 flex items-center justify-center text-success group-hover:bg-success/20 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <span class="text-xs text-tx-disabled font-medium group-hover:text-tx-secondary transition-colors">Total (Klik untuk Catat)</span>
                <h3 class="text-2xl font-bold text-tx-primary tracking-tight">Rp<?= number_format($totalIncome, 0, ',', '.') ?></h3>
            </div>
        </a>
 
        <!-- Total Expense / Pengeluaran -->
        <a href="<?= base_url('transaction?action=add_expense') ?>" class="group bg-surface p-6 rounded-2xl border border-br-default shadow-lg hover:border-danger/30 hover:bg-elevated transition-all duration-300 relative overflow-hidden block">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-danger/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-tx-secondary group-hover:text-danger transition-colors">Pengeluaran Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-danger/10 flex items-center justify-center text-danger group-hover:bg-danger/20 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <span class="text-xs text-tx-disabled font-medium group-hover:text-tx-secondary transition-colors">Total (Klik untuk Catat)</span>
                <h3 class="text-2xl font-bold text-tx-primary tracking-tight">Rp<?= number_format($totalExpense, 0, ',', '.') ?></h3>
            </div>
        </a>
 
        <!-- Debts / Utang -->
        <a href="<?= base_url('debt-receivable?action=add_debt') ?>" class="group bg-surface p-6 rounded-2xl border border-br-default shadow-lg hover:border-warning/30 hover:bg-elevated transition-all duration-300 relative overflow-hidden block">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-warning/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-tx-secondary group-hover:text-warning transition-colors">Total Utang Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-warning/10 flex items-center justify-center text-warning group-hover:bg-warning/20 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <span class="text-xs text-tx-disabled font-medium group-hover:text-tx-secondary transition-colors">Belum Lunas (Klik untuk Catat)</span>
                <h3 class="text-2xl font-bold text-tx-primary tracking-tight">Rp<?= number_format($totalDebt, 0, ',', '.') ?></h3>
            </div>
        </a>
 
        <!-- Receivables / Piutang -->
        <a href="<?= base_url('debt-receivable?action=add_receivable') ?>" class="group bg-surface p-6 rounded-2xl border border-br-default shadow-lg hover:border-info/30 hover:bg-elevated transition-all duration-300 relative overflow-hidden block">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-info/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-semibold text-tx-secondary group-hover:text-info transition-colors">Total Piutang Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center text-info group-hover:bg-info/20 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <span class="text-xs text-tx-disabled font-medium group-hover:text-tx-secondary transition-colors">Belum Ditagih (Klik untuk Catat)</span>
                <h3 class="text-2xl font-bold text-tx-primary tracking-tight">Rp<?= number_format($totalReceivable, 0, ',', '.') ?></h3>
            </div>
        </a>
 
    </div>
 
    <!-- Quick Wallets Overview Widget -->
    <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold text-tx-primary tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Rekening & Dompet Saya
            </h2>
            <a href="<?= base_url('wallets') ?>" class="text-xs text-brand hover:text-brand-hover font-semibold transition-colors">Kelola Dompet</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($wallets as $wallet): ?>
                <?php 
                $walletColor = 'bg-base hover:border-br-default';
                $walletText = 'text-tx-secondary';
                switch ($wallet['type']) {
                    case 'cash':
                        $walletColor = 'bg-success/10 hover:border-success/30';
                        $walletText = 'text-success';
                        break;
                    case 'bank':
                        $walletColor = 'bg-brand/10 hover:border-brand/30';
                        $walletText = 'text-brand';
                        break;
                    case 'e-wallet':
                        $walletColor = 'bg-danger/10 hover:border-danger/30';
                        $walletText = 'text-danger';
                        break;
                    case 'investment':
                        $walletColor = 'bg-info/10 hover:border-info/30';
                        $walletText = 'text-info';
                        break;
                }
                ?>
                <a href="<?= base_url('transaction?wallet_id=' . $wallet['id']) ?>" class="p-4 rounded-xl border border-br-subtle hover:border-br-default transition-all duration-300 flex items-center justify-between text-tx-primary <?= $walletColor ?>">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-tx-primary"><?= (string) esc($wallet['name']) ?></h4>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider <?= $walletText ?>"><?= $wallet['type'] ?></p>
                    </div>
                    <span class="text-base font-extrabold text-tx-primary">
                        Rp<?= number_format($wallet['balance'], 0, ',', '.') ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
 
    <!-- Main Content Section: Chart & Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Transactions Table (takes 2 cols) -->
        <div class="lg:col-span-2 bg-surface border border-br-default rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-tx-primary tracking-tight">Daftar Transaksi Terbaru</h2>
                    <a href="<?= base_url('transaction') ?>" class="text-xs text-brand hover:text-brand-hover font-semibold transition-colors">Lihat Semua</a>
                </div>
                
                <!-- Custom Responsive Table -->
                <div class="overflow-x-auto">
                    <?php if (empty($recentTransactions)) : ?>
                        <div class="p-8 text-center">
                            <p class="text-tx-secondary text-sm italic">Belum ada transaksi terbaru.</p>
                            <a href="<?= base_url('transaction') ?>" class="text-xs text-brand font-semibold underline mt-2 inline-block">Catat Transaksi Sekarang</a>
                        </div>
                    <?php else : ?>
                        <table class="w-full text-left text-sm text-tx-secondary">
                            <thead class="bg-elevated text-tx-secondary uppercase text-xs">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Tanggal</th>
                                    <th class="py-3 px-4">Deskripsi</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4 rounded-r-lg text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-br-default">
                                <?php foreach ($recentTransactions as $tx) : ?>
                                    <tr class="hover:bg-elevated transition-colors">
                                        <td class="py-4 px-4 font-medium text-tx-secondary whitespace-nowrap">
                                            <?= date('d M Y', strtotime($tx['transaction_date'])) ?>
                                        </td>
                                        <td class="py-4 px-4 text-tx-primary font-semibold wrap-break-word max-w-[180px] space-y-1">
                                            <div class="leading-tight"><?= (string) esc($tx['description'] ?: '-') ?></div>
                                            <?php if ($tx['type'] === 'transfer') : ?>
                                                <div class="text-[9px] font-bold text-tx-secondary bg-base border border-br-default px-1.5 py-0.5 rounded-md inline-block">
                                                    <?= (string) esc($tx['wallet_name']) ?> ➔ <?= (string) esc($tx['to_wallet_name']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-[9px] font-extrabold text-brand bg-brand/5 border border-brand/10 px-1.5 py-0.5 rounded-md inline-block">
                                                    <?= (string) esc($tx['wallet_name']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <?php if ($tx['type'] === 'income') : ?>
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-success/10 text-success"><?= (string) esc($tx['category_name']) ?></span>
                                            <?php elseif ($tx['type'] === 'expense') : ?>
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-danger/10 text-danger"><?= (string) esc($tx['category_name']) ?></span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-brand/10 text-brand">Transfer Saldo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold whitespace-nowrap">
                                            <?php if ($tx['type'] === 'income') : ?>
                                                <span class="text-success">+ Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                            <?php elseif ($tx['type'] === 'expense') : ?>
                                                <span class="text-danger">- Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                            <?php else : ?>
                                                <span class="text-tx-secondary">Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
 
        <!-- Right: Debts & Receivables quick overview -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xl space-y-6 flex flex-col">
            <div class="flex justify-between items-center border-b border-br-default pb-3">
                <h2 class="text-lg font-bold text-tx-primary tracking-tight">Kredit Aktif</h2>
                <a href="<?= base_url('debt-receivable') ?>" class="text-xs text-brand hover:text-brand-hover font-semibold transition-colors">Kelola</a>
            </div>
            
            <div class="space-y-4 grow">
                <?php if (empty($activeDebts) && empty($activeReceivables)) : ?>
                    <div class="p-6 text-center h-full flex items-center justify-center">
                        <p class="text-tx-secondary text-sm italic">Tidak ada utang atau piutang aktif.</p>
                    </div>
                <?php else : ?>
                    <!-- Active Debts -->
                    <?php foreach ($activeDebts as $debt) : ?>
                        <div class="p-4 rounded-xl bg-base border border-br-default hover:border-br-subtle transition-all duration-300 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-warning">Utang</span>
                                <span class="text-xs font-semibold text-tx-secondary">
                                    <?= $debt['due_date'] ? 'Tempo: ' . date('d M', strtotime($debt['due_date'])) : 'Tanpa Jatuh Tempo' ?>
                                </span>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-tx-primary truncate max-w-[200px]"><?= (string) esc($debt['creditor_name']) ?></h4>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-bold text-tx-primary">Rp<?= number_format($debt['total_amount'], 0, ',', '.') ?></span>
                                    <span class="text-xs font-semibold uppercase text-tx-secondary">
                                        <?= $debt['status'] === 'partial' ? 'Cicilan' : 'Belum Lunas' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
 
                    <!-- Active Receivables -->
                    <?php foreach ($activeReceivables as $rec) : ?>
                        <div class="p-4 rounded-xl bg-base border border-br-default hover:border-br-subtle transition-all duration-300 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-info">Piutang</span>
                                <span class="text-xs font-semibold text-tx-secondary">
                                    <?= $rec['due_date'] ? 'Tempo: ' . date('d M', strtotime($rec['due_date'])) : 'Tanpa Jatuh Tempo' ?>
                                </span>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-tx-primary truncate max-w-[200px]"><?= (string) esc($rec['borrower_name']) ?></h4>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-bold text-tx-primary">Rp<?= number_format($rec['total_amount'], 0, ',', '.') ?></span>
                                    <span class="text-xs font-semibold uppercase text-tx-secondary">
                                        <?= $rec['status'] === 'partial' ? 'Cicilan' : 'Belum Lunas' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
 
    </div>
</div>
<?= $this->endSection() ?>
