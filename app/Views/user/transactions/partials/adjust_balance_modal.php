<div id="adjustModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-base/80 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-md shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <button type="button" class="modal-close absolute right-4 top-4 text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 class="text-xl font-bold text-tx-primary">Sesuaikan Saldo Dompet</h3>
            <p class="text-tx-secondary text-xs mt-1">Ubah saldo secara manual. Sistem akan mencatat transaksi penyesuaian otomatis di dalam rekening terpilih agar saldo sesuai.</p>
        </div>
        <form action="<?= base_url('transaction/adjust-balance') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <div class="space-y-1.5">
                <label for="adjust_wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Pilih Rekening Terkait</label>
                <select id="adjust_wallet_id" name="wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-semibold">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= $w['id'] ?>" data-balance="<?= floatval($w['balance']) ?>"><?= (string) esc($w['name']) ?> (Saldo saat ini: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1.5">
                <label for="target_balance" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Target Saldo Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">Rp</div>
                    <input type="text" id="target_balance" name="target_balance" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                </div>
            </div>
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" class="modal-close w-1/3 py-3 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all">Batal</button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
