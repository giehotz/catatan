<div class="bg-surface/40 border border-br-default rounded-2xl p-6 shadow-xl">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
        <div class="space-y-1.5 sm:col-span-2 md:col-span-3 lg:col-span-2">
            <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Cari Deskripsi</label>
            <input type="text" id="filter_search" placeholder="Contoh: Belanja Bulanan" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm">
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe Transaksi</label>
            <select id="filter_type" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                <option value="">Semua Tipe</option>
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Rekening/Dompet</label>
            <select id="filter_wallet_id" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                <option value="">Semua Rekening</option>
                <?php foreach ($wallets as $w) : ?>
                    <option value="<?= $w['id'] ?>"><?= (string) esc($w['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" id="filter_start_date" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Tanggal Akhir</label>
            <input type="date" id="filter_end_date" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
        </div>
        <div class="flex gap-2 sm:col-span-2 md:col-span-2 lg:col-span-2">
            <button id="filterBtn" class="grow px-4 py-2.5 bg-brand hover:bg-brand-hover text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-brand/10">Filter</button>
            <button id="resetBtn" class="px-4 py-2.5 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold text-sm rounded-xl transition-all flex items-center justify-center shrink-0" title="Reset Filter">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </button>
        </div>
    </div>
</div>
