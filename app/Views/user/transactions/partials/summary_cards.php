<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-brand/5 rounded-full pointer-events-none"></div>
        <div class="flex justify-between items-center relative z-10">
            <span class="text-sm font-semibold text-tx-secondary">Saldo Bersih Periode Ini</span>
            <button type="button" class="open-adjust-btn p-1.5 text-brand/80 hover:text-brand bg-brand/5 hover:bg-brand/15 border border-brand/10 hover:border-brand/20 rounded-lg transition-all cursor-pointer" title="Sesuaikan Saldo Bersih Rekening">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        </div>
        <h3 id="dt-net-balance" class="text-3xl font-bold mt-2 tracking-tight <?= $netBalance >= 0 ? 'text-brand' : 'text-danger' ?> relative z-10">
            Rp<?= number_format($netBalance, 0, ',', '.') ?>
        </h3>
    </div>
    <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-success/5 rounded-full pointer-events-none"></div>
        <span class="text-sm font-semibold text-tx-secondary">Total Pemasukan</span>
        <h3 id="dt-total-income" class="text-3xl font-bold text-success mt-2 tracking-tight">
            Rp<?= number_format($totalIncome, 0, ',', '.') ?>
        </h3>
    </div>
    <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-danger/5 rounded-full pointer-events-none"></div>
        <span class="text-sm font-semibold text-tx-secondary">Total Pengeluaran</span>
        <h3 id="dt-total-expense" class="text-3xl font-bold text-danger mt-2 tracking-tight">
            Rp<?= number_format($totalExpense, 0, ',', '.') ?>
        </h3>
    </div>
</div>
