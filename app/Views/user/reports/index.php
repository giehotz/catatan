<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
/**
 * @var string|null $filterStartDate
 * @var string|null $filterEndDate
 * @var string|null $filterSearch
 * @var string|null $filterType
 * @var float $totalIncome
 * @var float $totalExpense
 * @var float $netBalance
 */
?>

<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header / Premium Welcome Banner -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-linear-to-r from-indigo-900 via-indigo-950 to-purple-950 p-6 rounded-2xl border border-indigo-500/20 shadow-xl shadow-indigo-950/20 relative overflow-hidden">
        <!-- Ambient decorative glows -->
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Analisis & Laporan
            </h1>
            <p class="text-indigo-200/70 dark:text-white text-sm sm:text-base">
                Visualisasikan tren keuangan bulanan dan unduh laporan transaksi Anda.
            </p>
        </div>
        
        <!-- Export Shortcuts -->
        <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
            <button type="button" onclick="openExportModal('pdf')" id="btnExportPdfTop" 
               class="grow md:grow-0 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/40 text-rose-400 text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                PDF Laporan
            </button>
            <button type="button" onclick="openExportModal('excel')" 
               class="grow md:grow-0 px-4 py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 hover:border-emerald-500/40 text-emerald-400 text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel (.xlsx)
            </button>
        </div>
    </div>

    <!-- Summary Box Period -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Pemasukan Periode -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 relative overflow-hidden shadow-xs flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 w-20 h-20 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full pointer-events-none blur-xl"></div>
            <div class="space-y-2 relative z-10">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Pemasukan Periode Ini</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 block">
                    Rp <?= number_format($totalIncome, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <!-- Pengeluaran Periode -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 relative overflow-hidden shadow-xs flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 w-20 h-20 bg-rose-500/5 dark:bg-rose-500/10 rounded-full pointer-events-none blur-xl"></div>
            <div class="space-y-2 relative z-10">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Pengeluaran Periode Ini</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-rose-600 dark:text-rose-400 block">
                    Rp <?= number_format($totalExpense, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <!-- Tabungan Bersih Periode -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 relative overflow-hidden shadow-xs flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 w-20 h-20 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full pointer-events-none blur-xl"></div>
            <div class="space-y-2 relative z-10">
                <span class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Tabungan Bersih Periode Ini</span>
                <span class="text-2xl sm:text-3xl font-extrabold <?= $netBalance >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' ?> block">
                    Rp <?= number_format($netBalance, 0, ',', '.') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Detailed Top Charts -->
    <div class="space-y-6 animate-fade-in mb-8">
        <!-- Top Row: Income and Expense Summaries -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Total Pemasukan -->
            <div class="bg-surface border border-br-default rounded-2xl p-6 flex justify-between items-center shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-full flex items-center justify-center border border-emerald-500/20">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-tx-primary mb-0.5">Total Pemasukan</h3>
                        <p class="text-xs text-tx-secondary"><?= $incomeCount ?> Transaksi</p>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-emerald-400">
                    Rp <?= number_format($totalIncome, 0, ',', '.') ?>
                </div>
            </div>

            <!-- Total Pengeluaran -->
            <div class="bg-surface border border-br-default rounded-2xl p-6 flex justify-between items-center shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-500/10 rounded-full flex items-center justify-center border border-rose-500/20">
                        <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-tx-primary mb-0.5">Total Pengeluaran</h3>
                        <p class="text-xs text-tx-secondary"><?= $expenseCount ?> Transaksi</p>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-rose-400">
                    Rp <?= number_format($totalExpense, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Donut Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pemasukan Terbesar -->
            <?php if ($incomeCount > 0): ?>
            <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-lg flex flex-col">
                <h3 class="text-[10px] sm:text-xs font-bold text-tx-secondary uppercase tracking-widest mb-6">Pemasukan Terbesar</h3>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="w-full sm:w-[45%] h-48 flex items-center justify-center relative">
                        <div id="topIncomeDonutChart" class="w-full h-full flex items-center justify-center"></div>
                    </div>
                    <div id="topIncomeLegend" class="w-full sm:w-[55%] space-y-3"></div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-lg flex flex-col items-center justify-center min-h-[250px]">
                <h3 class="text-[10px] sm:text-xs font-bold text-tx-secondary uppercase tracking-widest mb-2">Pemasukan Terbesar</h3>
                <p class="text-sm text-tx-secondary">Belum ada transaksi pada periode ini.</p>
            </div>
            <?php endif; ?>

            <!-- Pengeluaran Terbesar -->
            <?php if ($expenseCount > 0): ?>
            <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-lg flex flex-col">
                <h3 class="text-[10px] sm:text-xs font-bold text-tx-secondary uppercase tracking-widest mb-6">Pengeluaran Terbesar</h3>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="w-full sm:w-[45%] h-48 flex items-center justify-center relative">
                        <div id="topExpenseDonutChart" class="w-full h-full flex items-center justify-center"></div>
                    </div>
                    <div id="topExpenseLegend" class="w-full sm:w-[55%] space-y-3"></div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-lg flex flex-col items-center justify-center min-h-[250px]">
                <h3 class="text-[10px] sm:text-xs font-bold text-tx-secondary uppercase tracking-widest mb-2">Pengeluaran Terbesar</h3>
                <p class="text-sm text-tx-secondary">Belum ada transaksi pada periode ini.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xs">
        <h2 class="text-sm font-bold text-tx-primary uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter Rentang Data Laporan
        </h2>
        <form method="get" action="<?= url_to('reports') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Search -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Cari Keterangan</label>
                <input type="text" name="search" value="<?= (string) esc($filterSearch) ?>" placeholder="Contoh: Belanja" class="w-full px-4 py-2.5 bg-base border border-br-default rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-muted/65 transition-all outline-none text-sm">
            </div>
            <!-- Start Date -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= (string) esc($filterStartDate) ?>" class="w-full px-4 py-2.5 bg-base border border-br-default rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm">
            </div>
            <!-- End Date -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal Akhir</label>
                <input type="date" name="end_date" value="<?= (string) esc($filterEndDate) ?>" class="w-full px-4 py-2.5 bg-base border border-br-default rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary transition-all outline-none text-sm">
            </div>
            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="grow px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                    Terapkan
                </button>
                <a href="<?= url_to('reports') ?>" class="px-4 py-2.5 bg-base hover:bg-surface text-tx-primary border border-br-default font-bold text-sm rounded-xl transition-all flex items-center justify-center cursor-pointer" title="Reset Filter">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 15.89M9 11l3-3 3 3m-3-3v12" />
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Charts Grid Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Monthly cash flow trend (takes 2 cols) -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xs lg:col-span-2 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Tren Keuangan Bulanan (Tahun <?= date('Y') ?>)
                </h3>
            </div>
            
            <!-- Trend Chart Container -->
            <div class="w-full min-h-[300px] flex items-center justify-center bg-base border border-br-subtle rounded-xl p-4 overflow-hidden">
                <div id="trendChart" class="w-full"></div>
            </div>
        </div>

        <!-- Right: Category distribution (takes 1 col) -->
        <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xs lg:col-span-1 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    Alokasi Kategori
                </h3>
                
                <!-- Category type selector toggle -->
                <div class="flex bg-base border border-br-default p-0.5 rounded-lg text-[10px] font-bold">
                    <button id="btnCatExpense" onclick="switchCategoryChart('expense')" class="px-2 py-1 bg-indigo-600 text-white rounded-md transition-all cursor-pointer">
                        Keluar
                    </button>
                    <button id="btnCatIncome" onclick="switchCategoryChart('income')" class="px-2 py-1 text-tx-secondary hover:text-tx-primary rounded-md transition-all cursor-pointer">
                        Masuk
                    </button>
                </div>
            </div>

            <!-- Distribution Chart Container -->
            <div class="w-full min-h-[300px] flex items-center justify-center bg-base border border-br-subtle rounded-xl p-4 overflow-hidden">
                <div id="categoryChart" class="w-full"></div>
            </div>
        </div>

    </div>

    <!-- Premium Export Multi-format Section -->
    <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Pusat Ekspor Laporan Keuangan Lengkap
                </h3>
                <p class="text-xs text-tx-secondary opacity-80">
                    Ekspor seluruh data transaksi yang telah Anda filter ke dalam berkas laporan pilihan Anda.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="openExportModal('pdf')" id="btnExportPdfBottom" 
                   class="grow sm:grow-0 px-5 py-3 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/40 text-rose-600 dark:text-rose-400 text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Unduh PDF Premium
                </button>
                <button type="button" onclick="openExportModal('excel')" 
                   class="grow sm:grow-0 px-5 py-3 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 hover:border-emerald-500/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Unduh Excel (.xlsx)
                </button>
                <button type="button" onclick="openExportModal('csv')" 
                   class="grow sm:grow-0 px-5 py-3 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 hover:border-blue-500/40 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Unduh berkas CSV
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Load ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (session()->getFlashdata('error')): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('theme-dark');
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes((string) session()->getFlashdata('error')) ?>',
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#0f172a'
        });
    });
</script>
<?php endif; ?>

<script>
    // Detailed Top Charts Logic
    const topIncomeData = <?= $topIncomeJSON ?>;
    const topExpenseData = <?= $topExpenseJSON ?>;
    const countIncome = <?= $incomeCount ?>;
    const countExpense = <?= $expenseCount ?>;

    const topIncomeColors = ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#06B6D4', '#64748B'];
    const topExpenseColors = ['#EF4444', '#F97316', '#EAB308', '#A855F7', '#EC4899', '#64748B'];

    function formatRupiah(number) {
        return "Rp " + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function renderCustomLegend(containerId, labels, series, colors) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        let html = '';
        labels.forEach((label, index) => {
            const color = colors[index % colors.length];
            const value = formatRupiah(series[index]);
            html += `
                <div class="flex items-center justify-between text-xs sm:text-sm">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: ${color}"></span>
                        <span class="text-tx-secondary truncate max-w-[120px] sm:max-w-[150px]" title="${label}">${label}</span>
                    </div>
                    <span class="font-bold text-tx-primary shrink-0">${value}</span>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (countIncome > 0) {
            new ApexCharts(document.querySelector("#topIncomeDonutChart"), {
                series: topIncomeData.series,
                labels: topIncomeData.labels,
                colors: topIncomeColors,
                chart: { type: 'donut', height: 200, background: 'transparent', parentHeightOffset: 0 },
                plotOptions: { pie: { donut: { size: '65%' } } },
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: { show: false },
                tooltip: { theme: 'dark', y: { formatter: val => formatRupiah(val) } }
            }).render();
            renderCustomLegend('topIncomeLegend', topIncomeData.labels, topIncomeData.series, topIncomeColors);
        }

        if (countExpense > 0) {
            new ApexCharts(document.querySelector("#topExpenseDonutChart"), {
                series: topExpenseData.series,
                labels: topExpenseData.labels,
                colors: topExpenseColors,
                chart: { type: 'donut', height: 200, background: 'transparent', parentHeightOffset: 0 },
                plotOptions: { pie: { donut: { size: '65%' } } },
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: { show: false },
                tooltip: { theme: 'dark', y: { formatter: val => formatRupiah(val) } }
            }).render();
            renderCustomLegend('topExpenseLegend', topExpenseData.labels, topExpenseData.series, topExpenseColors);
        }
    });

    // Global data variables for charts
    let chartData = null;
    let categoryChartInstance = null;
    let trendChartInstance = null;

    document.addEventListener("DOMContentLoaded", function() {
        // Fetch chart data from our Service API endpoint
        fetch("<?= base_url('reports/chart-data') ?>")
            .then(res => res.json())
            .then(data => {
                chartData = data;
                initCharts();
                
                // Observe class changes on <html> to dynamically update theme of ApexCharts
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'class') {
                            const isDark = document.documentElement.classList.contains('theme-dark');
                            updateChartsTheme(isDark ? 'dark' : 'light');
                        }
                    });
                });
                observer.observe(document.documentElement, { attributes: true });
            })
            .catch(err => {
                console.error("Gagal memuat data grafik:", err);
            });
    });

    function initCharts() {
        if (!chartData) return;

        const isDark = document.documentElement.classList.contains('theme-dark');
        const themeMode = isDark ? 'dark' : 'light';
        const textColor = isDark ? '#94A3B8' : '#475569';
        const borderColor = isDark ? '#1e293b' : '#e2e8f0';
        const legendColor = isDark ? '#E2E8F0' : '#1e293b';

        // 1. Initializing Trend Chart (Monthly Cashflow)
        const trendOptions = {
            chart: {
                height: 320,
                type: 'area',
                toolbar: { show: false },
                background: 'transparent',
                theme: themeMode
            },
            theme: {
                mode: themeMode
            },
            colors: ['#10B981', '#EF4444'], // Emerald green, Rose red
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            series: [
                {
                    name: 'Pemasukan',
                    data: chartData.monthlyTrend.income
                },
                {
                    name: 'Pengeluaran',
                    data: chartData.monthlyTrend.expense
                }
            ],
            xaxis: {
                categories: chartData.monthlyTrend.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: textColor,
                        fontSize: '11px',
                        fontFamily: 'Outfit, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor,
                        fontSize: '11px',
                        fontFamily: 'Outfit, sans-serif'
                    },
                    formatter: function(val) {
                        return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: true } }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontFamily: 'Outfit, sans-serif',
                fontSize: '12px',
                labels: { colors: legendColor },
                itemMargin: { horizontal: 10 }
            },
            tooltip: {
                theme: themeMode,
                y: {
                    formatter: function(val) {
                        return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        };

        trendChartInstance = new ApexCharts(document.querySelector("#trendChart"), trendOptions);
        trendChartInstance.render();

        // 2. Initializing Category Distribution Chart (Default: Expense)
        renderCategoryChart('expense');
    }

    function renderCategoryChart(type) {
        const catData = type === 'income' ? chartData.incomeCategory : chartData.expenseCategory;
        const colorPalette = type === 'income' 
            ? ['#10B981', '#34D399', '#059669', '#6EE7B7', '#047857'] 
            : ['#EF4444', '#F87171', '#DC2626', '#FCA5A5', '#B91C1C'];

        const isDark = document.documentElement.classList.contains('theme-dark');
        const themeMode = isDark ? 'dark' : 'light';
        const textColor = isDark ? '#64748B' : '#475569';
        const valueColor = isDark ? '#E2E8F0' : '#0f172a';
        const legendColor = isDark ? '#E2E8F0' : '#1e293b';

        const catOptions = {
            chart: {
                height: 320,
                type: 'donut',
                background: 'transparent',
                theme: themeMode
            },
            theme: {
                mode: themeMode
            },
            colors: colorPalette,
            stroke: {
                show: true,
                width: 2,
                colors: [isDark ? '#0f172a' : '#ffffff']
            },
            series: catData.series,
            labels: catData.labels,
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '12px',
                                fontFamily: 'Outfit, sans-serif',
                                color: textColor,
                                offsetY: -4
                            },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontFamily: 'Outfit, sans-serif',
                                color: valueColor,
                                fontWeight: 'bold',
                                offsetY: 8,
                                formatter: function(val) {
                                    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            },
                            total: {
                                show: true,
                                label: type === 'income' ? 'Total Masuk' : 'Total Keluar',
                                color: textColor,
                                fontSize: '11px',
                                fontWeight: 'bold',
                                fontFamily: 'Outfit, sans-serif',
                                formatter: function(w) {
                                    const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return "Rp " + sum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontFamily: 'Outfit, sans-serif',
                fontSize: '11px',
                labels: { colors: legendColor },
                itemMargin: { horizontal: 5, vertical: 2 }
            },
            tooltip: {
                theme: themeMode,
                y: {
                    formatter: function(val) {
                        return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        };

        if (categoryChartInstance) {
            categoryChartInstance.destroy();
        }

        categoryChartInstance = new ApexCharts(document.querySelector("#categoryChart"), catOptions);
        categoryChartInstance.render();
    }

    function switchCategoryChart(type) {
        // Toggle buttons active state styling
        const btnExpense = document.getElementById("btnCatExpense");
        const btnIncome = document.getElementById("btnCatIncome");

        if (type === 'expense') {
            btnExpense.className = "px-2 py-1 bg-indigo-600 text-white rounded-md transition-all cursor-pointer";
            btnIncome.className = "px-2 py-1 text-tx-secondary hover:text-tx-primary rounded-md transition-all cursor-pointer";
        } else {
            btnIncome.className = "px-2 py-1 bg-indigo-600 text-white rounded-md transition-all cursor-pointer";
            btnExpense.className = "px-2 py-1 text-tx-secondary hover:text-tx-primary rounded-md transition-all cursor-pointer";
        }

        renderCategoryChart(type);
    }

    function updateChartsTheme(themeMode) {
        if (!chartData) return;
        
        const isDark = themeMode === 'dark';
        const textColor = isDark ? '#94A3B8' : '#475569';
        const borderColor = isDark ? '#1e293b' : '#e2e8f0';
        const legendColor = isDark ? '#E2E8F0' : '#1e293b';
        
        if (trendChartInstance) {
            trendChartInstance.updateOptions({
                chart: {
                    theme: { mode: themeMode }
                },
                theme: {
                    mode: themeMode
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: textColor
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: textColor
                        }
                    }
                },
                grid: {
                    borderColor: borderColor
                },
                legend: {
                    labels: {
                        colors: legendColor
                    }
                },
                tooltip: {
                    theme: themeMode
                }
            });
        }
        
        if (categoryChartInstance) {
            categoryChartInstance.updateOptions({
                chart: {
                    theme: { mode: themeMode }
                },
                theme: {
                    mode: themeMode
                },
                stroke: {
                    colors: [isDark ? '#0f172a' : '#ffffff']
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: {
                                    color: isDark ? '#64748B' : '#475569'
                                },
                                value: {
                                    color: isDark ? '#E2E8F0' : '#0f172a'
                                },
                                total: {
                                    color: isDark ? '#64748B' : '#475569'
                                }
                            }
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: legendColor
                    }
                },
                tooltip: {
                    theme: themeMode
                }
            });
        }
    }

    function openExportModal(format) {
        const isDark = document.documentElement.classList.contains('theme-dark');
        const swalBg = isDark ? '#0f172a' : '#ffffff';
        const swalColor = isDark ? '#f1f5f9' : '#0f172a';
        const isDarkTextClass = isDark ? 'text-slate-300' : 'text-slate-700';
        const isDarkInputClass = isDark ? 'bg-slate-800 border-slate-700 text-slate-200' : 'bg-white border-slate-300 text-slate-800';
        
        // Get current month as default (YYYY-MM)
        const d = new Date();
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const currentMonth = `${year}-${month}`;

        Swal.fire({
            title: 'Pilih Rentang Laporan',
            html: `
                <div class="space-y-4 text-left px-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold ${isDarkTextClass} uppercase tracking-wider block">Bulan Mulai</label>
                        <input type="month" id="swal-start-month" class="w-full px-4 py-2 border rounded-xl outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors ${isDarkInputClass}" value="${currentMonth}">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold ${isDarkTextClass} uppercase tracking-wider block">Bulan Sampai</label>
                        <input type="month" id="swal-end-month" class="w-full px-4 py-2 border rounded-xl outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors ${isDarkInputClass}" value="${currentMonth}">
                    </div>
                </div>
            `,
            background: swalBg,
            color: swalColor,
            showCancelButton: true,
            confirmButtonText: 'Unduh',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4F46E5',
            preConfirm: () => {
                const start = document.getElementById('swal-start-month').value;
                const end = document.getElementById('swal-end-month').value;
                
                if (!start || !end) {
                    Swal.showValidationMessage('Bulan mulai dan sampai wajib diisi.');
                    return false;
                }
                
                if (start > end) {
                    Swal.showValidationMessage('Bulan Sampai tidak boleh lebih awal dari Bulan Mulai.');
                    return false;
                }
                
                // Max 12 months calculation
                const startDate = new Date(start + '-01');
                const endDate = new Date(end + '-01');
                
                const diffMonths = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth()) + 1;
                
                if (diffMonths > 12) {
                    Swal.showValidationMessage('Rentang maksimal laporan adalah 12 bulan.');
                    return false;
                }
                
                return { startMonth: start, endMonth: end };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { startMonth, endMonth } = result.value;
                if (format === 'pdf') {
                    exportPDFWithCharts(startMonth, endMonth);
                } else {
                    // Redirect for excel/csv
                    const search = document.querySelector('input[name="search"]').value || '';
                    const type = document.querySelector('input[name="type"]')?.value || '<?= esc((string)$filterType) ?>';
                    
                    let url = '<?= base_url('reports/export') ?>?format=' + format + '&start_month=' + startMonth + '&end_month=' + endMonth;
                    if (search) url += '&search=' + encodeURIComponent(search);
                    if (type) url += '&type=' + encodeURIComponent(type);
                    
                    window.location.href = url;
                }
            }
        });
    }

    async function exportPDFWithCharts(startMonth, endMonth) {
        const isDark = document.documentElement.classList.contains('theme-dark');
        const swalBg = isDark ? '#0f172a' : '#ffffff';
        const swalColor = isDark ? '#f1f5f9' : '#0f172a';

        if (!trendChartInstance || !categoryChartInstance) {
            Swal.fire({
                icon: 'warning',
                title: 'Grafik Belum Siap',
                text: 'Harap tunggu hingga grafik selesai dimuat sebelum mengunduh PDF.',
                background: swalBg,
                color: swalColor
            });
            return;
        }

        const btnTop = document.getElementById('btnExportPdfTop');
        const btnBottom = document.getElementById('btnExportPdfBottom');
        const originalTextTop = btnTop.innerHTML;
        const originalTextBottom = btnBottom.innerHTML;

        const setButtonState = (isLoading) => {
            const loadingHtml = `<svg class="animate-spin w-4.5 h-4.5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...`;
            
            btnTop.disabled = isLoading;
            btnBottom.disabled = isLoading;
            
            if (isLoading) {
                btnTop.innerHTML = loadingHtml;
                btnBottom.innerHTML = loadingHtml;
                btnTop.classList.add('opacity-50', 'cursor-not-allowed');
                btnBottom.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btnTop.innerHTML = originalTextTop;
                btnBottom.innerHTML = originalTextBottom;
                btnTop.classList.remove('opacity-50', 'cursor-not-allowed');
                btnBottom.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        };

        try {
            setButtonState(true);

            // 1. Ambil snapshot Base64 (JPEG, quality 70%, max width ~800px)
            const trendURI = await trendChartInstance.dataURI();
            const categoryURI = await categoryChartInstance.dataURI();
            
            // Perlu memastikan base64 string aman
            const trendBase64 = trendURI.imgURI;
            const categoryBase64 = categoryURI.imgURI;

            // 2. Ambil parameter filter text
            const search = document.querySelector('input[name="search"]').value;
            const type = document.querySelector('input[name="type"]')?.value || '<?= esc((string)$filterType) ?>';

            // 3. Buat FormData
            const formData = new FormData();
            formData.append('format', 'pdf');
            formData.append('trend_chart', trendBase64);
            formData.append('category_chart', categoryBase64);
            formData.append('start_month', startMonth);
            formData.append('end_month', endMonth);
            formData.append('search', search);
            if (type) formData.append('type', type);
            // Append CSRF Token (CI4 default field is csrf_test_name)
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            // 4. Kirim Fetch API
            const response = await fetch('<?= base_url('reports/export') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Terjadi kesalahan pada server saat membuat PDF.');
            }

            // 5. Ubah response menjadi Blob & buat file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            
            // Nama file dengan timestamp
            const dateStr = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            a.download = `laporan_keuangan_grafik_${dateStr}.pdf`;
            
            document.body.appendChild(a);
            a.click();
            
            // Cleanup
            window.URL.revokeObjectURL(url);
            a.remove();
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'PDF Berhasil Diunduh!',
                showConfirmButton: false,
                timer: 3000,
                background: swalBg,
                color: swalColor
            });

        } catch (error) {
            console.error('PDF Export Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Ekspor PDF',
                text: error.message || 'Terjadi kesalahan saat mengekspor laporan.',
                background: swalBg,
                color: swalColor
            });
        } finally {
            setButtonState(false);
        }
    }
</script>

<?= $this->endSection() ?>
