<?= $this->extend('layouts/admin_base') ?>

<?= $this->section('content') ?>
<div class="space-y-8 max-w-6xl mx-auto">
    
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Log Audit Sistem</h1>
            <p class="text-slate-400 text-sm">Pemantauan riwayat aktivitas keamanan dan tindakan administratif tingkat tinggi.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            <a href="<?= base_url('admin/audit-logs/backup') ?>" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:border-indigo-500/50 hover:text-indigo-400 text-slate-300 font-bold rounded-xl text-xs transition-colors text-center flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Backup CSV
            </a>
            <button onclick="confirmClearLogs()" class="px-4 py-2 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 text-rose-400 font-bold rounded-xl text-xs transition-colors text-center flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Kosongkan Log
            </button>
            <form id="clearLogsForm" action="<?= base_url('admin/audit-logs/clear') ?>" method="POST" class="hidden">
                <?= csrf_field() ?>
            </form>
            <a href="<?= base_url('admin') ?>" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition-colors text-center">
                User Panel
            </a>
        </div>
    </div>

    <!-- Alert / Banner info -->
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs sm:text-sm flex items-start gap-3">
        <svg class="w-5 h-5 shrink-0 text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong class="font-bold block text-white mb-0.5">Catatan Log Dilindungi</strong>
            Log audit ini bersifat mutlak, diisi secara otomatis oleh sistem, dan dirancang untuk mencegah tampering demi menjaga kepatuhan dan integritas operasional. Data diload secara dinamis.
        </div>
    </div>

    <!-- Audit Logs Grid -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-900/60 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight">100 Tindakan Administratif Terbaru</h3>
            <span class="px-2.5 py-0.5 bg-slate-950/80 text-[11px] font-bold rounded-lg text-slate-400 border border-slate-900">
                Terproteksi
            </span>
        </div>

        <div class="p-6">
            <table id="auditTable" class="w-full text-left border-collapse !border-none">
                <thead>
                    <tr class="border-b border-slate-900/60 text-xs font-bold text-slate-400 uppercase tracking-wider bg-transparent">
                        <th class="py-4 px-2 w-48 border-b border-slate-800">Waktu Kejadian</th>
                        <th class="py-4 px-2 w-44 border-b border-slate-800">Pelaku Tindakan</th>
                        <th class="py-4 px-2 w-44 border-b border-slate-800">Jenis Aksi</th>
                        <th class="py-4 px-2 border-b border-slate-800">Rincian Aktivitas</th>
                        <th class="py-4 px-2 text-center w-36 border-b border-slate-800">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    <!-- Data diload via AJAX DataTables -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* DataTables Tailwind Customization */
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
        color: #94a3b8 !important;
        font-size: 0.875rem;
        margin-bottom: 1rem;
        margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        background-color: rgba(15, 23, 42, 0.6);
        border: 1px solid #334155;
        border-radius: 0.5rem;
        color: #fff;
        padding: 0.3rem 0.75rem;
        outline: none;
        margin-left: 0.5rem;
    }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #6366f1; }
    .dataTables_wrapper .dataTables_length select {
        background-color: rgba(15, 23, 42, 0.6);
        border: 1px solid #334155;
        border-radius: 0.5rem;
        color: #fff;
        padding: 0.2rem 0.5rem;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #94a3b8 !important;
        border: 1px solid #334155 !important;
        border-radius: 0.5rem;
        margin: 0 0.15rem;
        background: transparent !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #1e293b !important;
        color: #fff !important;
        border: 1px solid #475569 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: rgba(99, 102, 241, 0.1) !important;
        color: #818cf8 !important;
        border-color: rgba(99, 102, 241, 0.3) !important;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #1e293b;
    }
    table.dataTable tbody tr { background-color: transparent !important; }
    table.dataTable tbody tr.odd { background-color: rgba(15, 23, 42, 0.3) !important; }
    table.dataTable tbody tr:hover { background-color: rgba(15, 23, 42, 0.5) !important; }
    table.dataTable tbody td { border-bottom: 1px solid rgba(51, 65, 85, 0.6); padding: 1rem 0.5rem; }
    table.dataTable thead th, table.dataTable thead td { border-bottom: 1px solid #334155; }
</style>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#auditTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '<?= base_url('admin/audit-logs/data') ?>',
                type: 'POST',
                data: function (d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>'; // Pass CSRF Token
                }
            },
            order: [[0, 'desc']], // Urutkan berdasarkan Waktu Kejadian terbaru
            columnDefs: [
                { orderable: false, targets: [4] } // IP Address tidak usah di-sort
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });

    function confirmClearLogs() {
        Swal.fire({
            title: 'Yakin kosongkan log?',
            html: "Seluruh data audit saat ini akan dicetak ke dalam <b>PDF</b> dan dibroadcast ke seluruh anggota koperasi sebagai bukti transparansi.<br><br>Data log lama kemudian akan <b>Dihapus Permanen</b> dari database.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Ya, Kosongkan & Broadcast!',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#f1f5f9'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang membuat PDF dan menyebar ke Pesan Anggota. Harap tunggu.',
                    allowOutsideClick: false,
                    background: '#0f172a',
                    color: '#f1f5f9',
                    didOpen: () => {
                        Swal.showLoading();
                        document.getElementById('clearLogsForm').submit();
                    }
                });
            }
        });
    }
</script><?= $this->endSection() ?>
