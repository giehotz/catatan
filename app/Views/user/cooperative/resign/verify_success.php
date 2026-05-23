<?php
/**
 * @var array $request
 * @var array $member
 * @var array $user
 * @var array|null $admin
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="max-w-2xl mx-auto py-8 text-center space-y-8">
    
    <!-- Success Badge -->
    <div class="space-y-3">
        <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/10 animate-bounce">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Dokumen Terverifikasi Asli</h1>
        <p class="text-slate-400 text-sm">Surat Keputusan Pengunduran Diri Resmi Koperasi dinyatakan VALID dan terdaftar di database KSP.</p>
    </div>

    <!-- Verified Data Card -->
    <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800 text-left space-y-4 shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none blur-[20px]"></div>
        
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 pb-2 mb-3">Detail Berkas Resmi</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block mb-0.5">Nomor SK Pengurus:</span>
                <span class="text-white font-mono font-bold">SK-SKP/RE/<?= esc($request['nomor_surat']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block mb-0.5">Tanggal Disahkan:</span>
                <span class="text-white font-bold"><?= date('d F Y, H:i', strtotime($request['processed_at'])) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block mb-0.5">Nama Anggota:</span>
                <span class="text-white font-bold"><?= (string) esc($user['username'] ?? 'Anggota Koperasi') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block mb-0.5">Nomor Anggota Resmi:</span>
                <span class="text-indigo-400 font-mono font-bold"><?= (string) esc($member['nomor_anggota']) ?></span>
            </div>
            <div class="sm:col-span-2">
                <span class="text-slate-500 block mb-0.5">Status Keanggotaan Terkini:</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-450 border border-rose-500/20 uppercase tracking-wider">
                    Nonaktif (Resigned Resmi)
                </span>
            </div>
            <div class="sm:col-span-2 border-t border-slate-850 pt-3 mt-1">
                <span class="text-slate-500 block mb-0.5">Pernyataan Dewan Pengurus KSP:</span>
                <p class="text-slate-300 leading-relaxed italic">
                    "Menyatakan bahwa anggota tersebut di atas telah secara sah keluar dari keanggotaan KSP, seluruh hak simpanan telah dicairkan penuh, dan telah dinyatakan bebas dari segala beban kewajiban utang-piutang koperasi."
                </p>
            </div>
        </div>
    </div>

    <!-- Security Info / Actions -->
    <div class="space-y-4">
        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900 text-xs text-slate-400 max-w-lg mx-auto leading-relaxed">
            <strong>🔒 Keamanan Tanda Tangan Kriptografi:</strong><br>
            Keaslian dokumen ini dilindungi secara kriptografis menggunakan algoritma SHA-256 HMAC Signature yang dicocokkan dengan data stempel digital dinamis dewan pengurus KSP.
        </div>

        <div class="pt-2">
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda Utama
            </a>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
