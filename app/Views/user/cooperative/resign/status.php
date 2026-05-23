<?php
/**
 * @var array $pendingRequest
 * @var float $saldoPokok
 * @var float $saldoWajib
 * @var float $saldoSukarela
 * @var float $totalSimpanan
 * @var array $history
 * @var array $member
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 mt-4">
    
    <!-- Navigation Back links -->
    <div class="flex items-center justify-between">
        <a href="<?= base_url('cooperative') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Koperasi Hub
        </a>
        <span class="text-xs text-slate-500 font-semibold font-mono">Status Resign</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Status Pengunduran Diri</h1>
        <p class="text-slate-400 text-sm">Pantau proses peninjauan permohonan pengunduran diri keanggotaan koperasi Anda.</p>
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

    <!-- Status Tracking Dashboard -->
    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <!-- Glow effects -->
        <div class="absolute -right-16 -bottom-16 w-36 h-36 bg-amber-500/5 rounded-full pointer-events-none blur-2xl"></div>

        <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 relative z-10">
            <!-- Left Info -->
            <div class="space-y-4 text-center md:text-left">
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Menunggu Persetujuan
                    </span>
                    <span class="text-xs text-slate-500 font-semibold font-mono">
                        Diajukan pada: <?= date('d M Y, H:i', strtotime($pendingRequest['created_at'])) ?>
                    </span>
                </div>
                
                <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Permohonan Anda Sedang Ditinjau</h3>
                <p class="text-slate-400 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Dewan pengurus koperasi sedang memverifikasi rincian saldo simpanan pokok/wajib/sukarela dan mengaudit mutasi utang-piutang Anda. Proses peninjauan berkas formal memakan waktu hingga maksimal 30 hari kalender.
                </p>

                <!-- Reason Container -->
                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900 text-left">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Alasan Pengunduran Diri Anda:</span>
                    <p class="text-xs text-slate-300 italic leading-relaxed">"<?= (string) esc($pendingRequest['alasan_keluar']) ?>"</p>
                </div>
            </div>

            <!-- Right Actions & Summary -->
            <div class="bg-slate-950/50 p-6 rounded-2xl border border-slate-850 w-full md:w-80 shrink-0 space-y-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estimasi Pengembalian Dana</h4>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Simpanan Pokok:</span>
                        <span class="text-white font-bold">Rp <?= number_format($saldoPokok, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Simpanan Wajib:</span>
                        <span class="text-white font-bold">Rp <?= number_format($saldoWajib, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Simpanan Sukarela:</span>
                        <span class="text-white font-bold">Rp <?= number_format($saldoSukarela, 0, ',', '.') ?></span>
                    </div>
                    <div class="border-t border-slate-850 pt-2 flex justify-between text-sm">
                        <span class="text-slate-400 font-bold">Total Pengembalian:</span>
                        <span class="text-emerald-400 font-extrabold">Rp <?= number_format($totalSimpanan, 2, ',', '.') ?></span>
                    </div>
                </div>

                <!-- Cancel Button -->
                <form action="<?= base_url('cooperative/resign/cancel/' . $pendingRequest['id']) ?>" method="post"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan pengunduran diri ini? Keanggotaan Anda akan kembali aktif secara penuh.');"
                    class="pt-2">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl border border-slate-800 hover:border-rose-500/30 bg-slate-900/50 hover:bg-rose-500/5 text-slate-400 hover:text-rose-400 text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Batalkan Pengajuan Resign
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Resignation Historical Log Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60">
            <h3 class="text-lg font-bold text-white tracking-tight">Riwayat Pengajuan Pengunduran Diri</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Tanggal Pengajuan</th>
                        <th class="py-4 px-6">Alasan</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6">Surat SK Resmi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($history)) : ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 font-semibold">Belum ada riwayat pengajuan pengunduran diri.</td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($history as $h) : ?>
                            <tr class="hover:bg-slate-950/30 transition-colors">
                                <td class="py-4 px-6 text-center font-semibold text-slate-550"><?= $no++ ?></td>
                                <td class="py-4 px-6 text-xs text-slate-400 font-medium">
                                    <?= date('d M Y, H:i', strtotime($h['created_at'])) ?>
                                </td>
                                <td class="py-4 px-6 max-w-sm truncate text-xs text-slate-350" title="<?= (string) esc($h['alasan_keluar']) ?>">
                                    <?= (string) esc($h['alasan_keluar']) ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($h['status'] === 'pending') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider animate-pulse">
                                            Menunggu
                                        </span>
                                    <?php elseif ($h['status'] === 'approved') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                            Disetujui
                                        </span>
                                    <?php elseif ($h['status'] === 'rejected') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-450 border border-rose-500/20 uppercase tracking-wider" title="Alasan: <?= (string) esc($h['alasan_penolakan']) ?>">
                                            Ditolak
                                        </span>
                                    <?php elseif ($h['status'] === 'cancelled') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-700/30 text-slate-400 border border-slate-800 uppercase tracking-wider">
                                            Dibatalkan
                                        </span>
                                    <?php else : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-800/60 text-slate-500 border border-slate-900 uppercase tracking-wider">
                                            Expired
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-xs font-semibold">
                                    <?php if ($h['status'] === 'approved' && !empty($h['nomor_surat'])) : ?>
                                        <a href="<?= base_url('cooperative/resign/letter/' . $h['id']) ?>" target="_blank"
                                            class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 font-bold hover:underline">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Unduh SK Resmi
                                        </a>
                                    <?php elseif ($h['status'] === 'rejected' && !empty($h['alasan_penolakan'])) : ?>
                                        <span class="text-rose-450 text-xs block max-w-xs leading-relaxed">
                                            <strong>Ditolak:</strong> <?= (string) esc($h['alasan_penolakan']) ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-slate-500">-</span>
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
