<?php
/**
 * @var \App\Entities\User[] $users
 * @var \App\Entities\User $user
 * @var int $totalUsers
 * @var int $activeUsers
 * @var int $blockedUsers
 */
?>
<?= $this->extend('layouts/admin_base') ?>

<?= $this->section('content') ?>
<div class="space-y-8 max-w-6xl mx-auto">
    
    <!-- Welcome Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Panel Administrasi</h1>
        <p class="text-slate-400 text-sm">Kelola keamanan kredensial akun pengguna, kontrol aktivasi akun, dan pantau pengguna sistem.</p>
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

    <?php if (session('import_errors') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm space-y-2">
            <div class="flex items-center justify-between font-bold">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Detail Kesalahan Import Excel:
                </div>
                <a href="<?= base_url('admin/download-import-template') ?>" class="text-xs px-2.5 py-1 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-lg transition-colors flex items-center gap-1.5" title="Download Template Excel yang Benar">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh Template
                </a>
            </div>
            <ul class="list-disc pl-9 text-xs space-y-1">
                <?php foreach (session('import_errors') as $err) : ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <!-- Summary Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        
        <!-- Total Users -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400">Total Pengguna</span>
            <h3 class="text-3xl font-bold text-white mt-2 tracking-tight">
                <?= $totalUsers ?> <span class="text-xs text-slate-500 font-medium">Akun terdaftar</span>
            </h3>
        </div>

        <!-- Active Users -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl hover:border-emerald-500/10 transition-colors relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400">Akun Aktif</span>
            <h3 class="text-3xl font-bold text-emerald-400 mt-2 tracking-tight">
                <?= $activeUsers ?> <span class="text-xs text-slate-500 font-medium">Akun aktif</span>
            </h3>
        </div>

        <!-- Blocked Users -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl hover:border-rose-500/10 transition-colors relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-slate-400">Akun Diblokir</span>
            <h3 class="text-3xl font-bold text-rose-400 mt-2 tracking-tight">
                <?= $blockedUsers ?> <span class="text-xs text-slate-500 font-medium">Pengguna dinonaktifkan</span>
            </h3>
        </div>

    </div>

    <!-- Users Table Card -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">Direktori Pengguna Aplikasi</h3>
            <div class="flex items-center gap-3">
                <button type="button" onclick="openImportModal()" class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 text-emerald-400 hover:text-emerald-300 text-xs font-bold rounded-lg transition-all cursor-pointer flex items-center gap-1.5 shadow-lg shadow-emerald-500/5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Import Excel
                </button>
                <span class="px-3 py-1 bg-slate-950/80 text-xs font-semibold rounded-lg text-slate-400 border border-slate-900">
                    Total: <?= count($users) ?> Pengguna
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6 text-center w-16">ID</th>
                        <th class="py-4 px-6">Pengguna</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">Grup / Role</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6">Tanggal Terdaftar</th>
                        <th class="py-4 px-6 text-right w-44">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <?php foreach ($users as $user) : ?>
                        <tr class="hover:bg-slate-950/30 transition-colors">
                            <td class="py-4 px-6 text-center font-semibold text-slate-500"><?= $user->id ?></td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs flex items-center justify-center">
                                        <?= esc($user->getInitials()) ?>
                                    </div>
                                    <span class="font-bold text-white"><?= (string) esc((string) $user->username) ?></span>
                                    <?php if (auth()->id() === $user->id) : ?>
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-indigo-500/20 text-indigo-300">Anda</span>
                                    <?php endif ?>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-400"><?= (string) esc((string) $user->email) ?></td>
                            <td class="py-4 px-6 font-medium">
                                <?php if (auth()->id() === $user->id) : ?>
                                    <span class="px-2 py-1 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold rounded-lg uppercase tracking-wider">
                                        <?= esc($user->getRoleString()) ?>
                                    </span>
                                <?php else : ?>
                                    <form action="<?= base_url('admin/assign-role/' . $user->id) ?>" method="post" class="inline-block">
                                        <?= csrf_field() ?>
                                        <select name="role" onchange="this.form.submit()" class="bg-slate-950/60 border border-slate-900 rounded-lg px-2.5 py-1 text-xs text-indigo-300 font-semibold focus:border-indigo-500 outline-none cursor-pointer hover:border-slate-800 transition-colors">
                                            <option value="user" <?= $user->isOnlyUser() ? 'selected' : '' ?>>User</option>
                                            <option value="manager" <?= $user->isManager() ? 'selected' : '' ?>>Manager</option>
                                            <option value="admin" <?= $user->isAdmin() ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if ($user->active) : ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                <?php else : ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Diblokir
                                    </span>
                                <?php endif ?>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                <?= esc($user->getCreatedAtFormatted()) ?>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2.5">
                                    
                                    <!-- Toggle Block Status Form -->
                                    <?php if (auth()->id() !== $user->id) : ?>
                                        <form action="<?= base_url('admin/toggle-status/' . $user->id) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif pengguna ini?');">
                                            <?= csrf_field() ?>
                                            <?php if ($user->active) : ?>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer flex items-center gap-1" title="Blokir akun">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    Blokir
                                                </button>
                                            <?php else : ?>
                                                <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-emerald-500/20 hover:border-emerald-500/50 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-400 hover:text-emerald-300 transition-all cursor-pointer flex items-center gap-1" title="Aktifkan akun">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    Aktifkan
                                                </button>
                                            <?php endif ?>
                                        </form>
                                    <?php else : ?>
                                        <span class="px-3 py-1.5 text-xs text-slate-600 font-semibold cursor-not-allowed flex items-center gap-1" title="Anda tidak dapat memblokir akun Anda sendiri">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            Locked
                                        </span>
                                    <?php endif ?>

                                    <!-- Reset Password Trigger -->
                                    <button onclick="openResetModal(<?= $user->id ?>, '<?= (string) esc((string) $user->username) ?>')" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-indigo-500/20 hover:border-indigo-500/50 bg-indigo-500/5 hover:bg-indigo-500/10 text-indigo-400 hover:text-indigo-300 transition-all cursor-pointer flex items-center gap-1" title="Reset kata sandi pengguna">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        Sandi
                                    </button>

                                    <!-- Impersonation Action -->
                                    <?php if ($user->can_be_impersonated) : ?>
                                        <a href="<?= base_url('admin/impersonate/' . $user->id) ?>" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-amber-500/20 hover:border-amber-500/50 bg-amber-500/5 hover:bg-amber-500/10 text-amber-400 hover:text-amber-300 transition-all cursor-pointer flex items-center gap-1" title="Menyamar sebagai pengguna ini (Bantuan Teknis)">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Menyamar
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Reset Password Overlay Modal -->
<div id="resetModal" class="fixed inset-0 z-50 items-center justify-center bg-slate-950/80 backdrop-blur-md hidden transition-all duration-300 opacity-0">
    <div class="bg-slate-900 border border-slate-900 w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-2xl space-y-6 transform scale-95 transition-all duration-300">
        
        <!-- Modal Title -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="space-y-1">
                <h3 class="text-xl font-bold text-white tracking-tight">Reset Kata Sandi</h3>
                <p id="resetModalSubtitle" class="text-xs text-slate-400 font-medium">Tetapkan password baru untuk pengguna: <span id="resetTargetUser" class="text-indigo-400 font-bold"></span></p>
            </div>
            <button onclick="closeResetModal()" class="text-slate-500 hover:text-slate-300 transition-colors p-1.5 hover:bg-slate-800 rounded-lg cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form body -->
        <form id="resetForm" action="" method="post" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Password Field -->
            <div class="space-y-1.5">
                <label for="new_password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi Baru</label>
                <div class="relative">
                    <input type="text" id="new_password" name="password" required placeholder="Min. 8 karakter" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
                    <button type="button" onclick="generateRandomPassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors" title="Acak password">
                        Acak Sandi
                    </button>
                </div>
                <p class="text-[11px] text-slate-500">Kata sandi baru minimal harus terdiri dari 8 karakter.</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="closeResetModal()" class="w-1/3 py-3 bg-slate-950 hover:bg-slate-900 border border-slate-900 hover:border-slate-800 text-slate-300 hover:text-white font-bold rounded-xl text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/10 cursor-pointer">
                    Simpan Sandi Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.ADMIN_RESET_PASSWORD_URL = '<?= base_url('admin/reset-password') ?>';
</script>
<script src="<?= base_url('assets/js/admin/dashboard.js') ?>"></script>

<!-- Import Excel Overlay Modal -->
<div id="importModal" class="fixed inset-0 z-50 items-center justify-center bg-slate-950/80 backdrop-blur-md hidden transition-all duration-300 opacity-0">
    <div class="bg-slate-900 border border-slate-900 w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-2xl space-y-6 transform scale-95 transition-all duration-300">
        
        <!-- Modal Title -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="space-y-1">
                <h3 class="text-xl font-bold text-white tracking-tight">Import Pengguna</h3>
                <p class="text-xs text-slate-400 font-medium">Unggah data pengguna secara massal via file Excel.</p>
            </div>
            <button onclick="closeImportModal()" class="text-slate-500 hover:text-slate-300 transition-colors p-1.5 hover:bg-slate-800 rounded-lg cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form body -->
        <form id="importForm" action="<?= base_url('admin/import-excel') ?>" method="post" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Info Box -->
            <div class="p-3.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-bold mb-1">Format Kolom Excel (Mulai Baris 2):</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            <li><span class="font-bold">Kolom A:</span> Username</li>
                            <li><span class="font-bold">Kolom B:</span> Email</li>
                            <li><span class="font-bold">Kolom C:</span> Password</li>
                            <li><span class="font-bold">Kolom D:</span> Role (user/manager/admin)</li>
                        </ul>
                    </div>
                    <a href="<?= base_url('admin/download-import-template') ?>" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 font-bold rounded-lg transition-colors" title="Download Template Excel">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Template
                    </a>
                </div>
            </div>

            <!-- File Upload Input -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pilih File</label>
                <div class="relative group">
                    <input type="file" id="excel_file" name="excel_file" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this)">
                    <div class="w-full px-4 py-4 bg-slate-950/60 border border-dashed border-slate-700 group-hover:border-indigo-500 rounded-xl transition-all flex flex-col items-center justify-center gap-2">
                        <svg class="w-6 h-6 text-slate-500 group-hover:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span id="fileNameDisplay" class="text-sm font-semibold text-slate-400 group-hover:text-indigo-300">Pilih file .xls, .xlsx, .csv</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="closeImportModal()" class="w-1/3 py-3 bg-slate-950 hover:bg-slate-900 border border-slate-900 hover:border-slate-800 text-slate-300 hover:text-white font-bold rounded-xl text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-emerald-600/10 cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Mulai Import
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
