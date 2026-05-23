<?php
/**
 * @var string $title
 * @var array  $loans         Paginated loan records with progress data
 * @var object $pager         CodeIgniter Pager instance
 * @var string $search        Current search query
 * @var string $statusFilter  Current status filter (all|approved|paid)
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Daftar Pinjaman Koperasi</h1>
            <p class="text-slate-400 text-sm">Daftar seluruh pinjaman yang telah dicairkan beserta progres angsuran masing-masing anggota.</p>
        </div>
        <a href="<?= base_url('admin/cooperative') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-400 hover:text-white border border-slate-800 hover:border-slate-700 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Dashboard
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('message')): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs flex items-center gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <?= (string) esc(session()->getFlashdata('message')) ?>
    </div>
    <?php endif; ?>

    <!-- Search & Filter Panel -->
    <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5">
        <form method="GET" action="<?= base_url('admin/cooperative/loans/directory') ?>" id="search_form">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="sm:col-span-2 space-y-1">
                    <label class="text-xs font-bold text-slate-400 block">Cari Anggota</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" name="q" value="<?= (string) esc($search) ?>" placeholder="Cari berdasarkan nama atau nomor anggota..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white text-xs font-semibold outline-none focus:border-emerald-500 transition-colors placeholder:text-slate-600" id="search_input">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 block">Filter Status</label>
                    <select name="status" onchange="document.getElementById('search_form').submit()" 
                        class="w-full px-3 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white text-xs font-bold outline-none focus:border-emerald-500 cursor-pointer transition-colors">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Aktif</option>
                        <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Berjalan (Approved)</option>
                        <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Lunas (Paid)</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Loans Table -->
    <?php if (empty($loans)): ?>
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl p-10 text-center">
        <svg class="w-12 h-12 text-slate-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
        <p class="text-sm font-bold text-slate-500">Tidak ada data pinjaman ditemukan.</p>
        <p class="text-xs text-slate-600 mt-1">Coba ubah kata kunci pencarian atau filter status.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto border border-slate-900 rounded-2xl">
        <table class="w-full text-left border-collapse" id="loans_table">
            <thead>
                <tr class="bg-emerald-900/20 text-[10px] font-bold text-emerald-300 uppercase tracking-wider border-b border-emerald-900/30">
                    <th class="py-3 px-4 text-center">No</th>
                    <th class="py-3 px-4">Nama Anggota</th>
                    <th class="py-3 px-4">No. Anggota</th>
                    <th class="py-3 px-4 text-right">Nominal</th>
                    <th class="py-3 px-4 text-center">Tenor</th>
                    <th class="py-3 px-4 text-center">Bunga</th>
                    <th class="py-3 px-4">Progress Angsuran</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-900/60 text-xs text-slate-300">
                <?php foreach ($loans as $i => $loan): 
                    $no = ($pager->getCurrentPage('directory') - 1) * 15 + $i + 1;
                    $isLunas = $loan['status'] === 'paid';
                    $progress = $loan['progress_persen'];
                    $progressColor = $isLunas ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-teal-500' : 'bg-amber-500');
                ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-slate-900/20' : 'bg-slate-950/10' ?> hover:bg-slate-800/40 transition-colors">
                    <td class="py-3 px-4 text-center text-slate-500 font-mono"><?= $no ?></td>
                    <td class="py-3 px-4">
                        <span class="font-bold text-white"><?= (string) esc((string) ($loan['username'] ?? '-')) ?></span>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-400"><?= (string) esc((string) ($loan['nomor_anggota'] ?? '-')) ?></td>
                    <td class="py-3 px-4 text-right font-bold text-white">Rp <?= number_format(floatval($loan['nominal_pinjaman']), 0, ',', '.') ?></td>
                    <td class="py-3 px-4 text-center">
                        <span class="font-mono font-bold"><?= (string) esc((string) $loan['tenor_bulan']) ?></span>
                        <span class="text-slate-500 text-[10px]">bln</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="font-mono"><?= (string) esc((string) $loan['bunga_persen']) ?>%</span>
                        <span class="text-slate-600 text-[10px] block"><?= ucfirst((string) ($loan['jenis_bunga'] ?? 'flat')) ?></span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full <?= $progressColor ?> rounded-full transition-all duration-500" style="width: <?= $progress ?>%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 w-12 text-right"><?= $loan['paid_count'] ?>/<?= $loan['tenor_bulan'] ?></span>
                        </div>
                        <span class="text-[9px] font-semibold <?= $progress >= 100 ? 'text-emerald-400' : ($progress >= 50 ? 'text-teal-400' : 'text-amber-400') ?>"><?= $progress ?>%</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <?php if ($isLunas): ?>
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">Lunas</span>
                        <?php else: ?>
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 uppercase tracking-wider">Berjalan</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <a href="<?= base_url('admin/cooperative/loans/directory/' . $loan['id']) ?>" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 rounded-lg text-[10px] font-bold transition-all" id="detail_btn_<?= $loan['id'] ?>">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            Detail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pager->getPageCount('directory') > 1): ?>
    <div class="flex justify-center">
        <div class="inline-flex items-center gap-1 text-xs">
            <?php 
            $currentPage = $pager->getCurrentPage('directory');
            $totalPages = $pager->getPageCount('directory');
            
            // Build base URL with existing query params
            $baseParams = [];
            if ($search !== '') $baseParams['q'] = $search;
            if ($statusFilter !== 'all') $baseParams['status'] = $statusFilter;
            
            // Previous
            if ($currentPage > 1):
                $prevParams = array_merge($baseParams, ['page_directory' => $currentPage - 1]);
            ?>
            <a href="<?= base_url('admin/cooperative/loans/directory') ?>?<?= http_build_query($prevParams) ?>" class="px-3 py-2 bg-slate-900/60 border border-slate-800 rounded-lg text-slate-400 hover:text-white hover:border-slate-700 transition-colors font-bold">‹ Prev</a>
            <?php endif; ?>
            
            <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++):
                $pageParams = array_merge($baseParams, ['page_directory' => $p]);
            ?>
            <a href="<?= base_url('admin/cooperative/loans/directory') ?>?<?= http_build_query($pageParams) ?>"
               class="px-3 py-2 rounded-lg font-bold transition-colors <?= $p === $currentPage ? 'bg-emerald-500/20 border border-emerald-500/30' : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-white hover:border-slate-700' ?>"><?= $p ?></a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages):
                $nextParams = array_merge($baseParams, ['page_directory' => $currentPage + 1]);
            ?>
            <a href="<?= base_url('admin/cooperative/loans/directory') ?>?<?= http_build_query($nextParams) ?>" class="px-3 py-2 bg-slate-900/60 border border-slate-800 rounded-lg text-slate-400 hover:text-white hover:border-slate-700 transition-colors font-bold">Next ›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
