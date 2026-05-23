<?php
/**
 * @var string|null $title
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($title ?? 'Login Administrator')) ?> - Catatan Keuangan</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS production build -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Decorative blurred glowing backdrop spheres -->
    <div class="absolute w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl -top-12 -left-12 pointer-events-none"></div>
    <div class="absolute w-96 h-96 rounded-full bg-violet-500/10 blur-3xl -bottom-12 -right-12 pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-900/40 backdrop-blur-md border border-slate-900 shadow-2xl rounded-3xl p-8 sm:p-10 space-y-8 relative z-10">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-linear-to-r from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/20 items-center justify-center font-black text-white text-xl tracking-tight mb-2">
                CK
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight">Admin Portal</h2>
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-widest">Sistem Manajemen Catatan Keuangan</p>
        </div>

        <!-- Notification Banner -->
        <?php if (session('error') !== null) : ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-medium flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <?= session('error') ?>
            </div>
        <?php elseif (session('errors') !== null) : ?>
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-medium space-y-1">
                <?php foreach (session('errors') as $error) : ?>
                    <p class="flex items-center gap-2.5">
                        <span class="w-1 h-1 rounded-full bg-rose-400"></span>
                        <?= $error ?>
                    </p>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <!-- Form -->
        <form action="<?= base_url('admin/login') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Email Address -->
            <div class="space-y-2">
                <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Email Administrator</label>
                <input type="email" id="email" name="email" required value="<?= old('email') ?>" placeholder="admin@catatankeuangan.com" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-700 transition-all outline-none text-sm font-semibold">
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-4 pr-12 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-700 transition-all outline-none text-sm font-semibold">
                    <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-indigo-400 transition-colors cursor-pointer" title="Tampilkan kata sandi">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/15 cursor-pointer">
                Masuk sebagai Admin
            </button>
        </form>

        <!-- Footer Notice -->
        <div class="text-center">
            <p class="text-[11px] text-slate-600">Akses dibatasi. Halaman ini hanya untuk personel administrator yang sah. Pengguna biasa dipersilakan masuk melalui <a href="<?= base_url('login') ?>" class="text-indigo-400 hover:text-indigo-300 font-bold underline transition-colors">Portal Pengguna</a>.</p>
        </div>

    </div>
</body>

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
</html>
