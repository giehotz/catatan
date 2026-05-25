const TransactionModal = {
    element: null,
    form: null,
    mode: 'add',

    init() {
        this.element = document.getElementById('transactionModal');
        if (!this.element) return;
        this.form = document.getElementById('transactionForm');
        this.bindEvents();
    },

    bindEvents() {
        this.element.querySelector('.modal-close')?.addEventListener('click', () => this.close());
        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) this.close();
        });

        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            typeSelect.addEventListener('change', () => this.toggleCategory());
        }
    },

    open(mode = 'add', data = {}) {
        this.mode = mode;
        const config = window.TRANSACTIONS_CONFIG || {};
        const title = this.element.querySelector('#modalTitle');
        const subtitle = this.element.querySelector('#modalSubtitle');
        const submitBtn = this.element.querySelector('#modalSubmitBtn');

        if (mode === 'edit') {
            title.textContent = 'Edit Transaksi';
            subtitle.textContent = 'Perbarui data transaksi yang dipilih.';
            if (submitBtn) submitBtn.textContent = 'Simpan Perubahan';
            this.form.action = (config.updateUrl || '/transaction/update/') + data.id;

            document.getElementById('transaction_id').value = data.id || '';
            document.getElementById('type').value = data.type || 'expense';
            document.getElementById('transaction_date').value = data.date || '';
            document.getElementById('wallet_id').value = data.wallet || '';
            document.getElementById('description').value = data.description || '';

            const cleanAmount = Math.abs(parseFloat(data.amount || 0)).toString();
            document.getElementById('amount').value = formatCurrency(cleanAmount);

            this.toggleCategory();
            if (data.type === 'income') {
                document.getElementById('income_category_select').value = data.category || '';
            } else {
                document.getElementById('expense_category_select').value = data.category || '';
            }
            this.syncCategory();
        } else {
            title.textContent = 'Tambah Transaksi';
            subtitle.textContent = 'Isi formulir di bawah ini untuk mencatat transaksi baru.';
            if (submitBtn) submitBtn.textContent = 'Simpan Transaksi';
            this.form.action = config.createUrl || '/transaction/create';

            document.getElementById('transaction_id').value = '';
            document.getElementById('type').value = data.type || 'expense';
            document.getElementById('transaction_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('wallet_id').value = '';
            document.getElementById('description').value = '';
            document.getElementById('amount').value = '';

            this.toggleCategory();
        }

        showModal(this.element);
    },

    close() {
        hideModal(this.element);
    },

    toggleCategory() {
        const type = document.getElementById('type')?.value;
        const incomeContainer = document.getElementById('category_income_container');
        const expenseContainer = document.getElementById('category_expense_container');

        if (type === 'income') {
            incomeContainer?.classList.remove('hidden');
            expenseContainer?.classList.add('hidden');
        } else {
            incomeContainer?.classList.add('hidden');
            expenseContainer?.classList.remove('hidden');
        }
        this.syncCategory();
    },

    syncCategory() {
        const type = document.getElementById('type')?.value;
        const hiddenInput = document.getElementById('category_id');
        if (!hiddenInput) return;

        if (type === 'income') {
            const select = document.getElementById('income_category_select');
            hiddenInput.value = select ? select.value : '';
        } else {
            const select = document.getElementById('expense_category_select');
            hiddenInput.value = select ? select.value : '';
        }
    }
};

const AdjustBalanceModal = {
    element: null,

    init() {
        this.element = document.getElementById('adjustModal');
        if (!this.element) return;
        this.bindEvents();
    },

    bindEvents() {
        this.element.querySelector('.modal-close')?.addEventListener('click', () => this.close());
        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) this.close();
        });

        const walletSelect = document.getElementById('adjust_wallet_id');
        if (walletSelect) {
            walletSelect.addEventListener('change', () => this.updateTargetBalance());
        }
    },

    open() {
        showModal(this.element);
        this.updateTargetBalance();
    },

    close() {
        hideModal(this.element);
    },

    updateTargetBalance() {
        const select = document.getElementById('adjust_wallet_id');
        if (!select) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;
        const balance = parseInt(selectedOption.getAttribute('data-balance') || '0');
        const input = document.getElementById('target_balance');
        if (!input) return;
        const isNeg = balance < 0;
        const clean = Math.abs(balance).toString();
        input.value = (isNeg ? '-' : '') + formatCurrency(clean);
    }
};

document.addEventListener('click', (event) => {
    const openBtn = event.target.closest('.open-modal-btn');
    if (openBtn) {
        event.preventDefault();
        TransactionModal.open('add', { type: openBtn.dataset.type });
        return;
    }

    const editBtn = event.target.closest('.edit-transaction-btn');
    if (editBtn) {
        event.preventDefault();
        TransactionModal.open('edit', {
            id: editBtn.dataset.id,
            type: editBtn.dataset.type,
            date: editBtn.dataset.date,
            wallet: editBtn.dataset.wallet,
            category: editBtn.dataset.category,
            amount: editBtn.dataset.amount,
            description: editBtn.dataset.description
        });
        return;
    }

    const adjustBtn = event.target.closest('.open-adjust-btn');
    if (adjustBtn) {
        event.preventDefault();
        AdjustBalanceModal.open();
        return;
    }

    const modalCloseBtn = event.target.closest('.modal-close-btn');
    if (modalCloseBtn) {
        event.preventDefault();
        const modal = modalCloseBtn.closest('[id$="Modal"]');
        if (modal) hideModal(modal);
        return;
    }

    const deleteBtn = event.target.closest('.delete-transaction-btn');
    if (deleteBtn) {
        event.preventDefault();
        if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
            deleteBtn.closest('form')?.submit();
        }
        return;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    TransactionModal.init();
    AdjustBalanceModal.init();

    bindCurrencyInput('amount');
    bindCurrencyInput('target_balance');

    const categorySelects = ['income_category_select', 'expense_category_select'];
    categorySelects.forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => TransactionModal.syncCategory());
    });

    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');
    if (action === 'add_income') TransactionModal.open('add', { type: 'income' });
    else if (action === 'add_expense') TransactionModal.open('add', { type: 'expense' });
});
