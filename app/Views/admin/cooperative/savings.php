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
        <span class="text-xs text-slate-500 font-semibold font-mono">Savings Queue</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Verifikasi Simpanan Anggota</h1>
        <p class="text-slate-400 text-sm">Validasi pengajuan setoran simpanan wajib/pokok/sukarela dan permintaan penarikan sukarela anggota.</p>
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

    <!-- Transactions Table -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">Antrean Mutasi Simpanan</h3>
            <span class="px-2.5 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                Mutasi Kas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6">Anggota</th>
                        <th class="py-4 px-6 text-center">Jenis Simpanan</th>
                        <th class="py-4 px-6 text-center">Tipe Aksi</th>
                        <th class="py-4 px-6 text-right">Nominal</th>
                        <th class="py-4 px-6 text-center">Bukti Bayar</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6">Keterangan</th>
                        <th class="py-4 px-6 text-right w-52">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($savingsList)) : ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 font-semibold">Belum ada pengajuan mutasi simpanan.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($savingsList as $s) : ?>
                            <tr class="hover:bg-slate-950/30 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-bold text-white block"><?= (string) esc($s['username']) ?></span>
                                    <span class="text-[10px] text-slate-500 block font-mono"><?= (string) esc($s['nomor_anggota']) ?></span>
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-indigo-300 uppercase text-xs">
                                    <?= (string) esc($s['jenis_simpanan']) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($s['tipe_transaksi'] === 'setoran') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">Setoran</span>
                                    <?php else : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider">Penarikan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-white">
                                    Rp <?= number_format($s['nominal'], 2, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($s['bukti_transfer']) : ?>
                                        <a href="<?= base_url('uploads/bukti_setoran/' . $s['bukti_transfer']) ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 font-bold underline">
                                            Lihat Bukti
                                        </a>
                                    <?php else : ?>
                                        <span class="text-slate-600 italic text-xs">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($s['status'] === 'pending') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/10 text-slate-400 border border-slate-800 uppercase">Pending</span>
                                    <?php elseif ($s['status'] === 'approved') : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Disetujui</span>
                                    <?php else : ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-400 max-w-xs truncate" title="<?= (string) esc($s['keterangan']) ?>">
                                    <?= (string) esc($s['keterangan']) ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <?php if ($s['status'] === 'pending') : ?>
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Approve Form -->
                                            <form action="<?= base_url('admin/cooperative/approve-saving/' . $s['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin menyetujui mutasi simpanan ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-400 hover:text-emerald-300 transition-all cursor-pointer">
                                                    Setujui
                                                </button>
                                            </form>
                                            <!-- Reject Form -->
                                            <form action="<?= base_url('admin/cooperative/reject-saving/' . $s['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin menolak mutasi simpanan ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-xs text-slate-600 italic">Terproses</span>
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
