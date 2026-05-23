<?php
/**
 * @var \App\Entities\User[] $users
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="relative z-10 space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('admin/cooperative') ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-400 hover:text-white bg-slate-800/50 hover:bg-slate-700 rounded-lg border border-slate-700/50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Kirim Pesan & Undangan</h2>
                <p class="text-sm text-slate-400 mt-1">Broadcast pesan massal atau kirim Magic Link Undangan ke pengguna.</p>
            </div>
        </div>
    </div>

    <?php if (session()->has('message')) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) esc(session('message')) ?>
        </div>
    <?php endif ?>

    <?php if (session()->has('error')) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) esc(session('error')) ?>
        </div>
    <?php endif ?>

    <?php if (session()->has('errors')) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <ul class="list-disc list-inside">
                <?php foreach (session('errors') as $error) : ?>
                    <li><?= (string) esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form action="<?= base_url('admin/cooperative/messages/broadcast') ?>" method="post" id="broadcastForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6" onsubmit="return confirm('Pesan akan dikirim ke pengguna terpilih. Lanjutkan?');">
        <?= csrf_field() ?>
        
        <!-- Left Column: User Selection -->
        <div class="lg:col-span-1 space-y-4 bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-white">Pilih Penerima</h3>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-300 cursor-pointer">
                    <input type="checkbox" id="selectAll" class="rounded bg-slate-950 border-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900">
                    Pilih Semua
                </label>
            </div>
            
            <!-- Filters -->
            <div class="flex gap-2 mb-4">
                <button type="button" onclick="filterUsers('all')" class="flex-1 py-1.5 px-2 text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors border border-slate-700">Semua</button>
                <button type="button" onclick="filterUsers('non-member')" class="flex-1 py-1.5 px-2 text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors border border-slate-700">Non-Anggota</button>
                <button type="button" onclick="filterUsers('member')" class="flex-1 py-1.5 px-2 text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors border border-slate-700">Anggota</button>
            </div>
            
            <div class="space-y-2 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar" id="userList">
                <?php foreach ($users as $user): ?>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/50 cursor-pointer transition-colors user-item" data-status="<?= $user->is_member ? 'member' : 'non-member' ?>">
                        <input type="checkbox" name="user_ids[]" value="<?= $user->id ?>" class="user-checkbox rounded bg-slate-900 border-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-950">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white truncate"><?= esc($user->username) ?></p>
                            <p class="text-xs text-slate-500 truncate"><?= esc($user->email) ?></p>
                        </div>
                        <?php if ($user->is_member): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/20">Anggota</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Non</span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column: Message Content -->
        <div class="lg:col-span-2 space-y-5 bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-white border-b border-slate-800 pb-4 mb-4">Konten Pesan</h3>
            
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tipe Pesan</label>
                <select name="type" id="messageType" required class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white transition-all outline-none text-sm font-semibold">
                    <option value="system">Pemberitahuan Sistem</option>
                    <option value="invitation">Undangan Koperasi (Magic Link)</option>
                    <option value="billing">Tagihan / Pengingat Pembayaran</option>
                </select>
                <p id="typeHint" class="text-xs text-slate-500 mt-1">Gunakan tipe Undangan untuk merekrut Non-Anggota.</p>
            </div>
            
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Subjek Pesan</label>
                <input type="text" name="subject" required value="<?= old('subject') ?>" placeholder="Contoh: Undangan Bergabung Koperasi" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-semibold">
            </div>
            
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Isi Pesan (Bisa HTML)</label>
                    <div class="text-[11px] text-slate-500">
                        Tag: <code class="text-indigo-400 bg-indigo-500/10 px-1 rounded">{nama}</code> 
                        <code class="text-indigo-400 bg-indigo-500/10 px-1 rounded hidden" id="tagKode">{kode_undangan}</code>
                        <code class="text-indigo-400 bg-indigo-500/10 px-1 rounded hidden" id="tagLink">{magic_link}</code>
                    </div>
                </div>
                <textarea name="message" required rows="8" placeholder="Halo {nama}, ..." class="w-full px-4 py-3 bg-slate-950/60 border border-slate-900 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-white placeholder-slate-600 transition-all outline-none text-sm font-medium custom-scrollbar"><?= old('message') ?></textarea>
            </div>
            
            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/20 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Kirim Pesan (Broadcast)
                </button>
            </div>
        </div>
    </form>
    </div>

<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const userItems = document.querySelectorAll('.user-item');
    const messageType = document.getElementById('messageType');
    
    // Select All functionality
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            // Only check visible items
            if (cb.closest('.user-item').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
    });
    
    // Filter functionality
    function filterUsers(status) {
        let visibleCount = 0;
        userItems.forEach(item => {
            if (status === 'all' || item.dataset.status === status) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.querySelector('.user-checkbox').checked = false; // uncheck hidden
            }
        });
        selectAll.checked = false;
        
        // Auto-select type based on filter
        if (status === 'non-member') {
            messageType.value = 'invitation';
            updateTypeHint();
        } else if (status === 'member') {
            messageType.value = 'billing';
            updateTypeHint();
        }
    }
    
    // Type Hint logic
    function updateTypeHint() {
        const hint = document.getElementById('typeHint');
        const tagKode = document.getElementById('tagKode');
        const tagLink = document.getElementById('tagLink');
        
        if (messageType.value === 'invitation') {
            hint.textContent = 'Tag {kode_undangan} dan {magic_link} wajib disertakan atau akan di-inject otomatis.';
            hint.classList.replace('text-slate-500', 'text-amber-400');
            tagKode.classList.remove('hidden');
            tagLink.classList.remove('hidden');
        } else {
            hint.textContent = 'Pesan akan dikirim normal tanpa kode undangan khusus.';
            hint.classList.replace('text-amber-400', 'text-slate-500');
            tagKode.classList.add('hidden');
            tagLink.classList.add('hidden');
        }
    }
    
    messageType.addEventListener('change', updateTypeHint);
</script>
<?= $this->endSection() ?>
