<?php
/**
 * @var string $title
 * @var bool $is_member
 * @var array $member
 * @var float $wajibNominal
 * @var float $sosialNominal
 * @var int $wajibBatasHari
 * @var int $sosialBatasHari
 * @var array $bills
 * @var int $unpaidCount
 * @var float $totalUnpaidAmount
 * @var array $activeLoans
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 max-w-6xl mx-auto py-4 mt-4 animate-fade-in">

    <!-- Navigation Back Link -->
    <div class="flex items-center justify-between">
        <a href="<?= base_url('cooperative') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Koperasi Saya
        </a>
        <span class="text-xs text-tx-disabled font-semibold font-mono">Billing & Ledgers</span>
    </div>

    <!-- Message Banners -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>

    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-center gap-2 animate-fade-in">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        
        <!-- Unpaid Bills Count Card -->
        <div class="bg-base/40 border border-br-default rounded-2xl p-5 relative overflow-hidden backdrop-blur-sm">
            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-surface border border-br-default text-rose-400 shadow-inner">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] text-tx-disabled uppercase tracking-widest font-bold block">Tagihan Belum Lunas</span>
                    <strong class="text-2xl font-extrabold text-tx-primary tracking-tight block mt-0.5"><?= $unpaidCount ?> Tagihan</strong>
                </div>
            </div>
        </div>

        <!-- Total Unpaid Amount Card -->
        <div class="bg-base/40 border border-br-default rounded-2xl p-5 relative overflow-hidden backdrop-blur-sm">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl bg-surface border border-br-default text-indigo-400 shadow-inner">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2" />
                    </svg>
                </div>
                <div>
                    <span class="text-[10px] text-tx-disabled uppercase tracking-widest font-bold block">Total Tunggakan Iuran</span>
                    <strong class="text-2xl font-extrabold text-indigo-400 tracking-tight block mt-0.5">Rp <?= number_format($totalUnpaidAmount, 0, ',', '.') ?></strong>
                </div>
            </div>
        </div>

        <!-- Prepay Annual Button Card -->
        <div class="bg-base/40 border border-br-default rounded-2xl p-5 relative overflow-hidden backdrop-blur-sm flex flex-col justify-center">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <button onclick="openAnnualModal()" class="cursor-pointer bg-gradient-to-r from-emerald-500/20 to-teal-600/20 hover:from-emerald-500/30 hover:to-teal-600/30 text-emerald-400 border border-emerald-500/30 font-extrabold text-xs px-4 py-3 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Bayar Simpanan 1 Tahun Penuh
            </button>
        </div>
    </div>

    <!-- Main Content Layout Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Monthly Savings Status List -->
        <div class="lg:col-span-2 bg-surface/40 border border-br-default rounded-2xl overflow-hidden shadow-xl">
            <div class="p-6 border-b border-br-default flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-tx-primary tracking-tight">Status Simpanan Bulanan Saya</h3>
                    <p class="text-[11px] text-tx-disabled">Daftar iuran simpanan wajib & dana sosial sejak bergabung.</p>
                </div>
                <div class="text-right text-[10px] text-tx-secondary">
                    <span>Jatuh tempo setiap tanggal 1-7</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-300">
                    <thead class="bg-base/40 text-tx-disabled font-bold uppercase tracking-wider border-b border-br-default">
                        <tr>
                            <th class="px-6 py-4">Bulan & Tahun</th>
                            <th class="px-6 py-4">Simpanan Wajib (Rp <?= number_format($wajibNominal, 0, ',', '.') ?>)</th>
                            <th class="px-6 py-4">Dana Sosial (Rp <?= number_format($sosialNominal, 0, ',', '.') ?>)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-br-default/60">
                        <?php foreach ($bills as $b) : ?>
                            <tr class="hover:bg-surface/10 transition-colors">
                                <td class="px-6 py-4 font-bold text-tx-primary">
                                    <?= date('F', mktime(0, 0, 0, $b['bulan'], 10)) ?> <?= $b['tahun'] ?>
                                </td>
                                
                                <!-- Simpanan Wajib Status Column -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <?php if ($b['wajib_status'] === 'Lunas') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Lunas</span>
                                            <?php elseif ($b['wajib_status'] === 'Pending') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase animate-pulse">Pending</span>
                                            <?php elseif ($b['wajib_status'] === 'Menunggak') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Menunggak</span>
                                            <?php else : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-elevated text-tx-secondary border border-br-default uppercase">Belum Bayar</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($b['wajib_status'] === 'Belum Bayar' || $b['wajib_status'] === 'Menunggak') : ?>
                                            <button onclick="openPaymentModal('wajib', <?= $b['bulan'] ?>, <?= $b['tahun'] ?>, <?= $wajibNominal ?>)" class="cursor-pointer px-2 py-1 rounded bg-indigo-600/15 hover:bg-indigo-600/30 text-indigo-400 hover:text-tx-primary border border-indigo-500/20 font-bold transition-all text-[10px]">
                                                Bayar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Dana Sosial Status Column -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <?php if ($b['sosial_status'] === 'Lunas') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Lunas</span>
                                            <?php elseif ($b['sosial_status'] === 'Pending') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase animate-pulse">Pending</span>
                                            <?php elseif ($b['sosial_status'] === 'Menunggak') : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Menunggak</span>
                                            <?php else : ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-elevated text-tx-secondary border border-br-default uppercase">Belum Bayar</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($b['sosial_status'] === 'Belum Bayar' || $b['sosial_status'] === 'Menunggak') : ?>
                                            <button onclick="openPaymentModal('sosial', <?= $b['bulan'] ?>, <?= $b['tahun'] ?>, <?= $sosialNominal ?>)" class="cursor-pointer px-2 py-1 rounded bg-indigo-600/15 hover:bg-indigo-600/30 text-indigo-400 hover:text-tx-primary border border-indigo-500/20 font-bold transition-all text-[10px]">
                                                Bayar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Active Loans Schedule & Amortization -->
        <div class="space-y-6 lg:col-span-1">
            <div class="bg-surface/40 border border-br-default rounded-2xl p-6 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-tx-primary tracking-tight">Jadwal Angsuran Aktif</h3>
                    <p class="text-[11px] text-tx-disabled">Amortisasi pinjaman yang sedang berjalan.</p>
                </div>

                <?php if (empty($activeLoans)) : ?>
                    <div class="p-6 text-center text-tx-disabled font-semibold border border-dashed border-br-default rounded-xl text-xs">
                        Tidak ada fasilitas pinjaman aktif saat ini.
                    </div>
                <?php else : ?>
                    <?php foreach ($activeLoans as $loan) : ?>
                        <div class="space-y-4">
                            <div class="p-4 bg-base/60 border border-br-default/60 rounded-xl space-y-2 text-xs">
                                <div class="flex justify-between text-tx-secondary">
                                    <span>No Pinjaman:</span>
                                    <strong class="text-tx-primary">#<?= $loan['id'] ?></strong>
                                </div>
                                <div class="flex justify-between text-tx-secondary">
                                    <span>Pokok Awal:</span>
                                    <strong class="text-tx-primary">Rp <?= number_format($loan['nominal_pinjaman'], 0, ',', '.') ?></strong>
                                </div>
                                <div class="flex justify-between text-tx-secondary">
                                    <span>Tenor:</span>
                                    <strong class="text-tx-primary"><?= $loan['tenor_bulan'] ?> Bulan</strong>
                                </div>
                                <div class="flex justify-between text-tx-secondary border-t border-br-default/60 pt-2 font-bold text-indigo-400">
                                    <span>Angsuran Bulanan:</span>
                                    <strong>Rp <?= number_format($loan['angsuran_per_bulan'], 2, ',', '.') ?></strong>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[10px] text-tx-secondary font-bold uppercase tracking-wider block">Daftar Angsuran</span>
                                <div class="space-y-2 max-h-72 overflow-y-auto no-scrollbar border border-br-default rounded-xl p-2.5 bg-base/20">
                                    <?php foreach ($loan['amortization'] as $am) : ?>
                                        <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-surface/30 border border-br-default">
                                            <div class="space-y-0.5">
                                                <strong class="text-slate-200">Cicilan Ke-<?= $am['angsuran_ke'] ?></strong>
                                                <span class="text-[9px] text-tx-disabled block">Jatuh Tempo: <?= $am['due_date'] ?></span>
                                            </div>
                                            <div class="text-right">
                                                <strong class="text-tx-primary block text-[11px]">Rp <?= number_format($am['total'], 0, ',', '.') ?></strong>
                                                <?php if ($am['status'] === 'approved') : ?>
                                                    <span class="text-[8px] font-extrabold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 uppercase">Lunas</span>
                                                <?php elseif ($am['status'] === 'pending') : ?>
                                                    <span class="text-[8px] font-extrabold text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20 uppercase animate-pulse">Pending</span>
                                                <?php else : ?>
                                                    <span class="text-[8px] font-extrabold text-rose-400 bg-rose-500/10 px-1.5 py-0.5 rounded border border-rose-500/20 uppercase">Belum Lunas</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Single Month Payment Modal -->
<div id="paymentModal" class="fixed inset-0 z-50 hidden bg-base/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-md overflow-hidden shadow-2xl relative animate-scale-up">
        
        <div class="p-6 border-b border-br-default flex items-center justify-between">
            <h3 class="text-sm font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2" />
                </svg>
                Setor Pembayaran Simpanan
            </h3>
            <button onclick="closePaymentModal()" class="text-tx-disabled hover:text-tx-primary cursor-pointer transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="<?= base_url('cooperative/pay-saving-bill') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" id="modal_jenis" name="jenis_simpanan">
            <input type="hidden" id="modal_bulan" name="bulan">
            <input type="hidden" id="modal_tahun" name="tahun">
            <input type="hidden" id="modal_nominal" name="nominal">

            <div class="p-4 bg-base/60 rounded-xl space-y-1.5 text-tx-secondary border border-br-default">
                <div class="flex justify-between">
                    <span>Jenis Tagihan:</span>
                    <strong id="label_jenis" class="text-tx-primary"></strong>
                </div>
                <div class="flex justify-between">
                    <span>Bulan / Tahun:</span>
                    <strong id="label_periode" class="text-tx-primary"></strong>
                </div>
                <div class="flex justify-between border-t border-br-default/60 pt-2 font-bold text-indigo-400">
                    <span>Nominal Bayar:</span>
                    <strong id="label_nominal" class="text-sm"></strong>
                </div>
            </div>

            <!-- Rekening Transfer Info -->
            <div class="p-3.5 bg-indigo-500/5 border border-indigo-500/10 text-indigo-400 rounded-xl leading-relaxed text-[10px]">
                <strong class="font-bold block mb-1">Transfer Bank Rekening Koperasi:</strong>
                <span>Bank Mandiri: <strong>123-00-9876543-2</strong><br>A.N. Koperasi Simpan Pinjam</span>
            </div>

            <div class="space-y-1.5">
                <label for="bukti_transfer" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Unggah Bukti Transfer (Image)</label>
                <input type="file" id="bukti_transfer" name="bukti_transfer" required accept="image/*" class="w-full px-3 py-2 bg-base border border-br-default rounded-lg text-slate-300 outline-none file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600/15 file:text-indigo-400 hover:file:bg-indigo-600/30 cursor-pointer">
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-tx-primary font-bold rounded-xl shadow-lg transition-all cursor-pointer">
                Kirim Bukti Pembayaran
            </button>
        </form>
    </div>
</div>

<!-- Annual Prepayment Modal -->
<div id="annualModal" class="fixed inset-0 z-50 hidden bg-base/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-md overflow-hidden shadow-2xl relative animate-scale-up">
        
        <div class="p-6 border-b border-br-default flex items-center justify-between">
            <h3 class="text-sm font-bold text-tx-primary uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Prabayar Simpanan 1 Tahun
            </h3>
            <button onclick="closeAnnualModal()" class="text-tx-disabled hover:text-tx-primary cursor-pointer transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="<?= base_url('cooperative/pay-saving-bill') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
            <?= csrf_field() ?>
            <input type="hidden" name="jenis_simpanan" value="tahunan">
            <input type="hidden" name="bulan" value="">
            <input type="hidden" id="annual_nominal" name="nominal" value="<?= 12 * ($wajibNominal + $sosialNominal) ?>">

            <div class="space-y-1.5">
                <label for="annual_tahun" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Pilih Tahun Pembayaran</label>
                <select id="annual_tahun" name="tahun" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl text-tx-primary font-bold outline-none cursor-pointer focus:border-emerald-500">
                    <option value="<?= date('Y') ?>" selected>Tahun <?= date('Y') ?></option>
                    <option value="<?= date('Y') + 1 ?>">Tahun <?= date('Y') + 1 ?></option>
                </select>
            </div>

            <div class="p-4 bg-base/60 rounded-xl space-y-2 text-tx-secondary border border-br-default">
                <div class="flex justify-between">
                    <span>Simpanan Wajib (12 Bln):</span>
                    <strong class="text-tx-primary">Rp <?= number_format($wajibNominal * 12, 0, ',', '.') ?></strong>
                </div>
                <div class="flex justify-between">
                    <span>Dana Sosial (12 Bln):</span>
                    <strong class="text-tx-primary">Rp <?= number_format($sosialNominal * 12, 0, ',', '.') ?></strong>
                </div>
                <div class="flex justify-between border-t border-br-default/60 pt-2 font-bold text-emerald-400">
                    <span>Total Pembayaran:</span>
                    <strong class="text-sm">Rp <?= number_format(12 * ($wajibNominal + $sosialNominal), 0, ',', '.') ?></strong>
                </div>
            </div>

            <!-- Rekening Transfer Info -->
            <div class="p-3.5 bg-indigo-500/5 border border-indigo-500/10 text-indigo-400 rounded-xl leading-relaxed text-[10px]">
                <strong class="font-bold block mb-1">Transfer Bank Rekening Koperasi:</strong>
                <span>Bank Mandiri: <strong>123-00-9876543-2</strong><br>A.N. Koperasi Simpan Pinjam</span>
            </div>

            <div class="space-y-1.5">
                <label for="annual_bukti" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Unggah Bukti Transfer (Image)</label>
                <input type="file" id="annual_bukti" name="bukti_transfer" required accept="image/*" class="w-full px-3 py-2 bg-base border border-br-default rounded-lg text-slate-300 outline-none file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600/15 file:text-indigo-400 hover:file:bg-indigo-600/30 cursor-pointer">
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-tx-primary font-bold rounded-xl shadow-lg transition-all cursor-pointer">
                Kirim Bukti Prabayar 1 Tahun
            </button>
        </form>
    </div>
</div>

<script>
    // Month name translation array
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    function openPaymentModal(jenis, bulan, tahun, nominal) {
        document.getElementById('modal_jenis').value = jenis;
        document.getElementById('modal_bulan').value = bulan;
        document.getElementById('modal_tahun').value = tahun;
        document.getElementById('modal_nominal').value = nominal;

        document.getElementById('label_jenis').textContent = jenis === 'wajib' ? 'Simpanan Wajib' : 'Dana Sosial';
        document.getElementById('label_periode').textContent = monthNames[bulan - 1] + ' ' + tahun;
        
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        document.getElementById('label_nominal').textContent = formatter.format(nominal);

        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function openAnnualModal() {
        document.getElementById('annualModal').classList.remove('hidden');
    }

    function closeAnnualModal() {
        document.getElementById('annualModal').classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>
