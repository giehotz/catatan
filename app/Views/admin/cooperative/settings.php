<?php
/**
 * @var array $settings
 * @var bool $directLoanEnabled
 * @var string $title
 * @var array $activeUsers
 */
// Normalize array values to strictly string types to satisfy IDE static analysis
/** @var array<string, string> $strSettings */
$strSettings = [];
foreach ((array) $settings as $k => $v) {
    $strSettings[(string) $k] = is_array($v) ? json_encode($v) : (string) $v;
}
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>


<?= $this->section('koprasi_content') ?>
<div class="space-y-6 relative">
    
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Card wrapper with sleek backdrop blur and tailored HSL values -->
    <div class="bg-slate-950/40 border border-slate-900 rounded-2xl p-6 relative overflow-hidden backdrop-blur-sm">
        
        <!-- Subtle Glow -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-white tracking-wide">Pengaturan Fitur KSP</h3>
                <p class="text-[11px] text-slate-500">Kelola preferensi operasional dan aturan main modul koperasi.</p>
            </div>
        </div>

        <form action="<?= base_url('admin/cooperative/settings/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Direct Loan Toggle Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <label for="direct_loan_enabled" class="text-xs font-bold text-slate-200 cursor-pointer">Pemberian Pinjaman Langsung (Direct Loan)</label>
                        <p class="text-[11px] text-slate-400 leading-relaxed max-w-xl">
                            Mengizinkan Admin & Manager untuk memberikan pinjaman baru kepada anggota koperasi secara langsung dari dashboard, meskipun anggota tersebut belum mengajukan sebelumnya. 
                        </p>
                        <div class="flex items-center gap-2 mt-2 text-[10px] text-amber-500 font-semibold bg-amber-500/5 border border-amber-500/10 px-2.5 py-1 rounded-lg w-fit">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Manager tidak dapat menggunakan fitur ini apabila dimatikan oleh Administrator.</span>
                        </div>
                    </div>

                    <!-- Custom Elegant Toggle Switch -->
                    <div class="flex items-center shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="direct_loan_enabled" id="direct_loan_enabled" value="1" class="sr-only peer" <?= (isset($settings['direct_loan_enabled']) && $settings['direct_loan_enabled'] === '1') ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-emerald-500/30 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-slate-400 peer-checked:after:bg-emerald-400 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500/20 border border-slate-700/60 peer-checked:border-emerald-500/50"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Bunga Pinjaman Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300 space-y-4">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Aturan Bunga Pinjaman</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Persentase Bunga (%)</label>
                        <input type="number" step="0.01" name="kop_bunga_pinjaman_persen" value="<?= (string) esc((string) ($strSettings['kop_bunga_pinjaman_persen'] ?? '1.50')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold font-mono">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Jenis Bunga</label>
                        <select name="kop_bunga_pinjaman_jenis" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="flat" <?= ($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') === 'flat' ? 'selected' : '' ?>>Flat / Tetap</option>
                            <option value="efektif" <?= ($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') === 'efektif' ? 'selected' : '' ?>>Efektif (Menurun)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Periode Bunga</label>
                        <select name="kop_bunga_pinjaman_periode" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="bulanan" <?= ($settings['kop_bunga_pinjaman_periode'] ?? 'bulanan') === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                            <option value="tahunan" <?= ($settings['kop_bunga_pinjaman_periode'] ?? 'bulanan') === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Opsi Pembayaran Bunga</label>
                        <select name="kop_bunga_pinjaman_opsi_bayar" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="cicil" <?= ($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') === 'cicil' ? 'selected' : '' ?>>Dicicil Bersama Angsuran</option>
                            <option value="di_awal" <?= ($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') === 'di_awal' ? 'selected' : '' ?>>Dibayar Penuh Di Awal</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Jasa Pinjaman Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300 space-y-4">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Aturan Jasa Pinjaman</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Nominal / Persen Jasa</label>
                        <input type="number" step="0.01" name="kop_jasa_pinjaman_nominal" value="<?= (string) esc((string) ($strSettings['kop_jasa_pinjaman_nominal'] ?? '0')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold font-mono">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Jenis Jasa</label>
                        <select name="kop_jasa_pinjaman_jenis" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="nominal_tetap" <?= ($settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap') === 'nominal_tetap' ? 'selected' : '' ?>>Nominal Tetap (Rupiah)</option>
                            <option value="persentase" <?= ($settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap') === 'persentase' ? 'selected' : '' ?>>Persentase dari Pinjaman</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Cara Pembayaran Jasa</label>
                        <select name="kop_jasa_pinjaman_cara_bayar" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="cicil" <?= ($settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil') === 'cicil' ? 'selected' : '' ?>>Dicicil Bersama Angsuran</option>
                            <option value="di_awal" <?= ($settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil') === 'di_awal' ? 'selected' : '' ?>>Dibayar Penuh Di Awal</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Simpanan Wajib & Dana Sosial Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300 space-y-4">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2" />
                    </svg>
                    <span>Iuran Simpanan Wajib & Dana Sosial</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Nominal Simpanan Wajib (Rp)</label>
                        <input type="number" name="kop_simpanan_wajib_nominal" value="<?= (string) esc((string) ($strSettings['kop_simpanan_wajib_nominal'] ?? '50000')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Batas Hari Pembayaran (Hari Ke-X)</label>
                        <input type="number" min="1" max="28" name="kop_simpanan_wajib_batas_hari" value="<?= (string) esc((string) ($strSettings['kop_simpanan_wajib_batas_hari'] ?? '7')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Nominal Dana Sosial (Rp)</label>
                        <input type="number" name="kop_dana_sosial_nominal" value="<?= (string) esc((string) ($strSettings['kop_dana_sosial_nominal'] ?? '20000')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Batas Hari Pembayaran (Hari Ke-X)</label>
                        <input type="number" min="1" max="28" name="kop_dana_sosial_batas_hari" value="<?= (string) esc((string) ($strSettings['kop_dana_sosial_batas_hari'] ?? '7')) ?>" required class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                </div>
            </div>

            <!-- Rekening Tujuan Transfer Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300 space-y-4">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Rekening Tujuan Transfer (Ditampilkan ke Anggota)</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">Data rekening ini akan ditampilkan secara otomatis kepada anggota saat hendak menyetor simpanan atau membayar angsuran.</p>
                
                <!-- Bank 1 -->
                <div class="p-4 bg-slate-950/40 border border-slate-900/60 rounded-xl space-y-3">
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Rekening Bank 1 (Utama)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Nama Bank</label>
                            <input type="text" name="kop_rekening_bank_1_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_nama'] ?? '')) ?>" placeholder="Contoh: Bank Mandiri" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Nomor Rekening</label>
                            <input type="text" name="kop_rekening_bank_1_nomor" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_nomor'] ?? '')) ?>" placeholder="Contoh: 123-000-456-7890" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Atas Nama</label>
                            <input type="text" name="kop_rekening_bank_1_atas_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_atas_nama'] ?? '')) ?>" placeholder="Contoh: KSP Sejahtera" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                        </div>
                    </div>
                </div>
                
                <!-- Bank 2 -->
                <div class="p-4 bg-slate-950/40 border border-slate-900/60 rounded-xl space-y-3">
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Rekening Bank 2 (Opsional)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Nama Bank</label>
                            <input type="text" name="kop_rekening_bank_2_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_nama'] ?? '')) ?>" placeholder="Contoh: Bank BCA" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Nomor Rekening</label>
                            <input type="text" name="kop_rekening_bank_2_nomor" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_nomor'] ?? '')) ?>" placeholder="Contoh: 888-999-555-12" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-400 block">Atas Nama</label>
                            <input type="text" name="kop_rekening_bank_2_atas_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_atas_nama'] ?? '')) ?>" placeholder="Contoh: KSP Sejahtera" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Surat Resmi & KOP Koperasi Card -->
            <div class="bg-slate-900/50 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition-all duration-300 space-y-5">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-5.603-3.737A1 1 0 007.1 11v7a1 1 0 001 1h8a1 1 0 001-1v-7a1 1 0 00-.547-.888L14.25 14.5" />
                    </svg>
                    <span>Template & KOP Surat Keputusan Resmi</span>
                </div>
                
                <p class="text-[11px] text-slate-500 leading-relaxed">Sesuaikan format KOP surat, logo resmi, format penomoran otomatis, serta susunan dewan pengurus penanda tangan resmi koperasi secara dinamis.</p>

                <!-- KOP Identity Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 border-t border-slate-950/40 pt-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Nama Resmi Koperasi</label>
                        <input type="text" name="kop_nama_koperasi" value="<?= (string) esc((string) ($strSettings['kop_nama_koperasi'] ?? '')) ?>" placeholder="Contoh: KSP Catatan Keuangan" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">No. Badan Hukum Resmi</label>
                        <input type="text" name="kop_badan_hukum" value="<?= (string) esc((string) ($strSettings['kop_badan_hukum'] ?? '')) ?>" placeholder="Contoh: No. 00892/KSP/BH/2025" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Wilayah Kerja Operasional</label>
                        <input type="text" name="kop_wilayah_kerja" value="<?= (string) esc((string) ($strSettings['kop_wilayah_kerja'] ?? '')) ?>" placeholder="Contoh: Wilayah Kerja DKI Jakarta" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Nomor Telepon Koperasi</label>
                        <input type="text" name="kop_telepon" value="<?= (string) esc((string) ($strSettings['kop_telepon'] ?? '')) ?>" placeholder="Contoh: (021) 8089-9800" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">E-mail Resmi Koperasi</label>
                        <input type="email" name="kop_email" value="<?= (string) esc((string) ($strSettings['kop_email'] ?? '')) ?>" placeholder="Contoh: ksp@catatankeuangan.com" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Website Resmi Koperasi</label>
                        <input type="text" name="kop_website" value="<?= (string) esc((string) ($strSettings['kop_website'] ?? '')) ?>" placeholder="Contoh: www.catatankeuangan.com" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Alamat Lengkap Koperasi</label>
                        <input type="text" name="kop_alamat" value="<?= (string) esc((string) ($strSettings['kop_alamat'] ?? '')) ?>" placeholder="Contoh: Jl. Jend. Sudirman Kav. 21, Jakarta Selatan" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Kode Cabang/Unit</label>
                        <input type="text" name="kop_unit_code" value="<?= (string) esc((string) ($strSettings['kop_unit_code'] ?? 'PST')) ?>" placeholder="Contoh: PST" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold uppercase">
                    </div>
                </div>

                <!-- Logo upload with preview -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-950/40 pt-4 items-center">
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-xs font-bold text-slate-400 block">Unggah Logo Koperasi Baru</label>
                        <p class="text-[10px] text-slate-500 leading-relaxed">Hanya menerima format JPG/JPEG/PNG berukuran maksimal 2MB. Logo lama akan tetap dipertahankan permanen di disk untuk keabsahan arsip dokumen sejarah.</p>
                        <input type="file" id="kop_logo" name="kop_logo" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-450 hover:file:bg-emerald-500/20 file:cursor-pointer mt-1">
                    </div>
                    <div class="flex items-center justify-center p-3 bg-slate-950/40 border border-slate-900 rounded-xl min-h-[90px]">
                        <img id="logo_preview" src="<?= !empty($settings['kop_logo_path']) ? base_url($settings['kop_logo_path']) : base_url('assets/images/logo-ksp-default.png') ?>" alt="Logo Preview" class="max-h-[70px] max-width-[100px] object-contain">
                    </div>
                </div>

                <!-- Format string & Live AJAX preview -->
                <div class="border-t border-slate-950/40 pt-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="kop_format_nomor_surat" class="text-xs font-bold text-slate-400 block">Format Nomor Surat Resmi</label>
                            <input type="text" id="kop_format_nomor_surat" name="kop_format_nomor_surat" value="<?= (string) esc((string) ($strSettings['kop_format_nomor_surat'] ?? '{nomor_urut}/KOP-SKP/{kode}/{year}')) ?>" placeholder="Contoh: {nomor_urut}/KSP-RE/{kode}/{month_roman}/{year}" class="w-full px-3 py-2 bg-slate-950/60 border border-slate-900 rounded-lg focus:border-emerald-500 text-white transition-all outline-none text-xs font-semibold">
                        </div>
                        
                        <div class="p-3 bg-slate-950/40 border border-slate-900 rounded-xl flex flex-col justify-center">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Live Test Parser Preview (AJAX)</span>
                            <span id="nomor_surat_preview" class="text-xs font-mono font-bold text-indigo-400 mt-1 block">-</span>
                            <span id="nomor_surat_preview_error" class="text-[10px] text-rose-450 mt-1 font-semibold block"></span>
                        </div>
                    </div>

                    <!-- Placeholder list helper -->
                    <div class="p-3 bg-slate-950/20 border border-slate-900/60 rounded-xl text-[10px] text-slate-500 leading-relaxed space-y-1">
                        <span class="font-bold text-slate-400 block">💡 Panduan Placeholder Resmi:</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-1">
                            <div><code class="text-indigo-400 font-bold font-mono">{nomor_urut}</code>: Urutan digital (Wajib)</div>
                            <div><code class="text-indigo-400 font-bold font-mono">{kode}</code>: Kode tipe surat (e.g. RE)</div>
                            <div><code class="text-indigo-400 font-bold font-mono">{year}</code>: Tahun berjalan (e.g. 2026)</div>
                            <div><code class="text-indigo-400 font-bold font-mono">{month}</code>: Bulan angka (e.g. 05)</div>
                            <div><code class="text-indigo-400 font-bold font-mono">{month_roman}</code>: Bulan Romawi (e.g. V)</div>
                            <div><code class="text-indigo-400 font-bold font-mono">{unit_code}</code>: Kode Cabang (e.g. PST)</div>
                        </div>
                    </div>
                </div>

                <!-- Structured Dynamic CRUD for Dewan Pengurus Signers -->
                <div class="border-t border-slate-950/40 pt-4 space-y-4">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Daftar Dewan Pengurus (Penanda Tangan Resmi)</span>
                        <span class="text-[9px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md uppercase tracking-wider">Metode Prioritas & Fallback Terstruktur</span>
                    </div>

                    <!-- Hidden JSON storage input -->
                    <input type="hidden" id="kop_letter_signers" name="kop_letter_signers" value="<?= (string) esc((string) ($strSettings['kop_letter_signers'] ?? '[]')) ?>">

                    <!-- Interactive CRUD Table -->
                    <div class="overflow-x-auto border border-slate-900 rounded-xl">
                        <table class="w-full text-left border-collapse" id="signers_table">
                            <thead>
                                <tr class="border-b border-slate-900 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                                    <th class="py-2.5 px-4">Nama Pengurus</th>
                                    <th class="py-2.5 px-4">Jabatan/Peran</th>
                                    <th class="py-2.5 px-4 text-center">Tipe Dokumen</th>
                                    <th class="py-2.5 px-4 text-center">Prioritas</th>
                                    <th class="py-2.5 px-4 text-center">Status</th>
                                    <th class="py-2.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/60 text-xs text-slate-350" id="signers_tbody">
                                <!-- JS populated -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Adding signer form segment -->
                    <div class="p-4 bg-slate-950/30 border border-slate-900 rounded-xl space-y-3">
                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest block">Tambah Penanda Tangan Baru</span>
                        
                        <!-- Select from active users -->
                        <div class="space-y-1 max-w-xs">
                            <label class="text-[9px] font-bold text-slate-500 block">Pilih Dari User Aktif (Opsional)</label>
                            <select id="active_user_select" onchange="autoFillSignerFromUser()" class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-xs outline-none focus:border-emerald-500 cursor-pointer font-semibold">
                                <option value="">-- Pilih User Aktif --</option>
                                <?php if (!empty($activeUsers)): ?>
                                    <?php foreach ($activeUsers as $u): ?>
                                        <option value="<?= (string) esc((string) $u->username) ?>" data-email="<?= (string) esc((string) $u->email) ?>" data-id="<?= (string) esc((string) $u->id) ?>"><?= (string) esc((string) $u->username) ?> (<?= (string) esc((string) $u->email) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-bold text-slate-500 block">Nama Lengkap</label>
                                <input type="text" id="add_signer_name" placeholder="Contoh: H. Budi Santoso, M.B.A." class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-xs outline-none focus:border-emerald-500 font-semibold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-bold text-slate-500 block">Jabatan Resmi</label>
                                <input type="text" id="add_signer_role" placeholder="Contoh: Ketua Koperasi" class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-xs outline-none focus:border-emerald-500 font-semibold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-bold text-slate-500 block">Tipe Dokumen</label>
                                <select id="add_signer_type" class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-xs outline-none focus:border-emerald-500 cursor-pointer font-bold">
                                    <option value="resign">resign (Pengunduran Diri)</option>
                                    <option value="loan">loan (Pinjaman/Kredit)</option>
                                    <option value="default">default (Dokumen Lainnya)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-bold text-slate-500 block">Prioritas (Priority)</label>
                                <input type="number" id="add_signer_priority" value="0" min="0" max="100" class="w-full px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white text-xs outline-none focus:border-emerald-500 font-bold font-mono">
                            </div>
                            <div class="flex items-end justify-end">
                                <button type="button" onclick="addNewSignerRow()" class="w-full px-4 py-2 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 text-emerald-450 hover:text-emerald-300 rounded-lg text-xs font-bold transition-all cursor-pointer">
                                    + Tambah Pengurus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Block -->
            <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-900/60 flex-wrap">
                <!-- System Cache Flusher Trigger -->
                <div class="flex items-center gap-2">
                    <button type="button" onclick="triggerSystemCacheClear('config')" class="px-4 py-2.5 rounded-xl border border-slate-800 hover:border-indigo-500/30 bg-slate-900/40 hover:bg-indigo-500/5 text-slate-400 hover:text-indigo-400 text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5M4 8h3m14 8h-3m-15.356 2A8.001 8.001 0 102.21 16H4.5" />
                        </svg>
                        Refresh Cache Konfig
                    </button>
                    <button type="button" onclick="triggerSystemCacheClear('all')" class="px-4 py-2.5 rounded-xl border border-slate-800 hover:border-rose-500/30 bg-slate-900/40 hover:bg-rose-500/5 text-slate-400 hover:text-rose-450 text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Flush Semua Cache
                    </button>
                </div>

                <!-- Submit changes -->
                <button type="submit" class="cursor-pointer bg-linear-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-extrabold text-xs px-5 py-3 rounded-xl shadow-md shadow-emerald-950/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Structured Javascript CRUD & Preview Controller -->
<script>
    // 1. Live Logo Preview Controller
    const logoInput = document.getElementById('kop_logo');
    const logoPreview = document.getElementById('logo_preview');

    logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // 2. Debounced Live AJAX Preview for Format String
    const formatInput = document.getElementById('kop_format_nomor_surat');
    const previewEl = document.getElementById('nomor_surat_preview');
    const previewErrorEl = document.getElementById('nomor_surat_preview_error');
    
    let debounceTimer;
    function updatePreview() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const formatVal = formatInput.value.trim();
            if (!formatVal) {
                previewEl.textContent = '-';
                previewErrorEl.textContent = '';
                return;
            }

            fetch('<?= base_url('admin/cooperative/settings/preview-number') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                },
                body: new URLSearchParams({
                    'format': formatVal,
                    'kode': 'RE'
                })
            })
            .then(response => {
                if (response.status === 429) {
                    throw new Error('Terlalu banyak permintaan (Rate limit exceeded). Harap tunggu beberapa detik.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    previewEl.textContent = data.preview;
                    previewEl.classList.remove('text-rose-450');
                    previewEl.classList.add('text-indigo-400');
                    previewErrorEl.textContent = '';
                } else {
                    previewEl.textContent = 'Format Error';
                    previewEl.classList.remove('text-indigo-400');
                    previewEl.classList.add('text-rose-450');
                    previewErrorEl.textContent = data.error;
                }
            })
            .catch(err => {
                previewEl.textContent = 'Network Error';
                previewEl.classList.remove('text-indigo-400');
                previewEl.classList.add('text-rose-450');
                previewErrorEl.textContent = err.message;
            });
        }, 300); // 300ms debounce
    }
    formatInput.addEventListener('input', updatePreview);
    updatePreview(); // Trigger initial preview load

    // 3. Dynamic JSON signers array manager
    const signersInput = document.getElementById('kop_letter_signers');
    const signersTbody = document.getElementById('signers_tbody');

    // Read initial JSON
    let signersData = [];
    try {
        signersData = JSON.parse(signersInput.value || '[]');
    } catch(e) {
        signersData = [];
    }

    function renderSignersTable() {
        signersTbody.innerHTML = '';
        if (signersData.length === 0) {
            signersTbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-4 text-center text-slate-500 italic font-semibold">Belum ada dewan pengurus aktif penanda tangan. Sistem menggunakan default Ketua Koperasi.</td>
                </tr>
            `;
            return;
        }

        // Sort dynamically for rendering
        signersData.sort((a,b) => (b.priority || 0) - (a.priority || 0));

        signersData.forEach((signer, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-950/20 transition-colors border-b border-slate-900/40';
            
            const badgeClass = signer.letter_type === 'resign' 
                ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' 
                : (signer.letter_type === 'loan' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-slate-700/30 text-slate-400 border-slate-800');

            tr.innerHTML = `
                <td class="py-2.5 px-4 font-bold text-white">${esc(signer.name)}</td>
                <td class="py-2.5 px-4 text-slate-400">${esc(signer.role)}</td>
                <td class="py-2.5 px-4 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold border uppercase ${badgeClass}">
                        ${esc(signer.letter_type)}
                    </span>
                </td>
                <td class="py-2.5 px-4 text-center font-bold font-mono text-emerald-400">${signer.priority}</td>
                <td class="py-2.5 px-4 text-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${signer.is_active ? 'checked' : ''} onchange="toggleSignerActive('${signer.signer_id}')">
                        <div class="w-8 h-4 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-slate-400 peer-checked:after:bg-emerald-400 after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500/20 border border-slate-700/60 peer-checked:border-emerald-500/50"></div>
                    </label>
                </td>
                <td class="py-2.5 px-4 text-right">
                    <button type="button" onclick="deleteSignerRow('${signer.signer_id}')" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 text-rose-450 hover:text-rose-300 rounded-md font-bold text-[10px] cursor-pointer transition-colors">
                        Hapus
                    </button>
                </td>
            `;
            signersTbody.appendChild(tr);
        });
    }

    // Toggle active status in array
    window.toggleSignerActive = function(signerId) {
        signersData = signersData.map(s => {
            if (s.signer_id === signerId) {
                s.is_active = !s.is_active;
            }
            return s;
        });
        saveSignersJson();
    };

    // Delete signer from array
    window.deleteSignerRow = function(signerId) {
        if (confirm('Apakah Anda yakin ingin menghapus dewan pengurus ini?')) {
            signersData = signersData.filter(s => s.signer_id !== signerId);
            saveSignersJson();
            renderSignersTable();
        }
    };

    // Save array to hidden input JSON
    function saveSignersJson() {
        signersInput.value = JSON.stringify(signersData);
    }

    window.autoFillSignerFromUser = function() {
        const select = document.getElementById('active_user_select');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value !== "") {
            document.getElementById('add_signer_name').value = selectedOption.value;
        }
    };

    // Add new signer to array
    window.addNewSignerRow = function() {
        const nameEl = document.getElementById('add_signer_name');
        const roleEl = document.getElementById('add_signer_role');
        const typeEl = document.getElementById('add_signer_type');
        const priorityEl = document.getElementById('add_signer_priority');
        const activeUserSelect = document.getElementById('active_user_select');

        const name = nameEl.value.trim();
        const role = roleEl.value.trim();
        const type = typeEl.value;
        const priority = parseInt(priorityEl.value) || 0;

        if (!name || !role) {
            alert('Silakan isi Nama Lengkap dan Jabatan Pengurus.');
            return;
        }

        const selectedUserOption = activeUserSelect.options[activeUserSelect.selectedIndex];
        const userId = selectedUserOption && selectedUserOption.value !== "" ? selectedUserOption.getAttribute('data-id') : null;

        const newSigner = {
            schema_version: 1,
            signer_id: 'signer_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            name: name,
            role: role,
            letter_type: type,
            is_active: true,
            priority: priority,
            user_id: userId
        };

        signersData.push(newSigner);
        saveSignersJson();
        renderSignersTable();

        // Reset form
        nameEl.value = '';
        roleEl.value = '';
        typeEl.value = 'resign';
        priorityEl.value = '0';
        activeUserSelect.value = '';
    };

    // Helper sanitasi XSS ringan di JS
    function esc(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Render on load
    renderSignersTable();

    // 4. AJAX Administrative Cache Flusher Controller
    window.triggerSystemCacheClear = function(type) {
        const confirmMsg = type === 'all' 
            ? 'Apakah Anda yakin ingin MEMBERSIHKAN SELURUH CACHE SISTEM (KOP + Snapshot Surat approved)? Tindakan ini akan meng-refresh paksa database saat surat dibuka.'
            : 'Apakah Anda yakin ingin MEMBERSIHKAN CACHE KONFIGURASI Koperasi?';
            
        if (confirm(confirmMsg)) {
            fetch(`<?= base_url('admin/cooperative/settings/clear-cache') ?>?type=${type}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.status === 400) {
                    throw new Error('Parameter pembersihan cache tidak valid.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Gagal membersihkan cache: ' + data.error);
                }
            })
            .catch(err => {
                alert('Kesalahan jaringan: ' + err.message);
            });
        }
    };
</script>


        </form>
    </div>
</div>
<?= $this->endSection() ?>
