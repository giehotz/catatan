<?php
/**
 * @var array $members
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
        <span class="text-xs text-slate-500 font-semibold font-mono">Members Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Direktori Anggota Koperasi</h1>
        <p class="text-slate-400 text-sm">Lihat detail anggota, pantau nomor anggota resmi, dan kelola status keaktifan keanggotaan.</p>
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

    <!-- Members Table Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">Anggota Koperasi Terdaftar</h3>
            <span class="px-2.5 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                Aktif: <?= count($members) ?> Anggota
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6 text-center w-16">ID</th>
                        <th class="py-4 px-6">Nomor Anggota</th>
                        <th class="py-4 px-6">Nama Pengguna</th>
                        <th class="py-4 px-6">Email Terdaftar</th>
                        <th class="py-4 px-6 text-center">Status Koperasi</th>
                        <th class="py-4 px-6">Tanggal Bergabung</th>
                        <th class="py-4 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php if (empty($members)) : ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-semibold">Belum ada anggota koperasi terdaftar.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($members as $member) : ?>
                            <tr class="hover:bg-slate-950/30 transition-colors">
                                <td class="py-4 px-6 text-center font-semibold text-slate-500"><?= $member['id'] ?></td>
                                <td class="py-4 px-6 font-mono font-bold text-indigo-400"><?= (string) esc($member['nomor_anggota']) ?></td>
                                <td class="py-4 px-6 font-bold text-white"><?= (string) esc($member['username']) ?></td>
                                <td class="py-4 px-6 text-slate-400 text-xs"><?= (string) esc($member['email']) ?></td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($member['status_keaktifan'] === 'aktif') : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                            Aktif
                                        </span>
                                    <?php else : ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase tracking-wider">
                                            Ditangguhkan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                    <?= date('d M Y, H:i', strtotime($member['created_at'])) ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <form action="<?= base_url('admin/cooperative/toggle-member/' . $member['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status keanggotaan ini?');">
                                        <?= csrf_field() ?>
                                        <?php if ($member['status_keaktifan'] === 'aktif') : ?>
                                            <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                Tangguhkan
                                            </button>
                                        <?php else : ?>
                                            <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-400 hover:text-emerald-300 transition-all cursor-pointer">
                                                Aktifkan
                                            </button>
                                        <?php endif; ?>
                                    </form>
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
