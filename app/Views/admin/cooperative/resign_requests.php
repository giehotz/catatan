<?php
/**
 * @var array $requests
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
        <span class="text-xs text-slate-500 font-semibold font-mono">Resignation Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Persetujuan Pengunduran Diri</h1>
        <p class="text-slate-400 text-sm">Tinjau antrean permohonan pengunduran diri resmi, kelola pengembalian simpanan, dan verifikasi utang pinjaman.</p>
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

    <?php
        $pendingCount = 0;
        foreach ($requests as $r) {
            if ($r['status'] === 'pending') $pendingCount++;
        }
    ?>

    <!-- Resign Requests Table Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between gap-4 flex-wrap">
            <h3 class="text-lg font-bold text-white tracking-tight">Antrean Pengajuan Keluar Keanggotaan</h3>
            <div class="flex items-center gap-2">
                <?php if ($pendingCount > 0) : ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase tracking-wider animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <?= $pendingCount ?> PENDING ANTREAN
                    </span>
                <?php else : ?>
                    <span class="px-2.5 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                        Antrean Bersih
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6 text-center w-12">ID</th>
                        <th class="py-4 px-6">Anggota</th>
                        <th class="py-4 px-6">Alasan Keluar</th>
                        <th class="py-4 px-6 text-right">Total Tabungan</th>
                        <th class="py-4 px-6 text-right">Sisa Utang</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($requests)) : ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-semibold">Belum ada pengajuan pengunduran diri terdaftar.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($requests as $req) : ?>
                            <tr class="hover:bg-slate-950/30 transition-colors">
                                <td class="py-4 px-6 text-center font-semibold text-slate-550"><?= $req['id'] ?></td>
                                <td class="py-4 px-6">
                                    <div class="space-y-0.5">
                                        <span class="font-bold text-white block text-sm"><?= (string) esc($req['username']) ?></span>
                                        <span class="text-[10px] text-slate-500 font-mono block"><?= (string) esc($req['nomor_anggota']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 max-w-xs">
                                    <p class="text-xs text-slate-400 leading-relaxed line-clamp-2" title="<?= (string) esc($req['alasan_keluar']) ?>">
                                        "<?= (string) esc($req['alasan_keluar']) ?>"
                                    </p>
                                </td>
                                <td class="py-4 px-6 text-right font-semibold font-mono text-emerald-400 text-xs">
                                    Rp <?= number_format($req['total_simpanan'], 0, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-right font-semibold font-mono text-xs <?= $req['sisa_pinjaman'] > 0 ? 'text-rose-400' : 'text-slate-500' ?>">
                                    Rp <?= number_format($req['sisa_pinjaman'], 0, ',', '.') ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($req['status'] === 'pending') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                            Pending
                                        </span>
                                    <?php elseif ($req['status'] === 'approved') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                            Disetujui
                                        </span>
                                    <?php elseif ($req['status'] === 'rejected') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-rose-500/10 text-rose-450 border border-rose-500/20 uppercase tracking-wider" title="Alasan: <?= (string) esc($req['alasan_penolakan']) ?>">
                                            Ditolak
                                        </span>
                                    <?php elseif ($req['status'] === 'cancelled') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-slate-700/30 text-slate-400 border border-slate-800 uppercase tracking-wider">
                                            Batal
                                        </span>
                                    <?php else : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[9px] font-bold rounded-full bg-slate-800/60 text-slate-500 border border-slate-900 uppercase tracking-wider">
                                            Expired
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($req['status'] === 'pending') : ?>
                                            
                                            <!-- Tombol Tolak (Triggers rejection modal) -->
                                            <button type="button" 
                                                onclick="openRejectModal(<?= $req['id'] ?>, '<?= (string) esc($req['username']) ?>')"
                                                class="px-3 py-1.5 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                Tolak
                                            </button>

                                            <!-- Tombol Setujui -->
                                            <?php if ($req['sisa_pinjaman'] > 0) : ?>
                                                <!-- Disabled state since has remaining debts -->
                                                <button type="button" disabled
                                                    class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-800 bg-slate-900 text-slate-650 cursor-not-allowed"
                                                    title="Tidak bisa menyetujui: Anggota memiliki utang yang belum lunas.">
                                                    Setujui
                                                </button>
                                            <?php else : ?>
                                                <form action="<?= base_url('admin/cooperative/approve-resign/' . $req['id']) ?>" method="post" 
                                                    onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI pengunduran diri <?= (string) esc($req['username']) ?>? Tindakan ini akan menonaktifkan keanggotaan dan mencairkan sisa saldo tabungan sebesar Rp <?= number_format($req['total_simpanan'], 0, ',', '.') ?>.');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-450 hover:text-emerald-300 transition-all cursor-pointer">
                                                        Setujui
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                        <?php elseif ($req['status'] === 'approved') : ?>
                                            <a href="<?= base_url('cooperative/resign/letter/' . $req['id']) ?>" target="_blank"
                                                class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-350 font-bold hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Lihat Surat SK
                                            </a>
                                        <?php elseif ($req['status'] === 'rejected') : ?>
                                            <span class="text-[11px] text-slate-500 italic max-w-xs truncate" title="Alasan: <?= (string) esc($req['alasan_penolakan']) ?>">
                                                <strong>Alasan:</strong> <?= (string) esc($req['alasan_penolakan']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-600">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Premium Interactive Rejection Modal Dialog -->
<div id="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-300">
    <!-- Modal Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl transform scale-95 transition-all duration-300">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-850 flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Masukkan Alasan Penolakan</h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form content -->
        <form id="rejectForm" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>
            
            <p class="text-xs text-slate-400 leading-relaxed">
                Tolak permohonan pengunduran diri untuk anggota <strong id="rejectMemberName" class="text-indigo-400"></strong>. Anggota tersebut akan menerima pemberitahuan formal mengenai alasan penolakan ini secara tertulis di inbox.
            </p>

            <div class="space-y-1.5">
                <label for="alasan_penolakan" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea id="alasan_penolakan" name="alasan_penolakan" rows="4" required
                    class="w-full rounded-xl border border-slate-800 bg-slate-950/70 p-3 text-xs text-white placeholder-slate-650 focus:border-rose-500 focus:outline-hidden focus:ring-1 focus:ring-rose-500 transition-all"
                    placeholder="Contoh: Penolakan dilakukan karena Anggota masih memiliki data tagihan koperasi berjalan yang terlewat dalam verifikasi awal. Harap lunasi sisa angsuran bulan ini terlebih dahulu..."></textarea>
            </div>

            <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-850 mt-4">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/40 text-slate-400 hover:text-white text-xs font-bold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 active:bg-rose-700 text-white text-xs font-bold shadow-lg shadow-rose-600/15 transition-all cursor-pointer">
                    Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const nameSpan = document.getElementById('rejectMemberName');
    const textarea = document.getElementById('alasan_penolakan');

    function openRejectModal(id, username) {
        // Set action url dynamically
        form.action = `<?= base_url('admin/cooperative/reject-resign') ?>/${id}`;
        nameSpan.textContent = username;
        textarea.value = '';
        
        // Show modal smoothly
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.firstElementChild.classList.remove('scale-95');
    }

    function closeRejectModal() {
        // Hide modal smoothly
        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.firstElementChild.classList.add('scale-95');
    }

    // Close on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeRejectModal();
        }
    });
</script>
<?= $this->endSection() ?>
