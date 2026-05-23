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
<?= $this->extend('layouts/mobile_base') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    
    <!-- Welcome Header Summary -->
    <div class="space-y-1">
        <h1 class="text-xl font-extrabold text-tx-primary tracking-tight">Dasbor Keuangan</h1>
        <p class="text-xs text-tx-secondary">Ringkasan keuangan pribadi Anda hari ini</p>
    </div>

    <!-- Smart Alerts for Budgets -->
    <?php if (!empty($smartAlerts)) : ?>
        <div class="space-y-2">
            <?php foreach ($smartAlerts as $alert) : ?>
                <?php 
                    $isOver = $alert['percent'] > 100;
                    $alertBg = $isOver ? 'bg-rose-500/10 border-rose-500/20 text-rose-300' : 'bg-amber-500/10 border-amber-500/20 text-amber-300';
                    $alertBadge = $isOver ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400';
                ?>
                <div class="p-3.5 rounded-2xl border flex items-start gap-3 <?= $alertBg ?>">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 <?= $alertBadge ?>">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-1 min-w-0 grow">
                        <h4 class="text-xs font-bold text-tx-primary leading-tight">
                            <?= $isOver ? 'Limit Anggaran Terlampaui!' : 'Peringatan Anggaran Kritis!' ?>
                        </h4>
                        <p class="text-[11px] text-tx-secondary leading-normal">
                            Kategori <strong class="text-tx-primary"><?= (string) esc($alert['name']) ?></strong> mencapai <strong class="text-tx-primary"><?= $alert['percent'] ?>%</strong> (Rp<?= number_format($alert['spending'], 0, ',', '.') ?> / Rp<?= number_format($alert['limit_amount'], 0, ',', '.') ?>).
                        </p>
                        <a href="<?= base_url('budgets') ?>" class="text-[10px] text-indigo-400 font-bold hover:underline inline-block mt-1">Sesuaikan Anggaran ➔</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- 2x2 Compact Stats Grid -->
    <div class="grid grid-cols-2 gap-3.5">
        <!-- Pemasukan -->
        <a href="<?= base_url('transaction?action=add_income') ?>" class="p-4 rounded-2xl bg-surface border border-br-default active:border-success/30 active:scale-95 transition-all relative overflow-hidden block">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-success/5 rounded-full"></div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-semibold text-tx-secondary">Pemasukan</span>
                <div class="w-6 h-6 rounded-md bg-success/10 flex items-center justify-center text-success">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-[10px] text-tx-disabled leading-none">Bulan ini</p>
                <p class="text-sm font-extrabold text-tx-primary mt-1">Rp<?= number_format($totalIncome, 0, ',', '.') ?></p>
            </div>
        </a>

        <!-- Pengeluaran -->
        <a href="<?= base_url('transaction?action=add_expense') ?>" class="p-4 rounded-2xl bg-surface border border-br-default active:border-danger/30 active:scale-95 transition-all relative overflow-hidden block">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-danger/5 rounded-full"></div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-semibold text-tx-secondary">Pengeluaran</span>
                <div class="w-6 h-6 rounded-md bg-danger/10 flex items-center justify-center text-danger">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-[10px] text-tx-disabled leading-none">Bulan ini</p>
                <p class="text-sm font-extrabold text-tx-primary mt-1">Rp<?= number_format($totalExpense, 0, ',', '.') ?></p>
            </div>
        </a>

        <!-- Utang -->
        <a href="<?= base_url('debt-receivable?action=add_debt') ?>" class="p-4 rounded-2xl bg-surface border border-br-default active:border-warning/30 active:scale-95 transition-all relative overflow-hidden block">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-warning/5 rounded-full"></div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-semibold text-tx-secondary">Utang Aktif</span>
                <div class="w-6 h-6 rounded-md bg-warning/10 flex items-center justify-center text-warning">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-[10px] text-tx-disabled leading-none">Belum lunas</p>
                <p class="text-sm font-extrabold text-tx-primary mt-1">Rp<?= number_format($totalDebt, 0, ',', '.') ?></p>
            </div>
        </a>

        <!-- Piutang -->
        <a href="<?= base_url('debt-receivable?action=add_receivable') ?>" class="p-4 rounded-2xl bg-surface border border-br-default active:border-info/30 active:scale-95 transition-all relative overflow-hidden block">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-info/5 rounded-full"></div>
            <div class="flex justify-between items-center">
                <span class="text-[10px] font-semibold text-tx-secondary">Piutang Aktif</span>
                <div class="w-6 h-6 rounded-md bg-info/10 flex items-center justify-center text-info">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-[10px] text-tx-disabled leading-none">Belum ditagih</p>
                <p class="text-sm font-extrabold text-tx-primary mt-1">Rp<?= number_format($totalReceivable, 0, ',', '.') ?></p>
            </div>
        </a>
    </div>

    <!-- Rekening & Dompet Saya: Premium Carousel -->
    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-extrabold text-tx-primary tracking-tight flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Daftar Rekening
            </h2>
            <a href="<?= base_url('wallets') ?>" class="text-[10px] text-indigo-400 font-bold hover:underline">Kelola</a>
        </div>

        <!-- Scroll Snap Container -->
        <div class="relative w-full">
            <div id="walletCarousel" class="flex overflow-x-auto snap-x snap-mandatory gap-3.5 no-scrollbar scroll-smooth">
                <?php foreach ($wallets as $index => $wallet): ?>
                    <?php 
                    $cardColor = 'bg-linear-to-tr from-surface to-base border-br-default';
                    $typeText = 'text-tx-secondary';
                    $glowColor = 'bg-tx-secondary/5';
                    
                    switch ($wallet['type']) {
                        case 'cash':
                            $cardColor = 'bg-linear-to-tr from-success/20 to-base border-success/30';
                            $typeText = 'text-success';
                            $glowColor = 'bg-success/10';
                            break;
                        case 'bank':
                            $cardColor = 'bg-linear-to-tr from-brand/20 to-base border-brand/30';
                            $typeText = 'text-brand';
                            $glowColor = 'bg-brand/10';
                            break;
                        case 'e-wallet':
                            $cardColor = 'bg-linear-to-tr from-danger/20 to-base border-danger/30';
                            $typeText = 'text-danger';
                            $glowColor = 'bg-danger/10';
                            break;
                        case 'investment':
                            $cardColor = 'bg-linear-to-tr from-info/20 to-base border-info/30';
                            $typeText = 'text-info';
                            $glowColor = 'bg-info/10';
                            break;
                    }
                    ?>
                    <a href="<?= base_url('transaction?wallet_id=' . $wallet['id']) ?>" class="snap-center shrink-0 w-full p-5 rounded-2xl border shadow-md relative overflow-hidden flex flex-col justify-between h-32 <?= $cardColor ?>">
                        <!-- Soft decorative glow -->
                        <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl <?= $glowColor ?> pointer-events-none"></div>

                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-extrabold text-tx-primary truncate max-w-[150px]"><?= (string) esc($wallet['name']) ?></h4>
                                <span class="text-[9px] font-bold uppercase tracking-wider <?= $typeText ?>"><?= $wallet['type'] ?></span>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-brand animate-pulse"></span>
                        </div>

                        <div class="mt-4 flex justify-between items-end">
                            <div>
                                <span class="block text-[9px] text-tx-secondary font-medium">Saldo Dompet</span>
                                <span class="text-lg font-extrabold text-tx-primary">Rp<?= number_format($wallet['balance'], 0, ',', '.') ?></span>
                            </div>
                            
                            <!-- Small decorative chip-like overlay -->
                            <div class="w-8 h-6 rounded-md bg-white/5 border border-white/10 flex items-center justify-center shrink-0">
                                <span class="text-[8px] font-bold text-tx-secondary">PAY</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Carousel Pagination Dots -->
            <div class="flex justify-center items-center gap-1.5 mt-3" id="carouselDots">
                <?php foreach ($wallets as $index => $wallet): ?>
                    <button class="w-1.5 h-1.5 rounded-full transition-all duration-300 <?= $index === 0 ? 'bg-brand w-3' : 'bg-br-default' ?>" id="dot-<?= $index ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Card List -->
    <div class="space-y-3.5">
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-extrabold text-tx-primary tracking-tight flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Transaksi Terbaru
            </h2>
            <a href="<?= base_url('transaction') ?>" class="text-[10px] text-brand font-bold hover:underline">Semua</a>
        </div>

        <div class="space-y-2.5">
            <?php if (empty($recentTransactions)) : ?>
                <div class="p-8 text-center bg-surface border border-br-default rounded-2xl">
                    <p class="text-tx-secondary text-xs italic">Belum ada transaksi terbaru.</p>
                    <a href="<?= base_url('transaction') ?>" class="text-[10px] text-brand font-bold underline mt-1.5 inline-block">Catat Transaksi Pertama ➔</a>
                </div>
            <?php else : ?>
                <?php foreach ($recentTransactions as $tx) : ?>
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
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-surface border border-br-default active:scale-99 transition-all">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Category Avatar Circle -->
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 <?= $badgeClass ?>">
                                <?= $initial ?>
                            </div>
                            
                            <!-- Description and Metadata -->
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-tx-primary truncate max-w-[170px] leading-tight">
                                    <?= (string) esc($tx['description'] ?: '-') ?>
                                </h4>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[9px] text-tx-secondary font-medium shrink-0">
                                        <?= date('d M Y', strtotime($tx['transaction_date'])) ?>
                                    </span>
                                    <span class="w-1 h-1 rounded-full bg-br-subtle shrink-0"></span>
                                    
                                    <?php if ($tx['type'] === 'transfer') : ?>
                                        <span class="text-[8px] font-bold text-tx-secondary bg-base border border-br-default px-1 rounded-md truncate max-w-[120px]">
                                            <?= (string) esc($tx['wallet_name']) ?> ➔ <?= (string) esc($tx['to_wallet_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[8px] font-bold text-brand bg-brand/5 border border-brand/10 px-1 rounded-md truncate max-w-[90px]">
                                            <?= (string) esc($tx['wallet_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formatted Amount -->
                        <div class="text-right shrink-0">
                            <span class="text-xs font-extrabold <?= $amountClass ?>">
                                <?= $sign ?>Rp<?= number_format($tx['amount'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Credits (Debts & Receivables) List -->
    <?php if (!empty($activeDebts) || !empty($activeReceivables)) : ?>
        <div class="space-y-3.5">
            <div class="flex justify-between items-center">
                <h2 class="text-sm font-extrabold text-tx-primary tracking-tight flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Kredit Aktif
                </h2>
                <a href="<?= base_url('debt-receivable') ?>" class="text-[10px] text-brand font-bold hover:underline">Kelola</a>
            </div>

            <div class="grid grid-cols-1 gap-2.5">
                <!-- Utang -->
                <?php foreach ($activeDebts as $debt) : ?>
                    <div class="p-3.5 rounded-2xl bg-surface border border-br-default flex justify-between items-center gap-3">
                        <div class="min-w-0">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-warning bg-warning/10 border border-warning/20 px-1.5 py-0.5 rounded-md inline-block">Utang</span>
                            <h4 class="text-xs font-bold text-tx-primary truncate max-w-[150px] mt-1.5 leading-tight"><?= (string) esc($debt['creditor_name']) ?></h4>
                            <p class="text-[9px] text-tx-secondary mt-0.5 leading-none">
                                <?= $debt['due_date'] ? 'Tempo: ' . date('d M Y', strtotime($debt['due_date'])) : 'Tanpa Jatuh Tempo' ?>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-xs font-extrabold text-tx-primary">Rp<?= number_format($debt['total_amount'], 0, ',', '.') ?></span>
                            <span class="text-[9px] font-semibold text-tx-secondary">
                                <?= $debt['status'] === 'partial' ? 'Dicicil' : 'Belum Lunas' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Piutang -->
                <?php foreach ($activeReceivables as $rec) : ?>
                    <div class="p-3.5 rounded-2xl bg-surface border border-br-default flex justify-between items-center gap-3">
                        <div class="min-w-0">
                            <span class="text-[8px] font-bold uppercase tracking-wider text-info bg-info/10 border border-info/20 px-1.5 py-0.5 rounded-md inline-block">Piutang</span>
                            <h4 class="text-xs font-bold text-tx-primary truncate max-w-[150px] mt-1.5 leading-tight"><?= (string) esc($rec['borrower_name']) ?></h4>
                            <p class="text-[9px] text-tx-secondary mt-0.5 leading-none">
                                <?= $rec['due_date'] ? 'Tempo: ' . date('d M Y', strtotime($rec['due_date'])) : 'Tanpa Jatuh Tempo' ?>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-xs font-extrabold text-tx-primary">Rp<?= number_format($rec['total_amount'], 0, ',', '.') ?></span>
                            <span class="text-[9px] font-semibold text-tx-secondary">
                                <?= $rec['status'] === 'partial' ? 'Dicicil' : 'Belum Ditagih' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- CAROUSEL SCROLL DOTS SYNC LOGIC -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('walletCarousel');
        const dots = [];
        
        <?php foreach ($wallets as $index => $wallet): ?>
            dots.push(document.getElementById('dot-<?= $index ?>'));
        <?php endforeach; ?>

        if (carousel && dots.length > 0) {
            carousel.addEventListener('scroll', function() {
                const width = carousel.clientWidth;
                const scrollLeft = carousel.scrollLeft;
                const activeIndex = Math.round(scrollLeft / width);

                dots.forEach((dot, index) => {
                    if (index === activeIndex) {
                        dot.classList.add('bg-brand', 'w-3');
                        dot.classList.remove('bg-br-default');
                    } else {
                        dot.classList.remove('bg-brand', 'w-3');
                        dot.classList.add('bg-br-default');
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>
