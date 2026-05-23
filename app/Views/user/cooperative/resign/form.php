<?php
/**
 * @var float $totalSimpanan
 * @var float $saldoPokok
 * @var float $saldoWajib
 * @var float $saldoSukarela
 * @var float $sisaPinjaman
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
        <span class="text-xs text-slate-500 font-semibold font-mono">Form Resign</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Pengunduran Diri Keanggotaan</h1>
        <p class="text-slate-400 text-sm">Ajukan permohonan pengunduran diri resmi dari keanggotaan koperasi secara teratur.</p>
    </div>

    <!-- Financial Status Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Simpanan Ringkasan -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800 shadow-lg relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest block mb-1">Akumulasi Simpanan Anda</span>
            <h3 class="text-2xl font-extrabold text-white tracking-tight">
                Rp <?= number_format($totalSimpanan, 2, ',', '.') ?>
            </h3>
            <p class="text-xs text-slate-500 mt-2">Seluruh dana simpanan pokok, wajib, dan sukarela Anda akan dikembalikan secara penuh ke rekening Anda setelah pengajuan disetujui.</p>
            <div class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-3 gap-2 text-[10px] text-slate-400">
                <div>
                    <span class="block text-slate-500">Pokok</span>
                    <span class="font-bold text-white">Rp <?= number_format($saldoPokok, 0, ',', '.') ?></span>
                </div>
                <div>
                    <span class="block text-slate-500">Wajib</span>
                    <span class="font-bold text-white">Rp <?= number_format($saldoWajib, 0, ',', '.') ?></span>
                </div>
                <div>
                    <span class="block text-slate-500">Sukarela</span>
                    <span class="font-bold text-white">Rp <?= number_format($saldoSukarela, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Hutang/Pinjaman Ringkasan -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-850 shadow-lg relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full pointer-events-none"></div>
            <span class="text-xs font-bold text-rose-400 uppercase tracking-widest block mb-1">Sisa Pinjaman/Kewajiban</span>
            <h3 class="text-2xl font-extrabold <?= $sisaPinjaman > 0 ? 'text-rose-450' : 'text-slate-400' ?> tracking-tight">
                Rp <?= number_format($sisaPinjaman, 2, ',', '.') ?>
            </h3>
            <?php if ($sisaPinjaman > 0) : ?>
                <div class="mt-3 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-[11px] text-rose-300 leading-relaxed">
                    <strong class="font-bold block text-white mb-0.5">⚠️ Syarat Pelunasan Belum Terpenuhi</strong>
                    Anda memiliki sisa utang pinjaman yang belum lunas. Sesuai AD/ART koperasi, pengunduran diri hanya dapat diproses setelah sisa pinjaman bernilai <strong>Rp 0</strong>.
                </div>
            <?php else : ?>
                <p class="text-xs text-slate-300 mt-2">Bebas Kewajiban Finansial! Anda tidak memiliki sisa utang koperasi, sehingga permohonan pengunduran diri dapat diajukan saat ini.</p>
                <div class="mt-4 pt-4 border-t border-slate-800 flex items-center gap-1.5 text-xs text-emerald-350 font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Memenuhi syarat kelayakan pengajuan
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resignation Submission Form Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60">
            <h3 class="text-lg font-bold text-white tracking-tight">Kirim Permohonan Pengunduran Diri Resmi</h3>
        </div>

        <div class="p-6">
            <?php if ($sisaPinjaman > 0) : ?>
                <div class="p-6 rounded-xl bg-slate-950/50 border border-slate-850 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto border border-rose-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-white">Formulir Dikunci Sementara</h4>
                    <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                        Silakan lakukan pembayaran penuh atau pelunasan sisa tagihan pinjaman aktif Anda melalui menu <a href="<?= base_url('cooperative/loans') ?>" class="text-indigo-400 font-bold hover:underline">Pinjaman Saya</a> sebelum mengajukan pengunduran diri.
                    </p>
                </div>
            <?php else : ?>
                <form action="<?= base_url('cooperative/resign/submit') ?>" method="post" class="space-y-6">
                    <?= csrf_field() ?>

                    <div class="space-y-2">
                        <label for="alasan_keluar" class="text-xs font-bold text-rose-400 uppercase tracking-widest block mb-1">
                            Alasan Pengunduran Diri <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500">Tuliskan alasan pengunduran diri Anda secara formal (minimal 10 karakter, maksimal 1000 karakter).</p>
                        <textarea id="alasan_keluar" name="alasan_keluar" rows="5" required
                            class="w-full rounded-xl border border-slate-800 bg-slate-950/70 p-3.5 text-sm text-white placeholder-slate-650 transition-all focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500"
                            placeholder="Contoh: Saya berencana untuk pindah domisili ke luar kota sehingga tidak memungkinkan untuk berpartisipasi aktif dalam kegiatan koperasi..."><?= old('alasan_keluar') ?></textarea>
                        <?php if (isset(session('errors')['alasan_keluar'])) : ?>
                            <span class="text-xs text-rose-400 font-medium block mt-1"><?= session('errors')['alasan_keluar'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900 text-xs text-slate-400 space-y-1.5 leading-relaxed">
                        <span class="font-bold text-white block mb-0.5">💡 Catatan Kepatuhan:</span>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Pengajuan Anda akan ditinjau secara manual oleh dewan pengurus KSP.</li>
                            <li>Setelah disetujui, hak keanggotaan Anda akan dicabut (nonaktif) dan sisa saldo tabungan akan dicairkan.</li>
                            <li>Tanda tangan elektronik resmi dewan pengurus KSP akan disematkan secara digital dalam berkas Surat Keputusan.</li>
                        </ul>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/15 hover:shadow-indigo-500/25 transition-all cursor-pointer flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Kirim Pengajuan Pengunduran Diri
                        </button>
                    </div>
                </form>
            <?php endif; ?>
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
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider">
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
                                        <span class="text-rose-400 text-xs block max-w-xs leading-relaxed">
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
