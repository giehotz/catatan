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
$serverStateHash = md5(json_encode($strSettings));
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<style>
    .tab-btn.active {
        color: rgb(45 212 191);
        border-bottom-color: rgb(45 212 191);
    }
    .tab-btn {
        color: rgb(148 163 184);
        border-bottom-width: 2px;
        border-bottom-color: transparent;
    }
    .tab-btn:hover:not(.active) {
        color: rgb(226 232 240);
        border-bottom-color: rgb(71 85 105);
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="space-y-6 relative">
    
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs flex items-center gap-3 animate-fade-in mb-4">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= session()->getFlashdata('message') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs flex items-center gap-3 animate-fade-in mb-4">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- Main Settings Redesign Wrapper -->
    <div class="flex flex-col gap-6 relative" id="settings-container">
        
        <!-- Main Form & Content -->
        <div class="flex-1 min-w-0 bg-[#0f172a]/80 border border-slate-700/80 rounded-2xl relative shadow-2xl overflow-hidden backdrop-blur-md flex flex-col h-full">
            <form action="<?= base_url('admin/cooperative/settings/update') ?>" method="POST" enctype="multipart/form-data" id="settingsForm" class="flex flex-col h-full relative" onsubmit="return handleFormSubmit(event)">
                <?= csrf_field() ?>
                <input type="hidden" id="server_state_hash" value="<?= (string) esc($serverStateHash) ?>">
                <input type="hidden" id="server_timestamp" value="<?= time() ?>">

                <!-- HORIZONTAL TABS -->
                <div class="flex overflow-x-auto border-b border-slate-700/60 bg-[#1e293b]/40 hide-scrollbar" role="tablist">
                    <button type="button" role="tab" aria-selected="true" onclick="switchTab('aturan_umum')" class="px-5 py-4 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap active tab-btn" data-tab-id="aturan_umum">
                        <span class="material-symbols-outlined text-sm"></span> Aturan Pinjaman
                    </button>
                    <button type="button" role="tab" aria-selected="false" onclick="switchTab('aturan_bunga')" class="px-5 py-4 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap tab-btn" data-tab-id="aturan_bunga">
                        <span class="material-symbols-outlined text-sm"></span> Bunga & Jasa
                    </button>
                    <button type="button" role="tab" aria-selected="false" onclick="switchTab('iuran')" class="px-5 py-4 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap tab-btn" data-tab-id="iuran">
                        <span class="material-symbols-outlined text-sm"></span> Iuran & Sosial
                    </button>
                    <button type="button" role="tab" aria-selected="false" onclick="switchTab('rekening')" class="px-5 py-4 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap tab-btn" data-tab-id="rekening">
                        <span class="material-symbols-outlined text-sm"></span> Rekening Bank
                    </button>
                    <button type="button" role="tab" aria-selected="false" onclick="switchTab('kop_surat')" class="px-5 py-4 text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap tab-btn" data-tab-id="kop_surat">
                        <span class="material-symbols-outlined text-sm"></span> Template & KOP
                    </button>
                </div>

                <!-- Padding Container for contents -->
                <div class="p-6 pb-24 relative flex-1">
                    
                    <!-- TAB 1: Aturan Umum -->
                    <div id="tab-aturan_umum" class="tab-pane block" data-tab-id="aturan_umum" role="tabpanel">
                        <div class="mb-6 border-b border-slate-700/60 pb-4">
                            <h2 class="text-lg font-bold text-white mb-1">Aturan Pinjaman Umum</h2>
                            <p class="text-xs text-slate-400">Konfigurasi parameter inti untuk modul pinjaman. Perubahan pada pengaturan ini akan langsung mempengaruhi perhitungan pada transaksi baru.</p>
                        </div>
                        
                        <!-- Direct Loan Toggle Card -->
                        <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <label for="direct_loan_enabled" class="text-sm font-bold text-slate-200 cursor-pointer">Pemberian Pinjaman Langsung</label>
                                        <span class="px-2 py-0.5 bg-teal-500/10 text-teal-400 border border-teal-500/20 rounded text-[9px] font-bold uppercase tracking-wider">Manager Only</span>
                                    </div>
                                    <p class="text-xs text-slate-400 leading-relaxed max-w-2xl">
                                        Mengizinkan pencairan dana pinjaman secara instan tanpa melalui proses persetujuan berjenjang. Gunakan dengan hati-hati karena fitur ini mengabaikan antrean validasi standar. 
                                    </p>
                                </div>
                                <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in shrink-0">
                                    <input type="checkbox" name="direct_loan_enabled" id="direct_loan_enabled" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-slate-500 appearance-none cursor-pointer z-10 transition-all duration-300 right-0 checked:border-teal-500" <?= (isset($settings['direct_loan_enabled']) && $settings['direct_loan_enabled'] === '1') ? 'checked' : '' ?>>
                                    <label for="direct_loan_enabled" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-600 cursor-pointer transition-colors duration-300"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Aturan Bunga & Jasa -->
                    <div id="tab-aturan_bunga" class="tab-pane hidden" data-tab-id="aturan_bunga" role="tabpanel">
                        <div class="mb-6 border-b border-slate-700/60 pb-4">
                            <h2 class="text-lg font-bold text-white mb-1">Aturan Bunga & Jasa Pinjaman</h2>
                            <p class="text-xs text-slate-400">Parameter default untuk perhitungan persentase bunga dan biaya administrasi pinjaman anggota.</p>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Bunga Card -->
                            <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl overflow-hidden shadow-sm">
                                <div class="p-4 border-b border-slate-700/60 flex items-center gap-3 bg-[#0f172a]/40">
                                    <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-teal-400">
                                        <span class="material-symbols-outlined text-lg">percent</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-white">Komponen Bunga</h3>
                                    </div>
                                </div>
                                <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Persentase Bunga</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" name="kop_bunga_pinjaman_persen" id="kop_bunga_pinjaman_persen" value="<?= (string) esc((string) ($strSettings['kop_bunga_pinjaman_persen'] ?? '1.50')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 pr-8 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                            <span class="absolute right-3 top-2.5 text-slate-400 font-bold">%</span>
                                        </div>
                                        <span class="error-msg text-[10px] text-rose-450 hidden mt-1 font-semibold"></span>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Jenis Bunga</label>
                                        <select name="kop_bunga_pinjaman_jenis" id="kop_bunga_pinjaman_jenis" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all appearance-none cursor-pointer">
                                            <option value="flat" <?= ($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') === 'flat' ? 'selected' : '' ?>>Flat / Tetap</option>
                                            <option value="efektif" <?= ($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') === 'efektif' ? 'selected' : '' ?>>Efektif (Menurun)</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Periode Bunga</label>
                                        <select name="kop_bunga_pinjaman_periode" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all appearance-none cursor-pointer">
                                            <option value="bulanan" <?= ($settings['kop_bunga_pinjaman_periode'] ?? 'bulanan') === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                                            <option value="tahunan" <?= ($settings['kop_bunga_pinjaman_periode'] ?? 'bulanan') === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Pembayaran</label>
                                        <select name="kop_bunga_pinjaman_opsi_bayar" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all appearance-none cursor-pointer">
                                            <option value="cicil" <?= ($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') === 'cicil' ? 'selected' : '' ?>>Dicicil Bersama Angsuran</option>
                                            <option value="di_awal" <?= ($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') === 'di_awal' ? 'selected' : '' ?>>Dibayar Penuh Di Awal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Jasa Card -->
                            <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl overflow-hidden shadow-sm">
                                <div class="p-4 border-b border-slate-700/60 flex items-center gap-3 bg-[#0f172a]/40">
                                    <div class="w-8 h-8 rounded bg-slate-800 flex items-center justify-center text-teal-400">
                                        <span class="material-symbols-outlined text-lg">payments</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-white">Jasa / Administrasi</h3>
                                    </div>
                                </div>
                                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Jenis Jasa</label>
                                        <select name="kop_jasa_pinjaman_jenis" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all appearance-none cursor-pointer">
                                            <option value="nominal_tetap" <?= ($settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap') === 'nominal_tetap' ? 'selected' : '' ?>>Nominal Tetap (Rp)</option>
                                            <option value="persentase" <?= ($settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap') === 'persentase' ? 'selected' : '' ?>>Persentase dari Pinjaman (%)</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nilai Jasa</label>
                                        <input type="number" step="0.01" name="kop_jasa_pinjaman_nominal" id="kop_jasa_pinjaman_nominal" value="<?= (string) esc((string) ($strSettings['kop_jasa_pinjaman_nominal'] ?? '0')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                        <span class="error-msg text-[10px] text-rose-450 hidden mt-1 font-semibold"></span>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Cara Pembayaran</label>
                                        <select name="kop_jasa_pinjaman_cara_bayar" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all appearance-none cursor-pointer">
                                            <option value="cicil" <?= ($settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil') === 'cicil' ? 'selected' : '' ?>>Dicicil Bersama Angsuran</option>
                                            <option value="di_awal" <?= ($settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil') === 'di_awal' ? 'selected' : '' ?>>Dibayar Penuh Di Awal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Iuran & Sosial -->
                    <div id="tab-iuran" class="tab-pane hidden" data-tab-id="iuran" role="tabpanel">
                        <div class="mb-6 border-b border-slate-700/60 pb-4">
                            <h2 class="text-lg font-bold text-white mb-1">Simpanan Wajib & Dana Sosial</h2>
                            <p class="text-xs text-slate-400">Aturan iuran rutin bulanan untuk seluruh anggota koperasi.</p>
                        </div>
                        <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Simpanan Wajib (Rp)</label>
                                    <input type="number" name="kop_simpanan_wajib_nominal" value="<?= (string) esc((string) ($strSettings['kop_simpanan_wajib_nominal'] ?? '50000')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Batas Bayar (Tgl)</label>
                                    <input type="number" min="1" max="28" name="kop_simpanan_wajib_batas_hari" value="<?= (string) esc((string) ($strSettings['kop_simpanan_wajib_batas_hari'] ?? '7')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Dana Sosial (Rp)</label>
                                    <input type="number" name="kop_dana_sosial_nominal" value="<?= (string) esc((string) ($strSettings['kop_dana_sosial_nominal'] ?? '20000')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Batas Bayar (Tgl)</label>
                                    <input type="number" min="1" max="28" name="kop_dana_sosial_batas_hari" value="<?= (string) esc((string) ($strSettings['kop_dana_sosial_batas_hari'] ?? '7')) ?>" required class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Rekening -->
                    <div id="tab-rekening" class="tab-pane hidden" data-tab-id="rekening" role="tabpanel">
                        <div class="mb-6 border-b border-slate-700/60 pb-4">
                            <h2 class="text-lg font-bold text-white mb-1">Rekening Bank Tujuan</h2>
                            <p class="text-xs text-slate-400">Informasi rekening ini akan ditampilkan secara otomatis kepada anggota saat hendak menyetor atau membayar angsuran via transfer bank.</p>
                        </div>
                        <div class="space-y-5">
                            <!-- Bank 1 -->
                            <div class="p-5 bg-[#1e293b]/80 border border-slate-700/60 rounded-xl space-y-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-2 h-full bg-teal-500"></div>
                                <span class="text-xs font-bold text-teal-400 uppercase tracking-wider block">Rekening Utama</span>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nama Bank</label>
                                        <input type="text" name="kop_rekening_bank_1_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_nama'] ?? '')) ?>" placeholder="Contoh: Bank Mandiri" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nomor Rekening</label>
                                        <input type="text" name="kop_rekening_bank_1_nomor" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_nomor'] ?? '')) ?>" placeholder="Contoh: 123-000-456-7890" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Atas Nama</label>
                                        <input type="text" name="kop_rekening_bank_1_atas_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_1_atas_nama'] ?? '')) ?>" placeholder="Contoh: KSP Sejahtera" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank 2 -->
                            <div class="p-5 bg-[#1e293b]/80 border border-slate-700/60 rounded-xl space-y-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-2 h-full bg-slate-600"></div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Rekening Alternatif (Opsional)</span>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nama Bank</label>
                                        <input type="text" name="kop_rekening_bank_2_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_nama'] ?? '')) ?>" placeholder="Contoh: BCA" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nomor Rekening</label>
                                        <input type="text" name="kop_rekening_bank_2_nomor" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_nomor'] ?? '')) ?>" placeholder="Contoh: 888-999-555-12" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none font-mono transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Atas Nama</label>
                                        <input type="text" name="kop_rekening_bank_2_atas_nama" value="<?= (string) esc((string) ($strSettings['kop_rekening_bank_2_atas_nama'] ?? '')) ?>" placeholder="Contoh: KSP Sejahtera" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: Template & KOP -->
                    <div id="tab-kop_surat" class="tab-pane hidden" data-tab-id="kop_surat" role="tabpanel">
                        <div class="mb-6 border-b border-slate-700/60 pb-4 flex justify-between items-end flex-wrap gap-2">
                            <div>
                                <h2 class="text-lg font-bold text-white mb-1">Template & KOP Surat</h2>
                                <p class="text-xs text-slate-400">Kelola identitas resmi, logo, pola penomoran otomatis, serta susunan dewan pengurus penanda tangan.</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Identitas & Logo Card -->
                            <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl overflow-hidden shadow-sm">
                                <div class="p-4 border-b border-slate-700/60 flex items-center gap-2 bg-[#0f172a]/40">
                                    <span class="text-sm font-bold text-white">Identitas & Logo Koperasi</span>
                                </div>
                                <div class="p-5 grid grid-cols-1 lg:grid-cols-12 gap-6">
                                    <!-- Identitas Form -->
                                    <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Nama Resmi Koperasi</label>
                                            <input type="text" name="kop_nama_koperasi" value="<?= (string) esc((string) ($strSettings['kop_nama_koperasi'] ?? '')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">No. Badan Hukum</label>
                                            <input type="text" name="kop_badan_hukum" value="<?= (string) esc((string) ($strSettings['kop_badan_hukum'] ?? '')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Wilayah Kerja</label>
                                            <input type="text" name="kop_wilayah_kerja" value="<?= (string) esc((string) ($strSettings['kop_wilayah_kerja'] ?? '')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Telepon</label>
                                            <input type="text" name="kop_telepon" value="<?= (string) esc((string) ($strSettings['kop_telepon'] ?? '')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">E-mail</label>
                                            <input type="email" name="kop_email" value="<?= (string) esc((string) ($strSettings['kop_email'] ?? '')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Kode Cabang/Unit</label>
                                            <input type="text" name="kop_unit_code" value="<?= (string) esc((string) ($strSettings['kop_unit_code'] ?? 'PST')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none uppercase transition-all">
                                        </div>
                                        <div class="sm:col-span-2 space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Alamat Lengkap</label>
                                            <textarea name="kop_alamat" rows="2" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none resize-none transition-all"><?= (string) esc((string) ($strSettings['kop_alamat'] ?? '')) ?></textarea>
                                        </div>
                                    </div>
                                    <!-- Logo Upload -->
                                    <div class="lg:col-span-4 flex flex-col space-y-4 border-t lg:border-t-0 lg:border-l border-slate-700/60 pt-4 lg:pt-0 lg:pl-6">
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Preview Logo</label>
                                            <div class="bg-[#0f172a] border border-slate-600 rounded-lg p-4 flex items-center justify-center h-28">
                                                <img id="logo_preview" src="<?= !empty($settings['kop_logo_path']) ? base_url($settings['kop_logo_path']) : base_url('assets/images/logo-ksp-default.png') ?>" alt="Logo Preview" class="max-h-[80px] object-contain">
                                            </div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Unggah Baru</label>
                                            <input type="file" id="kop_logo" name="kop_logo" accept="image/*" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-teal-500/10 file:text-teal-400 hover:file:bg-teal-500/20 file:cursor-pointer cursor-pointer border border-slate-600 rounded">
                                            <p class="text-[9px] text-slate-500 mt-1">Format: JPG/PNG/SVG. Max 2MB.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Format Penomoran -->
                            <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl overflow-hidden shadow-sm">
                                <div class="p-4 border-b border-slate-700/60 flex items-center justify-between bg-[#0f172a]/40">
                                    <span class="text-sm font-bold text-white">Format Nomor Surat Otomatis</span>
                                </div>
                                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 block uppercase tracking-wide">Pola Penomoran</label>
                                        <input type="text" id="kop_format_nomor_surat" name="kop_format_nomor_surat" value="<?= (string) esc((string) ($strSettings['kop_format_nomor_surat'] ?? '{nomor_urut}/KOP-SKP/{kode}/{year}')) ?>" class="w-full bg-[#0f172a] border border-slate-600 rounded p-2.5 text-white text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/50 outline-none transition-all font-mono">
                                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[10px] text-slate-400 mt-2">
                                            <div><code class="text-teal-400 font-bold">{nomor_urut}</code>: No urut</div>
                                            <div><code class="text-teal-400 font-bold">{kode}</code>: Tipe surat</div>
                                            <div><code class="text-teal-400 font-bold">{year}</code>: Tahun 4 digit</div>
                                            <div><code class="text-teal-400 font-bold">{month_roman}</code>: Bln Romawi</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 flex items-center gap-2 uppercase tracking-wide">
                                            Live Test Preview
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        </label>
                                        <div class="bg-[#0f172a] border border-slate-600 rounded-lg p-4 flex flex-col justify-center min-h-[70px]">
                                            <span id="nomor_surat_preview" class="text-sm font-mono font-bold text-teal-400 block">-</span>
                                            <span id="nomor_surat_preview_error" class="text-[10px] text-rose-450 mt-1 font-semibold block"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Daftar Pengurus Tabel CRUD -->
                            <div class="bg-[#1e293b]/80 border border-slate-700/60 rounded-xl overflow-hidden shadow-sm">
                                <div class="p-4 border-b border-slate-700/60 flex items-center justify-between flex-wrap gap-2 bg-[#0f172a]/40">
                                    <span class="text-sm font-bold text-white">Daftar Dewan Pengurus (Penandatangan)</span>
                                </div>
                                <input type="hidden" id="kop_letter_signers" name="kop_letter_signers" value="<?= (string) esc((string) ($strSettings['kop_letter_signers'] ?? '[]')) ?>">
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse" id="signers_table">
                                        <thead>
                                            <tr class="bg-[#0f172a] border-b border-slate-700/60">
                                                <th class="p-3 font-bold text-[10px] text-slate-400 uppercase tracking-wider">Nama & Jabatan</th>
                                                <th class="p-3 font-bold text-[10px] text-slate-400 uppercase tracking-wider text-center">Tipe Dok</th>
                                                <th class="p-3 font-bold text-[10px] text-slate-400 uppercase tracking-wider text-center">Prioritas</th>
                                                <th class="p-3 font-bold text-[10px] text-slate-400 uppercase tracking-wider text-center">Aktif</th>
                                                <th class="p-3 font-bold text-[10px] text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-700/60" id="signers_tbody">
                                            <!-- JS populated -->
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Add form -->
                                <div class="p-4 bg-[#0f172a] border-t border-slate-700/60">
                                    <span class="text-[10px] font-bold text-teal-400 uppercase tracking-widest block mb-2">Tambah Pengurus Baru</span>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-bold text-slate-500 block uppercase">Pilih User (Opsional)</label>
                                            <select id="active_user_select" onchange="autoFillSignerFromUser()" class="w-full bg-[#1e293b] border border-slate-600 rounded px-2 py-1.5 text-white text-xs outline-none focus:border-teal-500 transition-all cursor-pointer">
                                                <option value="">-- Pilih User Aktif --</option>
                                                <?php if (!empty($activeUsers)): ?>
                                                    <?php foreach ($activeUsers as $u): ?>
                                                        <option value="<?= (string) esc((string) $u->username) ?>" data-email="<?= (string) esc((string) $u->email) ?>" data-id="<?= (string) esc((string) $u->id) ?>"><?= (string) esc((string) $u->username) ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-bold text-slate-500 block uppercase">Nama & Jabatan</label>
                                            <div class="flex gap-2">
                                                <input type="text" id="add_signer_name" placeholder="Nama..." class="w-1/2 bg-[#1e293b] border border-slate-600 rounded px-2 py-1.5 text-white text-xs outline-none focus:border-teal-500 transition-all">
                                                <input type="text" id="add_signer_role" placeholder="Jabatan..." class="w-1/2 bg-[#1e293b] border border-slate-600 rounded px-2 py-1.5 text-white text-xs outline-none focus:border-teal-500 transition-all">
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-bold text-slate-500 block uppercase">Tipe Dokumen</label>
                                            <select id="add_signer_type" class="w-full bg-[#1e293b] border border-slate-600 rounded px-2 py-1.5 text-white text-xs outline-none focus:border-teal-500 transition-all cursor-pointer">
                                                <option value="default">Default (Semua)</option>
                                                <option value="resign">Pengunduran Diri</option>
                                                <option value="loan">Pinjaman</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[9px] font-bold text-slate-500 block uppercase">Prioritas</label>
                                            <input type="number" id="add_signer_priority" value="0" min="0" max="100" class="w-full bg-[#1e293b] border border-slate-600 rounded px-2 py-1.5 text-white text-xs outline-none focus:border-teal-500 transition-all font-mono">
                                        </div>
                                        <div>
                                            <button type="button" onclick="addNewSignerRow()" class="w-full bg-teal-500 hover:bg-teal-600 text-white rounded px-3 py-1.5 font-bold text-xs transition-colors flex items-center justify-center gap-1 shadow-md cursor-pointer">
                                                + Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Action Bar (Bottom of the tab content container) -->
                <div class="sticky bottom-0 left-0 right-0 bg-[#0f172a]/95 backdrop-blur-md border-t border-slate-700/80 p-4 z-20 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-[0_-10px_30px_rgba(0,0,0,0.5)]">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="resetFormToDefault()" class="px-4 py-2 rounded border border-slate-600 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors text-xs font-bold cursor-pointer" title="Batalkan perubahan dan kembali ke setelan server">
                            Reset / Batal
                        </button>
                        <span id="unsaved-indicator" class="text-[10px] text-amber-400 font-semibold hidden animate-pulse bg-amber-500/10 px-2 py-1 rounded border border-amber-500/20">Perubahan belum disimpan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="triggerSystemCacheClear('config')" class="px-3 py-2 rounded bg-slate-800 border border-slate-700 hover:border-slate-500 text-slate-300 text-xs font-bold transition-colors cursor-pointer hidden md:flex items-center gap-1" title="Clear Configuration Cache">
                            <span class="material-symbols-outlined text-sm">refresh</span> Cache
                        </button>
                        <button type="submit" id="submit-btn" class="bg-teal-500 hover:bg-teal-400 text-slate-900 font-extrabold text-xs px-6 py-2.5 rounded shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all flex items-center gap-2 cursor-pointer group">
                            <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform" id="submit-icon">save</span>
                            <span id="submit-text">Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==========================================
// 1. Core State & LocalStorage Versioning
// ==========================================
const serverHash = document.getElementById('server_state_hash').value;
const serverTime = parseInt(document.getElementById('server_timestamp').value, 10);
const formEl = document.getElementById('settingsForm');
const unsavedIndicator = document.getElementById('unsaved-indicator');
let isSubmitting = false;

function saveDraftToStorage() {
    if (isSubmitting) return; // don't save draft if we are submitting
    const formData = new FormData(formEl);
    const draft = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'kop_logo' && key !== 'csrf_test_name') { // skip files and csrf
            draft[key] = value;
        }
    }
    
    // Checkboxes (like direct_loan_enabled) won't be in formData if unchecked
    const checkboxes = formEl.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
        if (cb.name && cb.name !== 'toggle') draft[cb.name] = cb.checked ? cb.value : '0';
    });

    const payload = {
        hash: serverHash,
        time: serverTime,
        data: draft
    };
    localStorage.setItem('ksp_settings_draft', JSON.stringify(payload));
    unsavedIndicator.classList.remove('hidden');
}

function loadDraftFromStorage() {
    const raw = localStorage.getItem('ksp_settings_draft');
    if (!raw) return;
    try {
        const payload = JSON.parse(raw);
        // Versioning check: If server hash changed or server time is newer than draft time by a large margin (e.g. 5 minutes)
        if (payload.hash !== serverHash) {
            console.warn("Server state has changed since last draft. Discarding stale draft.");
            localStorage.removeItem('ksp_settings_draft');
            return;
        }
        
        // Restore values
        for (let key in payload.data) {
            const el = formEl.querySelector(`[name="${key}"]`);
            if (el) {
                if (el.type === 'checkbox') {
                    el.checked = payload.data[key] === el.value;
                } else {
                    el.value = payload.data[key];
                }
            }
        }
        
        // specific for signers json
        if (payload.data['kop_letter_signers']) {
            signersData = JSON.parse(payload.data['kop_letter_signers']);
            renderSignersTable();
        }

        unsavedIndicator.classList.remove('hidden');
        updatePreview(); // update string preview
        
    } catch (e) {
        console.error("Error loading draft", e);
        localStorage.removeItem('ksp_settings_draft');
    }
}

function resetFormToDefault() {
    if (confirm("Yakin ingin membatalkan semua perubahan dan kembali ke data server?")) {
        localStorage.removeItem('ksp_settings_draft');
        window.location.reload();
    }
}

// Bind change events to save draft
formEl.addEventListener('input', () => saveDraftToStorage());
formEl.addEventListener('change', () => saveDraftToStorage());

// ==========================================
// 2. Tab Navigation & A11y & Mobile Dropdown
// ==========================================
function switchTab(tabId) {
    if(window.location.hash !== '#' + tabId) {
        history.replaceState(null, null, '#' + tabId);
    }
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('block');
    });
    // Deactivate all buttons
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-selected', 'false');
    });
    
    // Show target tab
    const targetPane = document.getElementById('tab-' + tabId);
    if (targetPane) {
        targetPane.classList.remove('hidden');
        targetPane.classList.add('block');
        // focus first input for a11y
        const firstInput = targetPane.querySelector('input, select, textarea, button');
        if (firstInput) firstInput.focus();
    }
    
    // Activate target buttons (desktop & mobile)
    const targetBtns = document.querySelectorAll(`.tab-btn[onclick="switchTab('${tabId}')"]`);
    targetBtns.forEach(btn => {
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
    });
}

// ==========================================
// 3. Extensible Custom Validator (Cross-Tab)
// ==========================================
function validateForm() {
    let isValid = true;
    let errorTabId = null;
    let errorEl = null;

    // Reset previous errors
    document.querySelectorAll('.error-msg').forEach(el => {
        el.classList.add('hidden');
        el.innerText = '';
        const input = el.previousElementSibling;
        if(input) input.classList.remove('border-rose-500', 'ring-rose-500/50');
    });

    const showError = (inputId, msg) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        const errSpan = input.nextElementSibling;
        if (errSpan && errSpan.classList.contains('error-msg')) {
            errSpan.innerText = msg;
            errSpan.classList.remove('hidden');
            input.classList.add('border-rose-500', 'ring-rose-500/50');
        }
        isValid = false;
        if (!errorTabId) {
            const pane = input.closest('.tab-pane');
            if (pane) errorTabId = pane.getAttribute('data-tab-id');
            errorEl = input;
        }
    };

    // Rule 1: Bunga Flat limit (Example)
    const bungaJenis = document.getElementById('kop_bunga_pinjaman_jenis').value;
    const bungaPersen = parseFloat(document.getElementById('kop_bunga_pinjaman_persen').value || 0);
    if (bungaJenis === 'flat' && bungaPersen > 3.0) { // e.g. arbitrary limit for demo
        showError('kop_bunga_pinjaman_persen', 'Bunga Flat maksimal 3.0%');
    }

    // Rule 2: Jasa limit
    const jasaJenis = document.querySelector('[name="kop_jasa_pinjaman_jenis"]').value;
    const jasaVal = parseFloat(document.getElementById('kop_jasa_pinjaman_nominal').value || 0);
    if (jasaJenis === 'persentase' && jasaVal > bungaPersen && bungaPersen > 0) {
        showError('kop_jasa_pinjaman_nominal', 'Persentase jasa tidak boleh melebihi persentase bunga utama');
    }

    // Navigation on error
    if (!isValid && errorTabId) {
        switchTab(errorTabId);
        if(errorEl) errorEl.focus();
    }
    
    return isValid;
}

// Form Submit Handler
function handleFormSubmit(e) {
    if (!validateForm()) {
        e.preventDefault();
        return false;
    }

    // Failsafe timeout logic & Loading state
    isSubmitting = true;
    const btn = document.getElementById('submit-btn');
    const icon = document.getElementById('submit-icon');
    const txt = document.getElementById('submit-text');
    
    // Store original
    const origIcon = icon.innerText;
    const origTxt = txt.innerText;
    
    // Set loading
    btn.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
    icon.classList.add('animate-spin');
    icon.innerText = 'sync';
    txt.innerText = 'Menyimpan...';

    // Set 15s failsafe
    setTimeout(() => {
        if(isSubmitting) {
            btn.classList.remove('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
            icon.classList.remove('animate-spin');
            icon.innerText = origIcon;
            txt.innerText = origTxt;
            alert("Koneksi ke server memakan waktu lebih lama dari biasanya. Harap periksa koneksi Anda atau coba tekan simpan lagi.");
            isSubmitting = false;
        }
    }, 15000);

    // Clear draft on successful submit (handled by browser navigation usually, but we clear it now since we're leaving)
    localStorage.removeItem('ksp_settings_draft');
    return true;
}

// Unsaved changes warning
window.addEventListener('beforeunload', (e) => {
    if (!unsavedIndicator.classList.contains('hidden') && !isSubmitting) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Keyboard Shortcut Ctrl+S
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if(document.activeElement) document.activeElement.blur();
        if(validateForm()) {
            formEl.submit();
        }
    }
});


// ==========================================
// 4. Component Scripts (Preview, Table)
// ==========================================
// Logo Preview
document.getElementById('kop_logo').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) { document.getElementById('logo_preview').src = e.target.result; }
        reader.readAsDataURL(file);
    }
});

// Debounced Format Preview
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
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' },
            body: new URLSearchParams({ 'format': formatVal, 'kode': 'RE' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                previewEl.textContent = data.preview;
                previewEl.classList.remove('text-rose-450');
                previewErrorEl.textContent = '';
            } else {
                previewEl.textContent = 'Format Error';
                previewEl.classList.add('text-rose-450');
                previewErrorEl.textContent = data.error;
            }
        }).catch(err => {
            // Ignore network errors in preview silently to avoid spam
        });
    }, 400);
}
formatInput.addEventListener('input', updatePreview);

// Dynamic Signers Table CRUD
const signersInput = document.getElementById('kop_letter_signers');
const signersTbody = document.getElementById('signers_tbody');
let signersData = [];

function initSignersData() {
    try { signersData = JSON.parse(signersInput.value || '[]'); } catch(e) { signersData = []; }
}

function renderSignersTable() {
    signersTbody.innerHTML = '';
    if (signersData.length === 0) {
        signersTbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-slate-500 italic text-xs">Belum ada pengurus penanda tangan.</td></tr>`;
        return;
    }
    signersData.sort((a,b) => (b.priority || 0) - (a.priority || 0));
    signersData.forEach(signer => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-800/30 transition-colors group';
        
        const badgeClass = signer.letter_type === 'resign' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' 
            : (signer.letter_type === 'loan' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-slate-700/30 text-slate-400 border-slate-600');

        tr.innerHTML = `
            <td class="p-3">
                <div class="font-bold text-white text-xs">${esc(signer.name)}</div>
                <div class="text-[10px] text-slate-400">${esc(signer.role)}</div>
            </td>
            <td class="p-3 text-center">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase ${badgeClass}">${esc(signer.letter_type)}</span>
            </td>
            <td class="p-3 text-center font-mono text-teal-400 text-xs font-bold">${signer.priority}</td>
            <td class="p-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" ${signer.is_active ? 'checked' : ''} onchange="toggleSignerActive('${signer.signer_id}')">
                    <div class="w-7 h-3.5 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full after:absolute after:top-[2px] after:left-[2px] after:bg-white peer-checked:after:bg-white after:rounded-full after:h-2.5 after:w-2.5 after:transition-all peer-checked:bg-teal-500"></div>
                </label>
            </td>
            <td class="p-3 text-right opacity-50 group-hover:opacity-100 transition-opacity">
                <button type="button" onclick="deleteSignerRow('${signer.signer_id}')" class="text-rose-400 hover:text-rose-300 p-1 cursor-pointer"><span class="material-symbols-outlined text-[18px]">delete</span></button>
            </td>
        `;
        signersTbody.appendChild(tr);
    });
}

window.toggleSignerActive = function(id) {
    signersData = signersData.map(s => { if(s.signer_id === id) s.is_active = !s.is_active; return s; });
    saveSignersJson();
};
window.deleteSignerRow = function(id) {
    if(confirm('Hapus dewan pengurus ini?')) {
        signersData = signersData.filter(s => s.signer_id !== id);
        saveSignersJson();
        renderSignersTable();
    }
};
function saveSignersJson() {
    signersInput.value = JSON.stringify(signersData);
    saveDraftToStorage(); // trigger unsaved changes
}
window.autoFillSignerFromUser = function() {
    const sel = document.getElementById('active_user_select');
    if(sel.value) document.getElementById('add_signer_name').value = sel.value;
};
window.addNewSignerRow = function() {
    const name = document.getElementById('add_signer_name').value.trim();
    const role = document.getElementById('add_signer_role').value.trim();
    const type = document.getElementById('add_signer_type').value;
    const prio = parseInt(document.getElementById('add_signer_priority').value) || 0;
    if(!name || !role) return alert('Isi Nama dan Jabatan');

    signersData.push({
        schema_version: 1, signer_id: 'sig_' + Date.now(), name: name, role: role,
        letter_type: type, is_active: true, priority: prio, user_id: null
    });
    saveSignersJson();
    renderSignersTable();
    
    // reset
    document.getElementById('add_signer_name').value = '';
    document.getElementById('add_signer_role').value = '';
    document.getElementById('add_signer_priority').value = '0';
};
function esc(str) { return str ? str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : ''; }

window.triggerSystemCacheClear = function(type) {
    if(confirm('Bersihkan cache sistem?')) {
        fetch(`<?= base_url('admin/cooperative/settings/clear-cache') ?>?type=${type}`, { headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(res => res.json()).then(data => alert(data.message)).catch(() => alert('Network error'));
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initSignersData();
    renderSignersTable();
    updatePreview();
    loadDraftFromStorage();
    
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        switchTab(hash);
    }
});

window.addEventListener('hashchange', () => {
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        switchTab(hash);
    }
});
</script>
<?= $this->endSection() ?>
