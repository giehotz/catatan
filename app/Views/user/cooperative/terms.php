<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="space-y-1 mt-4">
        <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Syarat & Ketentuan</h1>
        <p class="text-tx-secondary text-sm">Harap baca dengan teliti seluruh aturan yang berlaku di lingkungan Koperasi.</p>
    </div>

    <!-- Content Card -->
    <div class="bg-surface/40 border border-br-default rounded-2xl p-6 sm:p-8 space-y-6">
        <?= $this->include('admin/cooperative/partials/Syarat_dan_Ketentuan') ?>
    </div>
</div>
<?= $this->endSection() ?>
