<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto space-y-6 pt-10">

    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative">
        <div class="absolute top-0 inset-x-0 h-1 bg-linear-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>
        
        <div class="p-8 sm:p-10 text-center space-y-6">
            <div class="w-20 h-20 mx-auto bg-indigo-500/10 rounded-full flex items-center justify-center border border-indigo-500/20 shadow-[0_0_40px_-10px_rgba(99,102,241,0.5)]">
                <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">Undangan Koperasi</h2>
                <p class="text-slate-400">Pengurus Koperasi mengundang Anda untuk bergabung secara resmi sebagai anggota.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-950/50 border border-slate-800 text-left space-y-3">
                <div class="flex justify-between items-center text-sm border-b border-slate-800/50 pb-3">
                    <span class="text-slate-500">Dikirim Oleh</span>
                    <span class="font-bold text-white">Administrator Koperasi</span>
                </div>
                <div class="flex justify-between items-center text-sm border-b border-slate-800/50 pb-3">
                    <span class="text-slate-500">Untuk</span>
                    <span class="font-bold text-indigo-400"><?= esc($invitation['email']) ?></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">Kode Undangan</span>
                    <span class="font-mono font-bold text-slate-300"><?= esc($invitation['code']) ?></span>
                </div>
            </div>
            
            <p class="text-sm text-slate-500 pb-2">Dengan menerima undangan ini, Anda menyetujui seluruh syarat & ketentuan Koperasi.</p>

            <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-slate-800">
                <form action="<?= base_url('cooperative/reject-join/' . $invitation['code']) ?>" method="post" class="flex-1" onsubmit="return confirm('Anda yakin ingin menolak undangan ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full py-3.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-300 font-bold rounded-xl text-sm transition-all cursor-pointer">
                        Tolak Undangan
                    </button>
                </form>
                
                <form action="<?= base_url('cooperative/join') ?>" method="post" class="flex-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="invitation_code" value="<?= esc($invitation['code']) ?>">
                    <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/20 cursor-pointer">
                        Ya, Bergabung
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
