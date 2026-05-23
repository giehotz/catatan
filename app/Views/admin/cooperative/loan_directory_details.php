<?php
/**
 * @var string $title
 * @var array  $loan      Full loan record with username, nomor_anggota
 * @var array  $schedule  Amortization schedule with status & record data
 * @var array  $summary   Aggregated summary metrics from LoanAmortizationService
 * @var bool   $isAdmin   Whether current user is admin/superadmin
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>

<div class="space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="<?= base_url('admin/cooperative/loans/directory') ?>" class="hover:text-emerald-400 transition-colors">Daftar Pinjaman</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-400"><?= (string) esc((string) ($loan['username'] ?? 'Detail')) ?></span>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Detail Pinjaman #<?= (string) esc((string) $loan['id']) ?></h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= base_url('admin/cooperative/loans/directory/' . $loan['id'] . '/excel') ?>" 
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 rounded-xl text-xs font-bold transition-all" id="btn_export_excel">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Ekspor Excel
            </a>
            <a href="<?= base_url('admin/cooperative/loans/directory') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-400 hover:text-white border border-slate-800 hover:border-slate-700 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Loan Info Card -->
    <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 space-y-4">
        <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            Informasi Peminjam
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block">Nama Anggota</span>
                <span class="font-bold text-white"><?= (string) esc((string) ($loan['username'] ?? '-')) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block">No. Anggota</span>
                <span class="font-bold text-white font-mono"><?= (string) esc((string) ($loan['nomor_anggota'] ?? '-')) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block">Nominal Pinjaman</span>
                <span class="font-bold text-white">Rp <?= number_format(floatval($loan['nominal_pinjaman']), 0, ',', '.') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block">Tenor</span>
                <span class="font-bold text-white"><?= (string) esc((string) $loan['tenor_bulan']) ?> Bulan</span>
            </div>
            <div>
                <span class="text-slate-500 block">Jenis Bunga</span>
                <span class="font-bold text-white"><?= ucfirst((string) ($loan['jenis_bunga'] ?? 'flat')) ?> (<?= (string) esc((string) $loan['bunga_persen']) ?>%)</span>
            </div>
            <div>
                <span class="text-slate-500 block">Tanggal Cair</span>
                <span class="font-bold text-white"><?= $loan['approved_at'] ? date('d M Y', strtotime($loan['approved_at'])) : '-' ?></span>
            </div>
            <div>
                <span class="text-slate-500 block">Status Pinjaman</span>
                <?php if ($loan['status'] === 'paid'): ?>
                    <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Lunas</span>
                <?php else: ?>
                    <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 uppercase">Berjalan</span>
                <?php endif; ?>
            </div>
            <div>
                <span class="text-slate-500 block">Status Keanggotaan</span>
                <span class="font-bold <?= ($loan['status_keaktifan'] ?? '') === 'aktif' ? 'text-emerald-400' : 'text-amber-400' ?>"><?= ucfirst((string) ($loan['status_keaktifan'] ?? '-')) ?></span>
            </div>
        </div>
    </div>

    <!-- KPI Widget Cards (4 metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Kewajiban -->
        <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-800 transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Kewajiban</span>
            </div>
            <p class="text-xl font-extrabold text-white">Rp <?= number_format($summary['grand_total'], 0, ',', '.') ?></p>
            <div class="flex gap-3 mt-2 text-[10px] text-slate-500">
                <span>Pokok: <b class="text-slate-300">Rp <?= number_format($summary['total_pokok'], 0, ',', '.') ?></b></span>
            </div>
        </div>

        <!-- Sudah Diangsur -->
        <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-800 transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sudah Diangsur</span>
            </div>
            <p class="text-xl font-extrabold text-emerald-400">Rp <?= number_format($summary['paid_total'], 0, ',', '.') ?></p>
            <div class="flex gap-3 mt-2 text-[10px] text-slate-500">
                <span>Angsuran: <b class="text-emerald-300"><?= $summary['angsuran_dibayar'] ?>/<?= $summary['tenor'] ?></b></span>
            </div>
        </div>

        <!-- Tunggakan Berjalan -->
        <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-800 transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tunggakan Jatuh Tempo</span>
            </div>
            <p class="text-xl font-extrabold <?= $summary['overdue_total'] > 0 ? 'text-rose-400' : 'text-slate-600' ?>">
                Rp <?= number_format($summary['overdue_total'], 0, ',', '.') ?>
            </p>
            <?php if ($summary['overdue_total'] > 0): ?>
            <p class="text-[10px] text-rose-400/80 mt-1 font-semibold">⚠ Ada angsuran yang melewati tanggal jatuh tempo</p>
            <?php else: ?>
            <p class="text-[10px] text-emerald-500/80 mt-1 font-semibold">✓ Tidak ada tunggakan</p>
            <?php endif; ?>
        </div>

        <!-- Sisa Tagihan -->
        <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-800 transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sisa Tagihan</span>
            </div>
            <p class="text-xl font-extrabold text-amber-400">Rp <?= number_format($summary['sisa_tagihan'], 0, ',', '.') ?></p>
            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold text-slate-500">Progress Pembayaran</span>
                    <span class="text-[10px] font-extrabold <?= $summary['progress_persen'] >= 100 ? 'text-emerald-400' : 'text-amber-400' ?>"><?= $summary['progress_persen'] ?>%</span>
                </div>
                <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 <?= $summary['progress_persen'] >= 100 ? 'bg-emerald-500' : ($summary['progress_persen'] >= 50 ? 'bg-teal-500' : 'bg-amber-500') ?>" 
                         style="width: <?= min($summary['progress_persen'], 100) ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Amortization Schedule Table -->
    <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Jadwal Amortisasi
            </h2>
            <span class="text-[10px] font-bold text-slate-500 bg-slate-800/60 px-2.5 py-1 rounded-lg"><?= count($schedule) ?> angsuran</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-800/60">
            <table class="w-full text-left border-collapse" id="amortization_table">
                <thead>
                    <tr class="bg-slate-800/40 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800/60">
                        <th class="py-3 px-4 text-center">No.</th>
                        <th class="py-3 px-4">Tgl Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Pokok</th>
                        <th class="py-3 px-4 text-right">Bunga</th>
                        <th class="py-3 px-4 text-right">Jasa</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Tgl Bayar</th>
                        <th class="py-3 px-4 text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs text-slate-300">
                    <?php foreach ($schedule as $entry): 
                        $status = $entry['status'] ?? 'Belum Dibayar';
                        $record = $entry['record'] ?? null;
                        $isApproved = $status === 'approved';
                        $isPending  = $status === 'pending';
                        $isRejected = $status === 'rejected';
                        $isUnpaid   = (!$isApproved && !$isPending && !$isRejected);
                        
                        // Determine if this installment is overdue
                        $isOverdue = false;
                        if ($isUnpaid && !empty($entry['due_date'])) {
                            $isOverdue = strtotime($entry['due_date']) < time();
                        }
                        
                        $rowClass = $isApproved ? 'bg-emerald-500/[0.02]' : ($isOverdue ? 'bg-rose-500/[0.03] border-l-2 border-rose-500/40' : '');
                    ?>
                    <tr class="<?= $rowClass ?> hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4 text-center font-mono text-slate-500"><?= $entry['angsuran_ke'] ?></td>
                        <td class="py-3 px-4 font-mono text-slate-300">
                            <?= !empty($entry['due_date']) ? date('d M Y', strtotime($entry['due_date'])) : '-' ?>
                        </td>
                        <td class="py-3 px-4 text-right font-mono">Rp <?= number_format($entry['pokok'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-mono">Rp <?= number_format($entry['bunga'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-mono">Rp <?= number_format($entry['jasa'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-bold font-mono text-white">Rp <?= number_format($entry['total'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($isApproved): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Lunas</span>
                            <?php elseif ($isPending): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase">Pending</span>
                            <?php elseif ($isRejected): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Ditolak</span>
                            <?php elseif ($isOverdue): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase animate-pulse">Terlambat</span>
                            <?php else: ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-slate-500/10 text-slate-500 border border-slate-500/20 uppercase">Belum Bayar</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-slate-500 text-[10px]">
                            <?php if ($record && !empty($record['tanggal_bayar'])): ?>
                                <?= date('d M Y', strtotime($record['tanggal_bayar'])) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($record && !empty($record['bukti_bayar'])): ?>
                                <button onclick="openProofModal('<?= base_url('uploads/bukti_angsuran/' . $record['bukti_bayar']) ?>')" 
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-sky-500/10 border border-sky-500/20 hover:bg-sky-500/20 text-sky-400 rounded-md text-[9px] font-bold transition-all cursor-pointer" title="Lihat bukti bayar">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Bukti
                                </button>
                            <?php else: ?>
                                <span class="text-slate-600 text-[10px]">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-800/40 text-xs font-bold border-t border-slate-700/40">
                        <td colspan="2" class="py-3 px-4 text-right text-slate-400 uppercase tracking-wider text-[10px]">Total Keseluruhan</td>
                        <td class="py-3 px-4 text-right font-mono text-white">Rp <?= number_format($summary['total_pokok'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-mono text-white">Rp <?= number_format($summary['total_bunga'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-mono text-white">Rp <?= number_format($summary['total_jasa'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right font-mono font-extrabold text-emerald-400">Rp <?= number_format($summary['grand_total'], 0, ',', '.') ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Manager Notice (read-only for managers) -->
    <?php if (!$isAdmin): ?>
    <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-xs text-amber-400/80 font-semibold">Anda memiliki akses <b>baca saja</b> pada halaman ini. Tindakan keuangan hanya tersedia bagi Admin/Superadmin.</p>
    </div>
    <?php endif; ?>

</div>

<!-- Proof Modal (Bukti Bayar Zoom) -->
<div id="proof_modal" class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md hidden items-center justify-center p-4" onclick="closeProofModal(event)">
    <div class="relative max-w-2xl w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-2xl" onclick="event.stopPropagation()">
        <!-- Close button -->
        <button onclick="closeProofModal()" class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors cursor-pointer z-10">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        
        <h3 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Bukti Pembayaran
        </h3>

        <!-- Spinner -->
        <div id="modal_spinner" class="flex items-center justify-center py-16">
            <div class="w-10 h-10 rounded-full border-4 border-sky-500/30 border-t-sky-400 animate-spin"></div>
        </div>

        <!-- Image -->
        <img id="proof_image" src="" alt="Bukti Pembayaran" 
             class="hidden w-full max-h-[60vh] object-contain rounded-xl"
             onload="document.getElementById('modal_spinner').classList.add('hidden'); this.classList.remove('hidden');"
             onerror="document.getElementById('modal_spinner').innerHTML = '<p class=\'text-sm text-rose-400 font-bold\'>Gagal memuat gambar</p>';">
    </div>
</div>

<script>
function openProofModal(imageUrl) {
    const modal = document.getElementById('proof_modal');
    const img = document.getElementById('proof_image');
    const spinner = document.getElementById('modal_spinner');
    
    // Reset state
    img.classList.add('hidden');
    spinner.classList.remove('hidden');
    spinner.innerHTML = '<div class="w-10 h-10 rounded-full border-4 border-sky-500/30 border-t-sky-400 animate-spin"></div>';
    
    img.src = imageUrl;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeProofModal(event) {
    if (event && event.target !== document.getElementById('proof_modal')) return;
    const modal = document.getElementById('proof_modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

// ESC key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('proof_modal');
        if (!modal.classList.contains('hidden')) {
            closeProofModal();
        }
    }
});
</script>

<?= $this->endSection() ?>
