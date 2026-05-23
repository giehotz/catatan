// Open Reset Password Modal
function openResetModal(userId, username) {
    const modal = document.getElementById('resetModal');
    const targetLabel = document.getElementById('resetTargetUser');
    const formAction = document.getElementById('resetForm');
    
    targetLabel.textContent = `"${username}"`;
    // Use the global URL defined in the view
    formAction.action = `${window.ADMIN_RESET_PASSWORD_URL}/${userId}`;
    document.getElementById('new_password').value = ''; // clear previous inputs

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
    }, 50);
}

// Close Reset Password Modal
function closeResetModal() {
    const modal = document.getElementById('resetModal');
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 300);
}

// Quick helper to generate a secure random password for the admin
function generateRandomPassword() {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
    let pass = "";
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('new_password').value = pass;
}

// Import Excel Modal Handling
function openImportModal() {
    const modal = document.getElementById('importModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
    }, 50);
}

function closeImportModal() {
    const modal = document.getElementById('importModal');
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        // reset the file input
        document.getElementById('importForm').reset();
        document.getElementById('fileNameDisplay').textContent = "Pilih file .xls, .xlsx, .csv";
    }, 300);
}

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files.length > 0) {
        display.textContent = input.files[0].name;
        display.classList.add('text-indigo-300');
    } else {
        display.textContent = "Pilih file .xls, .xlsx, .csv";
        display.classList.remove('text-indigo-300');
    }
}
