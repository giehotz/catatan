<?php
/**
 * @var float $totalKas
 * @var float $piutangAktif
 * @var int $activeMembers
 * @var int $pendingSavings
 * @var int $pendingLoans
 * @var int $pendingInstallments
 * @var string $chartData
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 mt-4">
    
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Ringkasan Pengelola</h1>
            <p class="text-slate-400 text-sm">Monitor likuiditas kas, direktori anggota, verifikasi simpanan, dan pertimbangan pinjaman kredit.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/cooperative/invitations') ?>" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition-colors shadow-lg shadow-indigo-600/10 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Kode Undangan
            </a>
        </div>
    </div>

    <!-- Message Banners -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>

    <!-- Summary Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        
        <!-- Total Kas Koperasi -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400 block mb-1">Kas Likuid Koperasi</span>
            <span class="text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 uppercase tracking-wider">Approved Pokok + Wajib + Sukarela</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 tracking-tight">
                Rp <?= number_format($totalKas, 2, ',', '.') ?>
            </h3>
        </div>

        <!-- Total Piutang Aktif -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400 block mb-1">Piutang Anggota Aktif</span>
            <span class="text-[10px] font-bold rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2 py-0.5 uppercase tracking-wider">Pinjaman - Approved Cicilan</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-indigo-400 mt-3 tracking-tight">
                Rp <?= number_format($piutangAktif, 2, ',', '.') ?>
            </h3>
        </div>

        <!-- Active Members -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-purple-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400 block mb-1">Anggota Koperasi Aktif</span>
            <span class="text-[10px] font-bold rounded-md bg-purple-500/10 text-purple-400 border border-purple-500/20 px-2 py-0.5 uppercase tracking-wider">Terverifikasi Undangan</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-white mt-3 tracking-tight">
                <?= $activeMembers ?> <span class="text-xs text-slate-500 font-medium">Jiwa terdaftar</span>
            </h3>
        </div>

    </div>

    <!-- Quick Workflow Queues Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        
        <!-- Members Directory Link -->
        <a href="<?= base_url('admin/cooperative/members') ?>" class="p-5 bg-slate-900/40 border border-slate-900 hover:border-slate-800 hover:bg-slate-900/60 rounded-2xl flex flex-col justify-between h-36 transition-all group">
            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-400 group-hover:text-indigo-400 group-hover:bg-indigo-500/10 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">Direktori Anggota</h4>
                <p class="text-xs text-slate-500 mt-1">Kelola keaktifan dan suspend.</p>
            </div>
        </a>

        <!-- Savings Queue -->
        <a href="<?= base_url('admin/cooperative/savings') ?>" class="p-5 bg-slate-900/40 border border-slate-900 hover:border-slate-800 hover:bg-slate-900/60 rounded-2xl flex flex-col justify-between h-36 transition-all group relative">
            <?php if ($pendingSavings > 0) : ?>
                <span class="absolute top-4 right-4 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
            <?php endif; ?>
            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-400 group-hover:text-emerald-400 group-hover:bg-emerald-500/10 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white group-hover:text-emerald-300 transition-colors">Verifikasi Simpanan</h4>
                <p class="text-xs text-slate-500 mt-1">
                    <?= $pendingSavings ?> setoran pending
                </p>
            </div>
        </a>

        <!-- Loans Queue -->
        <a href="<?= base_url('admin/cooperative/loans') ?>" class="p-5 bg-slate-900/40 border border-slate-900 hover:border-slate-800 hover:bg-slate-900/60 rounded-2xl flex flex-col justify-between h-36 transition-all group relative">
            <?php if ($pendingLoans > 0) : ?>
                <span class="absolute top-4 right-4 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
            <?php endif; ?>
            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-400 group-hover:text-amber-400 group-hover:bg-amber-500/10 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white group-hover:text-amber-300 transition-colors">Verifikasi Pinjaman</h4>
                <p class="text-xs text-slate-500 mt-1">
                    <?= $pendingLoans ?> pengajuan baru
                </p>
            </div>
        </a>

        <!-- Installments Queue -->
        <a href="<?= base_url('admin/cooperative/installments') ?>" class="p-5 bg-slate-900/40 border border-slate-900 hover:border-slate-800 hover:bg-slate-900/60 rounded-2xl flex flex-col justify-between h-36 transition-all group relative">
            <?php if ($pendingInstallments > 0) : ?>
                <span class="absolute top-4 right-4 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
            <?php endif; ?>
            <div class="w-9 h-9 rounded-xl bg-slate-800 text-slate-400 group-hover:text-indigo-400 group-hover:bg-indigo-500/10 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">Bukti Angsuran</h4>
                <p class="text-xs text-slate-500 mt-1">
                    <?= $pendingInstallments ?> bukti bayar masuk
                </p>
            </div>
        </a>

    </div>

    <!-- Chart Analytics -->
    <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden mt-8">
        <h3 class="text-lg font-bold text-white mb-4">Dinamika Arus Kas Anggota (<?= date('Y') ?>)</h3>
        <div id="coopAnalyticsChart" class="w-full h-80"></div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const rawData = <?= $chartData ?>;
    
    const options = {
        series: [{
            name: 'Simpanan Masuk',
            data: rawData.simpanan
        }, {
            name: 'Penyaluran Pinjaman',
            data: rawData.pinjaman
        }, {
            name: 'Angsuran Diterima',
            data: rawData.angsuran
        }],
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif',
            background: 'transparent'
        },
        colors: ['#10b981', '#f43f5e', '#8b5cf6'], // Emerald, Rose, Purple
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
            labels: { style: { colors: '#94a3b8' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: '#94a3b8' },
                formatter: function (value) {
                    if (value >= 1000000) return "Rp" + (value / 1000000).toFixed(1) + "M";
                    if (value >= 1000) return "Rp" + (value / 1000).toFixed(0) + "K";
                    return "Rp" + value;
                }
            }
        },
        grid: {
            borderColor: '#1e293b',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } },
            xaxis: { lines: { show: false } }
        },
        theme: { mode: 'dark' },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function (val) {
                    return "Rp " + val.toLocaleString('id-ID');
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: { colors: '#cbd5e1' }
        }
    };

    const chart = new ApexCharts(document.querySelector("#coopAnalyticsChart"), options);
    chart.render();
});
</script>
<?= $this->endSection() ?>
