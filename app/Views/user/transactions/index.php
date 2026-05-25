<?php
/**
 * @var array $incomeCategories
 * @var array $expenseCategories
 * @var array $wallets
 * @var float $totalIncome
 * @var float $totalExpense
 * @var float $netBalance
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Kelola Transaksi</h1>
            <p class="text-tx-secondary text-sm">Catat semua pemasukan dan pengeluaran Anda secara terperinci.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= base_url('wallets/transfer') ?>" class="px-5 py-3 bg-linear-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-indigo-500/10 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Transfer Saldo
            </a>
            <button type="button" class="open-modal-btn px-5 py-3 bg-linear-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-rose-500/10 flex items-center gap-2" data-type="expense">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Pengeluaran
            </button>
            <button type="button" class="open-modal-btn px-5 py-3 bg-linear-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-emerald-500/10 flex items-center gap-2" data-type="income">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Pemasukan
            </button>
        </div>
    </div>

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

    <?= $this->include('user/transactions/partials/summary_cards') ?>
    <?= $this->include('user/transactions/partials/filter_form') ?>
    <?= $this->include('user/transactions/partials/transaction_table') ?>
</div>

<?= $this->include('user/transactions/partials/transaction_modal') ?>
<?= $this->include('user/transactions/partials/adjust_balance_modal') ?>

<script>
window.TRANSACTIONS_CONFIG = {
    dataUrl: '<?= base_url('transaction/data') ?>',
    createUrl: '<?= base_url('transaction/create') ?>',
    updateUrl: '<?= base_url('transaction/update/') ?>'
};
</script>
<script src="<?= base_url('assets/js/helpers.js') ?>"></script>
<script src="<?= base_url('assets/js/modal.js') ?>"></script>
<script src="<?= base_url('assets/js/transactions.js') ?>"></script>
<?= $this->endSection() ?>
