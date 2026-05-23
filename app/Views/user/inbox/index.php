<?php

/**
 * @var array $messages
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>
<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-tx-primary tracking-tight">Kotak Masuk</h1>
            <p class="text-sm text-tx-secondary mt-1">Pemberitahuan, Undangan, dan Pesan Sistem.</p>
        </div>
    </div>

    <?php if (session()->has('message')) : ?>
        <div class="p-4 rounded-xl bg-success/10 border border-success/20 text-success text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) esc(session('message')) ?>
        </div>
    <?php endif ?>

    <?php if (session()->has('error')) : ?>
        <div class="p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= (string) esc(session('error')) ?>
        </div>
    <?php endif ?>

    <div class="bg-surface border border-br-default rounded-2xl overflow-hidden shadow-xl">
        <?php if (empty($messages)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-elevated flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-tx-disabled" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-tx-primary mb-2">Inbox Kosong</h3>
                <p class="text-sm text-tx-secondary max-w-sm mx-auto">Anda belum memiliki pesan atau pemberitahuan apa pun saat ini.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-br-subtle">
                <?php foreach ($messages as $msg): 
                    $isUnread = (int)$msg['is_read'] === 0;
                ?>
                    <a href="<?= base_url('inbox/' . $msg['id']) ?>" 
                       class="block p-4 sm:p-5 transition-all duration-200 border-l-4 <?= $isUnread ? 'border-l-brand bg-brand/5 dark:bg-brand/8 shadow-sm' : 'border-l-transparent hover:bg-elevated/40' ?>">
                        <div class="flex items-start gap-4">
                            <!-- Icon Container -->
                            <div class="mt-1 shrink-0">
                                <?php if ($msg['type'] === 'invitation'): ?>
                                    <div class="w-10 h-10 rounded-xl bg-warning/10 text-warning flex items-center justify-center border border-warning/20 shadow-sm shadow-warning/5">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                        </svg>
                                    </div>
                                <?php elseif ($msg['type'] === 'billing'): ?>
                                    <div class="w-10 h-10 rounded-xl bg-danger/10 text-danger flex items-center justify-center border border-danger/20 shadow-sm shadow-danger/5">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center border border-brand/20 shadow-sm shadow-brand/5">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Content Container -->
                            <div class="flex-1 min-w-0 border-br-default">
                                <div class="flex items-center justify-between gap-3 mb-1.5">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <?php if ($isUnread): ?>
                                            <span class="w-2.5 h-2.5 rounded-full bg-brand shrink-0 animate-pulse relative" title="Belum dibaca">
                                                <span class="absolute inline-flex h-full w-full rounded-full bg-brand opacity-75 animate-ping"></span>
                                            </span>
                                        <?php endif; ?>
                                        <h4 class="text-sm sm:text-base font-semibold truncate <?= $isUnread ? 'text-tx-primary font-bold' : 'text-tx-secondary' ?>">
                                            <?= (string) esc($msg['subject']) ?>
                                        </h4>
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-semibold shrink-0 text-tx-disabled">
                                        <?= date('d M Y, H:i', strtotime($msg['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm leading-relaxed line-clamp-2 <?= $isUnread ? 'text-tx-primary/80 font-medium' : 'text-tx-secondary' ?>">
                                    <?= strip_tags($msg['message']) ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="p-4 border-t border-br-default">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>