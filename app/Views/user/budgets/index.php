<?php
/**
 * @var string $title
 * @var array $report
 * @var float $totalBudget
 * @var float $totalSpending
 * @var array $expenseCategories
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    
    <!-- Header Summary Widget (Ultra-Premium Dashboard Banner) -->
    <div class="bg-linear-to-br from-indigo-600 via-indigo-700 to-purple-800 text-white rounded-3xl shadow-2xl relative overflow-hidden p-6 sm:p-8 border border-white/10">
        <!-- Glowing gradient overlays -->
        <div class="absolute -right-20 -top-20 w-52 h-52 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-52 h-52 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-3">
                <span class="text-[10px] font-extrabold text-indigo-200/90 uppercase tracking-widest block">Manajemen Anggaran Bulanan</span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight drop-shadow-sm">Kendalikan Pengeluaran</h1>
                <p class="text-indigo-100 text-xs sm:text-sm">
                    Batasi pengeluaran bulanan Anda untuk setiap kategori agar impian finansial tetap terjaga.
                </p>
            </div>
            
            <button onclick="openBudgetModal()" class="px-5 py-3.5 bg-white hover:bg-indigo-50 text-indigo-700 font-extrabold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg shadow-indigo-950/15 flex items-center gap-2 hover:shadow-xl cursor-pointer">
                <svg class="w-5 h-5 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Setel Limit Anggaran
            </button>
        </div>
    </div>

    <!-- Alert Messages (Premium & Padded UI Alerts) -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 rounded-2xl bg-success/15 border border-success/30 text-success text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm animate-pulse">
            <div class="p-1.5 rounded-lg bg-success/10 border border-success/20">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 rounded-2xl bg-danger/15 border border-danger/30 text-danger text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm">
            <div class="p-1.5 rounded-lg bg-danger/10 border border-danger/20">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Overall Budgeting Health Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Total Budget Card -->
        <div class="bg-surface border border-br-default p-6 rounded-3xl shadow-xl flex flex-col justify-between hover:border-brand/20 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/5 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-tx-secondary">Total Anggaran Bulanan</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/5 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500 dark:text-indigo-400 border border-indigo-500/10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <h3 class="text-2xl font-black text-tx-primary tracking-tight">Rp<?= number_format($totalBudget, 0, ',', '.') ?></h3>
                <span class="text-[10px] font-extrabold text-tx-disabled uppercase tracking-wider block">Batas Limit Belanja</span>
            </div>
        </div>

        <!-- Total Terpakai Card -->
        <div class="bg-surface border border-br-default p-6 rounded-3xl shadow-xl flex flex-col justify-between hover:border-brand/20 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/5 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-tx-secondary">Total Belanja Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-danger/5 dark:bg-danger/10 flex items-center justify-center text-danger border border-danger/10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <h3 class="text-2xl font-black text-tx-primary tracking-tight">Rp<?= number_format($totalSpending, 0, ',', '.') ?></h3>
                <span class="text-[10px] font-extrabold text-tx-disabled uppercase tracking-wider block">Akumulasi Pengeluaran</span>
            </div>
        </div>

        <!-- Overall Health Card -->
        <div class="bg-surface border border-br-default p-6 rounded-3xl shadow-xl flex flex-col justify-between hover:border-brand/20 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/5 transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-tx-secondary">Status Anggaran Global</span>
                <?php 
                    $overallPercent = $totalBudget > 0 ? ($totalSpending / $totalBudget) * 100 : 0;
                    $healthColor = 'text-success bg-success/10 border border-success/20';
                    $healthText = 'Aman';
                    if ($overallPercent > 100) {
                        $healthColor = 'text-danger bg-danger/10 border border-danger/20';
                        $healthText = 'Overbudget';
                    } elseif ($overallPercent >= 80) {
                        $healthColor = 'text-warning bg-warning/10 border border-warning/20';
                        $healthText = 'Kritis';
                    }
                ?>
                <div class="px-2.5 py-1 text-xs font-extrabold rounded-full border <?= $healthColor ?>">
                    <?= $healthText ?>
                </div>
            </div>
            <div class="mt-4 space-y-1">
                <h3 class="text-2xl font-black text-tx-primary tracking-tight">
                    <?= $totalBudget > 0 ? round($overallPercent, 1) . '%' : '0%' ?>
                </h3>
                <span class="text-[10px] font-extrabold text-tx-disabled uppercase tracking-wider block">Rasio Anggaran Terpakai</span>
            </div>
        </div>

    </div>

    <!-- Category Budgets Grid -->
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-tx-primary tracking-tight">Daftar Anggaran per Kategori</h2>
        
        <div class="grid grid-cols-1 text-tx-primary md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($report as $item) : ?>
                <?php 
                    // Setup visual values based on status
                    $progressBarColor = 'bg-success';
                    $cardClass = 'bg-surface border border-br-default hover:border-brand/20';
                    $healthTextClass = 'text-success bg-success/5 dark:bg-success/10 border border-success/20';

                    if ($item['limit_amount'] > 0) {
                        if ($item['status_color'] === 'red') {
                            $progressBarColor = 'bg-danger';
                            $cardClass = 'bg-danger/5 dark:bg-danger/10 border-danger/25 dark:border-danger/20 hover:border-danger/35';
                            $healthTextClass = 'text-danger bg-danger/10 border border-danger/20';
                        } elseif ($item['status_color'] === 'yellow') {
                            $progressBarColor = 'bg-warning';
                            $cardClass = 'bg-warning/5 dark:bg-warning/10 border-warning/25 dark:border-warning/20 hover:border-warning/35';
                            $healthTextClass = 'text-warning bg-warning/10 border border-warning/20';
                        }
                    }
                ?>
                <div class="rounded-3xl p-5 shadow-lg flex flex-col justify-between transition-all duration-300 hover:shadow-xl <?= $cardClass ?>">
                    <div class="space-y-4">
                        <!-- Category Header -->
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <h3 class="font-bold text-base leading-tight">
                                    <?= (string) esc($item['name']) ?>
                                </h3>
                                <span class="text-[9px] font-extrabold text-tx-secondary uppercase tracking-widest block mt-1">
                                    <?= $item['is_parent'] ? 'Kategori Utama' : 'Subkategori' ?>
                                </span>
                            </div>

                            <!-- Options Button -->
                            <?php if ($item['limit_amount'] > 0) : ?>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- Edit -->
                                    <button onclick="openBudgetModal('<?= $item['category_id'] ?>', '<?= (string) esc(addslashes($item['name'])) ?>', '<?= $item['limit_amount'] ?>')" class="p-2 text-tx-secondary hover:text-brand bg-elevated hover:bg-elevated/80 border border-br-default rounded-xl transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Edit Batas Anggaran">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <!-- Delete -->
                                    <form action="<?= base_url('budgets/delete/' . $item['budgetId']) ?>" method="post" onsubmit="return confirm('Hapus batas anggaran untuk kategori ini?');" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2 text-danger/60 hover:text-danger bg-danger/5 hover:bg-danger/10 border border-danger/10 hover:border-danger/20 rounded-xl transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Hapus Batas Anggaran">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Bar & Amounts -->
                        <?php if ($item['limit_amount'] > 0) : ?>
                            <div class="space-y-3 pt-3 border-t border-br-subtle/60">
                                <div class="flex justify-between items-baseline text-xs">
                                    <span class="text-tx-secondary font-semibold">Terpakai: <strong class="text-tx-primary font-bold">Rp<?= number_format($item['spending'], 0, ',', '.') ?></strong></span>
                                    <span class="text-tx-secondary font-semibold">Limit: <strong class="text-tx-primary font-bold">Rp<?= number_format($item['limit_amount'], 0, ',', '.') ?></strong></span>
                                </div>

                                <!-- Progress Track -->
                                <div class="w-full h-3 bg-elevated border border-br-default rounded-full p-0.5 overflow-hidden shadow-inner relative">
                                    <div class="h-full rounded-full transition-all duration-500 <?= $progressBarColor ?>" style="width: <?= min($item['percent'], 100) ?>%"></div>
                                </div>

                                <div class="flex justify-between items-center pt-1">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-tx-disabled">
                                        <?= $item['is_parent'] && $item['children_count'] > 0 ? '*termasuk ' . $item['children_count'] . ' subkategori' : '' ?>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold shrink-0 border <?= $healthTextClass ?>">
                                        <?= $item['percent'] ?>% (<?= $item['status_text'] ?>)
                                    </span>
                                </div>
                            </div>
                        <?php else : ?>
                            <!-- Empty / Setup State -->
                            <div class="pt-4 border-t border-br-subtle/60 text-center space-y-3">
                                <p class="text-xs text-tx-secondary italic">Batas anggaran bulanan belum ditentukan.</p>
                                <button onclick="openBudgetModal('<?= $item['category_id'] ?>', '<?= (string) esc(addslashes($item['name'])) ?>')" class="w-full py-2.5 bg-brand/5 hover:bg-brand/10 text-brand font-bold border border-brand/10 hover:border-brand/20 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer transform hover:-translate-y-0.5 active:translate-y-0 shadow-sm hover:shadow-md">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Setel Anggaran
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Setel Anggaran Modal -->
<div id="setBudgetModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 opacity-0 transition-all duration-300">
    <div class="w-full max-w-md bg-surface border border-br-default/60 rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 relative flex flex-col">
        <!-- Top accent decoration line -->
        <div class="h-1.5 w-full bg-linear-to-r from-brand via-indigo-500 to-purple-500"></div>

        <!-- Close Button -->
        <button onclick="closeBudgetModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-br-subtle">
            <h3 id="modalTitle" class="text-xl font-bold text-tx-primary tracking-tight">Setel Anggaran Bulanan</h3>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed">Buat limit batasan pengeluaran bulanan kategori terpilih.</p>
        </div>

        <!-- Modal Form -->
        <form action="<?= base_url('budgets/set') ?>" method="post" class="p-6 space-y-5">
            <?= csrf_field() ?>

            <!-- Category -->
            <div class="space-y-1.5">
                <label for="category_id" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Kategori Pengeluaran</label>
                <div class="relative flex items-center">
                    <select id="category_id" name="category_id" required class="w-full pl-4 pr-10 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom appearance-none cursor-pointer">
                        <option value="" disabled selected class="bg-surface text-tx-primary">-- Pilih Kategori --</option>
                        <?php foreach ($expenseCategories as $cat) : ?>
                            <option value="<?= (string) $cat['id'] ?>" class="bg-surface text-tx-primary">
                                <?= $cat['parent_id'] !== null ? '↳ ' : '' ?><?= (string) esc($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-tx-secondary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Amount Limit -->
            <div class="space-y-1.5">
                <label for="limit_amount" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Batas Anggaran Bulanan</label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 text-tx-secondary text-sm font-semibold pointer-events-none">Rp</span>
                    <input type="number" id="limit_amount" name="limit_amount" required min="1" step="1" placeholder="Contoh: 1500000" class="w-full pl-10 pr-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-bold">
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t border-br-subtle">
                <button type="button" onclick="closeBudgetModal()" class="w-1/3 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3.5 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all shadow-lg shadow-brand/10 flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                    Simpan Anggaran
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    // Open Modal
    function openBudgetModal(categoryId = '', categoryName = '', limitAmount = '') {
        const modal = document.getElementById('setBudgetModal');
        const modalTitle = document.getElementById('modalTitle');
        const select = document.getElementById('category_id');
        const inputAmount = document.getElementById('limit_amount');

        if (categoryId !== '') {
            modalTitle.innerText = 'Ubah Batas Anggaran: ' + categoryName;
            select.value = categoryId;
            select.style.pointerEvents = 'none';
            select.style.opacity = '0.7';
        } else {
            modalTitle.innerText = 'Setel Anggaran Bulanan';
            select.value = '';
            select.style.pointerEvents = 'auto';
            select.style.opacity = '1';
        }

        inputAmount.value = limitAmount;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
        }, 50);
    }

    // Close Modal
    function closeModal() {
        // Alias closeBudgetModal to prevent any invocation discrepancy
        closeBudgetModal();
    }

    function closeBudgetModal() {
        const modal = document.getElementById('setBudgetModal');
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>
<?= $this->endSection() ?>
