<?php
/**
 * @var array $myShu
 * @var float $totalReceived
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 max-w-6xl mx-auto mt-4">

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Riwayat Penerimaan SHU</h1>
        <p class="text-slate-400 text-sm">Lihat pembagian Sisa Hasil Usaha (SHU) tahunan yang Anda terima dari Koperasi. SHU langsung dimasukkan ke saldo Simpanan Sukarela Anda.</p>
    </div>

    <!-- Stats Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total SHU Diterima -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between h-32">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total SHU Diterima (All Time)</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-400 font-mono">Rp <?= number_format($totalReceived, 2, ',', '.') ?></span>
            </div>
        </div>

        <!-- Card 2: Status Distribusi -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between h-32">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jumlah Pembagian</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-white font-mono"><?= count($myShu) ?> Kali</span>
            </div>
        </div>

        <!-- Card 3: Edukasi Singkat -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between h-32">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alokasi SHU Otomatis</span>
            <span class="text-xs text-slate-400 leading-relaxed font-semibold">Tiap SHU yang dibagikan otomatis dimasukkan ke saldo <strong class="text-indigo-400">Simpanan Sukarela</strong> Anda dan dapat ditarik sewaktu-waktu.</span>
        </div>
    </div>

    <!-- SHU Table Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">Rincian Pembagian SHU</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-900">
                    <tr>
                        <th class="px-6 py-4">Tahun Buku</th>
                        <th class="px-6 py-4 text-right">Jasa Modal (Simpanan)</th>
                        <th class="px-6 py-4 text-right">Jasa Anggota (Pinjaman)</th>
                        <th class="px-6 py-4 text-right text-emerald-400 font-bold">Total SHU Diterima</th>
                        <th class="px-6 py-4">Tanggal Penerimaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    <?php if (count($myShu) === 0) : ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-semibold">Belum ada catatan penerimaan SHU untuk Anda.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($myShu as $shu) : ?>
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 font-bold text-white"><?= (int)$shu['tahun'] ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format($shu['jasa_modal'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format($shu['jasa_anggota'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-emerald-400 font-bold">Rp <?= number_format($shu['total_shu'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-xs font-mono"><?= date('d M Y H:i', strtotime($shu['tanggal_distribusi'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Explanatory Banner about SHU Calculation -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Bagaimana Nilai SHU Anda Dihitung?
        </h3>
        <p class="text-slate-400 text-xs leading-relaxed mb-3">
            Sisa Hasil Usaha (SHU) yang dibagikan kepada Anda didasarkan pada asas keadilan dan asas koperasi berdasarkan kontribusi aktif Anda pada tahun buku tersebut:
        </p>
        <ul class="space-y-2 text-slate-400 text-xs list-disc pl-5">
            <li><strong class="text-indigo-300">Jasa Modal (Simpanan)</strong>: Dihitung secara proporsional berdasarkan rata-rata total saldo simpanan (pokok + wajib + sukarela) Anda berbanding total simpanan koperasi. Semakin rajin menabung, semakin besar jasa modal Anda.</li>
            <li><strong class="text-emerald-300">Jasa Anggota (Pinjaman)</strong>: Dihitung secara proporsional berdasarkan bunga kredit yang Anda sumbangkan ke koperasi dari pinjaman aktif Anda berbanding total pendapatan bunga koperasi. Semakin aktif berpartisipasi dalam modul pinjaman, semakin besar jasa anggota Anda.</li>
        </ul>
    </div>

</div>
<?= $this->endSection() ?>
