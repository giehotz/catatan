<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="space-y-1 mt-4">
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Syarat & Ketentuan</h1>
        <p class="text-slate-400 text-sm">Harap baca dengan teliti seluruh aturan yang berlaku di lingkungan Koperasi.</p>
    </div>

    <!-- Content Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6">
        <?= $this->include('admin/cooperative/partials/Syarat_dan_Ketentuan') ?>
    </div>
</div>
<?= $this->endSection() ?>
