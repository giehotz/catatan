<?php
/**
 * @var string $hash
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="max-w-2xl mx-auto py-8 text-center space-y-8">
    
    <!-- Failure Badge -->
    <div class="space-y-3">
        <div class="w-16 h-16 rounded-full bg-rose-500/10 text-rose-450 border border-rose-500/20 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10 animate-pulse">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Verifikasi Dokumen Gagal</h1>
        <p class="text-slate-400 text-sm">Kode atau tanda tangan digital surat pengunduran diri tidak cocok dengan database koperasi kami.</p>
    </div>

    <!-- Error Notice Card -->
    <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-850 text-left space-y-4 shadow-xl">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 pb-2 mb-3">Penyebab Masalah</h3>
        
        <ul class="list-disc pl-5 text-xs text-slate-400 space-y-2 leading-relaxed">
            <li>Tautan verifikasi salah ketik, tidak lengkap, atau telah dimodifikasi secara ilegal.</li>
            <li>Dokumen ini belum melalui proses persetujuan resmi oleh dewan pengurus KSP.</li>
            <li>Dokumen dibatalkan sepihak atau masa berlakunya telah kadaluarsa.</li>
            <li>Tanda tangan kriptografis terdeteksi tidak valid (salt hash tidak cocok).</li>
        </ul>

        <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-900 font-mono text-[10px] text-slate-550 break-all">
            <strong>Payload Hash:</strong> <?= (string) esc($hash) ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="pt-2">
        <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Kembali ke Beranda Utama
        </a>
    </div>

</div>
<?= $this->endSection() ?>
