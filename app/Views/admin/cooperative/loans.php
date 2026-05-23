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
        <span class="text-xs text-slate-500 font-semibold font-mono">Loans Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Persetujuan Kredit Pinjaman</h1>
        <p class="text-slate-400 text-sm">Verifikasi pengajuan pinjaman anggota, hitung tenor cicilan flat bulanan, dan sahkan piutang koperasi.</p>
    </div>

    <!-- Info Box for Flat Rate Sync -->
    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs sm:text-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="font-bold block text-white mb-0.5">Sinkronisasi Keuangan Otomatis (Two-Way Sync)</strong>
            Persetujuan pinjaman akan otomatis menghasilkan entri di pembukuan utama:
            <ul class="list-disc pl-5 mt-1 space-y-0.5">
                <li>Buku Besar Utang Anggota (Creditor: "Koperasi Simpan Pinjam", Status: <span class="underline">unpaid</span>)</li>
                <li>Buku Besar Piutang Koperasi (Borrower: Nama Anggota, Status: <span class="underline">unpaid</span>)</li>
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

    <!-- Loans Table Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">Daftar Pengajuan Kredit Koperasi</h3>
            <span class="px-2.5 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                Bunga Flat 1.50%
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6">Anggota</th>
                        <th class="py-4 px-6 text-right">Nominal Pokok</th>
                        <th class="py-4 px-6 text-center">Tenor (Bulan)</th>
                        <th class="py-4 px-6 text-right">Bunga (1.50%)</th>
                        <th class="py-4 px-6 text-right">Total Pengembalian</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6">Catatan Anggota</th>
                        <th class="py-4 px-6 text-right w-52">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($loans)) : ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 font-semibold">Belum ada pengajuan pinjaman kredit.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($loans as $l) : ?>
                            <tr class="hover:bg-slate-950/30 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-bold text-white block"><?= (string) esc($l['username']) ?></span>
                                    <span class="text-[10px] text-slate-500 block font-mono"><?= (string) esc($l['nomor_anggota']) ?></span>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-white">
                                    Rp <?= number_format($l['nominal_pinjaman'], 2, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-indigo-300 text-xs">
                                    <?= (string) esc($l['tenor_bulan']) ?> Bulan
                                </td>
                                <td class="py-4 px-6 text-right font-semibold text-rose-400">
                                    Rp <?= number_format(floatval($l['nominal_total']) - floatval($l['nominal_pinjaman']), 2, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-emerald-400">
                                    Rp <?= number_format($l['nominal_total'], 2, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($l['status'] === 'pending') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/10 text-slate-400 border border-slate-800 uppercase">Pending</span>
                                    <?php elseif ($l['status'] === 'approved') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Aktif</span>
                                    <?php elseif ($l['status'] === 'paid') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">Lunas</span>
                                    <?php else : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-400 max-w-xs truncate" title="<?= (string) esc($l['keterangan']) ?>">
                                    <?= (string) esc($l['keterangan']) ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <?php if ($l['status'] === 'pending') : ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Approve Form -->
                                            <form action="<?= base_url('admin/cooperative/approve-loan/' . $l['id']) ?>" method="post" class="flex flex-col gap-1 items-end" onsubmit="return confirm('Yakin ingin mencairkan pinjaman ini? Saldo kas yang dipilih akan terpotong secara otomatis.');">
                                                <?= csrf_field() ?>
                                                <div class="flex items-center gap-1">
                                                    <select name="sumber_dana" class="bg-slate-950 border border-slate-800 text-slate-300 text-[10px] rounded px-1 py-1 focus:outline-none" required title="Sumber Dana Pencairan">
                                                        <option value="">- Sumber Dana -</option>
                                                        <option value="kas_utama">Kas Utama</option>
                                                        <option value="dana_talangan">Dana Talangan</option>
                                                    </select>
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-400 hover:text-emerald-300 transition-all cursor-pointer">
                                                        Sah
                                                    </button>
                                                </div>
                                            </form>
                                            <!-- Reject Form -->
                                            <form action="<?= base_url('admin/cooperative/reject-loan/' . $l['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin menolak pengajuan kredit pinjaman ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    <?php else : ?>
                                        <div class="text-xs text-slate-500 font-semibold space-y-0.5">
                                            <span class="block">Terproses</span>
                                            <?php if ($l['debt_id_fk']) : ?>
                                                <span class="text-[9px] text-slate-600 block">Synced to #<?= $l['debt_id_fk'] ?></span>
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
<?= $this->endSection() ?>
