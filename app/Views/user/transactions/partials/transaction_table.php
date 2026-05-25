<div class="bg-surface/40 border border-br-default rounded-2xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table id="transactionTable" class="w-full text-left text-sm text-tx-secondary">
            <thead class="bg-base/60 text-tx-secondary uppercase text-xs">
                <tr>
                    <th class="py-3.5 px-6">Tanggal</th>
                    <th class="py-3.5 px-6">Tipe</th>
                    <th class="py-3.5 px-6">Rekening</th>
                    <th class="py-3.5 px-6">Kategori</th>
                    <th class="py-3.5 px-6">Deskripsi</th>
                    <th class="py-3.5 px-6 text-right">Jumlah</th>
                    <th class="py-3.5 px-6 text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #transactionTable_wrapper .dataTables_length,
    #transactionTable_wrapper .dataTables_filter,
    #transactionTable_wrapper .dataTables_info,
    #transactionTable_wrapper .dataTables_processing,
    #transactionTable_wrapper .dataTables_paginate {
        color: var(--color-tx-secondary);
        font-size: 0.875rem;
        padding: 0 1.5rem;
        margin-bottom: 0.75rem;
        margin-top: 0.75rem;
    }
    #transactionTable_wrapper .dataTables_filter input {
        background-color: color-mix(in srgb, var(--color-base) 60%, transparent);
        border: 1px solid var(--color-br-default);
        border-radius: 0.75rem;
        color: var(--color-tx-primary);
        padding: 0.4rem 0.75rem;
        outline: none;
        margin-left: 0.5rem;
    }
    #transactionTable_wrapper .dataTables_filter input:focus {
        border-color: var(--color-brand);
        box-shadow: 0 0 0 1px var(--color-brand);
    }
    #transactionTable_wrapper .dataTables_length select {
        background-color: color-mix(in srgb, var(--color-base) 60%, transparent);
        border: 1px solid var(--color-br-default);
        border-radius: 0.75rem;
        color: var(--color-tx-primary);
        padding: 0.2rem 0.5rem;
        outline: none;
    }
    #transactionTable_wrapper .dataTables_paginate .paginate_button {
        color: var(--color-tx-secondary) !important;
        border: 1px solid var(--color-br-default) !important;
        border-radius: 0.5rem;
        margin: 0 0.15rem;
        background: transparent !important;
        padding: 0.3rem 0.7rem;
    }
    #transactionTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: color-mix(in srgb, var(--color-elevated) 80%, transparent) !important;
        color: var(--color-tx-primary) !important;
        border-color: var(--color-br-default) !important;
    }
    #transactionTable_wrapper .dataTables_paginate .paginate_button.current,
    #transactionTable_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: color-mix(in srgb, var(--color-brand) 10%, transparent) !important;
        color: var(--color-brand) !important;
        border-color: color-mix(in srgb, var(--color-brand) 30%, transparent) !important;
    }
    #transactionTable_wrapper .dataTables_paginate .paginate_button.disabled {
        color: var(--color-tx-disabled) !important;
        border-color: transparent !important;
    }
    #transactionTable_wrapper table.dataTable.no-footer {
        border-bottom: 1px solid var(--color-br-default);
    }
    #transactionTable_wrapper table.dataTable tbody tr {
        background-color: transparent !important;
    }
    #transactionTable_wrapper table.dataTable tbody tr:hover {
        background-color: color-mix(in srgb, var(--color-elevated) 30%, transparent) !important;
    }
    #transactionTable_wrapper table.dataTable tbody td {
        border-bottom: 1px solid color-mix(in srgb, var(--color-br-default) 60%, transparent);
        padding: 1rem 1.5rem;
    }
    #transactionTable_wrapper table.dataTable thead th,
    #transactionTable_wrapper table.dataTable thead td {
        border-bottom: 1px solid var(--color-br-default);
        padding: 0.875rem 1.5rem;
    }
    #transactionTable_wrapper .dataTables_processing {
        background: var(--color-surface) !important;
        border: 1px solid var(--color-br-default) !important;
        border-radius: 0.75rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
