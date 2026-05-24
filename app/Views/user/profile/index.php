<?php
/**
 * @var \CodeIgniter\Shield\Entities\User $user
 * @var int $totalTransactions
 * @var int $totalIncomeTx
 * @var int $totalExpenseTx
 * @var int $totalDebts
 * @var int $totalReceivables
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-8 max-w-5xl mx-auto">
    
    <!-- Welcome Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Pengaturan Profil</h1>
        <p class="text-tx-secondary text-sm">Kelola informasi akun Anda dan pantau statistik penggunaan aplikasi Anda.</p>
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

    <?php if (session('errors') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm space-y-1">
            <div class="flex items-center gap-2 font-bold text-rose-300">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Ada beberapa kesalahan input:
            </div>
            <ul class="list-disc list-inside text-xs pl-2 space-y-0.5 text-rose-400">
                <?php foreach (session('errors') as $error) : ?>
                    <li><?= (string) esc((string) $error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Left Column: User Card & Quick Stats -->
        <div class="space-y-6 md:col-span-1">
            <div class="bg-surface border border-br-default rounded-2xl p-6 shadow-xl text-center space-y-6 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-28 h-28 bg-brand/5 rounded-full pointer-events-none"></div>
                                <!-- Avatar Display -->
                <div class="relative w-20 h-20 mx-auto group">
                    <?php if (!empty($user->avatar) && file_exists(FCPATH . 'uploads/avatars/' . $user->avatar)) : ?>
                        <img src="<?= base_url('uploads/avatars/' . $user->avatar) ?>" alt="<?= (string) esc((string) $user->username) ?>" class="w-20 h-20 rounded-2xl object-cover border border-br-default shadow-lg shadow-brand/10">
                    <?php else : ?>
                        <div class="w-20 h-20 rounded-2xl bg-linear-to-tr from-brand to-brand-hover flex items-center justify-center font-extrabold text-white text-3xl shadow-lg shadow-brand/20">
                            <?= (string) esc(strtoupper(substr((string) $user->username, 0, 2))) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Username & Email -->
                <div class="space-y-1">
                    <h3 class="text-xl font-bold text-tx-primary tracking-tight"><?= (string) esc((string) $user->username) ?></h3>
                    <p class="text-tx-secondary text-xs truncate" title="<?= (string) esc((string) $user->email) ?>"><?= (string) esc((string) $user->email) ?></p>
                </div>

                <!-- Active Date badge -->
                <div class="pt-3 border-t border-br-default/60 text-tx-disabled text-xs">
                    Terdaftar sejak:<br>
                    <strong class="text-tx-secondary"><?= $user->created_at ? date('d F Y', strtotime($user->created_at)) : '-' ?></strong>
                </div>
            </div>

            <!-- Stats Panel -->
            <div class="bg-surface border border-br-default/80 rounded-2xl p-6 shadow-xl space-y-4">
                <h4 class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Statistik Akun</h4>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-br-default/60">
                        <span class="text-sm text-tx-secondary">Total Transaksi</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-brand/10 text-brand"><?= $totalTransactions ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-br-default/60 pl-3">
                        <span class="text-sm text-tx-disabled">↳ Pemasukan</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-success/10 text-success"><?= $totalIncomeTx ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-br-default/60 pl-3">
                        <span class="text-sm text-tx-disabled">↳ Pengeluaran</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-danger/10 text-danger"><?= $totalExpenseTx ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-br-default/60">
                        <span class="text-sm text-tx-secondary">Total Catatan Utang</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-warning/10 text-warning"><?= $totalDebts ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-tx-secondary">Total Catatan Piutang</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-info/10 text-info"><?= $totalReceivables ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Settings Form -->
        <div class="md:col-span-2 bg-surface border border-br-default rounded-2xl p-6 sm:p-8 shadow-xl">
            <h3 class="text-lg font-bold text-tx-primary tracking-tight border-b border-br-default pb-4 mb-6">Ubah Detail Akun</h3>
            
            <form action="<?= base_url('profile/update') ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Profile Picture Upload Section -->
                <div class="space-y-2 pb-4 border-b border-br-default/40">
                    <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Foto Profil</label>
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <!-- Preview Thumbnail -->
                        <div class="relative shrink-0 w-16 h-16 rounded-xl border border-br-default bg-base overflow-hidden flex items-center justify-center" id="avatarPreviewContainer">
                            <?php if (!empty($user->avatar) && file_exists(FCPATH . 'uploads/avatars/' . $user->avatar)) : ?>
                                <img src="<?= base_url('uploads/avatars/' . $user->avatar) ?>" id="avatarPreview" class="w-full h-full object-cover">
                            <?php else : ?>
                                <div class="text-brand font-bold text-lg" id="avatarInitials">
                                    <?= (string) esc(strtoupper(substr((string) $user->username, 0, 2))) ?>
                                </div>
                                <img id="avatarPreview" class="w-full h-full object-cover hidden">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Upload Controls -->
                        <div class="grow space-y-2 text-center sm:text-left">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 bg-elevated hover:bg-elevated/80 border border-br-default text-tx-primary text-xs font-bold rounded-xl transition-all shadow-md shadow-base/40">
                                    <svg class="w-4 h-4 text-tx-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Pilih Foto Baru
                                    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpg, image/jpeg, image/webp" class="hidden" onchange="previewImage(this)">
                                </label>
                                
                                <?php if (!empty($user->avatar) && file_exists(FCPATH . 'uploads/avatars/' . $user->avatar)) : ?>
                                    <button type="button" onclick="confirmDeleteAvatar()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-danger/10 hover:bg-danger/20 border border-danger/20 hover:border-danger/40 text-danger text-xs font-bold rounded-xl transition-all shadow-md shadow-base/40 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus Foto
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="text-tx-disabled text-xs">Mendukung format PNG, JPG, JPEG, atau WEBP. Maksimal 2MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Email (Read-only for security) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Alamat Email</label>
                    <input type="email" value="<?= (string) esc((string) $user->email) ?>" disabled class="w-full px-4 py-3 bg-base/40 border border-br-default text-tx-disabled rounded-xl outline-none text-sm cursor-not-allowed">
                    <p class="text-tx-disabled/80 text-xs">Email terdaftar tidak dapat diubah demi keamanan akun.</p>
                </div>

                <!-- Username -->
                <div class="space-y-1.5">
                    <label for="username" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Username</label>
                    <input type="text" id="username" name="username" value="<?= old('username', esc((string) $user->username)) ?>" required class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                </div>

                <!-- Appearance Settings / Tampilan Aplikasi -->
                <div class="pt-4 border-t border-br-default/60 space-y-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-tx-primary">Tampilan Aplikasi (Mode Tema)</h4>
                        <p class="text-tx-secondary text-xs">Pilih preferensi tema visual untuk kenyamanan mata Anda.</p>
                    </div>

                    <div class="space-y-1.5 max-w-xs">
                        <label for="theme_preference_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Tema Terpilih</label>
                        <select id="theme_preference_select" name="theme_preference" onchange="updateThemeSelection(this.value)" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                            <option value="light" <?= ($user->theme_preference ?? 'system') === 'light' ? 'selected' : '' ?>>☀️ Mode Terang (Light Mode)</option>
                            <option value="dark" <?= ($user->theme_preference ?? 'system') === 'dark' ? 'selected' : '' ?>>🌙 Mode Gelap (Dark Mode)</option>
                            <option value="system" <?= ($user->theme_preference ?? 'system') === 'system' ? 'selected' : '' ?>>💻 Ikuti Sistem OS (System Mode)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-br-default/60 space-y-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-tx-primary">Ganti Kata Sandi (Password)</h4>
                        <p class="text-tx-secondary text-xs">Kosongkan kolom di bawah ini jika Anda tidak ingin merubah kata sandi lama Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label for="password" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Password Baru</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Min. 8 karakter" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1.5">
                            <label for="password_confirm" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Ulangi Password Baru</label>
                            <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password" placeholder="Ketik ulang password baru" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-br-default/60 flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-brand hover:bg-brand-hover text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>

<!-- Hidden Delete Avatar Form -->
<?php if (!empty($user->avatar) && file_exists(FCPATH . 'uploads/avatars/' . $user->avatar)) : ?>
    <form id="deleteAvatarForm" action="<?= base_url('profile/delete-avatar') ?>" method="post" class="hidden">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<script>
    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarPreview');
                const initials = document.getElementById('avatarInitials');
                
                img.src = e.target.result;
                img.classList.remove('hidden');
                
                if (initials) {
                    initials.classList.add('hidden');
                }
            }
            reader.readAsDataURL(file);
        }
    }

    function confirmDeleteAvatar() {
        if (confirm('Apakah Anda yakin ingin menghapus foto profil saat ini?')) {
            document.getElementById('deleteAvatarForm').submit();
        }
    }
</script>
<?= $this->endSection() ?>
