<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto my-12 bg-surface/60 p-8 rounded-2xl border border-br-default shadow-2xl relative overflow-hidden">
    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-brand/5 rounded-full pointer-events-none"></div>
    <div class="absolute -left-6 -top-6 w-32 h-32 bg-brand/5 rounded-full pointer-events-none"></div>

    <div class="space-y-6">
        <div class="text-center space-y-2">
            <h2 class="text-3xl font-extrabold text-tx-primary tracking-tight">Selamat Datang Kembali</h2>
            <p class="text-tx-secondary text-sm">Masuk ke akun Anda untuk memantau catatan keuangan.</p>
        </div>

        <?php if (session('error') !== null) : ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                <?= session('error') ?>
            </div>
        <?php elseif (session('errors') !== null) : ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm space-y-1">
                <?php foreach (session('errors') as $error) : ?>
                    <p><?= $error ?></p>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <?php if (session('message') !== null) : ?>
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                <?= session('message') ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('login') ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Email -->
            <div class="space-y-1.5">
                <label for="floatingEmailInput" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Alamat Email</label>
                <input type="email" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled/50 transition-all outline-none" id="floatingEmailInput" name="email" inputmode="email" placeholder="nama@email.com" value="<?= old('email') ?>" required>
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="floatingPasswordInput" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kata Sandi</label>
                <div class="relative">
                    <input type="password" class="w-full pl-4 pr-12 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled/50 transition-all outline-none" id="floatingPasswordInput" name="password" inputmode="text" placeholder="Masukkan kata sandi" required>
                    <button type="button" onclick="togglePasswordVisibility('floatingPasswordInput', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-tx-secondary hover:text-brand transition-colors cursor-pointer" title="Tampilkan kata sandi">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                <div class="flex items-center gap-2 py-1">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-br-default bg-base text-brand focus:ring-brand focus:ring-offset-base cursor-pointer" id="remember" <?php if (old('remember')): ?> checked <?php endif ?>>
                    <label for="remember" class="text-sm text-tx-secondary select-none cursor-pointer">Ingat saya di perangkat ini</label>
                </div>
            <?php endif; ?>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 mt-2 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">
                Masuk Sekarang
            </button>
        </form>

        <?php if (setting('Auth.allowRegistration')) : ?>
            <div class="text-center pt-2">
                <p class="text-sm text-tx-secondary">
                    Belum punya akun? 
                    <a href="<?= url_to('register') ?>" class="text-brand hover:text-brand-hover font-semibold transition-colors">Daftar sekarang</a>
                </p>
            </div>
        <?php endif ?>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const svg = button.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        // Eye-off icon (slashed eye)
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
        `;
        button.title = "Sembunyikan kata sandi";
    } else {
        input.type = 'password';
        // Normal Eye icon
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
        button.title = "Tampilkan kata sandi";
    }
}
</script>
<?= $this->endSection() ?>
