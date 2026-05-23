<?php
/**
 * @var array $wallets
 */
?>
<?= $this->extend('layouts/base') ?>
 
<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-8">
 
    <!-- Navigation back & Header -->
    <div class="space-y-4">
        <a href="<?= base_url('wallets') ?>" class="inline-flex items-center gap-2 text-brand hover:text-brand-hover text-sm font-semibold transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Rekening & Dompet
        </a>
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Transfer Saldo</h1>
            <p class="text-tx-secondary text-sm">Pindahkan dana antar rekening atau dompet Anda secara langsung dan aman.</p>
        </div>
    </div>
 
    <!-- Error/Alert messages -->
    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>
 
    <!-- Transfer Card Form -->
    <div class="bg-surface/60 border border-br-default p-6 sm:p-8 rounded-3xl shadow-2xl relative overflow-hidden backdrop-blur-md">
        <!-- Glowing gradient backgrounds -->
        <div class="absolute -right-24 -top-24 w-48 h-48 bg-brand/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 w-48 h-48 bg-danger/5 rounded-full blur-3xl pointer-events-none"></div>
 
        <form action="<?= base_url('wallets/transfer') ?>" method="post" id="transferForm" class="space-y-6 relative z-10">
            <?= csrf_field() ?>
 
            <!-- Source & Destination Wallet Selection Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <!-- Source Wallet -->
                <div class="space-y-2">
                    <label for="from_wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Rekening Asal (Sumber Dana)</label>
                    <select id="from_wallet_id" name="from_wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-semibold select-custom">
                        <option value="" disabled selected class="bg-surface">Pilih rekening asal...</option>
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= $w['id'] ?>" data-balance="<?= floatval($w['balance']) ?>" class="bg-surface" <?= old('from_wallet_id') == $w['id'] ? 'selected' : '' ?>>
                                <?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
 
                <!-- Connection Visual Indicator -->
                <div class="hidden md:flex justify-center items-center -mb-2 pointer-events-none">
                    <div class="w-10 h-10 rounded-full bg-base border border-br-default flex items-center justify-center text-brand shadow-md">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                </div>
 
                <!-- Destination Wallet -->
                <div class="space-y-2">
                    <label for="to_wallet_id" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Rekening Tujuan (Penerima)</label>
                    <select id="to_wallet_id" name="to_wallet_id" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-semibold select-custom">
                        <option value="" disabled selected class="bg-surface">Pilih rekening tujuan...</option>
                        <?php foreach ($wallets as $w): ?>
                            <option value="<?= $w['id'] ?>" class="bg-surface" <?= old('to_wallet_id') == $w['id'] ? 'selected' : '' ?>>
                                <?= (string) esc($w['name']) ?> (Saldo: Rp<?= number_format($w['balance'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
 
            <!-- Amount Input Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div class="space-y-2">
                    <label for="amount" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Jumlah Transfer</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-tx-secondary font-semibold text-sm">
                            Rp
                        </div>
                        <input type="text" id="amount" name="amount" placeholder="0" required class="w-full pl-10 pr-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-bold" value="<?= old('amount') ?>">
                    </div>
                </div>
 
                <!-- Transaction Date -->
                <div class="space-y-2">
                    <label for="transaction_date" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Tanggal Transfer</label>
                    <input type="date" id="transaction_date" name="transaction_date" required class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary transition-all outline-none text-sm font-semibold" value="<?= old('transaction_date', date('Y-m-d')) ?>">
                </div>
            </div>
 
            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Deskripsi / Catatan (Opsional)</label>
                <input type="text" id="description" name="description" placeholder="misal: Top Up OVO dari Rekening BCA" class="w-full px-4 py-3 bg-base/60 border border-br-default rounded-xl focus:border-brand focus:ring-1 focus:ring-brand text-tx-primary placeholder-tx-disabled transition-all outline-none text-sm font-medium" value="<?= old('description') ?>">
            </div>
 
            <!-- Submit Button Row -->
            <div class="flex gap-4 pt-4 border-t border-br-subtle">
                <a href="<?= base_url('wallets') ?>" class="w-1/3 py-3 bg-elevated hover:bg-elevated/85 text-tx-primary border border-br-default font-bold rounded-xl text-center text-sm transition-all flex items-center justify-center cursor-pointer">
                    Batal
                </a>
                <button type="submit" class="w-2/3 py-3 bg-linear-to-r from-brand to-brand-hover hover:from-brand-hover hover:to-brand text-white font-bold rounded-xl text-sm transition-all transform hover:-translate-y-0.5 shadow-lg shadow-brand/10 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Proses Transfer Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
 
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fromSelect = document.getElementById('from_wallet_id');
        const toSelect = document.getElementById('to_wallet_id');
        const amountInput = document.getElementById('amount');
        const form = document.getElementById('transferForm');
 
        // 1. Dynamic disable logic to prevent transferring to the same wallet
        function syncWalletDropdowns() {
            const selectedFrom = fromSelect.value;
            const selectedTo = toSelect.value;
 
            // Loop through destination wallet options and enable/disable
            Array.from(toSelect.options).forEach(option => {
                if (option.value === "") return;
                
                if (option.value === selectedFrom) {
                    option.disabled = true;
                    // If the disabled option was selected, clear selected option
                    if (option.value === selectedTo) {
                        toSelect.value = "";
                    }
                } else {
                    option.disabled = false;
                }
            });
        }
 
        fromSelect.addEventListener('change', syncWalletDropdowns);
        // Trigger initial sync in case of old input
        syncWalletDropdowns();
 
        // 2. Live Dot Numeric Formatting for Amount
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
        }
 
        // 3. Strip dots before submitting form
        if (form && amountInput) {
            form.addEventListener('submit', (e) => {
                const amountClean = amountInput.value.replace(/\D/g, "");
                
                // Extra check for positive number
                if (parseFloat(amountClean) <= 0 || amountClean === '') {
                    e.preventDefault();
                    alert('Nominal transfer harus lebih besar dari Rp0.');
                    return false;
                }
                
                // Set the value to plain number for post submission
                amountInput.value = amountClean;
            });
        }
    });
</script>
<?= $this->endSection() ?>
