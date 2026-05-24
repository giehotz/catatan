<?php
/**
 * @var array $member
 * @var float $totalSimpanan
 * @var float $saldoPokok
 * @var float $saldoWajib
 * @var float $saldoSukarela
 * @var float $sisaPinjaman
 * @var array $loans
 */
?>
<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 max-w-6xl mx-auto py-4 mt-4">
    
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Koperasi Simpan Pinjam Saya</h1>
            <p class="text-tx-secondary text-sm">
                Nomor Anggota Resmi: <span class="font-mono font-bold text-indigo-400 bg-indigo-500/10 px-2.5 py-0.5 rounded-md border border-indigo-500/20 text-xs inline-block sm:inline"><?= (string) esc($member['nomor_anggota']) ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Status Keanggotaan: Aktif
            </span>
        </div>
    </div>

    <!-- Suspension Alert (Only visible if membership suspended) -->
    <?php if ($member['status_keaktifan'] !== 'aktif') : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 text-rose-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong class="font-bold block text-tx-primary mb-0.5">Keanggotaan Ditangguhkan (Suspended)</strong>
                Status keanggotaan Anda saat ini dinonaktifkan sementara oleh pengurus koperasi. Anda tidak dapat melakukan transaksi simpanan/penarikan baru atau pengajuan pinjaman hingga status diaktifkan kembali.
            </div>
        </div>
    <?php endif; ?>

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

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        
        <!-- Total Simpanan Saya -->
        <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-tx-secondary block mb-1">Total Simpanan Saya</span>
            <span class="text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 uppercase tracking-wider">Pokok + Wajib + Sukarela</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-tx-primary mt-3 tracking-tight">
                Rp <?= number_format($totalSimpanan, 2, ',', '.') ?>
            </h3>
            <div class="mt-4 flex gap-4 text-xs text-tx-secondary">
                <div>Pokok: <span class="font-bold text-tx-primary">Rp <?= number_format($saldoPokok, 0, ',', '.') ?></span></div>
                <div>Wajib: <span class="font-bold text-tx-primary">Rp <?= number_format($saldoWajib, 0, ',', '.') ?></span></div>
                <div>Sukarela: <span class="font-bold text-tx-primary">Rp <?= number_format($saldoSukarela, 0, ',', '.') ?></span></div>
            </div>
        </div>

        <!-- Total Sisa Hutang Kredit Saya -->
        <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-tx-secondary block mb-1">Sisa Pinjaman Kredit Saya</span>
            <span class="text-[10px] font-bold rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 px-2 py-0.5 uppercase tracking-wider">Total Hutang - Total Terbayar</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-rose-400 mt-3 tracking-tight">
                Rp <?= number_format($sisaPinjaman, 2, ',', '.') ?>
            </h3>
        </div>

        <!-- Quick Info Banner -->
        <div class="bg-surface/40 p-6 rounded-2xl border border-br-default/60 shadow-xl flex flex-col justify-between">
            <div class="space-y-1.5">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest block">Informasi Koperasi</span>
                <p class="text-xs text-tx-secondary leading-relaxed">Nikmati kemudahan mengakses pembiayaan flat 1.50% bulanan tanpa agunan rumit. Seluruh pencatatan pembayaran dikoordinasikan secara penuh dengan Buku Besar Utang Piutang Pribadi Anda.</p>
            </div>
            <span class="text-[10px] font-semibold text-tx-disabled block mt-2">Diverifikasi & Terproteksi</span>
        </div>

    </div>

    <!-- Quick Features Menus -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        
        <!-- Tabungan Saya portal menu -->
        <a href="<?= base_url('cooperative/savings') ?>" class="p-6 bg-surface/40 border border-br-default hover:border-br-default hover:bg-surface/60 rounded-2xl flex flex-col justify-between h-40 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-elevated text-tx-secondary group-hover:text-emerald-400 group-hover:bg-emerald-500/10 transition-colors flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-lg font-bold text-tx-primary group-hover:text-emerald-300 transition-colors">Mutasi & Setor Simpanan</h4>
                <p class="text-xs text-tx-disabled mt-1">Lakukan penyetoran simpanan wajib, pokok bulanan, atau ajukan penarikan sukarela tabungan Anda.</p>
            </div>
        </a>

        <!-- Pinjaman Saya portal menu -->
        <a href="<?= base_url('cooperative/loans') ?>" class="p-6 bg-surface/40 border border-br-default hover:border-br-default hover:bg-surface/60 rounded-2xl flex flex-col justify-between h-40 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-elevated text-tx-secondary group-hover:text-amber-400 group-hover:bg-amber-500/10 transition-colors flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h4 class="text-lg font-bold text-tx-primary group-hover:text-amber-300 transition-colors">Pengajuan & Amortisasi Pinjaman</h4>
                <p class="text-xs text-tx-disabled mt-1">Simulasikan pinjaman kredit baru, ajukan limit, dan setor bukti transfer angsuran aktif Anda.</p>
            </div>
        </a>

        <!-- Resign Keanggotaan portal menu -->
        <a href="<?= base_url('cooperative/resign') ?>" class="p-6 bg-surface/40 border border-br-default hover:border-rose-500/30 hover:bg-rose-950/10 rounded-2xl flex flex-col justify-between h-40 transition-all group col-span-1 sm:col-span-2">
            <div class="w-10 h-10 rounded-xl bg-elevated text-tx-secondary group-hover:text-rose-400 group-hover:bg-rose-500/10 transition-colors flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3 3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div>
                <h4 class="text-lg font-bold text-tx-primary group-hover:text-rose-300 transition-colors">Keluar Keanggotaan Koperasi</h4>
                <p class="text-xs text-tx-disabled mt-1">Ajukan pengunduran diri resmi dari keanggotaan koperasi dan proses pencairan sisa simpanan Anda secara sah.</p>
            </div>
        </a>

    </div>

</div>
<?= $this->endSection() ?>
