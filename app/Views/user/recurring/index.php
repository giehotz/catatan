<?php
/**
 * @var string $title
 * @var array $schedules
 * @var array $incomeCategories
 * @var array $expenseCategories
 */
?>
<?= $this->extend('layouts/base') ?>
 
<?= $this->section('content') ?>
<div class="space-y-8 animate-fade-in">
    
    <!-- Header with Quick Action -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-linear-to-r from-indigo-900 via-indigo-950 to-purple-950 p-6 rounded-2xl border border-indigo-500/20 shadow-xl shadow-indigo-950/20 relative overflow-hidden">
        <!-- Ambient decorative glow -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="space-y-1 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Transaksi Berulang</h1>
            <p class="text-indigo-200/70 dark:text-white text-sm sm:text-base">Kelola pemasukan dan pengeluaran rutin Anda secara otomatis.</p>
        </div>
        <!-- Quick Action to Add Schedule -->
        <button onclick="openRecurringModal()" class="relative z-10 px-4 py-2.5 bg-linear-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-indigo-500/20 flex items-center gap-2 cursor-pointer border border-indigo-400/20">
            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Jadwal Baru
        </button>
    </div>
 
    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 bg-emerald-50/80 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-3 animate-fade-in shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
 
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 bg-rose-50/80 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-xl text-rose-800 dark:text-rose-400 text-sm font-semibold flex items-center gap-3 animate-fade-in shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
 
    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
            $activeCount = 0;
            $pausedCount = 0;
            $monthlyExpenseEstimate = 0.0;
            $monthlyIncomeEstimate = 0.0;
 
            foreach ($schedules as $s) {
                if ($s['is_active'] == 1) {
                    $activeCount++;
                    // Estimate monthly impact
                    $amount = floatval($s['amount']);
                    $multiplier = 1.0;
                    switch ($s['frequency']) {
                        case 'daily':
                            $multiplier = 30.0;
                            break;
                        case 'weekly':
                            $multiplier = 4.3;
                            break;
                        case 'monthly':
                            $multiplier = 1.0;
                            break;
                        case 'yearly':
                            $multiplier = 1.0 / 12.0;
                            break;
                    }
                    if ($s['type'] === 'expense') {
                        $monthlyExpenseEstimate += ($amount * $multiplier);
                    } else {
                        $monthlyIncomeEstimate += ($amount * $multiplier);
                    }
                } else {
                    $pausedCount++;
                }
            }
        ?>
        
        <!-- Total Active -->
        <div class="bg-surface border border-br-default rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Jadwal Aktif</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold text-tx-primary tracking-tight"><?= $activeCount ?></h3>
                <p class="text-tx-secondary text-xs mt-1 opacity-70">Sistem akan memantau & mengeksekusi</p>
            </div>
        </div>
 
        <!-- Total Paused -->
        <div class="bg-surface border border-br-default rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Ditangguhkan</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold text-tx-primary tracking-tight"><?= $pausedCount ?></h3>
                <p class="text-tx-secondary text-xs mt-1 opacity-70">Jadwal diistirahatkan sementara</p>
            </div>
        </div>
 
        <!-- Est. Monthly Income -->
        <div class="bg-surface border border-br-default rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Est. Pemasukan / Bulan</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tracking-tight">+ Rp<?= number_format($monthlyIncomeEstimate, 0, ',', '.') ?></h3>
                <p class="text-tx-secondary text-xs mt-1 opacity-70">Total proyeksi pemasukan berkala</p>
            </div>
        </div>
 
        <!-- Est. Monthly Expense -->
        <div class="bg-surface border border-br-default rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Est. Pengeluaran / Bulan</span>
                <div class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 tracking-tight">- Rp<?= number_format($monthlyExpenseEstimate, 0, ',', '.') ?></h3>
                <p class="text-tx-secondary text-xs mt-1 opacity-70">Total proyeksi pengeluaran rutin</p>
            </div>
        </div>
    </div>
 
    <!-- Schedules List Grid -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-tx-primary tracking-tight">Daftar Jadwal Transaksi Rutin</h2>
        
        <?php if (empty($schedules)) : ?>
            <!-- Empty State -->
            <div class="bg-surface border border-dashed border-br-default rounded-3xl p-12 text-center max-w-2xl mx-auto space-y-6 shadow-xs">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-tx-primary">Belum Ada Jadwal Transaksi Berulang</h3>
                    <p class="text-tx-secondary text-sm leading-relaxed max-w-md mx-auto">
                        Mulai otomatisasi pencatatan keuangan Anda. Tambahkan tagihan langganan (Spotify/Netflix), sewa bulanan, tagihan rutin, atau transfer gaji bulanan Anda di sini.
                    </p>
                </div>
                <button onclick="openRecurringModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-600/10 inline-flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Jadwal Pertama Anda
                </button>
            </div>
        <?php else : ?>
            <!-- Grid List of Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($schedules as $s) : ?>
                    <?php 
                        $isActive = ($s['is_active'] == 1);
                        $bgClass = $isActive ? 'bg-surface border-br-default hover:border-br-default/80 hover:shadow-md' : 'bg-surface/50 dark:bg-surface/30 border-br-subtle opacity-60';
                        $typeColor = ($s['type'] === 'income') ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20';
                        $typeLabel = ($s['type'] === 'income') ? 'Pemasukan' : 'Pengeluaran';
                        
                        $frequencyText = 'Bulanan';
                        switch ($s['frequency']) {
                            case 'daily': $frequencyText = 'Harian'; break;
                            case 'weekly': $frequencyText = 'Mingguan'; break;
                            case 'monthly': $frequencyText = 'Bulanan'; break;
                            case 'yearly': $frequencyText = 'Tahunan'; break;
                        }
                    ?>
                    <div class="border rounded-2xl p-5 shadow-xs flex flex-col justify-between transition-all duration-300 <?= $bgClass ?>">
                        <div class="space-y-4">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start gap-3">
                                <div>
                                    <h3 class="font-bold text-tx-primary leading-tight wrap-break-word">
                                        <?= (string) esc($s['description']) ?>
                                    </h3>
                                    <span class="text-[10px] text-tx-secondary font-bold uppercase tracking-wider block mt-1">
                                        Kategori: <?= (string) esc($s['category_name']) ?>
                                    </span>
                                </div>
                                <div class="shrink-0 flex flex-col items-end gap-1.5">
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-md uppercase tracking-wider <?= $typeColor ?>">
                                        <?= $typeLabel ?>
                                    </span>
                                    <?php if ($isActive) : ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-widest flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php else : ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                                            Ditangguhkan
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
 
                            <!-- Card Info Details -->
                            <div class="py-3 border-y border-br-subtle space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs text-tx-secondary font-semibold">Nominal Transaksi</span>
                                    <span class="text-lg font-extrabold text-tx-primary">Rp<?= number_format($s['amount'], 0, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-tx-secondary font-semibold">Frekuensi</span>
                                    <span class="text-tx-primary font-bold bg-bg-base px-2 py-0.5 rounded-md border border-br-subtle"><?= $frequencyText ?></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-tx-secondary font-semibold">Mulai Tanggal</span>
                                    <span class="text-tx-primary font-bold"><?= date('d M Y', strtotime($s['start_date'])) ?></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-tx-secondary font-semibold">Terakhir Dijalankan</span>
                                    <span class="text-tx-secondary font-medium">
                                        <?= $s['last_run'] ? date('d M Y', strtotime($s['last_run'])) : 'Belum Pernah' ?>
                                    </span>
                                </div>
                            </div>
 
                            <!-- Execution Alerts -->
                            <div class="flex items-center justify-between text-xs bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-500/10 p-2.5 rounded-xl">
                                <span class="text-tx-secondary font-semibold flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Eksekusi Berikutnya:
                                </span>
                                <span class="font-extrabold text-indigo-600 dark:text-indigo-300 flex items-center gap-1">
                                    <?= date('d M Y', strtotime($s['next_run'])) ?>
                                    <?php if ($isActive) : ?>
                                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded-md">
                                            (<?= (string) esc($s['countdown_text']) ?>)
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
 
                        <!-- Action Footer Buttons -->
                        <div class="mt-5 flex gap-2 pt-3 border-t border-br-subtle">
                            <!-- Toggle Active/Pause -->
                            <form action="<?= base_url('recurring/toggle/' . $s['id']) ?>" method="post" class="grow">
                                <?= csrf_field() ?>
                                <?php if ($isActive) : ?>
                                    <button type="submit" class="w-full py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-bold text-xs rounded-xl border border-amber-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer" title="Tangguhkan Jadwal">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tangguhkan
                                    </button>
                                <?php else : ?>
                                    <button type="submit" class="w-full py-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-bold text-xs rounded-xl border border-indigo-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer" title="Aktifkan Kembali">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Aktifkan
                                    </button>
                                <?php endif; ?>
                            </form>
 
                            <!-- Delete Button -->
                            <form action="<?= base_url('recurring/delete/' . $s['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal transaksi berulang ini? (Transaksi yang sudah pernah terbuat tidak akan terhapus)');" class="shrink-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-xl transition-all cursor-pointer" title="Hapus Jadwal">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
 
<!-- MODAL: ADD RECURRING SCHEDULE -->
<div id="recurringModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
    <!-- Backdrop blur shadow -->
    <div onclick="closeRecurringModal()" class="absolute inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity"></div>
 
    <!-- Modal Content -->
    <div class="bg-surface border border-br-default rounded-3xl w-full max-w-md p-6 relative z-10 shadow-2xl transform scale-95 transition-all duration-300 ease-out overflow-hidden" id="modalContainer">
        <!-- Top accent gradient line -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-linear-to-r from-indigo-500 to-purple-600"></div>

        <!-- Ambient background accents -->
        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-rose-500/5 rounded-full blur-xl pointer-events-none"></div>
 
        <!-- Header -->
        <div class="flex justify-between items-center pb-4 border-b border-br-subtle">
            <h3 class="text-lg font-bold text-tx-primary tracking-tight">Tambah Transaksi Berulang</h3>
            <button type="button" onclick="closeRecurringModal()" class="text-tx-secondary hover:text-tx-primary p-1 hover:bg-bg-base rounded-lg transition-all cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
 
        <!-- Form Content -->
        <form action="<?= base_url('recurring/create') ?>" method="post" class="space-y-5 mt-5">
            <?= csrf_field() ?>
 
            <!-- Type Selector -->
            <div class="space-y-1.5">
                <label for="type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe Transaksi</label>
                <div class="relative">
                    <select id="type" name="type" onchange="syncCategoryContainers()" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm cursor-pointer appearance-none">
                        <option value="expense" selected>Pengeluaran Rutin</option>
                        <option value="income">Pemasukan Rutin</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
 
            <!-- Income Category Container -->
            <div id="category_income_container" class="space-y-1.5 hidden">
                <label for="income_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pemasukan</label>
                <div class="relative">
                    <select id="income_category_select" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm cursor-pointer appearance-none">
                        <?php foreach ($incomeCategories as $cat) : ?>
                            <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
 
            <!-- Expense Category Container -->
            <div id="category_expense_container" class="space-y-1.5">
                <label for="expense_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pengeluaran</label>
                <div class="relative">
                    <select id="expense_category_select" name="category_id" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm cursor-pointer appearance-none">
                        <?php foreach ($expenseCategories as $cat) : ?>
                            <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
 
            <!-- Nominal (Amount) -->
            <div class="space-y-1.5">
                <label for="amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Jumlah Nominal (Rupiah)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary text-sm font-bold">
                        Rp
                    </div>
                    <input type="number" id="amount" name="amount" placeholder="0" required min="1" class="w-full pl-10 pr-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm font-semibold transition-all">
                </div>
            </div>
 
            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Deskripsi / Keterangan</label>
                <input type="text" id="description" name="description" placeholder="Contoh: Langganan Netflix, Uang Kosan" required class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm transition-all">
            </div>
 
            <!-- Frequency -->
            <div class="space-y-1.5">
                <label for="frequency" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Frekuensi Perulangan</label>
                <div class="relative">
                    <select id="frequency" name="frequency" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm cursor-pointer appearance-none">
                        <option value="daily">Setiap Hari (Harian)</option>
                        <option value="weekly">Setiap Minggu (Mingguan)</option>
                        <option value="monthly" selected>Setiap Bulan (Bulanan)</option>
                        <option value="yearly">Setiap Tahun (Tahunan)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
 
            <!-- Start Date -->
            <div class="space-y-1.5">
                <label for="start_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal Pertama Dimulai</label>
                <input type="date" id="start_date" name="start_date" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 rounded-xl outline-none text-sm transition-all">
            </div>
 
            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t border-br-subtle mt-6">
                <button type="button" onclick="closeRecurringModal()" class="w-1/3 py-3 bg-bg-base hover:bg-bg-base/80 border border-br-default text-tx-secondary hover:text-tx-primary font-bold text-sm rounded-xl transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-500/10 cursor-pointer">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
 
<script>
    function openRecurringModal() {
        const modal = document.getElementById('recurringModal');
        const container = document.getElementById('modalContainer');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95');
            container.classList.add('scale-100');
        }, 10);
    }
 
    function closeRecurringModal() {
        const modal = document.getElementById('recurringModal');
        const container = document.getElementById('modalContainer');
        container.classList.remove('scale-100');
        container.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 150);
    }
 
    function syncCategoryContainers() {
        const typeSelect = document.getElementById('type');
        const incomeSelect = document.getElementById('income_category_select');
        const expenseSelect = document.getElementById('expense_category_select');
        
        const incomeContainer = document.getElementById('category_income_container');
        const expenseContainer = document.getElementById('category_expense_container');
        
        if (typeSelect.value === 'income') {
            incomeContainer.classList.remove('hidden');
            expenseContainer.classList.add('hidden');
            
            // Assign the name attribute to income select and strip from expense select to prevent form errors
            incomeSelect.setAttribute('name', 'category_id');
            expenseSelect.removeAttribute('name');
        } else {
            incomeContainer.classList.add('hidden');
            expenseContainer.classList.remove('hidden');
            
            // Assign the name attribute to expense select and strip from income select
            expenseSelect.setAttribute('name', 'category_id');
            incomeSelect.removeAttribute('name');
        }
    }
</script>
<?= $this->endSection() ?>
