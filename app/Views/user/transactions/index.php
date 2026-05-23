<?php

/**
 * @var array $transactions
 * @var array $incomeCategories
 * @var array $expenseCategories
 * @var array $wallets
 * @var float $totalIncome
 * @var float $totalExpense
 * @var float $netBalance
 * @var string|null $filterType
 * @var string|null $filterStartDate
 * @var string|null $filterEndDate
 * @var string|null $filterSearch
 * @var string|null $filterWallet
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-8">

    <!-- Welcome Header & Add Button -->
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
            <button onclick="openModal('expense')" class="px-5 py-3 bg-linear-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-rose-500/10 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Pengeluaran
            </button>
            <button onclick="openModal('income')" class="px-5 py-3 bg-linear-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-sm rounded-xl transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-emerald-500/10 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Catat Pemasukan
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Saldo Bersih -->
        <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-brand/5 rounded-full pointer-events-none"></div>
            <div class="flex justify-between items-center relative z-10">
                <span class="text-sm font-semibold text-tx-secondary">Saldo Bersih Periode Ini</span>
                <button onclick="openAdjustModal()" class="p-1.5 text-brand/80 hover:text-brand bg-brand/5 hover:bg-brand/15 border border-brand/10 hover:border-brand/20 rounded-lg transition-all cursor-pointer" title="Sesuaikan Saldo Bersih Rekening">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>
            <h3 class="text-3xl font-bold mt-2 tracking-tight <?= $netBalance >= 0 ? 'text-brand' : 'text-danger' ?> relative z-10">
                Rp<?= number_format($netBalance, 0, ',', '.') ?>
            </h3>
        </div>
        <!-- Pemasukan -->
        <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-success/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-tx-secondary">Total Pemasukan</span>
            <h3 class="text-3xl font-bold text-success mt-2 tracking-tight">
                Rp<?= number_format($totalIncome, 0, ',', '.') ?>
            </h3>
        </div>
        <!-- Pengeluaran -->
        <div class="bg-surface/60 p-6 rounded-2xl border border-br-default shadow-xl relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-danger/5 rounded-full pointer-events-none"></div>
            <span class="text-sm font-semibold text-tx-secondary">Total Pengeluaran</span>
            <h3 class="text-3xl font-bold text-danger mt-2 tracking-tight">
                Rp<?= number_format($totalExpense, 0, ',', '.') ?>
            </h3>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-surface/40 border border-br-default rounded-2xl p-6 shadow-xl">
        <form method="get" action="<?= url_to('transaction') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- Search -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Cari Deskripsi</label>
                <input type="text" name="search" value="<?= (string) esc($filterSearch) ?>" placeholder="Contoh: Belanja Bulanan" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm">
            </div>
            <!-- Type -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe Transaksi</label>
                <select name="type" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
            <!-- Wallet Filter -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Rekening/Dompet</label>
                <select name="wallet_id" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <option value="">Semua Rekening</option>
                    <?php foreach ($wallets as $w) : ?>
                        <option value="<?= $w['id'] ?>" <?= $filterWallet == $w['id'] ? 'selected' : '' ?>><?= (string) esc($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Start Date -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= (string) esc($filterStartDate) ?>" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
            </div>
            <!-- End Date / Buttons -->
            <div class="space-y-1.5 grid grid-cols-2 gap-3">
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="<?= (string) esc($filterEndDate) ?>" class="w-full px-4 py-2.5 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                </div>
                <div class="flex gap-2 col-span-2 sm:col-span-1">
                    <button type="submit" class="grow px-4 py-2.5 bg-brand hover:bg-brand-hover text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-brand/10">
                        Filter
                    </button>
                    <a href="<?= url_to('transaction') ?>" class="px-4 py-2.5 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold text-sm rounded-xl transition-all flex items-center justify-center" title="Reset Filter">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions List Container -->
    <div class="bg-surface/40 border border-br-default rounded-2xl shadow-xl overflow-hidden">
        <?php if (empty($transactions)) : ?>
            <!-- Empty State -->
            <div class="p-12 text-center space-y-6">
                <div class="w-16 h-16 bg-elevated/40 rounded-2xl border border-br-default flex items-center justify-center mx-auto text-tx-secondary">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-lg font-bold text-tx-primary">Belum Ada Transaksi</h4>
                    <p class="text-tx-secondary text-sm">Tidak menemukan transaksi yang cocok. Mulai tambahkan transaksi keuangan Anda sekarang!</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-brand hover:bg-brand-hover text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-brand/10">
                    + Tambah Transaksi Pertama
                </button>
            </div>
        <?php else : ?>
            <!-- Responsive Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-tx-secondary">
                    <thead class="bg-base/60 text-tx-secondary uppercase text-xs">
                        <tr>
                            <th class="py-3.5 px-6">Tanggal</th>
                            <th class="py-3.5 px-6">Tipe</th>
                            <th class="py-3.5 px-6">Rekening</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Deskripsi</th>
                            <th class="py-3.5 px-6 text-right">Jumlah</th>
                            <th class="py-3.5 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-br-default/60">
                        <?php foreach ($transactions as $tx) : ?>
                            <tr class="hover:bg-elevated/30 transition-colors">
                                <td class="py-4 px-6 font-medium text-tx-secondary whitespace-nowrap">
                                    <?= date('d M Y', strtotime($tx['transaction_date'])) ?>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <?php if ($tx['type'] === 'income') : ?>
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-400">Pemasukan</span>
                                    <?php elseif ($tx['type'] === 'expense') : ?>
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-500/10 text-rose-400">Pengeluaran</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-400">Transfer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <?php if ($tx['type'] === 'transfer') : ?>
                                        <span class="text-xs font-semibold text-tx-primary bg-elevated border border-br-default px-2.5 py-1 rounded-lg">
                                            <?= (string) esc($tx['wallet_name']) ?> ➔ <?= (string) esc($tx['to_wallet_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-brand bg-brand/10 border border-brand/20 px-2.5 py-1 rounded-full">
                                            <?= (string) esc($tx['wallet_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 font-semibold text-tx-primary whitespace-nowrap">
                                    <?php if ($tx['type'] === 'transfer') : ?>
                                        <span class="text-brand font-bold text-xs">Transfer Saldo</span>
                                    <?php else: ?>
                                        <?= (string) esc($tx['category_name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-tx-secondary">
                                    <?= (string) esc($tx['description'] ?: '-') ?>
                                </td>
                                <td class="py-4 px-6 text-right font-bold whitespace-nowrap">
                                    <?php if ($tx['type'] === 'income') : ?>
                                        <span class="text-success">+ Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                    <?php elseif ($tx['type'] === 'expense') : ?>
                                        <span class="text-danger">- Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                    <?php else : ?>
                                        <span class="text-tx-secondary">Rp<?= number_format($tx['amount'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap flex justify-center gap-2">
                                    <?php if ($tx['type'] !== 'transfer') : ?>
                                        <!-- Edit Button -->
                                        <button onclick="openEditModal(<?= (int)$tx['id'] ?>, '<?= (string)$tx['type'] ?>', '<?= date('Y-m-d', strtotime((string)$tx['transaction_date'])) ?>', <?= (int)$tx['wallet_id'] ?>, <?= (int)$tx['category_id'] ?>, <?= floatval($tx['amount']) ?>, '<?= (string) esc((string)$tx['description'], 'js') ?>')" class="p-2 text-brand/70 hover:text-brand bg-brand/5 hover:bg-brand/15 border border-brand/10 hover:border-brand/20 rounded-lg transition-all" title="Edit Transaksi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <form action="<?= base_url('transaction/delete/' . $tx['id']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2 text-danger/70 hover:text-danger bg-danger/5 hover:bg-danger/15 border border-danger/10 hover:border-danger/20 rounded-lg transition-all" title="Hapus Transaksi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah Transaksi -->
<div id="addModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-base/80 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <!-- Close Button -->
        <button onclick="closeModal()" class="absolute right-4 top-4 text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 class="text-xl font-bold text-tx-primary">Tambah Transaksi</h3>
            <p class="text-tx-secondary text-xs mt-1">Isi formulir di bawah ini untuk mencatat transaksi baru.</p>
        </div>

        <!-- Body -->
        <form action="<?= base_url('transaction/create') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Real category ID field containing synchronized selection -->
            <input type="hidden" name="category_id" id="category_id" value="">

            <div class="grid grid-cols-2 gap-4">
                <!-- Type -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe</label>
                    <select id="type" name="type" onchange="toggleCategoryOptions()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                </div>

                <!-- Transaction Date -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="transaction_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal</label>
                    <input type="date" id="transaction_date" name="transaction_date" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Wallet Selection -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Rekening / Dompet</label>
                    <select id="wallet_id" name="wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <?php foreach ($wallets as $w) : ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface text-tx-primary"><?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Amount -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Jumlah (Nominal)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">
                            Rp
                        </div>
                        <input type="text" id="amount" name="amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                    </div>
                </div>
            </div>

            <!-- Income Category Container -->
            <div id="category_income_container" class="space-y-1.5 hidden">
                <label for="income_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pemasukan</label>
                <select id="income_category_select" onchange="syncCategory()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($incomeCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Expense Category Container -->
            <div id="category_expense_container" class="space-y-1.5">
                <label for="expense_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pengeluaran</label>
                <select id="expense_category_select" onchange="syncCategory()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($expenseCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Deskripsi (Keterangan)</label>
                <input type="text" id="description" name="description" placeholder="Contoh: Makan siang nasi padang" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm">
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" onclick="closeModal()" class="w-1/3 py-3 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Transaksi -->
<div id="editModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-base/80 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <!-- Close Button -->
        <button onclick="closeEditModal()" class="absolute right-4 top-4 text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 class="text-xl font-bold text-tx-primary">Edit Transaksi</h3>
            <p class="text-tx-secondary text-xs mt-1">Perbarui data transaksi yang dipilih.</p>
        </div>

        <!-- Body -->
        <form id="editForm" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <input type="hidden" name="category_id" id="edit_category_id" value="">

            <div class="grid grid-cols-2 gap-4">
                <!-- Type -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="edit_type" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tipe</label>
                    <select id="edit_type" name="type" onchange="toggleEditCategoryOptions()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                </div>

                <!-- Transaction Date -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="edit_transaction_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Tanggal</label>
                    <input type="date" id="edit_transaction_date" name="transaction_date" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Wallet Selection -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="edit_wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Rekening / Dompet</label>
                    <select id="edit_wallet_id" name="wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                        <?php foreach ($wallets as $w) : ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface text-tx-primary"><?= (string) esc($w['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Amount -->
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label for="edit_amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Jumlah (Nominal)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">
                            Rp
                        </div>
                        <input type="text" id="edit_amount" name="amount" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                    </div>
                </div>
            </div>

            <!-- Income Category Container -->
            <div id="edit_category_income_container" class="space-y-1.5 hidden">
                <label for="edit_income_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pemasukan</label>
                <select id="edit_income_category_select" onchange="syncEditCategory()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($incomeCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Expense Category Container -->
            <div id="edit_category_expense_container" class="space-y-1.5 hidden">
                <label for="edit_expense_category_select" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Kategori Pengeluaran</label>
                <select id="edit_expense_category_select" onchange="syncEditCategory()" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm cursor-pointer">
                    <?php foreach ($expenseCategories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= (string) esc($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="edit_description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Deskripsi (Keterangan)</label>
                <input type="text" id="edit_description" name="description" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm">
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" onclick="closeEditModal()" class="w-1/3 py-3 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Penyesuaian Saldo -->
<div id="adjustModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-base/80 backdrop-blur-sm hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default rounded-2xl w-full max-w-md shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <!-- Close Button -->
        <button onclick="closeAdjustModal()" class="absolute right-4 top-4 text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 class="text-xl font-bold text-tx-primary">Sesuaikan Saldo Dompet</h3>
            <p class="text-tx-secondary text-xs mt-1">Ubah saldo secara manual. Sistem akan mencatat transaksi penyesuaian otomatis di dalam rekening terpilih agar saldo sesuai.</p>
        </div>

        <!-- Body -->
        <form action="<?= base_url('transaction/adjust-balance') ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Wallet Select for Adjustment -->
            <div class="space-y-1.5">
                <label for="adjust_wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Pilih Rekening Terkait</label>
                <select id="adjust_wallet_id" name="wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-semibold select-custom">
                    <?php foreach ($wallets as $w): ?>
                        <option value="<?= $w['id'] ?>" data-balance="<?= floatval($w['balance']) ?>" class="bg-surface text-tx-primary">
                            <?= (string) esc($w['name']) ?> (Saldo saat ini: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Target Balance Input -->
            <div class="space-y-1.5">
                <label for="target_balance" class="text-xs font-bold text-tx-secondary uppercase tracking-wider">Target Saldo Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">
                        Rp
                    </div>
                    <input type="text" id="target_balance" name="target_balance" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
                </div>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-3 border-t border-br-subtle">
                <button type="button" onclick="closeAdjustModal()" class="w-1/3 py-3 bg-elevated hover:bg-elevated/80 text-tx-primary border border-br-default font-bold rounded-xl text-sm transition-all">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open Edit Modal and Populate Fields
    function openEditModal(id, type, date, wallet_id, category_id, amount, description) {
        document.getElementById('editForm').action = '<?= base_url('transaction/update/') ?>' + id;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_transaction_date').value = date;
        document.getElementById('edit_wallet_id').value = wallet_id;
        document.getElementById('edit_description').value = description;

        let cleanAmount = Math.abs(amount).toString();
        document.getElementById('edit_amount').value = cleanAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        toggleEditCategoryOptions();

        if (type === 'income') {
            document.getElementById('edit_income_category_select').value = category_id;
        } else {
            document.getElementById('edit_expense_category_select').value = category_id;
        }
        syncEditCategory();

        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 50);
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    function toggleEditCategoryOptions() {
        const type = document.getElementById('edit_type').value;
        const incomeSelect = document.getElementById('edit_category_income_container');
        const expenseSelect = document.getElementById('edit_category_expense_container');

        if (type === 'income') {
            incomeSelect.classList.remove('hidden');
            expenseSelect.classList.add('hidden');
        } else {
            incomeSelect.classList.add('hidden');
            expenseSelect.classList.remove('hidden');
        }
        syncEditCategory();
    }

    function syncEditCategory() {
        const type = document.getElementById('edit_type').value;
        const realCategoryInput = document.getElementById('edit_category_id');

        if (type === 'income') {
            const select = document.getElementById('edit_income_category_select');
            realCategoryInput.value = select ? select.value : '';
        } else {
            const select = document.getElementById('edit_expense_category_select');
            realCategoryInput.value = select ? select.value : '';
        }
    }

    // Open Modal with optional preselected type
    function openModal(type = null) {
        if (type) {
            document.getElementById('type').value = type;
        }
        const modal = document.getElementById('addModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 50);
        toggleCategoryOptions();
    }

    // Close Modal
    function closeModal() {
        const modal = document.getElementById('addModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    // Open Adjust Modal
    function openAdjustModal() {
        const modal = document.getElementById('adjustModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 50);

        updateAdjustTargetBalance();
    }

    // Dynamically set adjustment target balance value as clean formatted number from data-balance
    function updateAdjustTargetBalance() {
        const select = document.getElementById('adjust_wallet_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;
        const currentBalance = parseInt(selectedOption.getAttribute('data-balance') || 0);

        const targetInput = document.getElementById('target_balance');
        if (targetInput) {
            let isNeg = currentBalance < 0;
            let cleanVal = Math.abs(currentBalance).toString();
            let formatted = cleanVal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            targetInput.value = (isNeg ? '-' : '') + formatted;
        }
    }

    // Close Adjust Modal
    function closeAdjustModal() {
        const modal = document.getElementById('adjustModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    // Toggle categories appropriate for the active type
    function toggleCategoryOptions() {
        const type = document.getElementById('type').value;
        const incomeSelect = document.getElementById('category_income_container');
        const expenseSelect = document.getElementById('category_expense_container');

        if (type === 'income') {
            incomeSelect.classList.remove('hidden');
            expenseSelect.classList.add('hidden');
        } else {
            incomeSelect.classList.add('hidden');
            expenseSelect.classList.remove('hidden');
        }
        syncCategory();
    }

    // Sync selected drop-down category ID to the hidden input field
    function syncCategory() {
        const type = document.getElementById('type').value;
        const realCategoryInput = document.getElementById('category_id');

        if (type === 'income') {
            const select = document.getElementById('income_category_select');
            realCategoryInput.value = select ? select.value : '';
        } else {
            const select = document.getElementById('expense_category_select');
            realCategoryInput.value = select ? select.value : '';
        }
    }

    // Auto-open modal based on URL query parameter action
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        if (action === 'add_income') {
            openModal('income');
        } else if (action === 'add_expense') {
            openModal('expense');
        }

        // Bind change listener for adjust select dropdown
        const adjustSelect = document.getElementById('adjust_wallet_id');
        if (adjustSelect) {
            adjustSelect.addEventListener('change', updateAdjustTargetBalance);
        }

        // Live dot formatting for amount input
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formattedValue;

                let newLength = formattedValue.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });

            // Strip dots on submit
            const form = amountInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    amountInput.value = amountInput.value.replace(/\D/g, "");
                });
            }
        }

        // Live dot formatting for target balance input
        const targetBalanceInput = document.getElementById('target_balance');
        if (targetBalanceInput) {
            targetBalanceInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let hasMinus = e.target.value.startsWith('-');
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                if (hasMinus && cleanValue !== '') {
                    formattedValue = '-' + formattedValue;
                } else if (hasMinus) {
                    formattedValue = '-';
                }
                e.target.value = formattedValue;

                let newLength = formattedValue.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });

            // Strip dots on submit
            const form = targetBalanceInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    let hasMinus = targetBalanceInput.value.startsWith('-');
                    let clean = targetBalanceInput.value.replace(/\D/g, "");
                    targetBalanceInput.value = (hasMinus ? '-' : '') + clean;
                });
            }
        }
        // Live dot formatting for edit amount input
        const editAmountInput = document.getElementById('edit_amount');
        if (editAmountInput) {
            editAmountInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let cleanValue = e.target.value.replace(/\D/g, "");
                let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                e.target.value = formattedValue;

                let newLength = formattedValue.length;
                cursorPosition = cursorPosition + (newLength - originalLength);
                e.target.setSelectionRange(cursorPosition, cursorPosition);
            });

            // Strip dots on submit
            const form = editAmountInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    editAmountInput.value = editAmountInput.value.replace(/\D/g, "");
                });
            }
        }
    });
</script>
<?= $this->endSection() ?>