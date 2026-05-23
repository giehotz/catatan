<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="<?= base_url('inbox') ?>" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-tx-secondary hover:text-tx-primary bg-elevated hover:bg-elevated/80 rounded-lg border border-br-default transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
        <form action="<?= base_url('inbox/' . $message['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus pesan ini?')">
            <?= csrf_field() ?>
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-danger hover:text-danger/80 bg-danger/10 hover:bg-danger/20 rounded-lg border border-danger/20 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
            </button>
        </form>
    </div>

    <div class="bg-surface border border-br-default rounded-2xl overflow-hidden shadow-xl">
        <!-- Message Header -->
        <div class="p-5 sm:p-6 border-b border-br-default bg-base/40">
            <h1 class="text-xl sm:text-2xl font-bold text-tx-primary mb-3"><?= (string) esc($message['subject']) ?></h1>
            <div class="flex flex-wrap gap-4 text-xs font-semibold text-tx-secondary">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?= date('d F Y, H:i', strtotime($message['created_at'])) ?>
                </div>
                <div class="flex items-center gap-1.5">
                    <?php if ($message['type'] === 'invitation'): ?>
                        <span class="px-2 py-0.5 rounded bg-warning/10 text-warning border border-warning/20">Undangan Koperasi</span>
                    <?php elseif ($message['type'] === 'billing'): ?>
                        <span class="px-2 py-0.5 rounded bg-danger/10 text-danger border border-danger/20">Tagihan</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 rounded bg-brand/10 text-brand border border-brand/20">Sistem</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Message Body -->
        <div class="p-5 sm:p-6 prose dark:prose-invert max-w-none text-tx-primary prose-p:text-tx-secondary prose-a:text-brand hover:prose-a:text-brand-hover prose-headings:text-tx-primary">
            <?= $message['message'] ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
