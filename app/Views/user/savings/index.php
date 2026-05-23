<?php
/**
 * @var array $goals
 * @var array $wallets
 * @var float $totalSavings
 * @var array $historyByGoal
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
                <span class="text-[10px] font-extrabold text-indigo-200/90 uppercase tracking-widest block">Akumulasi Aset Masa Depan</span>
                <div class="flex flex-wrap items-baseline gap-2.5">
                    <span class="text-4xl sm:text-5xl font-black tracking-tight drop-shadow-sm">
                        Rp<?= number_format($totalSavings, 0, ',', '.') ?>
                    </span>
                    <span class="text-[10px] font-extrabold text-emerald-300 bg-white/10 backdrop-blur-xs px-2.5 py-0.5 rounded-md border border-white/15 uppercase tracking-wider">Setoran Terkumpul</span>
                </div>
                <p class="text-indigo-100 text-xs sm:text-sm">
                    Terbagi dalam <span class="font-extrabold text-white underline decoration-emerald-400 decoration-2 underline-offset-4"><?= count($goals) ?> Target Impian Finansial</span> aktif Anda.
                </p>
            </div>
            
            <button onclick="openCreateGoalModal()" class="px-5 py-3.5 bg-white hover:bg-indigo-50 text-indigo-700 font-extrabold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg shadow-indigo-950/15 flex items-center gap-2 hover:shadow-xl cursor-pointer">
                <svg class="w-5 h-5 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Target Baru
            </button>
        </div>
    </div>

    <!-- Error/Alert messages (Premium & Padded UI Alerts) -->
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

    <?php if (session('errors') !== null) : ?>
        <div class="p-4 rounded-2xl bg-danger/15 border border-danger/30 text-danger text-xs sm:text-sm font-semibold space-y-2 shadow-sm">
            <div class="flex items-center gap-2 text-danger font-bold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Mohon perbaiki kendala berikut:</span>
            </div>
            <div class="pl-6 space-y-1 text-tx-secondary font-medium">
                <?php foreach (session('errors') as $error) : ?>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-danger shrink-0"></span>
                        <span><?= $error ?></span>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>

    <!-- Financial Goal Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php if (empty($goals)) : ?>
            <!-- Masterclass Empty State Card -->
            <div class="col-span-full bg-surface/90 border border-dashed border-br-default/80 rounded-3xl p-12 text-center flex flex-col items-center justify-center space-y-6 hover:border-brand/40 transition-all duration-300 shadow-md">
                <div class="w-20 h-20 rounded-2xl bg-brand/5 border border-brand/10 flex items-center justify-center text-brand relative shadow-inner overflow-hidden">
                    <div class="absolute inset-0 bg-linear-to-tr from-brand/10 to-transparent"></div>
                    <svg class="w-10 h-10 text-brand relative z-10" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-opacity="0.2" stroke-width="1.5" stroke-dasharray="4 4" />
                        <circle cx="32" cy="32" r="22" stroke="currentColor" stroke-opacity="0.35" stroke-width="2" />
                        <circle cx="32" cy="32" r="14" fill="currentColor" fill-opacity="0.1" stroke="currentColor" stroke-opacity="0.65" stroke-width="2" />
                        <path d="M48 16L35 29M32 32L35 29M35 29H41M35 29V35" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18 20L18.5 21L19.5 21.2L18.8 22L19 23L18 22.5L17 23L17.2 22L16.5 21.2L17.5 21L18 20Z" fill="#fbbf24" />
                        <path d="M46 44L46.3 44.6L46.9 44.7L46.4 45.2L46.5 45.8L46 45.5L45.5 45.8L45.6 45.2L45.1 44.7L45.7 44.6L46 44Z" fill="#fbbf24" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-bold text-tx-primary tracking-tight">Belum Ada Target Tabungan</h3>
                    <p class="text-tx-secondary text-sm max-w-md leading-relaxed mx-auto">Tentukan impian finansial Anda seperti membeli laptop baru, dana darurat, atau tabungan investasi sekarang!</p>
                </div>
                <button onclick="openCreateGoalModal()" class="px-6 py-3 bg-linear-to-r from-brand to-indigo-600 hover:from-brand-hover hover:to-indigo-500 text-white font-bold rounded-xl text-xs transition-all transform hover:-translate-y-0.5 active:translate-y-0 shadow-lg shadow-brand/20 cursor-pointer">
                    Buat Target Pertama Anda
                </button>
            </div>
        <?php else : ?>
            <?php foreach ($goals as $goal) : ?>
                <?php
                // Setup visual styles based on progress percent
                $progressPercent = $goal['percent'];
                $progressColor = 'from-brand to-indigo-500 shadow-xs shadow-brand/25';
                if ($progressPercent >= 100) {
                    $progressColor = 'from-success to-emerald-500 shadow-xs shadow-success/25';
                } elseif ($progressPercent >= 75) {
                    $progressColor = 'from-info to-blue-500 shadow-xs shadow-info/25';
                }
                
                // ETA badge colors
                $etaClass = 'bg-tx-secondary/5 border-br-default text-tx-secondary';
                if ($goal['eta_type'] === 'success') {
                    $etaClass = 'bg-success/10 border-success/20 text-success';
                } elseif ($goal['eta_type'] === 'warning') {
                    $etaClass = 'bg-warning/10 border-warning/20 text-warning';
                } elseif ($goal['eta_type'] === 'danger') {
                    $etaClass = 'bg-danger/10 border-danger/20 text-danger';
                } elseif ($goal['eta_type'] === 'info') {
                    $etaClass = 'bg-info/10 border-info/20 text-info';
                }
                ?>
                <!-- Premium Goal Card Widget -->
                <div class="bg-surface border border-br-default p-6 rounded-3xl shadow-xl flex flex-col justify-between space-y-6 hover:border-brand/20 hover:-translate-y-1 hover:shadow-2xl hover:shadow-brand/5 transition-all duration-300 relative overflow-hidden group">
                    <div class="space-y-4">
                        <!-- Card Header -->
                        <div class="flex justify-between items-start gap-4">
                            <div class="space-y-1 min-w-0 flex-1">
                                <h3 class="text-lg font-bold text-tx-primary tracking-tight line-clamp-1 group-hover:text-brand transition-colors" title="<?= (string) esc($goal['name']) ?>"><?= (string) esc($goal['name']) ?></h3>
                                <?php if (!empty($goal['target_date'])): ?>
                                    <span class="text-[9px] font-extrabold text-tx-secondary uppercase tracking-wider block">
                                        Target Selesai: <?= date('d M Y', strtotime($goal['target_date'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-[9px] font-extrabold text-tx-secondary uppercase tracking-wider block">Target Terbuka</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Options Actions -->
                            <div class="flex items-center gap-1 shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button onclick="openEditGoalModal(<?= $goal['id'] ?>, '<?= (string) esc($goal['name']) ?>', '<?= floatval($goal['target_amount']) ?>', '<?= $goal['target_date'] ?? '' ?>')" class="p-2 hover:bg-elevated text-tx-secondary hover:text-brand rounded-xl transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Ubah Target">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button onclick="openDeleteGoalModal(<?= $goal['id'] ?>, '<?= (string) esc($goal['name']) ?>', <?= floatval($goal['current_amount']) ?>)" class="p-2 hover:bg-danger/10 text-tx-secondary hover:text-danger rounded-xl transition-all cursor-pointer transform hover:scale-105 active:scale-95" title="Hapus Target">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Progress Bar & Gauge -->
                        <div class="space-y-2.5">
                            <div class="flex justify-between items-baseline text-xs">
                                <span class="text-tx-secondary font-semibold">Progress Tercapai</span>
                                <span class="font-extrabold text-brand text-sm bg-brand/5 px-2 py-0.5 rounded-lg border border-brand/10"><?= $progressPercent ?>%</span>
                            </div>
                            <div class="w-full h-3.5 bg-elevated rounded-full overflow-hidden p-0.5 border border-br-default shadow-inner">
                                <div class="h-full rounded-full bg-linear-to-r <?= $progressColor ?> transition-all duration-500" style="width: <?= $progressPercent ?>%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] font-bold text-tx-secondary">
                                <span class="text-tx-primary">Rp<?= number_format($goal['current_amount'], 0, ',', '.') ?></span>
                                <span>Target: Rp<?= number_format($goal['target_amount'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <!-- ETA Planner Badge (Padded pastel design) -->
                        <div class="p-3 rounded-2xl border <?= $etaClass ?> text-[10px] sm:text-[11px] font-bold flex items-start gap-2 shadow-xs leading-tight">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="leading-tight"><?= $goal['eta_message'] ?></span>
                        </div>
                    </div>

                    <!-- Action Row (Microinteractions) -->
                    <div class="flex gap-3 pt-3.5 border-t border-br-subtle/80">
                        <button onclick="openHistoryModal(<?= $goal['id'] ?>, '<?= (string) esc($goal['name']) ?>')" class="px-3.5 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-secondary hover:text-tx-primary border border-br-default rounded-xl transition-all shrink-0 flex items-center justify-center active:scale-95 cursor-pointer" title="Riwayat Log Tabungan">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </button>
                        
                        <button onclick="openAllocateModal(<?= $goal['id'] ?>, '<?= (string) esc($goal['name']) ?>', 'add')" class="flex-1 py-3 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all transform hover:-translate-y-0.5 active:translate-y-0 shadow-md shadow-brand/10 flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Isi Tabungan
                        </button>
                        
                        <?php if ($goal['current_amount'] > 0) : ?>
                            <button onclick="openAllocateModal(<?= $goal['id'] ?>, '<?= (string) esc($goal['name']) ?>', 'withdraw')" class="flex-1 py-3 bg-danger/10 hover:bg-danger/20 text-danger font-extrabold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tarik Saldo
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Embed goal's micro ledger history into a hidden box for instant JS access -->
                <div id="history-data-<?= $goal['id'] ?>" class="hidden">
                    <?php if (empty($historyByGoal[$goal['id']])): ?>
                        <div class="text-center py-10 space-y-2">
                            <span class="text-tx-disabled text-sm italic block">Belum ada transaksi alokasi untuk target ini.</span>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-2xl border border-br-default">
                            <table class="w-full text-left text-xs text-tx-secondary">
                                <thead class="bg-elevated text-tx-secondary uppercase text-[10px] font-bold tracking-wider">
                                    <tr>
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-4">Rekening Asal</th>
                                        <th class="py-3 px-4">Tipe</th>
                                        <th class="py-3 px-4 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-br-subtle bg-surface">
                                    <?php foreach ($historyByGoal[$goal['id']] as $tx): ?>
                                        <tr class="hover:bg-elevated/40 transition-colors">
                                            <td class="py-3.5 px-4 text-tx-secondary whitespace-nowrap font-medium"><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></td>
                                            <td class="py-3.5 px-4 text-tx-primary font-semibold whitespace-nowrap">
                                                <div class="max-w-[120px] truncate"><?= (string) esc($tx['wallet_name']) ?></div>
                                                <?php if (!empty($tx['notes'])): ?>
                                                    <span class="text-[9px] text-tx-disabled font-medium block max-w-[120px] truncate leading-tight"><?= (string) esc($tx['notes']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 whitespace-nowrap">
                                                <?php if ($tx['type'] === 'add'): ?>
                                                    <span class="px-2.5 py-0.5 rounded-md bg-success/10 text-success text-[9px] font-extrabold uppercase tracking-wide border border-success/20">Setor</span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-0.5 rounded-md bg-danger/10 text-danger text-[9px] font-extrabold uppercase tracking-wide border border-danger/20">Tarik</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-right font-black whitespace-nowrap <?= $tx['type'] === 'add' ? 'text-success' : 'text-danger' ?>">
                                                <?= $tx['type'] === 'add' ? '+' : '-' ?> Rp<?= number_format($tx['amount'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: CREATE GOAL (Masterclass Visuals) -->
<div id="createGoalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 overflow-y-auto">
    <div class="bg-surface border border-br-default/60 w-full max-w-md rounded-3xl shadow-2xl relative overflow-hidden">
        <!-- Top accent decoration line -->
        <div class="h-1.5 w-full bg-linear-to-r from-brand via-indigo-500 to-purple-500"></div>

        <!-- Close Button -->
        <button onclick="closeCreateGoalModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 border-b border-br-subtle">
            <h2 class="text-xl font-bold text-tx-primary tracking-tight">Tambah Target Impian</h2>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed">Buat pos tabungan baru untuk mewujudkan rencana finansial Anda.</p>
        </div>

        <form action="<?= base_url('savings/create') ?>" method="post" id="createGoalForm" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <div class="space-y-1.5">
                <label for="create_name" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Nama Impian</label>
                <input type="text" id="create_name" name="name" placeholder="misal: Beli Laptop Asus, Liburan Bali" required class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
            </div>
            <div class="space-y-1.5">
                <label for="create_target_amount" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider">Jumlah Target Dana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-disabled font-extrabold text-sm">
                        Rp
                    </div>
                    <input type="text" id="create_target_amount" name="target_amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-bold">
                </div>
            </div>
            <div class="space-y-1.5">
                <label for="create_target_date" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Target Tanggal Selesai (Opsional)</label>
                <input type="date" id="create_target_date" name="target_date" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom">
            </div>
            <div class="flex gap-3 pt-4 border-t border-br-subtle">
                <button type="button" onclick="closeCreateGoalModal()" class="w-1/3 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3.5 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all shadow-lg shadow-brand/10 flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                    Simpan Rencana
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT GOAL -->
<div id="editGoalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 overflow-y-auto">
    <div class="bg-surface border border-br-default/60 w-full max-w-md rounded-3xl shadow-2xl relative overflow-hidden">
        <div class="h-1.5 w-full bg-linear-to-r from-brand via-indigo-500 to-purple-500"></div>

        <button onclick="closeEditGoalModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 border-b border-br-subtle">
            <h2 class="text-xl font-bold text-tx-primary tracking-tight">Ubah Rencana Impian</h2>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed">Perbarui nominal target atau tenggat waktu impian Anda.</p>
        </div>

        <form action="" method="post" id="editGoalForm" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <div class="space-y-1.5">
                <label for="edit_name" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Nama Impian</label>
                <input type="text" id="edit_name" name="name" placeholder="misal: Beli Laptop Asus" required class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-semibold">
            </div>
            <div class="space-y-1.5">
                <label for="edit_target_amount" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider">Jumlah Target Dana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-disabled font-extrabold text-sm">
                        Rp
                    </div>
                    <input type="text" id="edit_target_amount" name="target_amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-bold">
                </div>
            </div>
            <div class="space-y-1.5">
                <label for="edit_target_date" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Target Tanggal Selesai (Opsional)</label>
                <input type="date" id="edit_target_date" name="target_date" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom">
            </div>
            <div class="flex gap-3 pt-4 border-t border-br-subtle">
                <button type="button" onclick="closeEditGoalModal()" class="w-1/3 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="w-2/3 py-3.5 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all shadow-lg shadow-brand/10 flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ALLOCATE (Setor/Tarik) -->
<div id="allocateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 overflow-y-auto">
    <div class="bg-surface border border-br-default/60 w-full max-w-md rounded-3xl shadow-2xl relative overflow-hidden">
        <div id="allocateAccentBar" class="h-1.5 w-full bg-success"></div>

        <button onclick="closeAllocateModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 border-b border-br-subtle">
            <h2 class="text-xl font-bold text-tx-primary tracking-tight" id="allocateTitle">Alokasikan Saldo Tabungan</h2>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed" id="allocateDesc">Pindahkan dana secara instan demi mewujudkan target finansial.</p>
        </div>

        <form action="" method="post" id="allocateForm" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="type" id="allocate_type" value="add">
 
            <!-- Wallet Selector -->
            <div class="space-y-1.5">
                <label for="allocate_wallet_id" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block" id="walletLabel">Pilih Rekening Sumber</label>
                <div class="relative flex items-center">
                    <select id="allocate_wallet_id" name="wallet_id" required class="w-full pl-4 pr-10 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary transition-all outline-none text-sm font-semibold select-custom appearance-none cursor-pointer">
                        <option value="" disabled selected class="bg-surface text-tx-primary">Pilih rekening...</option>
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface text-tx-primary">
                                <?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)
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
 
            <!-- Amount -->
            <div class="space-y-1.5">
                <label for="allocate_amount" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider">Nominal Dana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-disabled font-extrabold text-sm">
                        Rp
                    </div>
                    <input type="text" id="allocate_amount" name="amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-bold">
                </div>
            </div>
 
            <!-- Notes -->
            <div class="space-y-1.5">
                <label for="allocate_notes" class="text-xs font-extrabold text-tx-secondary uppercase tracking-wider block">Catatan Tambahan (Opsional)</label>
                <input type="text" id="allocate_notes" name="notes" placeholder="misal: Setoran sisa gajian, Tarik dana darurat" class="w-full px-4 py-3 bg-base border border-br-default rounded-xl focus:border-brand focus:ring-4 focus:ring-brand/10 text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-medium">
            </div>
 
            <div class="flex gap-3 pt-4 border-t border-br-subtle">
                <button type="button" onclick="closeAllocateModal()" class="w-1/3 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="allocateSubmitBtn" class="w-2/3 py-3.5 bg-brand hover:bg-brand-hover text-white font-extrabold rounded-xl text-xs transition-all shadow-lg flex items-center justify-center gap-1.5 cursor-pointer active:scale-98">
                    Konfirmasi Alokasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: HISTORY LOGS -->
<div id="historyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 overflow-y-auto">
    <div class="bg-surface border border-br-default/60 w-full max-w-xl rounded-3xl shadow-2xl relative overflow-hidden">
        <div class="h-1.5 w-full bg-linear-to-r from-brand via-indigo-500 to-purple-500"></div>

        <button onclick="closeHistoryModal()" class="absolute top-5 right-5 text-tx-secondary hover:text-tx-primary transition-colors cursor-pointer p-1.5 hover:bg-elevated rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 border-b border-br-subtle">
            <h2 class="text-xl font-bold text-tx-primary tracking-tight" id="historyTitle">Riwayat Mutasi Tabungan</h2>
            <p class="text-tx-secondary text-xs mt-1 leading-relaxed">Audit log setoran dan penarikan terdedikasi.</p>
        </div>
        <div class="p-6 max-h-[350px] overflow-y-auto" id="historyModalBody">
            <!-- Dynamically populated from JS -->
        </div>
        <div class="p-6 border-t border-br-subtle flex justify-end">
            <button type="button" onclick="closeHistoryModal()" class="px-5 py-2.5 bg-base hover:bg-elevated text-tx-primary border border-br-default/80 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL: DELETE GOAL (WITH REFUND WARNING) -->
<div id="deleteGoalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 overflow-y-auto">
    <div class="bg-surface border border-br-default/60 w-full max-w-md rounded-3xl shadow-2xl relative p-6 space-y-6 overflow-hidden">
        <div class="h-1.5 absolute top-0 inset-x-0 bg-danger"></div>

        <div class="flex items-start gap-4 text-danger pt-2">
            <div class="w-12 h-12 rounded-2xl bg-danger/10 border border-danger/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-tx-primary tracking-tight">Hapus Rencana Impian?</h3>
                <p class="text-tx-secondary text-xs leading-relaxed">Aksi ini bersifat permanen. Seluruh target impian akan dihapus dari sistem.</p>
            </div>
        </div>
 
        <div class="p-5 rounded-2xl bg-base border border-br-default/60 text-xs text-tx-secondary space-y-3 leading-relaxed">
            <div>Target Tabungan: <strong class="text-tx-primary font-bold text-sm block mt-0.5" id="deleteGoalName">...</strong></div>
            <div id="refundAlert" class="hidden p-3 rounded-xl bg-warning/15 border border-warning/20 text-warning font-semibold gap-2">
                <span class="shrink-0 mt-0.5 text-xs">⚠️</span>
                <span>Sisa saldo terkumpul sebesar <strong class="font-black text-tx-primary underline underline-offset-2" id="refundAmountText">Rp0</strong> akan dikembalikan secara aman ke <strong class="text-tx-primary font-bold">Dompet Utama</strong> Anda.</span>
            </div>
        </div>
 
        <form action="" method="post" id="deleteGoalForm" class="flex gap-3">
            <?= csrf_field() ?>
            <button type="button" onclick="closeDeleteGoalModal()" class="w-1/2 py-3.5 bg-elevated hover:bg-elevated/80 text-tx-primary font-bold rounded-xl text-center text-xs transition-colors cursor-pointer">
                Batal
            </button>
            <button type="submit" class="w-1/2 py-3.5 bg-danger hover:bg-danger/90 text-white font-extrabold rounded-xl text-xs transition-all shadow-lg shadow-danger/10 flex items-center justify-center gap-1.5 cursor-pointer">
                Ya, Hapus Rencana
            </button>
        </form>
    </div>
</div>
 
<script>
    // Format helpers
    function formatRupiahInput(e) {
        let cursorPosition = e.target.selectionStart;
        let originalLength = e.target.value.length;
        let cleanValue = e.target.value.replace(/\D/g, "");
        let formattedValue = cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        e.target.value = formattedValue;
        
        let newLength = formattedValue.length;
        cursorPosition = cursorPosition + (newLength - originalLength);
        e.target.setSelectionRange(cursorPosition, cursorPosition);
    }
 
    function cleanFormSubmitDots(formId, inputId) {
        const form = document.getElementById(formId);
        const input = document.getElementById(inputId);
        if (form && input) {
            form.addEventListener('submit', () => {
                input.value = input.value.replace(/\D/g, "");
            });
        }
    }
 
    document.addEventListener('DOMContentLoaded', () => {
        // Apply numeric dot formatting
        const targetAmountCreate = document.getElementById('create_target_amount');
        const targetAmountEdit = document.getElementById('edit_target_amount');
        const allocateAmount = document.getElementById('allocate_amount');
 
        if (targetAmountCreate) targetAmountCreate.addEventListener('input', formatRupiahInput);
        if (targetAmountEdit) targetAmountEdit.addEventListener('input', formatRupiahInput);
        if (allocateAmount) allocateAmount.addEventListener('input', formatRupiahInput);
 
        // Form strip-dots safety
        cleanFormSubmitDots('createGoalForm', 'create_target_amount');
        cleanFormSubmitDots('editGoalForm', 'edit_target_amount');
        cleanFormSubmitDots('allocateForm', 'allocate_amount');
    });
 
    // Create Goal Modal
    function openCreateGoalModal() {
        const m = document.getElementById('createGoalModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeCreateGoalModal() {
        const m = document.getElementById('createGoalModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
 
    // Edit Goal Modal
    function openEditGoalModal(id, name, targetAmount, targetDate) {
        const form = document.getElementById('editGoalForm');
        form.action = `<?= base_url('savings/update') ?>/${id}`;
        
        document.getElementById('edit_name').value = name;
        
        // Format to dots
        const cleanAmount = String(targetAmount).replace(/\D/g, "");
        document.getElementById('edit_target_amount').value = cleanAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        document.getElementById('edit_target_date').value = targetDate;
        
        const m = document.getElementById('editGoalModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeEditGoalModal() {
        const m = document.getElementById('editGoalModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
 
    // Allocate Modal
    function openAllocateModal(id, name, type) {
        const form = document.getElementById('allocateForm');
        form.action = `<?= base_url('savings/allocate') ?>/${id}`;
        
        document.getElementById('allocate_type').value = type;
        
        const title = document.getElementById('allocateTitle');
        const desc = document.getElementById('allocateDesc');
        const walletLabel = document.getElementById('walletLabel');
        const submitBtn = document.getElementById('allocateSubmitBtn');
        const accentBar = document.getElementById('allocateAccentBar');
 
        if (type === 'add') {
            title.textContent = `Setor ke Tabungan: ${name}`;
            desc.textContent = "Kurangi saldo dompet Anda untuk diinvestasikan ke impian finansial.";
            walletLabel.textContent = "Pilih Rekening Sumber Dana (Sumber Setoran)";
            submitBtn.textContent = "Proses Setoran Sekarang";
            submitBtn.className = "w-2/3 py-3.5 bg-success hover:bg-success/90 text-white font-extrabold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-lg shadow-success/15 cursor-pointer active:scale-95";
            if (accentBar) {
                accentBar.className = "h-1.5 w-full bg-success";
            }
        } else {
            title.textContent = `Tarik dari Tabungan: ${name}`;
            desc.textContent = "Keluarkan saldo yang sudah terkumpul kembali ke dalam rekening aktif.";
            walletLabel.textContent = "Pilih Rekening Tujuan (Penerima Dana Tarik)";
            submitBtn.textContent = "Proses Penarikan Sekarang";
            submitBtn.className = "w-2/3 py-3.5 bg-danger hover:bg-danger/90 text-white font-extrabold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-lg shadow-danger/15 cursor-pointer active:scale-95";
            if (accentBar) {
                accentBar.className = "h-1.5 w-full bg-danger";
            }
        }
 
        document.getElementById('allocate_amount').value = "";
        document.getElementById('allocate_notes').value = "";
        
        const m = document.getElementById('allocateModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeAllocateModal() {
        const m = document.getElementById('allocateModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
 
    // History Modal
    function openHistoryModal(id, name) {
        document.getElementById('historyTitle').textContent = `Riwayat Log Tabungan: ${name}`;
        
        const sourceBox = document.getElementById(`history-data-${id}`);
        const modalBody = document.getElementById('historyModalBody');
        
        if (sourceBox && modalBody) {
            modalBody.innerHTML = sourceBox.innerHTML;
        }
        
        const m = document.getElementById('historyModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeHistoryModal() {
        const m = document.getElementById('historyModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
 
    // Delete Goal Modal
    function openDeleteGoalModal(id, name, currentAmount) {
        const form = document.getElementById('deleteGoalForm');
        form.action = `<?= base_url('savings/delete') ?>/${id}`;
        
        document.getElementById('deleteGoalName').textContent = name;
        
        const refundAlert = document.getElementById('refundAlert');
        const refundAmountText = document.getElementById('refundAmountText');
        
        if (currentAmount > 0) {
            // Format to Rupiah
            const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(currentAmount);
            refundAmountText.textContent = formatted;
            refundAlert.classList.remove('hidden');
            refundAlert.classList.add('flex');
        } else {
            refundAlert.classList.remove('flex');
            refundAlert.classList.add('hidden');
        }
        
        const m = document.getElementById('deleteGoalModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeDeleteGoalModal() {
        const m = document.getElementById('deleteGoalModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
</script>
<?= $this->endSection() ?>
