<script>
// Toast Singleton
const Toast = {
    show(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 max-w-sm w-full px-4 pointer-events-none';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-2xl border shadow-2xl flex items-center gap-3 text-xs font-semibold transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto ${
            type === 'success' 
            ? 'bg-success/15 border-success/30 text-success' 
            : 'bg-danger/15 border-danger/30 text-danger'
        }`;
        const icon = type === 'success' 
            ? '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
            : '<svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        toast.innerHTML = `${icon}<span class="leading-tight">${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
};

// Global Fetch Interceptor for CSRF auto-renewal
(function() {
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        let [resource, config] = args;
        
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        const csrfHeaderName = 'X-CSRF-TOKEN';
        
        let url = '';
        let isRequestInstance = false;
        if (typeof resource === 'string') {
            url = resource;
        } else if (resource && typeof resource === 'object' && 'url' in resource) {
            url = resource.url;
            isRequestInstance = true;
        } else {
            url = String(resource);
        }
        
        let isSameOrigin = false;
        try {
            const urlObj = new URL(url, window.location.href);
            isSameOrigin = urlObj.origin === window.location.origin;
        } catch(e) {
            isSameOrigin = true; // relative path
        }
        
        if (isSameOrigin && csrfToken) {
            if (config === null || config === undefined) {
                config = {};
            }
            if (isRequestInstance) {
                // To avoid mutating the Request's headers directly (which are often immutable/guarded),
                // we merge the Request's headers and config.headers into a new Headers object,
                // and assign it to config.headers. Standard fetch will use this to override the request headers.
                const mergedHeaders = new Headers(resource.headers);
                if (config.headers) {
                    const configHeaders = new Headers(config.headers);
                    for (const [key, value] of configHeaders.entries()) {
                        mergedHeaders.set(key, value);
                    }
                }
                mergedHeaders.set(csrfHeaderName, csrfToken);
                config.headers = mergedHeaders;
            } else {
                const mergedHeaders = new Headers(config.headers || {});
                if (!mergedHeaders.has(csrfHeaderName)) {
                    mergedHeaders.set(csrfHeaderName, csrfToken);
                }
                config.headers = mergedHeaders;
            }
        }
        
        args[0] = resource;
        if (config !== undefined) {
            args[1] = config;
        }
        
        const response = await originalFetch(...args);
        
        // Cek kegagalan CSRF secara cerdas dan spesifik (menghindari loop pada status 403 biasa)
        let isCsrfFailure = response.status === 419;
        
        if (response.status === 403) {
            const errorType = response.headers.get('X-Error-Type');
            if (errorType === 'csrf_mismatch') {
                isCsrfFailure = true;
            } else {
                // Fallback pencarian teks demi kompatibilitas hibrida optimal
                try {
                    const clone = response.clone();
                    const text = await clone.text();
                    if (text.includes('CSRF') || text.includes('mismatch') || text.includes('action you requested is not allowed')) {
                        isCsrfFailure = true;
                    }
                } catch (e) {
                    // ignore if parsing fails
                }
            }
        }
        
        if (isCsrfFailure) {
            if (typeof Toast !== 'undefined') {
                Toast.show('Sesi keamanan Anda telah kedaluwarsa. Sistem sedang menyegarkan halaman...', 'danger');
            } else {
                console.warn('CSRF Expired. Reloading page...');
            }
            setTimeout(() => {
                window.location.reload();
            }, 1500);
            return response;
        }
        
        const newCsrf = response.headers.get('X-CSRF-TOKEN');
        if (newCsrf) {
            updateGlobalCsrf(newCsrf);
        }
        return response;
    };
})();

function updateGlobalCsrf(newToken) {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', newToken);
    }
    const tokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
    document.querySelectorAll(`input[name="${tokenName}"]`).forEach(input => {
        input.value = newToken;
    });
}

// ====================== Unified Theme Cycling & Selection ======================
let currentThemePref = localStorage.getItem('theme') || 'system';

let themeTransitionTimer = null;
let saveThemeDebounceTimer = null;

function applyThemeClass(themeVal) {
    if (themeTransitionTimer) clearTimeout(themeTransitionTimer);
    document.body.classList.add('theme-transitioned');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    let targetClass = 'dark';
    if (themeVal === 'light' || themeVal === 'dark') targetClass = themeVal;
    else targetClass = prefersDark ? 'dark' : 'light';
    
    document.documentElement.className = 'h-full theme-' + targetClass;
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', targetClass === 'dark' ? '#020617' : '#f8fafc');
    themeTransitionTimer = setTimeout(() => {
        document.body.classList.remove('theme-transitioned');
        themeTransitionTimer = null;
    }, 200);
}

function updateToggleIcons() {
    const btn = document.getElementById('themeToggleBtn');
    if (!btn) return;
    const sun = btn.querySelector('.theme-icon-sun');
    const moon = btn.querySelector('.theme-icon-moon');
    const system = btn.querySelector('.theme-icon-system');
    if (sun) sun.classList.add('hidden');
    if (moon) moon.classList.add('hidden');
    if (system) system.classList.add('hidden');
    if (currentThemePref === 'light') { if (sun) sun.classList.remove('hidden'); }
    else if (currentThemePref === 'dark') { if (moon) moon.classList.remove('hidden'); }
    else { if (system) system.classList.remove('hidden'); }
}

function updateThemeSelection(themeVal, syncToServer = true, updateUrl = '') {
    const oldThemePref = currentThemePref;
    currentThemePref = themeVal;
    localStorage.setItem('theme', themeVal);
    applyThemeClass(themeVal);
    updateToggleIcons();
    updateMobileSheetControls();
    updateProfileDropdownControls();
    if (syncToServer && updateUrl) {
        if (saveThemeDebounceTimer) clearTimeout(saveThemeDebounceTimer);
        saveThemeDebounceTimer = setTimeout(() => {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
            if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
            fetch(updateUrl, {
                method: 'POST', headers: headers, body: JSON.stringify({ theme_preference: themeVal })
            })
            .then(res => { if (!res.ok) throw new Error('Gagal memperbarui tema di server.'); return res.json(); })
            .then(data => { if (data.status !== 'success') throw new Error(data.message || 'Gagal menyimpan preferensi.'); Toast.show('Preferensi tema berhasil disimpan.', 'success'); })
            .catch(err => { console.error(err); Toast.show('Gagal menyelaraskan tema dengan server. Pilihan dikembalikan.', 'danger'); updateThemeSelection(oldThemePref, false); });
        }, 300);
    }
}

function cycleTheme(updateUrl = '') {
    let nextTheme = 'system';
    if (currentThemePref === 'system') nextTheme = 'light';
    else if (currentThemePref === 'light') nextTheme = 'dark';
    else if (currentThemePref === 'dark') nextTheme = 'system';
    updateThemeSelection(nextTheme, true, updateUrl);
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => { if (currentThemePref === 'system') applyThemeClass('system'); });
function updateMobileSheetControls() { ['light', 'dark', 'system'].forEach(t => { const btn = document.getElementById(`mobile-theme-btn-${t}`); if (btn) btn.className = currentThemePref === t ? "flex-1 py-2 text-center text-xs font-bold bg-brand text-white rounded-xl shadow-sm transition-all" : "flex-1 py-2 text-center text-xs font-semibold text-tx-secondary hover:text-tx-primary transition-all"; }); }
function updateProfileDropdownControls() { const select = document.getElementById('theme_preference_select'); if (select) select.value = currentThemePref; }

// Bind initial events
document.addEventListener('DOMContentLoaded', () => {
    updateToggleIcons();
});

// ====================== Smart Navigation Scroll (Desktop Nav) ======================
(function() {
    document.addEventListener('DOMContentLoaded', () => {
        const navContainer = document.getElementById('mainNavLinks');
        const leftBtn = document.getElementById('navScrollLeft');
        const rightBtn = document.getElementById('navScrollRight');
        const fadeLeft = document.getElementById('navFadeLeft');
        const fadeRight = document.getElementById('navFadeRight');
        if (!navContainer) return;
        
        function updateNavButtonsAndFade() {
            const maxScroll = navContainer.scrollWidth - navContainer.clientWidth;
            const currentScroll = navContainer.scrollLeft;
            if (leftBtn) {
                if (currentScroll > 5) { leftBtn.classList.remove('opacity-0', 'pointer-events-none'); leftBtn.classList.add('opacity-100', 'pointer-events-auto'); }
                else { leftBtn.classList.add('opacity-0', 'pointer-events-none'); leftBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
            }
            if (rightBtn) {
                if (currentScroll < maxScroll - 5) { rightBtn.classList.remove('opacity-0', 'pointer-events-none'); rightBtn.classList.add('opacity-100', 'pointer-events-auto'); }
                else { rightBtn.classList.add('opacity-0', 'pointer-events-none'); rightBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
            }
            if (fadeLeft) {
                if (currentScroll > 5) { fadeLeft.classList.remove('opacity-0'); fadeLeft.classList.add('opacity-100'); }
                else { fadeLeft.classList.add('opacity-0'); fadeLeft.classList.remove('opacity-100'); }
            }
            if (fadeRight) {
                if (currentScroll < maxScroll - 5) { fadeRight.classList.remove('opacity-0'); fadeRight.classList.add('opacity-100'); }
                else { fadeRight.classList.add('opacity-0'); fadeRight.classList.remove('opacity-100'); }
            }
        }
        if (leftBtn) leftBtn.addEventListener('click', () => navContainer.scrollBy({ left: -200, behavior: 'smooth' }));
        if (rightBtn) rightBtn.addEventListener('click', () => navContainer.scrollBy({ left: 200, behavior: 'smooth' }));
        navContainer.addEventListener('scroll', updateNavButtonsAndFade);
        window.addEventListener('resize', () => setTimeout(updateNavButtonsAndFade, 100));
        setTimeout(updateNavButtonsAndFade, 50);
    });
})();
</script>
