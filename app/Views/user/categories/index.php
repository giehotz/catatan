<?php
/**
 * @var array $incomeTree
 * @var array $expenseTree
 * @var array $rawIncomes
 * @var array $rawExpenses
 */

$expenseColors = [
    [
        'bg' => 'bg-rose-50 dark:bg-rose-950/25',
        'border' => 'border-rose-200/80 dark:border-rose-900/50 border-l-rose-500 border-l-4',
        'bullet' => 'text-rose-500 font-extrabold',
        'bg_hover' => 'hover:bg-rose-100/50 dark:hover:bg-rose-900/20 hover:border-rose-300/80 dark:hover:border-rose-800/60',
        'text_hover' => 'group-hover:text-rose-600 dark:group-hover:text-rose-400'
    ],
    [
        'bg' => 'bg-orange-50 dark:bg-orange-950/25',
        'border' => 'border-orange-200/80 dark:border-orange-900/50 border-l-orange-500 border-l-4',
        'bullet' => 'text-orange-500 font-extrabold',
        'bg_hover' => 'hover:bg-orange-100/50 dark:hover:bg-orange-900/20 hover:border-orange-300/80 dark:hover:border-orange-800/60',
        'text_hover' => 'group-hover:text-orange-600 dark:group-hover:text-orange-400'
    ],
    [
        'bg' => 'bg-amber-50 dark:bg-amber-950/25',
        'border' => 'border-amber-200/80 dark:border-amber-900/50 border-l-amber-500 border-l-4',
        'bullet' => 'text-amber-500 font-extrabold',
        'bg_hover' => 'hover:bg-amber-100/50 dark:hover:bg-amber-900/20 hover:border-amber-300/80 dark:hover:border-amber-800/60',
        'text_hover' => 'group-hover:text-amber-600 dark:group-hover:text-amber-400'
    ],
    [
        'bg' => 'bg-red-50 dark:bg-red-950/25',
        'border' => 'border-red-200/80 dark:border-red-900/50 border-l-red-500 border-l-4',
        'bullet' => 'text-red-500 font-extrabold',
        'bg_hover' => 'hover:bg-red-100/50 dark:hover:bg-red-900/20 hover:border-red-300/80 dark:hover:border-red-800/60',
        'text_hover' => 'group-hover:text-red-600 dark:group-hover:text-red-400'
    ],
];

$incomeColors = [
    [
        'bg' => 'bg-emerald-50 dark:bg-emerald-950/25',
        'border' => 'border-emerald-200/80 dark:border-emerald-900/50 border-l-emerald-500 border-l-4',
        'bullet' => 'text-emerald-500 font-extrabold',
        'bg_hover' => 'hover:bg-emerald-100/50 dark:hover:bg-emerald-900/20 hover:border-emerald-300/80 dark:hover:border-emerald-800/60',
        'text_hover' => 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400'
    ],
    [
        'bg' => 'bg-teal-50 dark:bg-teal-950/25',
        'border' => 'border-teal-200/80 dark:border-teal-900/50 border-l-teal-500 border-l-4',
        'bullet' => 'text-teal-500 font-extrabold',
        'bg_hover' => 'hover:bg-teal-100/50 dark:hover:bg-teal-900/20 hover:border-teal-300/80 dark:hover:border-teal-800/60',
        'text_hover' => 'group-hover:text-teal-600 dark:group-hover:text-teal-400'
    ],
    [
        'bg' => 'bg-sky-50 dark:bg-sky-950/25',
        'border' => 'border-sky-200/80 dark:border-sky-900/50 border-l-sky-500 border-l-4',
        'bullet' => 'text-sky-500 font-extrabold',
        'bg_hover' => 'hover:bg-sky-100/50 dark:hover:bg-sky-900/20 hover:border-sky-300/80 dark:hover:border-sky-800/60',
        'text_hover' => 'group-hover:text-sky-600 dark:group-hover:text-sky-400'
    ],
    [
        'bg' => 'bg-indigo-50 dark:bg-indigo-950/25',
        'border' => 'border-indigo-200/80 dark:border-indigo-900/50 border-l-indigo-500 border-l-4',
        'bullet' => 'text-indigo-500 font-extrabold',
        'bg_hover' => 'hover:bg-indigo-100/50 dark:hover:bg-indigo-900/20 hover:border-indigo-300/80 dark:hover:border-indigo-800/60',
        'text_hover' => 'group-hover:text-indigo-600 dark:group-hover:text-indigo-400'
    ],
];
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    
    <!-- Welcome Header & Add Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Kelola Kategori</h1>
            <p class="text-tx-secondary text-sm">Kelompokkan transaksi keuangan Anda ke dalam kategori utama dan subkategori.</p>
        </div>
        <button onclick="openModal()" class="px-5 py-3.5 bg-linear-to-r from-brand to-indigo-600 hover:from-brand-hover hover:to-indigo-500 text-white font-extrabold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg shadow-brand/10 flex items-center gap-2 cursor-pointer">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-2xl bg-success/15 border border-success/30 text-success text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-pulse">
            <div class="p-1.5 rounded-lg bg-success/10 border border-success/20">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <?= session('message') ?>
        </div>
    <?php endif ?>
    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-2xl bg-danger/15 border border-danger/30 text-danger text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm">
            <div class="p-1.5 rounded-lg bg-danger/10 border border-danger/20">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <!-- Dual Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- COLUMN 1: EXPENSE CATEGORIES -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 border-b border-br-subtle pb-3">
                <div class="w-2.5 h-6 bg-rose-500 rounded-full"></div>
                <h2 class="text-xl font-bold text-tx-primary">Kategori Pengeluaran</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php if (empty($expenseTree)) : ?>
                    <p class="text-tx-secondary text-sm italic col-span-2">Belum ada kategori pengeluaran.</p>
                <?php else : ?>
                    <?php foreach ($expenseTree as $index => $parent) : 
                        $color = $expenseColors[$index % count($expenseColors)];
                    ?>
                        <div class="<?= $color['bg'] ?> border <?= $color['border'] ?> <?= $color['bg_hover'] ?> rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
                            <div class="space-y-3">
                                <!-- Parent Header -->
                                <div class="flex justify-between items-start gap-2">
                                    <h3 class="font-bold text-tx-primary leading-tight wrap-break-word <?= $color['text_hover'] ?> transition-colors"><?= (string) esc($parent['name']) ?></h3>
                                    <!-- Delete Button -->
                                    <form action="<?= base_url('category/delete/expense/' . (string) $parent['id']) ?>" method="post" onsubmit="return confirm('Menghapus kategori utama akan menyetel semua subkategori menjadi Tanpa Kategori Utama. Lanjutkan?');" class="shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-1.5 text-rose-500/60 hover:text-rose-505 bg-rose-500/5 hover:bg-rose-500/10 border border-rose-500/10 hover:border-rose-500/20 rounded-lg transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Hapus Kategori Utama">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Children Subcategories -->
                                <?php if (!empty($parent['children'])) : ?>
                                    <ul class="space-y-1.5 border-t border-br-subtle/60 pt-2.5">
                                        <?php foreach ($parent['children'] as $child) : ?>
                                            <li class="flex justify-between items-center text-xs text-tx-secondary pl-1 py-1 hover:text-tx-primary transition-colors">
                                                <span class="truncate pr-2 font-medium"><span class="<?= $color['bullet'] ?>">↳</span> <?= (string) esc($child['name']) ?></span>
                                                <!-- Delete Child -->
                                                <form action="<?= base_url('category/delete/expense/' . (string) $child['id']) ?>" method="post" onsubmit="return confirm('Hapus subkategori ini?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-rose-500/50 hover:text-rose-500 transition-colors cursor-pointer transform hover:scale-110 active:scale-90">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUMN 2: INCOME CATEGORIES -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 border-b border-br-subtle pb-3">
                <div class="w-2.5 h-6 bg-emerald-500 rounded-full"></div>
                <h2 class="text-xl font-bold text-tx-primary">Kategori Pemasukan</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php if (empty($incomeTree)) : ?>
                    <p class="text-tx-secondary text-sm italic col-span-2">Belum ada kategori pemasukan.</p>
                <?php else : ?>
                    <?php foreach ($incomeTree as $index => $parent) : 
                        $color = $incomeColors[$index % count($incomeColors)];
                    ?>
                        <div class="<?= $color['bg'] ?> border <?= $color['border'] ?> <?= $color['bg_hover'] ?> rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
                            <div class="space-y-3">
                                <!-- Parent Header -->
                                <div class="flex justify-between items-start gap-2">
                                    <h3 class="font-bold text-tx-primary leading-tight wrap-break-word <?= $color['text_hover'] ?> transition-colors"><?= (string) esc($parent['name']) ?></h3>
                                    <!-- Delete Button -->
                                    <form action="<?= base_url('category/delete/income/' . (string) $parent['id']) ?>" method="post" onsubmit="return confirm('Menghapus kategori utama akan menyetel semua subkategori menjadi Tanpa Kategori Utama. Lanjutkan?');" class="shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-1.5 text-rose-500/60 hover:text-rose-505 bg-rose-500/5 hover:bg-rose-500/10 border border-rose-500/10 hover:border-rose-500/20 rounded-lg transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Hapus Kategori Utama">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <!-- Children Subcategories -->
                                <?php if (!empty($parent['children'])) : ?>
                                    <ul class="space-y-1.5 border-t border-br-subtle/60 pt-2.5">
                                        <?php foreach ($parent['children'] as $child) : ?>
                                            <li class="flex justify-between items-center text-xs text-tx-secondary pl-1 py-1 hover:text-tx-primary transition-colors">
                                                <span class="truncate pr-2 font-medium"><span class="<?= $color['bullet'] ?>">↳</span> <?= (string) esc($child['name']) ?></span>
                                                <!-- Delete Child -->
                                                <form action="<?= base_url('category/delete/income/' . (string) $child['id']) ?>" method="post" onsubmit="return confirm('Hapus subkategori ini?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="text-rose-500/50 hover:text-rose-500 transition-colors cursor-pointer transform hover:scale-110 active:scale-90">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal Tambah Kategori -->
<div id="addModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden opacity-0 transition-all duration-300">
    <div class="bg-surface border border-br-default/60 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative">
        <!-- Top accent decoration line -->
        <div class="h-1.5 w-full bg-linear-to-r from-brand via-indigo-500 to-purple-500"></div>

        <!-- Close Button -->
        <button onclick="closeModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 class="text-xl font-bold text-tx-primary tracking-tight">Tambah Kategori</h3>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed">Buat kategori baru atau jadikan sebagai subkategori.</p>
        </div>

        <!-- Body -->
        <form action="<?= base_url('category/create') ?>" method="post" class="p-6 space-y-5">
            <?= csrf_field() ?>
            
            <!-- Real parent ID field containing synchronized selection -->
            <input type="hidden" name="parent_id" id="parent_id" value="">

            <!-- Type -->
            <div class="space-y-1.5">
                <label for="type" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Tipe Kategori</label>
                <div class="relative flex items-center">
                    <select id="type" name="type" onchange="toggleParentOptions()" class="w-full pl-4 pr-10 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom appearance-none cursor-pointer">
                        <option value="expense" class="bg-surface text-tx-primary">Pengeluaran</option>
                        <option value="income" class="bg-surface text-tx-primary">Pemasukan</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Nama Kategori</label>
                <input type="text" id="name" name="name" required placeholder="Contoh: Bensin, Uang Saku, Kopi" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
            </div>

            <!-- Parent Income Container -->
            <div id="parent_income_container" class="space-y-1.5 hidden">
                <label for="parent_income_select" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Kategori Utama (Opsional)</label>
                <div class="relative flex items-center">
                    <select id="parent_income_select" onchange="syncParent()" class="w-full pl-4 pr-10 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom appearance-none cursor-pointer">
                        <option value="" class="bg-surface text-tx-primary">-- Tanpa Kategori Utama (Sebagai Utama) --</option>
                        <?php foreach ($rawIncomes as $cat) : ?>
                            <?php if ($cat['parent_id'] === null) : ?>
                                <option value="<?= (string) $cat['id'] ?>" class="bg-surface text-tx-primary"><?= (string) esc($cat['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Parent Expense Container -->
            <div id="parent_expense_container" class="space-y-1.5">
                <label for="parent_expense_select" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Kategori Utama (Opsional)</label>
                <div class="relative flex items-center">
                    <select id="parent_expense_select" onchange="syncParent()" class="w-full pl-4 pr-10 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom appearance-none cursor-pointer">
                        <option value="" class="bg-surface text-tx-primary">-- Tanpa Kategori Utama (Sebagai Utama) --</option>
                        <?php foreach ($rawExpenses as $cat) : ?>
                            <?php if ($cat['parent_id'] === null) : ?>
                                <option value="<?= (string) $cat['id'] ?>" class="bg-surface text-tx-primary"><?= (string) esc($cat['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-4 border-t border-br-subtle">
                <button type="button" onclick="closeModal()" class="w-1/3 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3.5 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all shadow-lg shadow-brand/10 flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Open Modal
    function openModal() {
        const modal = document.getElementById('addModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 50);
        toggleParentOptions();
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

    // Toggle parent options appropriate for selected type
    function toggleParentOptions() {
        const type = document.getElementById('type').value;
        const incomeContainer = document.getElementById('parent_income_container');
        const expenseContainer = document.getElementById('parent_expense_container');

        if (type === 'income') {
            incomeContainer.classList.remove('hidden');
            expenseContainer.classList.add('hidden');
        } else {
            incomeContainer.classList.add('hidden');
            expenseContainer.classList.remove('hidden');
        }
        syncParent();
    }

    // Sync selected parent category ID to the hidden input field
    function syncParent() {
        const type = document.getElementById('type').value;
        const realParentInput = document.getElementById('parent_id');

        if (type === 'income') {
            const select = document.getElementById('parent_income_select');
            realParentInput.value = select ? select.value : '';
        } else {
            const select = document.getElementById('parent_expense_select');
            realParentInput.value = select ? select.value : '';
        }
    }
</script>
<?= $this->endSection() ?>
