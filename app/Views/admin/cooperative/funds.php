<?php
/**
 * @var float $saldoKasUtama
 * @var float $saldoDanaTalangan
 * @var float $totalTarget
 * @var float $totalTerkumpul
 * @var array $riwayatDana
 * @var array $auditTrail
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-6 mt-4">

    <!-- Navigation + Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= base_url('admin/cooperative') ?>" class="hover:text-white transition-colors font-semibold">Dasbor</a>
                <span>/</span>
                <span class="text-emerald-400 font-semibold">Kas & Dana</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Catat Mutasi / Transfer</h1>
        </div>
        <span class="text-[10px] text-slate-600 font-mono font-semibold px-3 py-1 border border-slate-800 rounded-full bg-slate-900/60">Treasury Vault</span>
    </div>

    <!-- Balance Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-2 relative z-10">
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Kas Utama</span>
                <div class="p-1.5 bg-emerald-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
            </div>
            <div class="text-xl font-black text-white tracking-tight relative z-10">Rp <?= number_format($saldoKasUtama, 0, ',', '.') ?></div>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-blue-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-2 relative z-10">
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Dana Talangan</span>
                <div class="p-1.5 bg-blue-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <div class="text-xl font-black text-white tracking-tight relative z-10">Rp <?= number_format($saldoDanaTalangan, 0, ',', '.') ?></div>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-orange-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-2 relative z-10">
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Target Piutang</span>
                <div class="p-1.5 bg-orange-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
            </div>
            <div class="text-xl font-black text-white tracking-tight relative z-10">Rp <?= number_format($totalTarget, 0, ',', '.') ?></div>
        </div>
        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-purple-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-2 relative z-10">
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Terkumpul</span>
                <div class="p-1.5 bg-purple-500/20 rounded-lg">
                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="text-xl font-black text-white tracking-tight relative z-10">Rp <?= number_format($totalTerkumpul, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- Progress Bar -->
    <?php $progressPercent = $totalTarget > 0 ? min(100, ($totalTerkumpul / $totalTarget) * 100) : 0; ?>
    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-4">
        <div class="flex justify-between text-[10px] font-bold mb-2">
            <span class="text-slate-400">Rasio Pengembalian Piutang</span>
            <span class="text-emerald-400"><?= number_format($progressPercent, 1) ?>%</span>
        </div>
        <div class="w-full bg-slate-950 rounded-full h-2">
            <div class="bg-linear-to-r from-emerald-500 to-emerald-300 h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?= $progressPercent ?>%"></div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <!-- Main Grid: Form + Riwayat Mutasi -->
    <div class="grid grid-cols-4 lg:grid-cols-5 gap-6">
        
        <!-- LEFT: Form Catat Mutasi / Transfer -->
        <div class="lg:col-span-8">
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-5 shadow-lg h-full">
                <h3 class="text-base font-bold text-white mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </span>
                    Catat Mutasi / Transfer
                </h3>

                <form action="<?= base_url('admin/cooperative/funds/store') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis Transaksi</label>
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-950 rounded-xl" role="radiogroup">
                            <button type="button" data-value="pemasukan" onclick="selectJenis(this)" class="jenis-btn active-jenis px-3 py-2 text-xs font-bold rounded-lg bg-emerald-500/20 text-emerald-400 transition-all">Masuk</button>
                            <button type="button" data-value="pengeluaran" onclick="selectJenis(this)" class="jenis-btn px-3 py-2 text-xs font-bold rounded-lg text-slate-500 hover:text-white transition-all">Keluar</button>
                            <button type="button" data-value="transfer_internal" onclick="selectJenis(this)" class="jenis-btn px-3 py-2 text-xs font-bold rounded-lg text-slate-500 hover:text-white transition-all">Transfer</button>
                        </div>
                        <input type="hidden" name="jenis_transaksi" id="jenis_transaksi" value="pemasukan">
                    </div>

                    <div id="manual-mode">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kategori Dana</label>
                        <select name="kategori_dana" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white outline-hidden focus:ring-2 focus:ring-emerald-500/50">
                            <option value="kas_utama">Kas Utama</option>
                            <option value="dana_talangan">Dana Talangan</option>
                            <option value="hibah">Dana Hibah / Bantuan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div id="transfer-mode" class="hidden space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dari Kas (Sumber)</label>
                            <select name="kategori_dari" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white outline-hidden focus:ring-2 focus:ring-emerald-500/50">
                                <option value="kas_utama">Kas Utama</option>
                                <option value="dana_talangan">Dana Talangan</option>
                            </select>
                        </div>
                        <div class="flex justify-center">
                            <div class="bg-slate-800 rounded-full p-1.5 border border-slate-700">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Ke Kas (Tujuan)</label>
                            <select name="kategori_ke" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white outline-hidden focus:ring-2 focus:ring-emerald-500/50">
                                <option value="dana_talangan">Dana Talangan</option>
                                <option value="kas_utama">Kas Utama</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 text-sm font-medium">Rp</span>
                            <input type="text" name="nominal" id="nominal_input" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-3.5 py-2.5 text-sm text-white font-mono outline-hidden focus:ring-2 focus:ring-emerald-500/50"
                                placeholder="0">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan</label>
                        <input type="text" name="keterangan" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white outline-hidden focus:ring-2 focus:ring-emerald-500/50"
                            placeholder="Deskripsi transaksi...">
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-xl transition-all shadow-lg shadow-emerald-500/20 text-sm">
                        Simpan Mutasi
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Riwayat Mutasi -->
        <div class="lg:col-span-8">
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg flex flex-col h-full">
                <div class="p-4 border-b border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Riwayat Mutasi
                            <span class="text-[10px] text-slate-600 font-normal">(50 transaksi terakhir)</span>
                        </h3>
                    </div>

                    <form action="<?= base_url('admin/cooperative/funds/pdf') ?>" method="POST" class="flex items-center gap-1.5" target="_blank">
                        <?= csrf_field() ?>
                        <select name="bulan" class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1.5 text-[10px] text-white outline-hidden">
                            <?php $bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; foreach(range(1,12) as $b): ?>
                                <option value="<?= $b ?>" <?= $b==date('n')?'selected':'' ?>><?= $bln[$b-1] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="tahun" class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1.5 text-[10px] text-white outline-hidden">
                            <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" formaction="<?= base_url('admin/cooperative/funds/pdf') ?>" class="p-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-lg transition-colors" title="Export PDF">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        </button>
                        <button type="submit" formaction="<?= base_url('admin/cooperative/funds/excel') ?>" class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg transition-colors" title="Export Excel">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto grow">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-950/40">
                                <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Tanggal</th>
                                <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Kas</th>
                                <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Jenis</th>
                                <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Nominal</th>
                                <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-sm">
                            <?php if (empty($riwayatDana)): ?>
                                <tr><td colspan="5" class="p-8 text-center text-slate-600 text-xs">Belum ada riwayat mutasi kas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($riwayatDana as $row): ?>
                                <tr class="hover:bg-slate-800/20 transition-colors">
                                    <td class="p-3 text-slate-300 whitespace-nowrap text-[11px] font-mono"><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td>
                                    <td class="p-3">
                                        <?php
                                        $kc = match($row['kategori_dana']) {
                                            'kas_utama' => 'bg-emerald-500/15 text-emerald-400',
                                            'dana_talangan' => 'bg-blue-500/15 text-blue-400',
                                            'hibah' => 'bg-amber-500/15 text-amber-400',
                                            default => 'bg-slate-500/15 text-slate-400',
                                        };
                                        ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $kc ?>"><?= str_replace('_', ' ', $row['kategori_dana']) ?></span>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($row['jenis_transaksi'] === 'pemasukan'): ?>
                                            <span class="text-emerald-400 font-bold text-[11px] flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>Masuk</span>
                                        <?php elseif ($row['jenis_transaksi'] === 'pengeluaran'): ?>
                                            <span class="text-rose-400 font-bold text-[11px] flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>Keluar</span>
                                        <?php else: ?>
                                            <span class="text-purple-400 font-bold text-[11px] flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>Mutasi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 font-mono font-bold text-xs <?= $row['jenis_transaksi']==='pengeluaran'?'text-rose-400':'text-emerald-400' ?>">
                                        <?= $row['jenis_transaksi']==='pengeluaran'?'−':'+' ?>Rp<?= number_format($row['nominal'],0,',','.') ?>
                                    </td>
                                    <td class="p-3 text-slate-500 text-[11px] max-w-[200px] truncate" title="<?= esc($row['keterangan']) ?>"><?= esc($row['keterangan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- AUDIT TRAIL -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <div class="p-4 border-b border-slate-800/60 flex items-center justify-between">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Audit Trail
                <span class="text-[10px] text-slate-600 font-normal">(30 aktivitas terakhir)</span>
            </h3>
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 text-[10px] text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Real-time
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-950/40">
                        <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Waktu</th>
                        <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Aktor</th>
                        <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Aksi</th>
                        <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">Detail</th>
                        <th class="p-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-800/60 whitespace-nowrap">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-sm">
                    <?php if (empty($auditTrail)): ?>
                        <tr><td colspan="5" class="p-8 text-center text-slate-600 text-xs">Belum ada aktivitas audit trail.</td></tr>
                    <?php else: ?>
                        <?php foreach ($auditTrail as $log): ?>
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <td class="p-3 text-slate-300 whitespace-nowrap text-[11px] font-mono">
                                <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                            </td>
                            <td class="p-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-slate-800/60 rounded-md text-[11px] font-semibold text-slate-300">
                                    <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <?= esc($log['actor']) ?>
                                </span>
                            </td>
                            <td class="p-3">
                                <?php if ($log['action'] === 'coop_fund_transfer'): ?>
                                    <span class="text-purple-400 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-purple-500/10 rounded">Transfer</span>
                                <?php else: ?>
                                    <span class="text-cyan-400 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-cyan-500/10 rounded">Manual</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-slate-400 text-[11px] max-w-[300px] truncate" title="<?= esc($log['details']) ?>">
                                <?= esc($log['details']) ?>
                            </td>
                            <td class="p-3 text-slate-600 text-[10px] font-mono whitespace-nowrap">
                                <?= esc($log['ip_address'] ?? '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Jenis transaksi selector (tab style)
    function selectJenis(btn) {
        document.querySelectorAll('.jenis-btn').forEach(el => {
            el.classList.remove('active-jenis', 'bg-emerald-500/20', 'text-emerald-400');
            el.classList.add('text-slate-500');
        });
        btn.classList.remove('text-slate-500');
        btn.classList.add('active-jenis', 'bg-emerald-500/20', 'text-emerald-400');

        document.getElementById('jenis_transaksi').value = btn.dataset.value;

        const manual = document.getElementById('manual-mode');
        const transfer = document.getElementById('transfer-mode');
        if (btn.dataset.value === 'transfer_internal') {
            manual.classList.add('hidden');
            transfer.classList.remove('hidden');
        } else {
            manual.classList.remove('hidden');
            transfer.classList.add('hidden');
        }
    }

    // Nominal formatting
    const nominalInput = document.getElementById('nominal_input');
    if (nominalInput) {
        nominalInput.addEventListener('input', function() {
            let v = this.value.replace(/[^0-9]/g, '');
            if (v) v = parseInt(v, 10).toLocaleString('en-US');
            this.value = v;
        });
    }
</script>
<?= $this->endSection() ?>
