<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<?php
    // Extract settings with defaults
    $bungaPersen     = floatval($settings['kop_bunga_pinjaman_persen'] ?? '1.50');
    $jenisBunga      = $settings['kop_bunga_pinjaman_jenis'] ?? 'flat';
    $bungaPeriode    = $settings['kop_bunga_pinjaman_periode'] ?? 'bulanan';
    $bungaOpsiBayar  = $settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil';
    $jasaNominal     = floatval($settings['kop_jasa_pinjaman_nominal'] ?? '0');
    $jasaJenis       = $settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap';
    $jasaCaraBayar   = $settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil';

    // Labels for display
    $labelJenisBunga = $jenisBunga === 'flat' ? 'Flat / Tetap' : 'Efektif (Menurun)';
    $labelPeriode    = $bungaPeriode === 'bulanan' ? 'per Bulan' : 'per Tahun';
    $labelBungaBayar = $bungaOpsiBayar === 'cicil' ? 'Dicicil bersama angsuran' : 'Dipotong di awal pencairan';
    $labelJasaJenis  = $jasaJenis === 'persentase' ? '% dari pinjaman' : 'Nominal tetap (Rp)';
    $labelJasaBayar  = $jasaCaraBayar === 'cicil' ? 'Dicicil bersama angsuran' : 'Dipotong di awal pencairan';
?>
<div class="space-y-6 relative animate-fade-in">
    
    <!-- Flash Messages & Validation Errors -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs space-y-1">
            <div class="flex items-center gap-2 font-bold mb-1">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                <span>Mohon perbaiki kesalahan berikut:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Active Settings Info Banner -->
    <div class="bg-indigo-500/5 border border-indigo-500/15 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-[11px] text-indigo-300 leading-relaxed">
            <strong class="font-extrabold block mb-1">Simulasi menggunakan pengaturan aktif sistem:</strong>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-slate-400">
                <span>Bunga: <strong class="text-indigo-300"><?= $bungaPersen ?>% <?= $labelPeriode ?></strong></span>
                <span>Jenis: <strong class="text-indigo-300"><?= $labelJenisBunga ?></strong></span>
                <span>Bayar Bunga: <strong class="text-indigo-300"><?= $labelBungaBayar ?></strong></span>
                <span>Jasa: <strong class="text-indigo-300"><?= $jasaNominal ?> (<?= $labelJasaJenis ?>)</strong></span>
            </div>
            <a href="<?= base_url('admin/cooperative/settings') ?>" class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 font-bold mt-1.5 transition-colors">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ubah Pengaturan
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-slate-950/40 border border-slate-900 rounded-2xl p-6 sm:p-8 relative overflow-hidden backdrop-blur-sm">
        
        <!-- Subtle Glow -->
        <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-3 mb-8">
            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-emerald-400 shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-white tracking-wide">Pemberian Pinjaman Langsung</h3>
                <p class="text-[11px] text-slate-500">Cairkan dana pinjaman instan kepada anggota aktif (langsung disetujui).</p>
            </div>
        </div>

        <form action="<?= base_url('admin/cooperative/loans/direct/store') ?>" method="POST" class="space-y-6" id="direct-loan-form">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Col 1: Form Inputs -->
                <div class="space-y-5">
                    
                    <!-- Anggota Penerima -->
                    <div class="space-y-1.5">
                        <label for="anggota_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Anggota Penerima</label>
                        <div class="relative">
                            <select name="anggota_id" id="anggota_id" required class="cursor-pointer w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-emerald-500/50 transition-all appearance-none">
                                <option value="" disabled selected>-- Pilih Anggota Aktif --</option>
                                <?php foreach ($members as $m) : ?>
                                    <option value="<?= $m['id'] ?>" <?= old('anggota_id') == $m['id'] ? 'selected' : '' ?>>
                                        <?= esc($m['username']) ?> (<?= esc($m['nomor_anggota']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Nominal Pinjaman -->
                    <div class="space-y-1.5">
                        <label for="nominal_pinjaman" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nominal Pinjaman (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-4 flex items-center text-xs text-slate-500 font-bold">Rp</span>
                            <input type="number" step="any" name="nominal_pinjaman" id="nominal_pinjaman" required min="10000" value="<?= old('nominal_pinjaman', '1000000') ?>" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-xs text-white font-semibold focus:outline-none focus:border-emerald-500/50 transition-all" placeholder="Contoh: 5000000">
                        </div>
                    </div>

                    <!-- Tenor Bulan -->
                    <div class="space-y-1.5">
                        <label for="tenor_bulan" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tenor (Bulan)</label>
                        <div class="relative">
                            <select name="tenor_bulan" id="tenor_bulan" required class="cursor-pointer w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-emerald-500/50 transition-all appearance-none">
                                <?php for($i = 1; $i <= 60; $i++): ?>
                                    <option value="<?= $i ?>" <?= old('tenor_bulan', '12') == $i ? 'selected' : '' ?>><?= $i ?> Bulan</option>
                                <?php endfor; ?>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Sumber Dana -->
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sumber Dana Kas Koperasi</label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative" id="label-kas-utama">
                                <input type="radio" name="sumber_dana" id="sumber_dana_utama" value="kas_utama" checked class="accent-emerald-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Kas Utama</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Saldo Operasional KSP</p>
                                </div>
                            </label>

                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative" id="label-dana-talangan">
                                <input type="radio" name="sumber_dana" id="sumber_dana_talangan" value="dana_talangan" class="accent-emerald-500">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Dana Talangan</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Pendanaan Cadangan</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Opsi Pembayaran Bunga -->
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pembayaran Bunga</label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative">
                                <input type="radio" name="bunga_opsi_bayar" id="bunga_cicil" value="cicil" <?= ($bungaOpsiBayar === 'cicil') ? 'checked' : '' ?> class="accent-emerald-500" onchange="calculateSimulation()">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Dicicil Bulanan</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Bunga dibayar setiap bulan bersama angsuran</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative">
                                <input type="radio" name="bunga_opsi_bayar" id="bunga_di_awal" value="di_awal" <?= ($bungaOpsiBayar === 'di_awal') ? 'checked' : '' ?> class="accent-emerald-500" onchange="calculateSimulation()">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Potong di Awal</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Bunga dipotong langsung saat pencairan</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Opsi Pembayaran Jasa -->
                    <?php if ($jasaNominal > 0) : ?>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pembayaran Jasa (<?= $jasaJenis === 'persentase' ? $jasaNominal . '%' : 'Rp ' . number_format($jasaNominal, 0, ',', '.') ?>)</label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative">
                                <input type="radio" name="metode_bayar_jasa" id="jasa_cicil" value="cicil" <?= ($jasaCaraBayar === 'cicil') ? 'checked' : '' ?> class="accent-emerald-500" onchange="calculateSimulation()">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Dicicil Bulanan</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Jasa dibayar setiap bulan bersama angsuran</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex-1 bg-slate-950 border border-slate-850 hover:border-slate-800 rounded-xl p-3.5 flex items-center gap-3 transition-all relative">
                                <input type="radio" name="metode_bayar_jasa" id="jasa_di_awal" value="di_awal" <?= ($jasaCaraBayar === 'di_awal') ? 'checked' : '' ?> class="accent-emerald-500" onchange="calculateSimulation()">
                                <div>
                                    <p class="text-xs font-bold text-slate-200">Potong di Awal</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Jasa dipotong langsung saat pencairan</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <?php else : ?>
                    <input type="hidden" name="metode_bayar_jasa" value="cicil">
                    <?php endif; ?>


                    <div class="space-y-1.5">
                        <label for="keterangan" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keterangan / Tujuan Pinjaman</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-emerald-500/50 transition-all resize-none" placeholder="Masukkan alasan atau keperluan pinjaman..."><?= old('keterangan') ?></textarea>
                    </div>

                </div>

                <!-- Col 2: Simulation & Preview Box -->
                <div class="flex flex-col justify-between">
                    <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 space-y-5 h-full flex flex-col justify-between">
                        
                        <div>
                            <div class="flex items-center gap-2 border-b border-slate-800/80 pb-3 mb-4">
                                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs font-extrabold text-white tracking-wide">Simulasi Pinjaman Langsung</span>
                            </div>

                            <div class="space-y-3">
                                <!-- Interest Info -->
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-450">Bunga <?= esc($labelJenisBunga) ?></span>
                                    <span class="text-slate-200 font-bold"><?= $bungaPersen ?>% <?= $labelPeriode ?></span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-450">Beban bunga total</span>
                                    <span class="text-emerald-400 font-bold" id="sim-bunga">Rp 0,00</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-450">Pembayaran bunga</span>
                                    <span class="text-slate-300 font-semibold" id="sim-label-bunga-bayar">-</span>
                                </div>

                                <!-- Service Fee Info -->
                                <div class="border-t border-slate-800/60 pt-3 space-y-3">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-450">Jasa pinjaman</span>
                                        <span class="text-slate-200 font-bold"><?= $jasaJenis === 'persentase' ? $jasaNominal . '%' : 'Rp ' . number_format($jasaNominal, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-450">Beban jasa total</span>
                                        <span class="text-amber-400 font-bold" id="sim-jasa">Rp 0,00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-450">Pembayaran jasa</span>
                                        <span class="text-slate-300 font-semibold" id="sim-label-jasa-bayar">-</span>
                                    </div>
                                </div>

                                <!-- Upfront Deductions (always rendered, toggled by JS) -->
                                <div id="sim-upfront-section" class="border-t border-slate-800/60 pt-3 space-y-3 hidden">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-rose-400 font-bold">Potongan di awal (total)</span>
                                        <span class="text-rose-400 font-extrabold" id="sim-upfront">Rp 0,00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-450">Dana diterima anggota</span>
                                        <span class="text-white font-extrabold" id="sim-payout">Rp 0,00</span>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div class="border-t border-slate-800/60 pt-3">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-450">Total pengembalian cicilan</span>
                                        <span class="text-white font-bold" id="sim-total">Rp 0,00</span>
                                    </div>
                                </div>

                                <div class="bg-emerald-500/5 border border-emerald-500/10 rounded-xl p-4 flex flex-col items-center justify-center text-center mt-4">
                                    <span class="text-[10px] text-emerald-400 uppercase tracking-wider font-extrabold mb-1">Estimasi Angsuran Bulanan</span>
                                    <h4 class="text-lg font-black text-white" id="sim-angsuran">Rp 0,00</h4>
                                    <span class="text-[9px] text-slate-500 mt-0.5">Pokok + bunga + jasa per bulan</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="mt-6 flex gap-3 w-full">
                            <a href="<?= base_url('admin/cooperative/loans') ?>" class="flex-1 text-center border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-slate-200 font-bold text-xs py-3 rounded-xl transition-all cursor-pointer">
                                Batal
                            </a>
                            <button type="submit" class="cursor-pointer flex-1 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-extrabold text-xs py-3 rounded-xl shadow-md shadow-emerald-950/20 hover:scale-[1.01] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Cairkan Pinjaman
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </form>
    </div>
</div>

<script>
    // Settings injected from PHP (base rates only, payment methods come from form)
    const CFG = {
        bungaPersen:     <?= $bungaPersen ?>,
        jenisBunga:      '<?= $jenisBunga ?>',
        bungaPeriode:    '<?= $bungaPeriode ?>',
        jasaNominal:     <?= $jasaNominal ?>,
        jasaJenis:       '<?= $jasaJenis ?>',
    };

    const inputNominal = document.getElementById("nominal_pinjaman");
    const selectTenor = document.getElementById("tenor_bulan");

    function formatRupiah(val) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(val);
    }

    function getRadioValue(name) {
        const el = document.querySelector('input[name="' + name + '"]:checked');
        return el ? el.value : 'cicil';
    }

    function calculateSimulation() {
        const nominal = parseFloat(inputNominal.value) || 0;
        const tenor = parseInt(selectTenor.value) || 12;

        // Read payment methods from form radio buttons
        const bungaOpsiBayar = getRadioValue('bunga_opsi_bayar');
        const metodeBayarJasa = getRadioValue('metode_bayar_jasa');

        // 1. Calculate monthly rate
        let monthlyRate = (CFG.bungaPeriode === 'tahunan') ? (CFG.bungaPersen / 12) : CFG.bungaPersen;

        // 2. Calculate total interest
        let bungaTotal = 0;
        if (CFG.jenisBunga === 'flat') {
            bungaTotal = nominal * (monthlyRate / 100) * tenor;
        } else {
            // Efektif (declining balance)
            let monthlyPrincipal = nominal / tenor;
            for (let i = 0; i < tenor; i++) {
                let remaining = nominal - (i * monthlyPrincipal);
                bungaTotal += remaining * (monthlyRate / 100);
            }
        }

        // 3. Calculate service fee (jasa)
        let jasaTotal = 0;
        if (CFG.jasaJenis === 'persentase') {
            jasaTotal = nominal * (CFG.jasaNominal / 100);
        } else {
            jasaTotal = CFG.jasaNominal;
        }

        // 4. Upfront vs installment splits based on FORM selection
        let bungaDiAwal = (bungaOpsiBayar === 'di_awal') ? bungaTotal : 0;
        let bungaCicilan = (bungaOpsiBayar === 'cicil') ? bungaTotal : 0;

        let jasaDiAwal = (metodeBayarJasa === 'di_awal') ? jasaTotal : 0;
        let jasaCicilan = (metodeBayarJasa === 'cicil') ? jasaTotal : 0;

        let upfrontTotal = bungaDiAwal + jasaDiAwal;
        let payoutAmount = nominal - upfrontTotal;

        // Total that member repays via monthly installments
        let totalRepay = nominal + bungaCicilan + jasaCicilan;
        let angsuranBulanan = tenor > 0 ? totalRepay / tenor : 0;

        // Update simulation display
        document.getElementById('sim-bunga').innerText = formatRupiah(bungaTotal);
        document.getElementById('sim-jasa').innerText = formatRupiah(jasaTotal);
        document.getElementById('sim-total').innerText = formatRupiah(totalRepay);
        document.getElementById('sim-angsuran').innerText = formatRupiah(angsuranBulanan);
        document.getElementById('sim-upfront').innerText = formatRupiah(upfrontTotal);
        document.getElementById('sim-payout').innerText = formatRupiah(payoutAmount);

        // Update dynamic labels
        document.getElementById('sim-label-bunga-bayar').innerText =
            bungaOpsiBayar === 'di_awal' ? 'Dipotong di awal pencairan' : 'Dicicil bersama angsuran';
        document.getElementById('sim-label-jasa-bayar').innerText =
            metodeBayarJasa === 'di_awal' ? 'Dipotong di awal pencairan' : 'Dicicil bersama angsuran';

        // Toggle upfront section visibility
        const upfrontSection = document.getElementById('sim-upfront-section');
        if (upfrontTotal > 0) {
            upfrontSection.classList.remove('hidden');
        } else {
            upfrontSection.classList.add('hidden');
        }
    }

    inputNominal.addEventListener("input", calculateSimulation);
    selectTenor.addEventListener("change", calculateSimulation);

    // Initial calculation on load
    document.addEventListener("DOMContentLoaded", calculateSimulation);
</script>
<?= $this->endSection() ?>
