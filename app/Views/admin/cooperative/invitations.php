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
        <span class="text-xs text-slate-500 font-semibold font-mono">Secure Codes Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Kode Undangan Keanggotaan</h1>
        <p class="text-slate-400 text-sm">Gunakan panel ini untuk mengesahkan dan menghasilkan tiket pendaftaran anggota baru secara otomatis.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Code Generator Column (Form) -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-6 sm:p-8 space-y-6 h-fit">
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-white tracking-tight">Generate Kode Baru</h3>
                <p class="text-xs text-slate-500">Tiket sekali pakai untuk otorisasi anggota koperasi baru.</p>
            </div>

            <form action="<?= base_url('admin/cooperative/generate-invitation') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>

                <div class="space-y-2">
                    <label for="invitation_code" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kode Undangan</label>
                    <div class="relative">
                        <input type="text" id="invitation_code" name="code" required placeholder="KOP-XXXXXX" class="w-full pl-4 pr-24 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white font-mono font-bold tracking-wider placeholder-slate-600 transition-all outline-none text-sm uppercase">
                        <button type="button" onclick="generateRandomCoopCode()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer" title="Acak kode baru">
                            Acak Kode
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-relaxed">Kode pendaftaran otomatis yang dapat disesuaikan atau diacak ulang.</p>
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Email Calon Anggota (Opsional)</label>
                    <input type="email" id="email" name="email" placeholder="penerima@example.com" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
                    <p class="text-[10px] text-slate-500 leading-relaxed">Jika diisi, kode ini dicatat khusus untuk otorisasi email tersebut.</p>
                </div>

                <button type="submit" class="w-full py-3 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/10 cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Simpan & Daftarkan Kode
                </button>
            </form>
        </div>

        <!-- Invitations List Table Column -->
        <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden md:col-span-2">
            <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white tracking-tight">Daftar Kode Undangan</h3>
                <span class="px-2 py-0.5 bg-slate-950/80 text-[10px] font-bold rounded-lg text-slate-400 border border-slate-900 uppercase">
                    Sekali Pakai
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                            <th class="py-4 px-6">Kode Undangan</th>
                            <th class="py-4 px-6">Ditujukan Kepada</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6">Pengguna Pakai</th>
                            <th class="py-4 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/60 text-sm text-slate-300">
                        <?php if (empty($invitations)) : ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500 font-semibold">Belum ada kode undangan yang dibuat.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($invitations as $inv) : ?>
                                <tr class="hover:bg-slate-950/30 transition-colors">
                                    <td class="py-4 px-6 font-medium">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-indigo-400 select-all" id="code-<?= $inv['id'] ?>"><?= (string) esc($inv['code']) ?></span>
                                            <button onclick="copyToClipboard('<?= (string) esc($inv['code']) ?>', this)" class="text-slate-500 hover:text-indigo-400 transition-colors p-1 hover:bg-slate-800 rounded-md cursor-pointer" title="Salin kode ke clipboard">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m-2 4h10m-5-5v10m-5-5h10" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-400 text-xs">
                                        <?= $inv['email'] ? (string) esc($inv['email']) : '<span class="text-slate-600 font-normal">Siapa saja (Terbuka)</span>' ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($inv['status'] === 'unused') : ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">Tersedia</span>
                                        <?php else : ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-500/10 text-slate-500 border border-slate-900 uppercase tracking-wider">Terpakai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($inv['user_name']) : ?>
                                            <span class="font-bold text-white"><?= (string) esc($inv['user_name']) ?></span>
                                            <span class="text-[10px] text-slate-500 block"><?= date('d M Y, H:i', strtotime($inv['used_at'])) ?></span>
                                        <?php else : ?>
                                            <span class="text-slate-600">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <?php if ($inv['status'] === 'unused') : ?>
                                            <form action="<?= base_url('admin/cooperative/delete-invitation/' . $inv['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus kode undangan ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition-all cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php else : ?>
                                            <span class="text-xs text-slate-600 italic">No Action</span>
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

</div>

<script>
    // Generate secure random code in KOP-XXXXXX format
    function generateRandomCoopCode() {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        let randomPart = "";
        for (let i = 0; i < 6; i++) {
            randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('invitation_code').value = 'KOP-' + randomPart;
    }

    // Copy to clipboard with premium micro-feedback
    function copyToClipboard(text, btnElement) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = btnElement.innerHTML;
            btnElement.innerHTML = `
                <svg class="w-3.5 h-3.5 text-emerald-400 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            `;
            setTimeout(() => {
                btnElement.innerHTML = originalHTML;
            }, 1500);
        });
    }

    // Auto-generate code when the page finishes loading
    document.addEventListener("DOMContentLoaded", function() {
        generateRandomCoopCode();
    });
</script>
<?= $this->endSection() ?>
