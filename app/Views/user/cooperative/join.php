<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="max-w-xl mx-auto space-y-8 py-4 mt-4">
    
    <!-- Title Header -->
    <div class="text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-linear-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20 mx-auto">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 14a15.917 15.917 0 00-6-2.24c0-2.496.096-4.707.725-6.58A15.918 15.918 0 0112 2.913a15.919 15.919 0 018.275 3.266c.628 1.873.725 4.084.725 6.58a15.917 15.917 0 00-6 2.24c-.29 1.103-.619 2.195-1 3.266M12 11h.01M12 12h.01M12 13h.01" />
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-tx-primary tracking-tight">Koperasi Simpan Pinjam Saya</h1>
        <p class="text-tx-secondary text-xs sm:text-sm max-w-sm mx-auto">Akses eksklusif simpanan, penarikan sukarela, dan pengajuan pinjaman kredit berbunga flat ringan.</p>
    </div>

    <!-- Activation Card Form -->
    <div class="bg-surface border border-br-default rounded-3xl p-6 sm:p-8 space-y-6 relative overflow-hidden shadow-2xl backdrop-blur-xs">
        <!-- Top decorative gradient line -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-linear-to-r from-indigo-500 to-purple-600"></div>
        
        <div class="space-y-1.5 relative z-10">
            <h3 class="text-lg font-bold text-tx-primary tracking-tight">Aktivasi Keanggotaan Koperasi</h3>
            <p class="text-xs text-tx-secondary/80 leading-relaxed">Keanggotaan koperasi bersifat terbatas. Untuk membuka portal ini, silakan masukkan Kode Undangan Keanggotaan yang sah yang diberikan oleh tim manajemen koperasi.</p>
        </div>

        <!-- Banners feedback -->
        <?php if (session('error') !== null) : ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2 relative z-10 animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <?= (string) esc(session('error')) ?>
            </div>
        <?php endif ?>

        <form action="<?= base_url('cooperative/join') ?>" method="post" class="space-y-6 relative z-10">
            <?= csrf_field() ?>

            <div class="space-y-2">
                <label for="invitation_code" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Kode Undangan Keanggotaan</label>
                <input type="text" id="invitation_code" name="invitation_code" required placeholder="Contoh: KOP-XXXX-YYYY" class="w-full px-4 py-3.5 bg-bg-base/60 dark:bg-bg-base/40 border border-br-default rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-tx-primary placeholder-tx-secondary/40 transition-all outline-none text-center font-mono font-bold text-sm tracking-widest uppercase">
                <p class="text-[10px] text-tx-secondary/60 leading-relaxed text-center">Setiap kode hanya dapat digunakan satu kali untuk mengaktifkan satu akun pengguna.</p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/10 cursor-pointer flex items-center justify-center gap-1.5 border-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Aktifkan Portal Koperasi Saya
            </button>
        </form>
    </div>

</div>
<?= $this->endSection() ?>
