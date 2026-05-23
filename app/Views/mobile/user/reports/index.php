<?= $this->extend('layouts/mobile_base') ?>

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

<!-- Import external libraries immediately in the section so they are available early -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="space-y-6">
    
    <!-- Top Header -->
    <div class="space-y-1">
        <h1 class="text-xl font-extrabold text-tx-primary tracking-tight">
            Analisis & Laporan
        </h1>
        <p class="text-xs text-tx-secondary mt-0.5">
            Visualisasikan tren keuangan bulanan dan alokasi dana
        </p>
    </div>

    <!-- Summary Box Period -->
    <div class="space-y-3">
        <!-- Tabungan Bersih Periode -->
        <div class="bg-surface border border-br-default rounded-2xl p-4.5 relative overflow-hidden shadow-xs">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full pointer-events-none blur-xl"></div>
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Tabungan Bersih Periode Ini</span>
                <span class="text-lg font-extrabold <?= $netBalance >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' ?> block">
                    Rp <?= number_format($netBalance, 0, ',', '.') ?>
                </span>
            </div>
        </div>

        <!-- Pemasukan & Pengeluaran Grid -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Pemasukan Periode -->
            <div class="bg-surface border border-br-default rounded-2xl p-4 relative overflow-hidden shadow-xs">
                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full pointer-events-none blur-lg"></div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Pemasukan</span>
                    <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 block">
                        Rp <?= number_format($totalIncome, 0, ',', '.') ?>
                    </span>
                </div>
            </div>

            <!-- Pengeluaran Periode -->
            <div class="bg-surface border border-br-default rounded-2xl p-4 relative overflow-hidden shadow-xs">
                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-rose-500/5 dark:bg-rose-500/10 rounded-full pointer-events-none blur-lg"></div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Pengeluaran</span>
                    <span class="text-sm font-extrabold text-rose-600 dark:text-rose-400 block">
                        Rp <?= number_format($totalExpense, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Panel (Touch-friendly & Collapsible) -->
    <div class="bg-surface border border-br-default rounded-2xl p-4 space-y-3 shadow-xs">
        <form method="get" action="<?= url_to('reports') ?>" id="reportFilterForm">
            <div class="flex gap-2">
                <div class="relative grow">
                    <input type="text" name="search" id="filterSearchMobile" value="<?= (string) esc((string) $filterSearch) ?>" placeholder="Cari keterangan..." class="w-full pl-9 pr-4 py-2.5 bg-base border border-br-default rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-muted/65 transition-all outline-none text-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-tx-secondary opacity-70">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                <button type="button" onclick="toggleReportFilterDrawer()" class="px-3.5 py-2.5 bg-base hover:bg-surface border border-br-default rounded-xl text-tx-secondary hover:text-tx-primary active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer relative focus:outline-hidden">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                    </svg>
                    <?php if (!empty($filterStartDate) || !empty($filterEndDate)): ?>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-indigo-500 shadow-xs shadow-indigo-500/50"></span>
                    <?php endif; ?>
                </button>
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl active:scale-95 transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                    Filter
                </button>
            </div>

            <!-- Collapsible Date Drawer -->
            <div id="reportFilterDrawer" class="hidden mt-4 pt-4 border-t border-br-subtle space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[10px] text-tx-secondary uppercase tracking-wider block font-semibold">Mulai Tanggal</label>
                        <input type="date" name="start_date" id="filterStartDateMobile" value="<?= (string) esc((string) $filterStartDate) ?>" class="w-full px-3 py-2 bg-base border border-br-default rounded-xl text-tx-primary focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-tx-secondary uppercase tracking-wider block">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="filterEndDateMobile" value="<?= (string) esc((string) $filterEndDate) ?>" class="w-full px-3 py-2 bg-base border border-br-default rounded-xl text-tx-primary focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs">
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <a href="<?= url_to('reports') ?>" class="w-1/3 py-2.5 bg-base hover:bg-surface text-tx-primary border border-br-default font-bold text-xs rounded-xl transition-all text-center flex items-center justify-center">
                        Reset
                    </a>
                    <button type="submit" class="w-2/3 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl active:scale-95 transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Charts Stacking Area -->
    <div class="space-y-6">
        
        <!-- Cashflow Trend Chart Card -->
        <div class="bg-surface border border-br-default rounded-2xl p-4.5 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Tren Keuangan Bulanan (Tahun <?= date('Y') ?>)
            </h3>
            
            <div class="w-full min-h-[260px] flex items-center justify-center bg-base border border-br-subtle rounded-xl p-2 overflow-hidden">
                <div id="trendChart" class="w-full"></div>
            </div>
        </div>

        <!-- Category Distribution Chart Card -->
        <div class="bg-surface border border-br-default rounded-2xl p-4.5 shadow-xs space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    Alokasi Kategori
                </h3>
                
                <!-- Category Type Toggle -->
                <div class="flex bg-base border border-br-default p-0.5 rounded-lg text-[9px] font-bold">
                    <button id="btnCatExpense" onclick="switchCategoryChart('expense')" class="px-2 py-1 bg-indigo-600 text-white rounded-md transition-all cursor-pointer">
                        Keluar
                    </button>
                    <button id="btnCatIncome" onclick="switchCategoryChart('income')" class="px-2 py-1 text-tx-secondary hover:text-tx-primary rounded-md transition-all cursor-pointer">
                        Masuk
                    </button>
                </div>
            </div>

            <div class="w-full min-h-[260px] flex items-center justify-center bg-base border border-br-subtle rounded-xl p-2 overflow-hidden">
                <div id="categoryChart" class="w-full"></div>
            </div>
        </div>

    </div>

    <!-- Export Center Card -->
    <div class="bg-surface border border-br-default rounded-2xl p-4.5 shadow-xs space-y-4">
        <div class="space-y-1">
            <h3 class="text-xs font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Pusat Unduhan Laporan
            </h3>
            <p class="text-[10px] text-tx-secondary opacity-80">
                Ekspor seluruh data transaksi yang telah Anda filter ke dalam berkas laporan pilihan Anda.
            </p>
        </div>
        
        <div class="grid grid-cols-1 gap-2.5">
            <!-- Premium PDF Export Button -->
            <button type="button" onclick="exportPDFWithCharts()" id="btnExportPdf" 
               class="w-full py-3.5 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/40 text-rose-600 dark:text-rose-400 text-xs font-extrabold rounded-2xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer focus:outline-hidden">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Unduh PDF Premium (Dengan Grafik)
            </button>
            
            <div class="grid grid-cols-2 gap-2.5">
                <!-- Excel -->
                <a href="<?= base_url('reports/export?format=excel' . (!empty($filterStartDate) ? '&start_date=' . $filterStartDate : '') . (!empty($filterEndDate) ? '&end_date=' . $filterEndDate : '') . (!empty($filterSearch) ? '&search=' . $filterSearch : '') . (!empty($filterType) ? '&type=' . $filterType : '')) ?>" 
                   class="py-3 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 hover:border-emerald-500/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer text-center active:scale-98">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Excel (.xlsx)
                </a>
                <!-- CSV -->
                <a href="<?= base_url('reports/export?format=csv' . (!empty($filterStartDate) ? '&start_date=' . $filterStartDate : '') . (!empty($filterEndDate) ? '&end_date=' . $filterEndDate : '') . (!empty($filterSearch) ? '&search=' . $filterSearch : '') . (!empty($filterType) ? '&type=' . $filterType : '')) ?>" 
                   class="py-3 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 hover:border-blue-500/40 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-2xl transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer text-center active:scale-98">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    CSV (.csv)
                </a>
            </div>
        </div>
    </div>

</div>

<!-- JS FOR INTERACTIVITY & CHARTS RENDERING -->
<script>
    // Advanced Filter Drawer Toggle
    function toggleReportFilterDrawer() {
        const drawer = document.getElementById('reportFilterDrawer');
        if (drawer.classList.contains('hidden')) {
            drawer.classList.remove('hidden');
        } else {
            drawer.classList.add('hidden');
        }
    }

    // Global variables for charts
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

        // 1. Initializing Trend Chart (Monthly Cashflow) - Compact 260px Height for mobile viewports
        const trendOptions = {
            chart: {
                height: 260,
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
                width: 2.5
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
                        fontSize: '9px',
                        fontFamily: 'Outfit, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor,
                        fontSize: '9px',
                        fontFamily: 'Outfit, sans-serif'
                    },
                    formatter: function(val) {
                        if (val >= 1000000) {
                            return "Rp " + (val / 1000000).toFixed(1) + "M";
                        } else if (val >= 1000) {
                            return "Rp " + (val / 1000).toFixed(0) + "K";
                        }
                        return "Rp " + val;
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
                fontSize: '10px',
                labels: { colors: legendColor },
                itemMargin: { horizontal: 5 }
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

        // Compact 260px Height with custom padding
        const catOptions = {
            chart: {
                height: 260,
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
                                fontSize: '10px',
                                fontFamily: 'Outfit, sans-serif',
                                color: textColor,
                                offsetY: -4
                            },
                            value: {
                                show: true,
                                fontSize: '14px',
                                fontFamily: 'Outfit, sans-serif',
                                color: valueColor,
                                fontWeight: 'bold',
                                offsetY: 4,
                                formatter: function(val) {
                                    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                            },
                            total: {
                                show: true,
                                label: type === 'income' ? 'Total Masuk' : 'Total Keluar',
                                color: textColor,
                                fontSize: '9px',
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
                fontSize: '10px',
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

    async function exportPDFWithCharts() {
        // Safe check for libraries loaded
        if (typeof Swal === 'undefined') {
            alert('Mengunduh laporan PDF... Harap tunggu sebentar.');
            return;
        }

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

        const btn = document.getElementById('btnExportPdf');
        const originalText = btn.innerHTML;

        const setButtonState = (isLoading) => {
            const loadingHtml = `<svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses PDF...`;
            btn.disabled = isLoading;
            if (isLoading) {
                btn.innerHTML = loadingHtml;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        };

        try {
            setButtonState(true);

            // 1. Ambil snapshot Base64
            const trendURI = await trendChartInstance.dataURI();
            const categoryURI = await categoryChartInstance.dataURI();
            
            const trendBase64 = trendURI.imgURI;
            const categoryBase64 = categoryURI.imgURI;

            // 2. Ambil parameter filter
            const startDate = document.getElementById('filterStartDateMobile').value;
            const endDate = document.getElementById('filterEndDateMobile').value;
            const search = document.getElementById('filterSearchMobile').value;

            // 3. Buat FormData
            const formData = new FormData();
            formData.append('format', 'pdf');
            formData.append('trend_chart', trendBase64);
            formData.append('category_chart', categoryBase64);
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);
            formData.append('search', search);
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
            
            const dateStr = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            a.download = `laporan_keuangan_grafik_mobile_${dateStr}.pdf`;
            
            document.body.appendChild(a);
            a.click();
            
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
