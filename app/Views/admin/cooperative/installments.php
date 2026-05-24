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
        <span class="text-xs text-slate-500 font-semibold font-mono">Installments Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Verifikasi Cicilan Anggota</h1>
        <p class="text-slate-400 text-sm">Validasi bukti transfer pembayaran angsuran kredit bulanan anggota koperasi.</p>
    </div>

    <!-- Info Box for Installment Sync -->
    <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs sm:text-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="font-bold block text-white mb-0.5">Integrasi Transaksi Angsuran & Rekonsiliasi Otomatis</strong>
            Persetujuan cicilan akan memicu rekonsiliasi:
            <ul class="list-disc pl-5 mt-1 space-y-0.5">
                <li>Menghasilkan entri bayar lunas parsial di `debt_payments` dan `receivable_payments`.</li>
                <li>Menghitung sisa nominal hutang dan memperbarui status pinjaman koperasi (`kop_pinjaman`, `debts`, dan `receivables`) menjadi `paid` (lunas) jika seluruh angsuran terpenuhi.</li>
            </ul>
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

    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <div class="space-y-8">
        
        <!-- Manual Installment Form Row -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-white tracking-tight">Catat Angsuran Manual</h3>
                <p class="text-xs text-slate-500">Mendaftarkan bukti setoran angsuran anggota secara langsung.</p>
            </div>

            <form action="<?= base_url('admin/cooperative/installments/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start">
                    <div class="space-y-2">
                        <label for="pinjaman_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih Pinjaman Aktif</label>
                        <select id="pinjaman_id" name="pinjaman_id" required class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white font-semibold outline-none text-sm cursor-pointer" onchange="updateInstallmentSuggestion(this)">
                            <option value="" disabled selected>-- Pilih Pinjaman Anggota --</option>
                            <?php if (!empty($activeLoans)) : ?>
                                <?php foreach ($activeLoans as $loan) : ?>
                                    <?php 
                                        $monthlyPayment = floatval($loan['nominal_total']) / intval($loan['tenor_bulan']);
                                    ?>
                                    <option value="<?= $loan['id'] ?>" data-flat="<?= $monthlyPayment ?>" data-tenor="<?= $loan['tenor_bulan'] ?>">
                                        <?= (string) esc($loan['username']) ?> (<?= (string) esc($loan['nomor_anggota']) ?>) - Rp <?= number_format($loan['nominal_total'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="" disabled>Tidak ada pinjaman aktif</option>
                            <?php endif; ?>
                        </select>
                        <p class="text-[10px] text-slate-500 leading-relaxed">Pilih pinjaman anggota yang berstatus aktif.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="nominal_bayar" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nominal Bayar (Rp)</label>
                        <input type="number" id="nominal_bayar" name="nominal_bayar" required min="1" step="0.01" placeholder="Contoh: 500000" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
                        <p class="text-[10px] text-slate-500 leading-relaxed">Nominal setoran pembayaran angsuran.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="tujuan_dana" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tujuan Kas Penerima</label>
                        <select id="tujuan_dana" name="tujuan_dana" required class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white font-semibold outline-none text-sm cursor-pointer">
                            <option value="kas_utama" selected>Kas Utama Koperasi</option>
                            <option value="dana_talangan">Dana Talangan</option>
                        </select>
                        <p class="text-[10px] text-slate-500 leading-relaxed">Dana angsuran akan dicatat masuk ke kas ini.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="bukti_bayar" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Bukti Bayar (Opsional)</label>
                        <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/*" class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 text-slate-400 text-xs font-semibold cursor-pointer file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 file:cursor-pointer hover:file:bg-slate-700">
                        <p class="text-[10px] text-slate-500 leading-relaxed">Unggah file struk transfer bank (jika ada).</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="w-full md:w-auto px-8 py-3 bg-linear-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-emerald-600/10 cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Simpan Angsuran Manual
                    </button>
                </div>
            </form>
        </div>

        <!-- Installments List Table Row -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white tracking-tight">Antrean Pengajuan Pembayaran Angsuran</h3>
                <span class="px-2.5 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                    Bukti Transfer
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                            <th class="py-4 px-6">Peminjam</th>
                            <th class="py-4 px-6 text-right">Pokok Pinjaman</th>
                            <th class="py-4 px-6 text-right">Nominal Transfer</th>
                            <th class="py-4 px-6 text-center">Bukti Transfer</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6">Tgl Submit</th>
                            <th class="py-4 px-6 text-right w-52">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                        <?php if (empty($installments)) : ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 font-semibold">Belum ada pengajuan angsuran kredit.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($installments as $inst) : ?>
                                <tr class="hover:bg-slate-950/30 transition-colors">
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-white block"><?= (string) esc($inst['username']) ?></span>
                                        <span class="text-[10px] text-slate-500 block font-mono"><?= (string) esc($inst['nomor_anggota']) ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-right font-semibold text-slate-400">
                                        Rp <?= number_format($inst['nominal_pinjaman'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-extrabold text-emerald-400">
                                        Rp <?= number_format($inst['nominal_pengajuan'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($inst['bukti_bayar']) : ?>
                                            <a href="<?= base_url('uploads/bukti_cicilan/' . $inst['bukti_bayar']) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 font-bold underline">
                                                Lihat Bukti
                                            </a>
                                        <?php else : ?>
                                            <span class="text-slate-600 italic text-xs">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($inst['status'] === 'pending') : ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/10 text-slate-400 border border-slate-800 uppercase">Pending</span>
                                        <?php elseif ($inst['status'] === 'approved') : ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Valid</span>
                                        <?php else : ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Ditolak</span>
                                            <?php if (!empty($inst['catatan_tolak'])) : ?>
                                                <p class="text-[10px] text-rose-400/70 mt-1 max-w-[180px] leading-snug italic" title="<?= esc($inst['catatan_tolak']) ?>">
                                                    "<?= esc(mb_strimwidth($inst['catatan_tolak'], 0, 60, '...')) ?>"
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500 font-semibold font-mono">
                                        <?= date('d M Y, H:i', strtotime($inst['created_at'])) ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if ($inst['status'] === 'pending') : ?>
                                            <div class="flex flex-col items-end gap-2">
                                                <!-- Approve Form -->
                                                <form action="<?= base_url('admin/cooperative/approve-installment/' . $inst['id']) ?>" method="post" class="flex flex-col gap-1 items-end" onsubmit="return confirm('Yakin mensahkan angsuran ini? Dana akan otomatis ditambahkan ke kas yang dipilih.');">
                                                    <?= csrf_field() ?>
                                                    <div class="flex items-center gap-1">
                                                        <select name="tujuan_dana" class="bg-slate-950 border border-slate-800 text-slate-300 text-[10px] rounded px-1 py-1 focus:outline-none" required title="Dana Masuk Ke">
                                                            <option value="">- Dana Masuk -</option>
                                                            <option value="kas_utama">Kas Utama</option>
                                                            <option value="dana_talangan">Dana Talangan</option>
                                                        </select>
                                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-400 hover:text-emerald-300 transition-all cursor-pointer">
                                                            Sah
                                                        </button>
                                                    </div>
                                                </form>
                                                <!-- Reject Form with Note -->
                                                <form action="<?= base_url('admin/cooperative/reject-installment/' . $inst['id']) ?>" method="post" id="reject-form-<?= $inst['id'] ?>" onsubmit="return validateRejectForm(<?= $inst['id'] ?>);">
                                                    <?= csrf_field() ?>
                                                    <button type="button" onclick="toggleRejectNote(<?= $inst['id'] ?>)" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                        Tolak
                                                    </button>
                                                    <div id="reject-note-<?= $inst['id'] ?>" class="hidden mt-2 space-y-2 animate-fade-in">
                                                        <textarea name="catatan_tolak" id="catatan-tolak-<?= $inst['id'] ?>" rows="2" required placeholder="Tulis alasan penolakan..." class="w-full min-w-[200px] bg-slate-950 border border-rose-500/30 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-rose-500/60 resize-none transition-all"></textarea>
                                                        <button type="submit" class="w-full py-1.5 text-[11px] font-bold rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 transition-all cursor-pointer">
                                                            Konfirmasi Tolak
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else : ?>
                                            <div class="flex flex-col items-end gap-1 text-xs text-slate-500">
                                                <span class="italic">Terproses</span>
                                                <?php if ($inst['status'] === 'approved') : ?>
                                                    <a href="<?= base_url('admin/cooperative/installments/receipt/' . $inst['id']) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-400 hover:text-indigo-300 font-bold uppercase tracking-wider border border-indigo-500/30 px-2 py-1 rounded bg-indigo-500/10 hover:bg-indigo-500/20 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                        </svg>
                                                        Cetak
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
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

<script>
    function updateInstallmentSuggestion(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (!selectedOption) return;

        const flatAmount = selectedOption.getAttribute('data-flat');
        const tenor = selectedOption.getAttribute('data-tenor');

        if (flatAmount) {
            // Set the suggested nominal payment (rounded to nearest integer for user friendly feel)
            document.getElementById('nominal_bayar').value = Math.round(parseFloat(flatAmount));
        }
        if (tenor) {
            document.getElementById('nominal_bayar').setAttribute('data-max', (parseFloat(flatAmount) * parseFloat(tenor)));
        }
    }

    function toggleRejectNote(id) {
        const noteDiv = document.getElementById('reject-note-' + id);
        if (noteDiv.classList.contains('hidden')) {
            noteDiv.classList.remove('hidden');
            document.getElementById('catatan-tolak-' + id).focus();
        } else {
            noteDiv.classList.add('hidden');
        }
    }

    function validateRejectForm(id) {
        const textarea = document.getElementById('catatan-tolak-' + id);
        if (!textarea.value.trim()) {
            textarea.focus();
            textarea.classList.add('border-rose-500');
            return false;
        }
        return confirm('Apakah Anda yakin menolak bukti angsuran ini?\n\nAlasan: ' + textarea.value.trim());
    }
</script>
<?= $this->endSection() ?>
