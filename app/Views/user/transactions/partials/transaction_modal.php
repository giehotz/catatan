<div id="transactionModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-base/80 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <button type="button" class="modal-close absolute right-4 top-4 text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 id="modalTitle" class="text-xl font-bold text-tx-primary">Tambah Transaksi</h3>
            <p id="modalSubtitle" class="text-tx-secondary text-xs mt-1">Isi formulir di bawah ini untuk mencatat transaksi baru.</p>
        </div>
        <form id="transactionForm" action="<?= base_url('transaction/create') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="category_id" id="category_id" value="">
            <input type="hidden" id="transaction_id" name="transaction_id" value="">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe</label>
                    <select id="type" name="type" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                </div>
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="transaction_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal</label>
                    <input type="date" id="transaction_date" name="transaction_date" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Rekening / Dompet</label>
                    <select id="wallet_id" name="wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <?php foreach ($wallets as $w) : ?>
                            <option value="<?= $w['id'] ?>"><?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Jumlah (Nominal)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">Rp</div>
                        <input type="text" id="amount" name="amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                    </div>
                </div>
            </div>
            <div id="category_income_container" class="space-y-1.5 hidden">
                <label for="income_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pemasukan</label>
                <select id="income_category_select" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($incomeCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="category_expense_container" class="space-y-1.5">
                <label for="expense_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pengeluaran</label>
                <select id="expense_category_select" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($expenseCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Deskripsi (Keterangan)</label>
                <input type="text" id="description" name="description" placeholder="Contoh: Makan siang nasi padang" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm">
            </div>
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" class="modal-close w-1/3 py-3 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all">Batal</button>
                <button type="submit" id="modalSubmitBtn" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>
