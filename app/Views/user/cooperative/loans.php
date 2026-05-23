<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 max-w-6xl mx-auto py-4 mt-4">
    
    <!-- Navigation Back links -->
    <div class="flex items-center justify-between">
        <a href="<?= base_url('cooperative') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Koperasi Saya
        </a>
        <span class="text-xs text-slate-500 font-semibold font-mono">Loans Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Pinjaman & Angsuran Saya</h1>
        <p class="text-slate-400 text-sm">Ajukan fasilitas pinjaman baru dengan bunga flat bulanan ringan, hitung simulasi, dan setor bukti transfer angsuran.</p>
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <?php if (isset($errors)) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-1">
            <strong class="font-bold text-white block mb-1">Gagal mengajukan pinjaman:</strong>
            <?php foreach ($errors as $error) : ?>
                <p>• <?= (string) esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Application and Simulator Form Column -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 sm:p-8 space-y-6 h-fit md:col-span-1">
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-white tracking-tight">Ajukan Pinjaman Baru</h3>
                <p class="text-xs text-slate-500">Hitung nilai pengembalian flat Anda di bawah ini secara seketika.</p>
            </div>

            <form action="<?= base_url('cooperative/request-loan') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>

                <div class="space-y-2">
                    <label for="nominal_pinjaman" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nominal Pinjaman</label>
                    <input type="number" id="nominal_pinjaman" name="nominal_pinjaman" required min="100000" placeholder="Min: Rp 100.000" oninput="runSimulation()" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
                </div>

                <div class="space-y-2">
                    <label for="tenor_bulan" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tenor Pembiayaan</label>
                    <select id="tenor_bulan" name="tenor_bulan" required onchange="runSimulation()" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl text-white text-xs font-bold outline-none focus:border-indigo-500 cursor-pointer">
                        <option value="3">3 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="12" selected>12 Bulan</option>
                        <option value="24">24 Bulan</option>
                        <option value="36">36 Bulan</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="keterangan" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tujuan Penggunaan</label>
                    <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Renovasi atau Modal Usaha" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
                </div>

                <!-- Simulation Widget Box -->
                <div class="p-4 bg-slate-950/80 border border-slate-900 rounded-2xl space-y-3.5 text-xs">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest block">Simulasi Perhitungan</span>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Aturan Bunga:</span>
                            <span class="font-bold text-white"><?= floatval($settings['kop_bunga_pinjaman_persen'] ?? '1.50') ?>% (<?= ucfirst($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') ?>) / <?= $settings['kop_bunga_pinjaman_periode'] ?? 'bulanan' ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Bunga Dibayar:</span>
                            <span class="font-bold text-slate-300"><?= ($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') === 'di_awal' ? 'Di Awal (Dipotong)' : 'Dicicil Bulanan' ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Jasa Layanan:</span>
                            <span id="sim_jasa_layanan" class="font-bold text-white">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Total Bunga:</span>
                            <span id="sim_total_bunga" class="font-bold text-rose-400">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Pencairan Bersih:</span>
                            <span id="sim_pencairan_bersih" class="font-bold text-indigo-400">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Total Pengembalian:</span>
                            <span id="sim_total_bayar" class="font-bold text-emerald-400">Rp 0</span>
                        </div>
                        <div class="border-t border-slate-900 my-2 pt-2 flex items-center justify-between font-bold text-white text-sm">
                            <span>Cicilan Per Bulan:</span>
                            <span id="sim_cicilan" class="text-indigo-400">Rp 0</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/10 cursor-pointer flex items-center justify-center gap-1.5">
                    Kirim Pengajuan Kredit
                </button>
            </form>
        </div>

        <!-- Loans Amortization Ledger Column -->
        <div class="space-y-6 md:col-span-2">
            
            <!-- Loan Lists -->
            <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-slate-900/60">
                    <h3 class="text-lg font-bold text-white tracking-tight">Fasilitas Pinjaman Kredit Saya</h3>
                </div>

                <div class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($loans)) : ?>
                        <div class="p-8 text-center text-slate-500 font-semibold">Anda belum memiliki riwayat pengajuan pinjaman kredit.</div>
                    <?php else : ?>
                        <?php foreach ($loans as $l) : ?>
                            <div class="p-6 space-y-6">
                                <!-- Top Row: Details -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <span class="text-xs text-slate-500 block">Dibuat: <?= date('d M Y', strtotime($l['created_at'])) ?></span>
                                        <div class="flex items-center gap-2">
                                            <strong class="text-white text-base">Pokok: Rp <?= number_format($l['nominal_pinjaman'], 0, ',', '.') ?></strong>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase"><?= (string) esc($l['tenor_bulan']) ?> Bulan</span>
                                        </div>
                                    </div>
                                    <div class="space-y-1 sm:text-right">
                                        <span class="text-xs text-slate-500 block">Total Pengembalian</span>
                                        <strong class="text-emerald-400 text-base block">Rp <?= number_format($l['nominal_total'], 0, ',', '.') ?></strong>
                                    </div>
                                    <div>
                                        <?php if ($l['status'] === 'pending') : ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-500/10 text-slate-400 border border-slate-800 uppercase block text-center">Pending Review</span>
                                        <?php elseif ($l['status'] === 'approved') : ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase block text-center animate-pulse">Aktif</span>
                                        <?php elseif ($l['status'] === 'paid') : ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase block text-center">Lunas</span>
                                        <?php else : ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase block text-center">Ditolak</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Amortization/Payment widgets ONLY if approved/paid -->
                                <?php if ($l['status'] === 'approved' || $l['status'] === 'paid') : ?>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-950/60 p-4 sm:p-5 rounded-2xl border border-slate-900/60">
                                        
                                        <!-- Installment Stats -->
                                        <div class="space-y-3">
                                            <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Cicilan Koperasi</h5>
                                            <div class="space-y-1.5 text-xs">
                                                <div class="flex items-center justify-between text-slate-400">
                                                    <span>Nilai Angsuran Flat:</span>
                                                    <strong class="text-white">Rp <?= number_format(floatval($l['nominal_total']) / intval($l['tenor_bulan']), 2, ',', '.') ?> / bulan</strong>
                                                </div>
                                                <div class="flex items-center justify-between text-slate-400">
                                                    <span>Terbayar Valid:</span>
                                                    <strong class="text-emerald-400">Rp <?= number_format($l['total_paid'], 2, ',', '.') ?> (<?= $l['approved_installments'] ?> Kali)</strong>
                                                </div>
                                                <div class="flex items-center justify-between text-slate-400">
                                                    <span>Antrean Verifikasi:</span>
                                                    <strong class="text-amber-400"><?= $l['pending_installments'] ?> cicilan</strong>
                                                </div>
                                                <div class="flex items-center justify-between text-slate-400">
                                                    <span>Sisa Hutang:</span>
                                                    <strong class="text-indigo-400 font-extrabold">Rp <?= number_format(floatval($l['nominal_total']) - floatval($l['total_paid']), 2, ',', '.') ?></strong>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Installment payment upload form -->
                                        <?php if ($l['status'] === 'approved') : ?>
                                            <div class="space-y-3">
                                                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Setor Bukti Transfer Angsuran</h5>
                                                
                                                <!-- Bank transfer guide reminder -->
                                                <p class="text-[9px] text-slate-500 leading-relaxed">Silakan transfer angsuran flat Anda ke salah satu rekening koperasi terdaftar, lalu unggah buktinya.</p>

                                                <form action="<?= base_url('cooperative/pay-installment/' . $l['id']) ?>" method="post" enctype="multipart/form-data" class="space-y-3">
                                                    <?= csrf_field() ?>
                                                    
                                                    <div class="flex gap-2">
                                                        <div class="w-1/3">
                                                            <label for="angsuran_ke_<?= $l['id'] ?>" class="text-[9px] text-slate-500 uppercase block mb-1">Angsuran Ke-</label>
                                                            <input type="number" id="angsuran_ke_<?= $l['id'] ?>" name="angsuran_ke" required min="1" max="<?= $l['tenor_bulan'] ?>" placeholder="1" class="w-full px-2 py-1.5 bg-slate-950 border border-slate-900 rounded-lg focus:border-indigo-500 text-white text-xs font-bold text-center">
                                                        </div>
                                                        <div class="w-2/3">
                                                            <label for="bukti_bayar_<?= $l['id'] ?>" class="text-[9px] text-slate-500 uppercase block mb-1">Bukti Transfer (Image)</label>
                                                            <input type="file" id="bukti_bayar_<?= $l['id'] ?>" name="bukti_bayar" required accept="image/*" class="w-full px-2 py-1 bg-slate-950 border border-slate-900 rounded-lg text-[10px] text-slate-400 outline-none file:mr-2 file:py-0.5 file:px-1.5 file:rounded file:border-0 file:text-[9px] file:font-bold file:bg-slate-800 file:text-indigo-400 hover:file:bg-slate-700 cursor-pointer">
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg text-xs transition-colors cursor-pointer shadow-md shadow-indigo-600/10">
                                                        Setor Bukti Cicilan
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else : ?>
                                            <div class="flex items-center justify-center p-4 bg-indigo-950/20 rounded-xl border border-indigo-500/20 text-indigo-400 font-bold text-xs">
                                                Fasilitas Pinjaman Lunas Terbayar
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Realtime Simulation Formula JS -->
<script>
    function runSimulation() {
        const nominalInput = document.getElementById('nominal_pinjaman');
        const tenorSelect = document.getElementById('tenor_bulan');
        
        const simTotalBunga = document.getElementById('sim_total_bunga');
        const simJasaLayanan = document.getElementById('sim_jasa_layanan');
        const simPencairanBersih = document.getElementById('sim_pencairan_bersih');
        const simTotalBayar = document.getElementById('sim_total_bayar');
        const simCicilan = document.getElementById('sim_cicilan');

        const nominal = parseFloat(nominalInput.value) || 0;
        const tenor = parseInt(tenorSelect.value) || 0;

        if (nominal <= 0 || tenor <= 0) {
            simTotalBunga.textContent = "Rp 0";
            simJasaLayanan.textContent = "Rp 0";
            simPencairanBersih.textContent = "Rp 0";
            simTotalBayar.textContent = "Rp 0";
            simCicilan.textContent = "Rp 0";
            return;
        }

        // Dynamic settings injected from PHP
        const bungaPersen = <?= floatval($settings['kop_bunga_pinjaman_persen'] ?? '1.50') ?>;
        const jenisBunga = "<?= esc($settings['kop_bunga_pinjaman_jenis'] ?? 'flat') ?>";
        const bungaPeriode = "<?= esc($settings['kop_bunga_pinjaman_periode'] ?? 'bulanan') ?>";
        const bungaOpsiBayar = "<?= esc($settings['kop_bunga_pinjaman_opsi_bayar'] ?? 'cicil') ?>";
        
        const jasaNominalSetting = <?= floatval($settings['kop_jasa_pinjaman_nominal'] ?? '0') ?>;
        const jasaJenis = "<?= esc($settings['kop_jasa_pinjaman_jenis'] ?? 'nominal_tetap') ?>";
        const jasaCaraBayar = "<?= esc($settings['kop_jasa_pinjaman_cara_bayar'] ?? 'cicil') ?>";

        // 1. Calculate Jasa
        let jasaTotal = 0;
        if (jasaJenis === 'persentase') {
            jasaTotal = nominal * (jasaNominalSetting / 100);
        } else {
            jasaTotal = jasaNominalSetting;
        }

        // 2. Adjust interest rate based on period
        const monthlyRate = (bungaPeriode === 'tahunan') ? (bungaPersen / 12) : bungaPersen;

        // 3. Calculate Bunga
        let bungaTotal = 0;
        if (jenisBunga === 'flat') {
            bungaTotal = nominal * (monthlyRate / 100) * tenor;
        } else { // efektif
            const monthlyPrincipal = nominal / tenor;
            for (let i = 0; i < tenor; i++) {
                let remaining = nominal - (i * monthlyPrincipal);
                bungaTotal += remaining * (monthlyRate / 100);
            }
        }

        // 4. Determine upfront vs monthly payments
        const jasaDiAwal = (jasaCaraBayar === 'di_awal') ? jasaTotal : 0.00;
        const jasaCicilan = (jasaCaraBayar === 'cicil') ? jasaTotal : 0.00;

        const bungaDiAwal = (bungaOpsiBayar === 'di_awal') ? bungaTotal : 0.00;
        const bungaCicilan = (bungaOpsiBayar === 'cicil') ? bungaTotal : 0.00;

        const payoutBersih = nominal - bungaDiAwal - jasaDiAwal;
        const totalBayar = nominal + bungaCicilan + jasaCicilan;
        const cicilan = totalBayar / tenor;

        // Formatting currency Indonesian Rupiah
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        simJasaLayanan.textContent = formatter.format(jasaTotal) + ' (' + (jasaCaraBayar === 'di_awal' ? 'Di Awal' : 'Dicicil') + ')';
        simTotalBunga.textContent = formatter.format(bungaTotal);
        simPencairanBersih.textContent = formatter.format(payoutBersih);
        simTotalBayar.textContent = formatter.format(totalBayar);
        simCicilan.textContent = formatter.format(cicilan);
    }

    // Call initially to render the 12-month default correctly
    window.onload = function() {
        runSimulation();
    };
</script>
<?= $this->endSection() ?>
