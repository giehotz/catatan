const TransactionsTable = {
    table: null,

    init() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) {
            console.warn('DataTables not loaded. Retrying...');
            setTimeout(() => this.init(), 300);
            return;
        }

        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        const csrfTokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';

        this.table = jQuery('#transactionTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.TRANSACTIONS_CONFIG?.dataUrl || '/transaction/data',
                type: 'POST',
                data: function(d) {
                    d.filter_type = document.getElementById('filter_type')?.value || '';
                    d.filter_start_date = document.getElementById('filter_start_date')?.value || '';
                    d.filter_end_date = document.getElementById('filter_end_date')?.value || '';
                    d.filter_wallet_id = document.getElementById('filter_wallet_id')?.value || '';
                    d.filter_search = document.getElementById('filter_search')?.value || '';
                    d[csrfTokenName] = getCsrfToken();
                }
            },
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2, orderable: false },
                { data: 3, orderable: false },
                { data: 4, orderable: false },
                { data: 5 },
                { data: 6, orderable: false }
            ],
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            drawCallback: function(settings) {
                const json = settings.json;
                if (json && json.totalIncome !== undefined) {
                    const incomeEl = document.getElementById('dt-total-income');
                    const expenseEl = document.getElementById('dt-total-expense');
                    const netEl = document.getElementById('dt-net-balance');
                    if (incomeEl) {
                        incomeEl.textContent = 'Rp' + Number(json.totalIncome).toLocaleString('id-ID');
                    }
                    if (expenseEl) {
                        expenseEl.textContent = 'Rp' + Number(json.totalExpense).toLocaleString('id-ID');
                    }
                    if (netEl) {
                        const net = json.totalIncome - json.totalExpense;
                        netEl.textContent = 'Rp' + Number(net).toLocaleString('id-ID');
                        netEl.className = 'text-3xl font-bold mt-2 tracking-tight relative z-10 ' +
                            (net >= 0 ? 'text-brand' : 'text-danger');
                    }
                }
            }
        });

        this.table.on('xhr', function(e, settings, json, xhr) {
            const newCsrf = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newCsrf) {
                refreshCsrf(newCsrf);
            }
        });
    },

    bindEvents() {
        document.getElementById('filterBtn')?.addEventListener('click', () => {
            this.table?.ajax.reload();
        });

        document.getElementById('resetBtn')?.addEventListener('click', () => {
            ['filter_search', 'filter_type', 'filter_wallet_id', 'filter_start_date', 'filter_end_date'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            this.table?.ajax.reload();
        });

        document.getElementById('filter_search')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.table?.ajax.reload();
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    TransactionsTable.init();
});
