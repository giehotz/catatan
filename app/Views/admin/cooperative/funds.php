<?php
/**
 * @var float $saldoKasUtama
 * @var float $saldoDanaTalangan
 * @var float $totalTarget
 * @var float $totalTerkumpul
 * @var array $riwayatDana
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 mt-4">
    
    <!-- Navigation Back links -->
    <div class="flex items-center justify-between">
        <a href="<?= base_url('admin/cooperative') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Koperasi
        </a>
        <span class="text-xs text-slate-500 font-semibold font-mono">Treasury Vault</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Kas & Dana Eksternal</h1>
        <p class="text-slate-400 text-sm">Kelola likuiditas koperasi (Kas Utama, Dana Talangan) di luar simpanan anggota, serta pantau target pelunasan piutang.</p>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kas Utama -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Kas Utama</h3>
                <div class="p-2 bg-emerald-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="relative z-10">
                <span class="text-2xl font-black text-white tracking-tight">Rp <?= number_format($saldoKasUtama, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Dana Talangan -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Dana Talangan</h3>
                <div class="p-2 bg-blue-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="relative z-10">
                <span class="text-2xl font-black text-white tracking-tight">Rp <?= number_format($saldoDanaTalangan, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Target Angsuran -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Target Angsuran</h3>
                <div class="p-2 bg-orange-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="relative z-10">
                <span class="text-2xl font-black text-white tracking-tight">Rp <?= number_format($totalTarget, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Terkumpul -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Terkumpul</h3>
                <div class="p-2 bg-purple-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="relative z-10">
                <span class="text-2xl font-black text-white tracking-tight">Rp <?= number_format($totalTerkumpul, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- Progress Bar Analitik -->
    <?php 
    $progressPercent = $totalTarget > 0 ? min(100, ($totalTerkumpul / $totalTarget) * 100) : 0; 
    ?>
    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md relative overflow-hidden">
        <div class="flex justify-between text-xs font-bold mb-3">
            <span class="text-slate-300">Rasio Pengembalian Piutang Koperasi</span>
            <span class="text-emerald-400"><?= number_format($progressPercent, 1) ?>%</span>
        </div>
        <div class="w-full bg-slate-950 rounded-full h-3">
            <div class="bg-linear-to-r from-emerald-500 to-emerald-300 h-3 rounded-full transition-all duration-1000 ease-out" style="width: <?= $progressPercent ?>%"></div>
        </div>
        <p class="text-xs text-slate-500 mt-3">Metrik ini menghitung total pengembalian uang muka dari pinjaman berstatus aktif/lunas. Berfungsi memantau NPL (Non-Performing Loans).</p>
    </div>

    <!-- Main Grid: Forms & History -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Forms -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Form Mutasi / Transfer -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Catat Mutasi / Transfer
                </h3>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm font-medium">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('message')): ?>
                    <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm font-medium">
                        <?= session()->getFlashdata('message') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('admin/cooperative/funds/store') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jenis Transaksi</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden" onchange="toggleFormMode()">
                            <option value="pemasukan">Pemasukan Manual</option>
                            <option value="pengeluaran">Pengeluaran Manual</option>
                            <option value="transfer_internal">Transfer Antar Kas</option>
                        </select>
                    </div>

                    <!-- Mode: Manual (Pemasukan/Pengeluaran) -->
                    <div id="manual-mode" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori Dana</label>
                            <select name="kategori_dana" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden">
                                <option value="kas_utama">Kas Utama</option>
                                <option value="dana_talangan">Dana Talangan</option>
                                <option value="hibah">Dana Hibah / Bantuan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mode: Transfer -->
                    <div id="transfer-mode" class="space-y-4 hidden">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Dari Kas (Sumber)</label>
                            <select name="kategori_dari" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden">
                                <option value="kas_utama">Kas Utama</option>
                                <option value="dana_talangan">Dana Talangan</option>
                            </select>
                        </div>
                        <div class="flex justify-center -my-2 relative z-10">
                            <div class="bg-slate-800 rounded-full p-1 border border-slate-700">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ke Kas (Tujuan)</label>
                            <select name="kategori_ke" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden">
                                <option value="dana_talangan">Dana Talangan</option>
                                <option value="kas_utama">Kas Utama</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nominal (Rp)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-500 font-medium">Rp</span>
                            </div>
                            <input type="text" name="nominal" id="nominal_input" required
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-12 pr-4 py-2.5 text-sm text-white font-mono focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden"
                                placeholder="0">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Keterangan / Deskripsi</label>
                        <input type="text" name="keterangan" required
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-hidden"
                            placeholder="Contoh: Suntikan Dana Talangan / Bantuan Yayasan">
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-emerald-500/20 mt-2">
                        Proses Mutasi Kas
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: History -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl shadow-lg flex flex-col h-full">
                <div class="p-5 border-b border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Riwayat Mutasi & Audit Trail
                        </h3>
                        <p class="text-[10px] text-slate-500 font-medium">Menampilkan 50 transaksi terakhir &mdash; gunakan ekspor untuk melihat semua data di periode tertentu.</p>
                    </div>
                    
                    <form action="<?= base_url('admin/cooperative/funds/pdf') ?>" method="POST" class="flex flex-wrap items-center gap-2" target="_blank">
                        <?= csrf_field() ?>
                        <select name="bulan" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:ring-2 focus:ring-emerald-500 outline-hidden">
                            <?php 
                            $bulanLabel = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            foreach(range(1, 12) as $b): 
                                $selected = ($b == date('n')) ? 'selected' : '';
                            ?>
                                <option value="<?= $b ?>" <?= $selected ?>><?= $bulanLabel[$b-1] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="tahun" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:ring-2 focus:ring-emerald-500 outline-hidden">
                            <?php 
                            $currentYear = date('Y');
                            for($y = $currentYear; $y >= $currentYear - 5; $y--): 
                            ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>

                        <button type="submit" formaction="<?= base_url('admin/cooperative/funds/pdf') ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-lg text-xs font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            PDF
                        </button>
                        <button type="submit" formaction="<?= base_url('admin/cooperative/funds/excel') ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Excel
                        </button>
                    </form>
                </div>
                
                <div class="p-0 overflow-x-auto grow">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/50">
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Tanggal</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Kas</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Transaksi</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Nominal</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-sm">
                            <?php if (empty($riwayatDana)): ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-slate-500">
                                        Belum ada riwayat mutasi kas koperasi.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayatDana as $row): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="p-4 text-slate-300 whitespace-nowrap">
                                            <?= date('d M Y, H:i', strtotime($row['tanggal_transaksi'])) ?>
                                        </td>
                                        <td class="p-4">
                                            <?php
                                            $kasColor = 'bg-slate-500/20 text-slate-400';
                                            if ($row['kategori_dana'] === 'kas_utama') $kasColor = 'bg-emerald-500/20 text-emerald-400';
                                            if ($row['kategori_dana'] === 'dana_talangan') $kasColor = 'bg-blue-500/20 text-blue-400';
                                            ?>
                                            <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider <?= $kasColor ?>">
                                                <?= str_replace('_', ' ', $row['kategori_dana']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <?php if ($row['jenis_transaksi'] === 'pemasukan'): ?>
                                                <span class="text-emerald-400 font-bold text-xs uppercase flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg> Masuk
                                                </span>
                                            <?php elseif ($row['jenis_transaksi'] === 'pengeluaran'): ?>
                                                <span class="text-rose-400 font-bold text-xs uppercase flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg> Keluar
                                                </span>
                                            <?php else: ?>
                                                <span class="text-purple-400 font-bold text-xs uppercase flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg> Mutasi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 font-mono font-bold <?= $row['jenis_transaksi'] === 'pengeluaran' ? 'text-rose-400' : 'text-emerald-400' ?>">
                                            <?= $row['jenis_transaksi'] === 'pengeluaran' ? '-' : '+' ?>Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                        </td>
                                        <td class="p-4 text-slate-400 text-xs">
                                            <?= (string) esc($row['keterangan']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    function toggleFormMode() {
        const type = document.getElementById('jenis_transaksi').value;
        const manualMode = document.getElementById('manual-mode');
        const transferMode = document.getElementById('transfer-mode');

        if (type === 'transfer_internal') {
            manualMode.classList.add('hidden');
            transferMode.classList.remove('hidden');
        } else {
            manualMode.classList.remove('hidden');
            transferMode.classList.add('hidden');
        }
    }

    // Nominal formatting
    const nominalInput = document.getElementById('nominal_input');
    if(nominalInput) {
        nominalInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if(value) {
                value = parseInt(value, 10).toLocaleString('en-US');
            }
            this.value = value;
        });
    }
</script>
<?= $this->endSection() ?>
