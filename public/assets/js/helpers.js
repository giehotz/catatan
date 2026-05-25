function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function refreshCsrf(newToken) {
    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newToken);
    const tokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
    document.querySelectorAll(`input[name="${tokenName}"]`).forEach(input => {
        input.value = newToken;
    });
}

function formatCurrency(value) {
    const clean = value.replace(/\D/g, '');
    return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function stripCurrency(value) {
    return value.replace(/\D/g, '');
}

function handleCurrencyInput(e) {
    const cursorPos = e.target.selectionStart;
    const origLen = e.target.value.length;
    let hasMinus = e.target.value.startsWith('-');
    let clean = e.target.value.replace(/\D/g, '');
    let formatted = clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (hasMinus && clean !== '') formatted = '-' + formatted;
    else if (hasMinus) formatted = '-';
    e.target.value = formatted;
    const newLen = formatted.length;
    e.target.setSelectionRange(cursorPos + (newLen - origLen), cursorPos + (newLen - origLen));
}

function bindCurrencyInput(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', handleCurrencyInput);
    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            input.value = stripCurrency(input.value);
        });
    }
}

function showModal(element) {
    element.classList.remove('hidden');
    element.classList.add('flex');
    requestAnimationFrame(() => {
        element.classList.remove('opacity-0');
        element.querySelector('div')?.classList.remove('scale-95');
    });
}

function hideModal(element) {
    element.classList.add('opacity-0');
    element.querySelector('div')?.classList.add('scale-95');
    setTimeout(() => {
        element.classList.remove('flex');
        element.classList.add('hidden');
    }, 300);
}
