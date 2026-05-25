<?php
/**
 * @var string $title
 * @var int $tahun
 * @var float $totalShu
 * @var float $cadangan_persen
 * @var float $jasa_modal_persen
 * @var float $jasa_usaha_persen
 * @var float $dana_pengurus_persen
 * @var float $dana_pendidikan_persen
 * @var array $simulation
 * @var array $shuHistory
 * @var float $totalCoopSavings
 * @var float $totalCoopVolume
 * @var array|null $existingAlokasi
 * @var bool $useDefault
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
        <span class="text-xs text-slate-500 font-semibold font-mono">SHU Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Kalkulator & Pembagian SHU</h1>
        <p class="text-slate-400 text-sm">Simulasikan pembagian Sisa Hasil Usaha (SHU) berdasarkan standar resmi Koperasi Simpan Pinjam — alokasi Cadangan, Jasa Modal (Simpanan), Jasa Usaha (Volume Pinjaman), Dana Pengurus, dan Dana Pendidikan.</p>
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

    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 4c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <!-- STEP 1 & 2: Parameters + Mode Selector -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Input Form -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl lg:col-span-1">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Parameter SHU
            </h3>

            <form id="shuForm" action="<?= base_url('admin/cooperative/shu') ?>" method="GET" class="space-y-4">
                <div>
                    <label for="tahun" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tahun Buku</label>
                    <select id="tahun" name="tahun" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--) : ?>
                            <option value="<?= $y ?>" <?= $tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label for="total_shu" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Step 1: Total SHU Bersih (Rp)</label>
                    <input type="number" id="total_shu" name="total_shu" min="0" step="1000" value="<?= (int)$totalShu ?>" placeholder="Contoh: 10000000" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-600 focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Step 2: Mode Input Alokasi</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 bg-slate-950/60 rounded-xl border border-slate-800 cursor-pointer hover:border-indigo-500/30 transition-colors">
                            <input type="radio" name="use_default" value="1" <?= $useDefault ? 'checked' : '' ?> onchange="document.getElementById('shuForm').submit()" class="text-indigo-500 focus:ring-indigo-500/50">
                            <div>
                                <span class="block text-sm font-semibold text-white">Gunakan Default Konfigurasi</span>
                                <span class="text-[11px] text-slate-500">40% Cadangan / 20% Jasa Modal / 25% Jasa Usaha / 10% Pengurus / 5% Pendidikan</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-slate-950/60 rounded-xl border border-slate-800 cursor-pointer hover:border-indigo-500/30 transition-colors">
                            <input type="radio" name="use_default" value="0" <?= !$useDefault ? 'checked' : '' ?> onchange="document.getElementById('shuForm').submit()" class="text-indigo-500 focus:ring-indigo-500/50">
                            <div>
                                <span class="block text-sm font-semibold text-white">Input Manual Persentase</span>
                                <span class="text-[11px] text-slate-500">Atur sendiri alokasi tiap kategori</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/10 transition-all duration-200 cursor-pointer">
                    Simulasikan SHU
                </button>
            </form>
        </div>

        <!-- Cooperative Stats -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl lg:col-span-2 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Ringkasan Konstanta Koperasi (Anggota Aktif)
                </h3>
                <p class="text-slate-400 text-xs mb-4">Total simpanan dan volume pinjaman yang digunakan sebagai penyebut dalam pembagian rasio SHU.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Simpanan Seluruh Anggota</span>
                        <span class="text-xl font-bold text-indigo-400 font-mono">Rp <?= number_format($totalCoopSavings, 2, ',', '.') ?></span>
                    </div>
                    <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Volume Pinjaman</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono">Rp <?= number_format($totalCoopVolume, 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <?php if ($totalShu > 0 && count($simulation) > 0) : ?>
                <div class="mt-6 p-4 bg-indigo-500/5 border border-indigo-500/10 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h4 class="text-xs font-bold text-indigo-300">Siap Distribusikan?</h4>
                        <p class="text-slate-400 text-[11px] leading-relaxed">Kalkulasi simulasi di bawah ini akan disimpan ke histori dan saldo simpanan sukarela anggota akan langsung bertambah.</p>
                    </div>

                    <div class="flex gap-2">
                        <?php if (!$existingAlokasi || $existingAlokasi['status'] !== 'distributed') : ?>
                        <form action="<?= base_url('admin/cooperative/shu/save-alokasi') ?>" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tahun" value="<?= $tahun ?>">
                            <input type="hidden" name="total_shu" value="<?= $totalShu ?>">
                            <input type="hidden" name="cadangan_persen" value="<?= $cadangan_persen ?>">
                            <input type="hidden" name="jasa_modal_persen" value="<?= $jasa_modal_persen ?>">
                            <input type="hidden" name="jasa_usaha_persen" value="<?= $jasa_usaha_persen ?>">
                            <input type="hidden" name="dana_pengurus_persen" value="<?= $dana_pengurus_persen ?>">
                            <input type="hidden" name="dana_pendidikan_persen" value="<?= $dana_pendidikan_persen ?>">
                            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-indigo-600/10 transition-all duration-200 cursor-pointer">
                                Simpan Alokasi
                            </button>
                        </form>
                        <?php endif; ?>

                        <form action="<?= base_url('admin/cooperative/shu/distribute') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membagikan total SHU sebesar Rp <?= number_format($totalShu, 0, ',', '.') ?> ke semua anggota aktif? Tindakan ini akan langsung mengkreditkan dana ke Simpanan Sukarela mereka.')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tahun" value="<?= $tahun ?>">
                            <input type="hidden" name="total_shu" value="<?= $totalShu ?>">
                            <input type="hidden" name="cadangan_persen" value="<?= $cadangan_persen ?>">
                            <input type="hidden" name="jasa_modal_persen" value="<?= $jasa_modal_persen ?>">
                            <input type="hidden" name="jasa_usaha_persen" value="<?= $jasa_usaha_persen ?>">
                            <input type="hidden" name="dana_pengurus_persen" value="<?= $dana_pengurus_persen ?>">
                            <input type="hidden" name="dana_pendidikan_persen" value="<?= $dana_pendidikan_persen ?>">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-600/10 transition-all duration-200 cursor-pointer">
                                Bagikan SHU
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- STEP 3: Allocation Percentage Breakdown -->
    <?php if ($totalShu > 0) : ?>
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 shadow-xl">
        <h3 class="text-lg font-bold text-white tracking-tight mb-4">Step 3: Alokasi Persentase SHU (Total harus 100%)</h3>

        <?php
        $totalPercent = $cadangan_persen + $jasa_modal_persen + $jasa_usaha_persen + $dana_pengurus_persen + $dana_pendidikan_persen;
        $isValid = abs($totalPercent - 100.0) < 0.01;

        $allocationCadangan   = $totalShu * ($cadangan_persen / 100);
        $allocationModal      = $totalShu * ($jasa_modal_persen / 100);
        $allocationUsaha      = $totalShu * ($jasa_usaha_persen / 100);
        $allocationPengurus   = $totalShu * ($dana_pengurus_persen / 100);
        $allocationPendidikan = $totalShu * ($dana_pendidikan_persen / 100);
        ?>

        <form action="<?= base_url('admin/cooperative/shu') ?>" method="GET" class="space-y-4">
            <input type="hidden" name="tahun" value="<?= $tahun ?>">
            <input type="hidden" name="total_shu" value="<?= $totalShu ?>">
            <input type="hidden" name="use_default" value="0">

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Cadangan -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Cadangan Koperasi</label>
                    <input type="number" name="cadangan_persen" value="<?= $cadangan_persen ?>" min="0" max="100" step="0.1" <?= ($existingAlokasi && $existingAlokasi['status'] === 'distributed') ? 'disabled' : '' ?> class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white text-center font-bold focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                    <span class="block mt-1 text-xs font-mono text-slate-400">Rp <?= number_format($allocationCadangan, 0, ',', '.') ?></span>
                </div>
                <!-- Jasa Modal -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Jasa Modal</label>
                    <input type="number" name="jasa_modal_persen" value="<?= $jasa_modal_persen ?>" min="0" max="100" step="0.1" <?= ($existingAlokasi && $existingAlokasi['status'] === 'distributed') ? 'disabled' : '' ?> class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white text-center font-bold focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                    <span class="block mt-1 text-xs font-mono text-slate-400">Rp <?= number_format($allocationModal, 0, ',', '.') ?></span>
                </div>
                <!-- Jasa Usaha -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900 text-center">
                    <label class="block text-[10px] font-bold text-emerald-400 uppercase tracking-wider mb-2">Jasa Usaha</label>
                    <input type="number" name="jasa_usaha_persen" value="<?= $jasa_usaha_persen ?>" min="0" max="100" step="0.1" <?= ($existingAlokasi && $existingAlokasi['status'] === 'distributed') ? 'disabled' : '' ?> class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white text-center font-bold focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                    <span class="block mt-1 text-xs font-mono text-slate-400">Rp <?= number_format($allocationUsaha, 0, ',', '.') ?></span>
                </div>
                <!-- Dana Pengurus -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Dana Pengurus</label>
                    <input type="number" name="dana_pengurus_persen" value="<?= $dana_pengurus_persen ?>" min="0" max="100" step="0.1" <?= ($existingAlokasi && $existingAlokasi['status'] === 'distributed') ? 'disabled' : '' ?> class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white text-center font-bold focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                    <span class="block mt-1 text-xs font-mono text-slate-400">Rp <?= number_format($allocationPengurus, 0, ',', '.') ?></span>
                </div>
                <!-- Dana Pendidikan -->
                <div class="p-4 bg-slate-950/60 rounded-xl border border-slate-900 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Dana Pendidikan</label>
                    <input type="number" name="dana_pendidikan_persen" value="<?= $dana_pendidikan_persen ?>" min="0" max="100" step="0.1" <?= ($existingAlokasi && $existingAlokasi['status'] === 'distributed') ? 'disabled' : '' ?> class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white text-center font-bold focus:outline-hidden focus:ring-1 focus:ring-indigo-500/50">
                    <span class="block mt-1 text-xs font-mono text-slate-400">Rp <?= number_format($allocationPendidikan, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-950/40 rounded-xl border <?= $isValid ? 'border-emerald-500/20' : 'border-rose-500/20' ?>">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-400">Total Persentase:</span>
                    <span class="text-2xl font-extrabold font-mono <?= $isValid ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= number_format($totalPercent, 1) ?>%
                    </span>
                    <?php if ($isValid) : ?>
                        <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full font-bold">OK</span>
                    <?php else : ?>
                        <span class="text-xs bg-rose-500/10 text-rose-400 px-2 py-0.5 rounded-full font-bold">HARUS 100%</span>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Alokasi</span>
                    <span class="text-lg font-bold text-white font-mono">Rp <?= number_format($totalShu, 0, ',', '.') ?></span>
                </div>
            </div>

            <?php if (!$existingAlokasi || $existingAlokasi['status'] !== 'distributed') : ?>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/10 transition-all duration-200 cursor-pointer">
                Hitung Ulang Simulasi
            </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Simulation Table -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60">
            <h3 class="text-lg font-bold text-white tracking-tight">Simulasi Hasil SHU Anggota (Tahun Buku <?= $tahun ?>)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-900">
                    <tr>
                        <th class="px-6 py-4">Nomor Anggota</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4 text-right">Simpanan</th>
                        <th class="px-6 py-4 text-right">Volume Pinjaman</th>
                        <th class="px-6 py-4 text-right text-indigo-400">Jasa Modal</th>
                        <th class="px-6 py-4 text-right text-emerald-400">Jasa Usaha</th>
                        <th class="px-6 py-4 text-right text-white font-bold">Total SHU</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    <?php if (count($simulation) === 0) : ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500 font-semibold">Tidak ada anggota aktif yang berpartisipasi tahun ini.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($simulation as $sim) : ?>
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 font-semibold font-mono text-xs"><?= (string) esc($sim['nomor_anggota']) ?></td>
                                <td class="px-6 py-4 font-medium text-white"><?= (string) esc($sim['username']) ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format($sim['total_savings'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format($sim['total_loan_volume'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-indigo-400">Rp <?= number_format($sim['jasa_modal'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-emerald-400">Rp <?= number_format($sim['jasa_usaha'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-white font-bold">Rp <?= number_format($sim['total_shu'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Past SHU Distribution Logs -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60">
            <h3 class="text-lg font-bold text-white tracking-tight">Riwayat Distribusi SHU Koperasi</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-900">
                    <tr>
                        <th class="px-6 py-4">Tahun Buku</th>
                        <th class="px-6 py-4">Nomor Anggota</th>
                        <th class="px-6 py-4">Nama Penerima</th>
                        <th class="px-6 py-4 text-right">Jasa Modal</th>
                        <th class="px-6 py-4 text-right">Jasa Usaha</th>
                        <th class="px-6 py-4 text-right text-emerald-400 font-bold">Total SHU</th>
                        <th class="px-6 py-4">Tanggal Distribusi</th>
                        <th class="px-6 py-4">Didistribusikan Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/50">
                    <?php if (count($shuHistory) === 0) : ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500 font-semibold">Belum pernah ada distribusi SHU sebelumnya.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($shuHistory as $history) : ?>
                            <tr class="hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4 font-bold text-white"><?= (int)$history['tahun'] ?></td>
                                <td class="px-6 py-4 font-semibold font-mono text-xs"><?= (string) esc($history['nomor_anggota']) ?></td>
                                <td class="px-6 py-4 font-medium text-white"><?= (string) esc($history['username']) ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format($history['jasa_modal'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs">Rp <?= number_format(($history['jasa_usaha'] ?? $history['jasa_anggota']), 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-emerald-400 font-bold">Rp <?= number_format($history['total_shu'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 text-xs font-mono"><?= date('d M Y H:i', strtotime($history['tanggal_distribusi'])) ?></td>
                                <td class="px-6 py-4 text-xs font-medium text-slate-400"><?= (string) esc($history['distributed_by_name'] ?? 'Sistem') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
