<?php
/**
 * @var array $users
 * @var int $onlineCount
 * @var int $todayCount
 * @var int $weekCount
 * @var int $inactiveCount
 * @var int $totalCount
 */
?>
<?= $this->extend('layouts/admin_base') ?>

<?= $this->section('content') ?>
<div class="space-y-8 max-w-[1600px] mx-auto">
    
    <!-- Header & Live Indicator -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="space-y-1.5">
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Monitor Pengguna Aktif</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-md">
                    <span id="livePulseDot" class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live
                </span>
            </div>
            <p class="text-slate-400 text-sm">Pemantauan aktivitas log masuk dan interaksi pengguna sistem secara real-time demi keamanan operasional.</p>
        </div>
        
        <!-- Live Controls & Countdown -->
        <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-4 flex items-center justify-between sm:justify-start gap-4 shadow-xl shrink-0 backdrop-blur-md">
            <div class="flex items-center gap-2.5">
                <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-slate-950/80 border border-slate-800" id="countdownContainer">
                    <svg class="w-8 h-8 transform -rotate-90">
                        <circle cx="16" cy="16" r="14" stroke="currentColor" class="text-slate-800" stroke-width="2.5" fill="transparent" />
                        <circle cx="16" cy="16" r="14" stroke="currentColor" class="text-indigo-500 transition-all duration-1000" stroke-width="2.5" fill="transparent"
                            stroke-dasharray="88" stroke-dashoffset="0" id="countdownCircle" />
                    </svg>
                    <span class="absolute text-xs font-bold text-slate-300" id="countdownText">60</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-300" id="refreshStatus">Pembaruan Otomatis</span>
                    <span class="text-[10px] text-slate-500 font-medium" id="lastUpdatedLabel">Terakhir: Baru saja</span>
                </div>
            </div>
            
            <div class="h-8 w-px bg-slate-800 hidden sm:block"></div>
            
            <div class="flex items-center gap-2">
                <button type="button" onclick="togglePause()" id="pauseBtn" class="p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white rounded-xl transition-all cursor-pointer shadow-md flex items-center gap-1.5 text-xs font-bold" title="Jeda/Lanjutkan Pembaruan">
                    <svg id="pauseIcon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                    </svg>
                    <span class="hidden sm:inline" id="pauseBtnText">Jeda</span>
                </button>
                <button type="button" onclick="manualRefresh()" id="refreshBtn" class="p-2 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-indigo-400 hover:text-indigo-300 rounded-xl transition-all cursor-pointer shadow-md flex items-center gap-1.5 text-xs font-bold" title="Refresh Sekarang">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span class="hidden sm:inline">Segarkan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alert / Banner info -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Online Now -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden transition-all duration-300 hover:border-emerald-500/30 group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-400">Online Sekarang</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
            <h3 class="text-4xl font-extrabold text-emerald-400 mt-3 tracking-tight transition-all" id="statOnline">
                <?= $onlineCount ?>
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Aktif dalam 15 menit terakhir</p>
        </div>

        <!-- Active Today -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden transition-all duration-300 hover:border-amber-500/30 group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/5 rounded-full pointer-events-none group-hover:scale-110 transition-transform"></div>
            <span class="text-sm font-semibold text-slate-400">Aktif Hari Ini</span>
            <h3 class="text-4xl font-extrabold text-amber-400 mt-3 tracking-tight transition-all" id="statToday">
                <?= $todayCount ?>
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Sejak tengah malam (eksklusif online)</p>
        </div>

        <!-- Active This Week -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden transition-all duration-300 hover:border-indigo-500/30 group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full pointer-events-none group-hover:scale-110 transition-transform"></div>
            <span class="text-sm font-semibold text-slate-400">Aktif Minggu Ini</span>
            <h3 class="text-4xl font-extrabold text-indigo-400 mt-3 tracking-tight transition-all" id="statWeek">
                <?= $weekCount ?>
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-1">7 hari terakhir (eksklusif hari ini/online)</p>
        </div>

        <!-- Inactive -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-900 shadow-xl relative overflow-hidden transition-all duration-300 hover:border-slate-700/60 group">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-slate-500/5 rounded-full pointer-events-none group-hover:scale-110 transition-transform"></div>
            <span class="text-sm font-semibold text-slate-400">Tidak Aktif / Belum Login</span>
            <h3 class="text-4xl font-extrabold text-slate-400 mt-3 tracking-tight transition-all" id="statInactive">
                <?= $inactiveCount ?>
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-1">Lebih dari 7 hari atau belum pernah login</p>
        </div>

    </div>

    <!-- Main Table Container -->
    <div class="bg-slate-900/40 border border-slate-900 rounded-2xl shadow-xl overflow-hidden backdrop-blur-md">
        
        <!-- Controls Bar: Filter Tab + Search -->
        <div class="p-6 border-b border-slate-900/60 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <!-- Filters Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-950/80 border border-slate-900 rounded-xl overflow-x-auto no-scrollbar shrink-0">
                <button onclick="setFilter('all')" id="tab-all" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer select-none bg-indigo-500 text-white shadow-sm ring-1 ring-indigo-500/20">
                    Semua <span class="ml-1 text-[10px] opacity-75 font-semibold" id="tabCount-all"><?= $totalCount ?></span>
                </button>
                <button onclick="setFilter('online')" id="tab-online" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer select-none">
                    Online <span class="ml-1 text-[10px] bg-emerald-500/10 text-emerald-400 px-1 rounded-md" id="tabCount-online"><?= $onlineCount ?></span>
                </button>
                <button onclick="setFilter('today')" id="tab-today" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer select-none">
                    Hari Ini <span class="ml-1 text-[10px] bg-amber-500/10 text-amber-400 px-1 rounded-md" id="tabCount-today"><?= $todayCount ?></span>
                </button>
                <button onclick="setFilter('week')" id="tab-week" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer select-none">
                    Minggu Ini <span class="ml-1 text-[10px] bg-indigo-500/10 text-indigo-400 px-1 rounded-md" id="tabCount-week"><?= $weekCount ?></span>
                </button>
                <button onclick="setFilter('inactive')" id="tab-inactive" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer select-none">
                    Tidak Aktif <span class="ml-1 text-[10px] bg-slate-700/20 text-slate-400 px-1 rounded-md" id="tabCount-inactive"><?= $inactiveCount ?></span>
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full md:w-80 shrink-0">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="searchInput" oninput="handleSearch(this.value)" placeholder="Cari username atau email..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-950/60 border border-slate-900 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500/80 focus:ring-1 focus:ring-indigo-500/30 transition-all">
                <button onclick="clearSearch()" id="clearSearchBtn" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 hidden">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>

        <!-- Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-900 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-950/40">
                        <th class="py-4 px-6 text-center w-20">No</th>
                        <th class="py-4 px-6">Pengguna</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">Grup / Role</th>
                        <th class="py-4 px-6 text-center w-40">Status</th>
                        <th class="py-4 px-6 text-right w-56">Terakhir Aktif</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="divide-y divide-slate-900/60 text-sm text-slate-300">
                    <!-- Dynamic rendering by JS -->
                </tbody>
            </table>
        </div>

        <!-- Empty State View -->
        <div id="emptyState" class="hidden flex flex-col items-center justify-center py-16 px-6 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-900/80 border border-slate-800 flex items-center justify-center text-slate-500 mb-4 shadow-xl">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h4 class="text-base font-bold text-white tracking-tight">Tidak Ada Pengguna Ditemukan</h4>
            <p class="text-slate-500 text-xs mt-1 max-w-sm">Silakan sesuaikan filter pencarian atau tab status Anda.</p>
        </div>

    </div>

</div>

<!-- System Notification Toast / Alert -->
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-2xl transition-all duration-300 translate-y-24 opacity-0 pointer-events-none">
    <div class="w-6 h-6 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0" id="toastIcon">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <span id="toastMessage">Sesi pembaruan sinkron!</span>
</div>

<script>
    // Initial users injected from controller
    let users = <?= json_encode($users) ?>;
    const DATA_URL = "<?= base_url('admin/active-users/data') ?>";
    
    // Filters & Search State
    let activeFilter = 'all';
    let searchQuery = '';
    
    // Countdown Timer Settings
    let countdown = 60;
    const MAX_COUNTDOWN = 60;
    let isPaused = false;
    let timerInterval = null;
    let lastRefreshedAt = new Date();

    // DOM Elements
    const tableBody = document.getElementById('userTableBody');
    const emptyState = document.getElementById('emptyState');
    const countdownCircle = document.getElementById('countdownCircle');
    const countdownText = document.getElementById('countdownText');
    const lastUpdatedLabel = document.getElementById('lastUpdatedLabel');
    const pauseBtn = document.getElementById('pauseBtn');
    const pauseIcon = document.getElementById('pauseIcon');
    const pauseBtnText = document.getElementById('pauseBtnText');
    const livePulseDot = document.getElementById('livePulseDot');
    const refreshStatus = document.getElementById('refreshStatus');
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    // Stats Elements
    const statOnline = document.getElementById('statOnline');
    const statToday = document.getElementById('statToday');
    const statWeek = document.getElementById('statWeek');
    const statInactive = document.getElementById('statInactive');

    // Tab Counts
    const countAll = document.getElementById('tabCount-all');
    const countOnline = document.getElementById('tabCount-online');
    const countToday = document.getElementById('tabCount-today');
    const countWeek = document.getElementById('tabCount-week');
    const countInactive = document.getElementById('tabCount-inactive');

    // Relative Time Parser (Indonesian Language)
    function timeAgo(dateStr) {
        if (!dateStr || dateStr === 'null') {
            return '<span class="text-slate-600 font-medium">Belum pernah login</span>';
        }
        
        const timestamp = new Date(dateStr).getTime();
        if (isNaN(timestamp)) {
            return '<span class="text-slate-500">Tidak diketahui</span>';
        }

        const diff = Date.now() - timestamp;
        if (diff < 0) {
            return '<span class="text-indigo-400 font-medium">Baru saja</span>';
        }

        const minutes = Math.floor(diff / 60000);
        if (minutes < 1)  return '<span class="text-indigo-400 font-semibold">Baru saja</span>';
        if (minutes < 60) return `<span class="text-slate-300 font-medium">${minutes} menit lalu</span>`;
        
        const hours = Math.floor(minutes / 60);
        if (hours < 24)   return `<span class="text-slate-300">${hours} jam lalu</span>`;
        
        const days = Math.floor(hours / 24);
        if (days === 1)   return '<span class="text-slate-400">Kemarin</span>';
        return `<span class="text-slate-400">${days} hari lalu</span>`;
    }

    // Render User Table Row
    function renderRows(filteredUsers) {
        tableBody.innerHTML = '';
        
        if (filteredUsers.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        filteredUsers.forEach((u, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-950/30 transition-colors duration-200 border-b border-slate-900/60';
            
            // Generate Avatar / Initials
            let avatarHtml = '';
            if (u.avatar) {
                avatarHtml = `<img src="${u.avatar}" alt="${u.username}" class="w-full h-full object-cover">`;
            } else {
                avatarHtml = `<span class="text-xs">${u.initials}</span>`;
            }
            
            // Badges mapping
            let statusBadge = '';
            if (u.status === 'online') {
                statusBadge = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        ONLINE
                    </span>`;
            } else if (u.status === 'today') {
                statusBadge = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        AKTIF HARI INI
                    </span>`;
            } else if (u.status === 'week') {
                statusBadge = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        AKTIF MINGGU INI
                    </span>`;
            } else {
                statusBadge = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-800/65 text-slate-500 border border-slate-800/40">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                        TIDAK AKTIF
                    </span>`;
            }

            const meBadge = u.is_me ? '<span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded-md bg-indigo-500/20 text-indigo-300 tracking-wider">ANDA</span>' : '';

            tr.innerHTML = `
                <td class="py-4 px-6 text-center font-semibold text-slate-500">${index + 1}</td>
                <td class="py-4 px-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs flex items-center justify-center overflow-hidden shrink-0">
                            ${avatarHtml}
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-white">${u.username}</span>
                                ${meBadge}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-6 text-slate-400 font-medium">${u.email}</td>
                <td class="py-4 px-6">
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wider uppercase bg-slate-950/60 border border-slate-900 text-indigo-300">
                        ${u.role}
                    </span>
                </td>
                <td class="py-4 px-6 text-center">${statusBadge}</td>
                <td class="py-4 px-6 text-right font-medium">${timeAgo(u.last_active)}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // Main Filter & Search function
    function applyFilterAndSearch() {
        let filtered = users;
        
        // 1. Status Filter
        if (activeFilter !== 'all') {
            filtered = filtered.filter(u => {
                if (activeFilter === 'online') return u.status === 'online';
                if (activeFilter === 'today') return u.status === 'today';
                if (activeFilter === 'week') return u.status === 'week';
                if (activeFilter === 'inactive') return u.status === 'inactive' || u.status === 'never';
                return true;
            });
        }
        
        // 2. Search Query
        if (searchQuery.trim() !== '') {
            const query = searchQuery.toLowerCase().trim();
            filtered = filtered.filter(u => 
                u.username.toLowerCase().includes(query) || 
                u.email.toLowerCase().includes(query)
            );
        }
        
        renderRows(filtered);
    }

    // Set Active Status Tab
    function setFilter(filterName) {
        activeFilter = filterName;
        
        // Update tab styles
        const tabs = ['all', 'online', 'today', 'week', 'inactive'];
        tabs.forEach(tab => {
            const tabBtn = document.getElementById(`tab-${tab}`);
            if (tab === filterName) {
                tabBtn.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer select-none bg-indigo-500 text-white shadow-sm ring-1 ring-indigo-500/20";
            } else {
                tabBtn.className = "px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer select-none";
            }
        });
        
        applyFilterAndSearch();
    }

    // Handle Client Side Search
    function handleSearch(val) {
        searchQuery = val;
        if (val.trim() !== '') {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
        applyFilterAndSearch();
    }

    // Clear Search Input
    function clearSearch() {
        searchInput.value = '';
        searchQuery = '';
        clearSearchBtn.classList.add('hidden');
        applyFilterAndSearch();
    }

    // Toast Toast popup notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        const toastMsg = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        
        toastMsg.innerText = message;
        
        if (type === 'error') {
            toastIcon.className = "w-6 h-6 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0";
            toastIcon.innerHTML = `
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>`;
        } else {
            toastIcon.className = "w-6 h-6 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0";
            toastIcon.innerHTML = `
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
        }
        
        toast.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
        
        setTimeout(() => {
            toast.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
        }, 3500);
    }

    // Fetch dynamic JSON data via AJAX with try-catch error safety
    async function fetchAndUpdateTable() {
        try {
            const res = await fetch(DATA_URL);
            if (!res.ok) {
                throw new Error(`Koneksi Gagal: HTTP Status ${res.status}`);
            }
            
            const data = await res.json();
            
            // Guard safety
            if (!data || !data.users) {
                throw new Error("Format data respon tidak valid.");
            }
            
            // Sync users data
            users = data.users;
            
            // Update Stats Cards numbers
            statOnline.innerText = data.onlineCount;
            statToday.innerText = data.todayCount;
            statWeek.innerText = data.weekCount;
            statInactive.innerText = data.inactiveCount;

            // Update Tab Badge Counts
            countAll.innerText = data.totalCount;
            countOnline.innerText = data.onlineCount;
            countToday.innerText = data.todayCount;
            countWeek.innerText = data.weekCount;
            countInactive.innerText = data.inactiveCount;
            
            // Re-render based on current filter & search
            applyFilterAndSearch();
            
            // Update Last Updated Timestamp
            lastRefreshedAt = new Date();
            updateLastUpdatedLabel();
            
            // Notify success
            showToast("Sinkronisasi data real-time berhasil.");
            
            // Visual restore if error state previously happened
            restoreLiveUIState();
        } catch (error) {
            console.error("Fetch error on ActiveUsers monitor:", error);
            showToast(error.message || "Gagal sinkron data real-time.", "error");
            setUIErrorState();
        }
    }

    // UI state helper when error occurs
    function setUIErrorState() {
        livePulseDot.className = "w-1.5 h-1.5 rounded-full bg-rose-500";
        refreshStatus.innerText = "Koneksi Terputus";
        refreshStatus.className = "text-xs font-bold text-rose-400";
    }

    // UI state helper when successfully restored
    function restoreLiveUIState() {
        livePulseDot.className = "w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse";
        refreshStatus.innerText = isPaused ? "Pembaruan Dijeda" : "Pembaruan Otomatis";
        refreshStatus.className = "text-xs font-bold text-slate-300";
    }

    // Update relative duration label in controls box
    function updateLastUpdatedLabel() {
        const diffMs = Date.now() - lastRefreshedAt.getTime();
        const diffSec = Math.floor(diffMs / 1000);
        
        if (diffSec < 5) {
            lastUpdatedLabel.innerText = "Terakhir: Baru saja";
        } else if (diffSec < 60) {
            lastUpdatedLabel.innerText = `Terakhir: ${diffSec} dtk lalu`;
        } else {
            const min = Math.floor(diffSec / 60);
            lastUpdatedLabel.innerText = `Terakhir: ${min} mnt lalu`;
        }
    }

    // Pause/Resume Auto Refresh
    function togglePause() {
        isPaused = !isPaused;
        
        if (isPaused) {
            // Pause
            pauseIcon.innerHTML = `
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                </svg>`;
            pauseBtnText.innerText = "Lanjutkan";
            pauseBtn.className = "p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 text-indigo-400 hover:text-indigo-300 rounded-xl transition-all cursor-pointer shadow-md flex items-center gap-1.5 text-xs font-bold";
            refreshStatus.innerText = "Pembaruan Dijeda";
        } else {
            // Resume
            pauseIcon.innerHTML = `
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                </svg>`;
            pauseBtnText.innerText = "Jeda";
            pauseBtn.className = "p-2 bg-slate-950/80 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white rounded-xl transition-all cursor-pointer shadow-md flex items-center gap-1.5 text-xs font-bold";
            refreshStatus.innerText = "Pembaruan Otomatis";
        }
        
        restoreLiveUIState();
    }

    // Manual Trigger Refresh
    function manualRefresh() {
        countdown = MAX_COUNTDOWN;
        updateCircleProgress();
        fetchAndUpdateTable();
    }

    // Circular progress countdown calculation
    function updateCircleProgress() {
        countdownText.innerText = countdown;
        
        // Circumference of our circle: 2 * PI * r = 2 * 3.14159 * 14 ≈ 88
        const maxOffset = 88;
        const dashOffset = maxOffset - (countdown / MAX_COUNTDOWN) * maxOffset;
        countdownCircle.setAttribute('stroke-dashoffset', dashOffset);
    }

    // Initialize Timer loop
    function startTimerLoop() {
        // Clear existing if any
        if (timerInterval) clearInterval(timerInterval);
        
        timerInterval = setInterval(() => {
            updateLastUpdatedLabel();
            
            if (isPaused) return;
            
            countdown--;
            if (countdown <= 0) {
                countdown = MAX_COUNTDOWN;
                fetchAndUpdateTable();
            }
            updateCircleProgress();
        }, 1000);
    }

    // Initialization on load
    document.addEventListener('DOMContentLoaded', () => {
        // Initial render
        applyFilterAndSearch();
        updateCircleProgress();
        startTimerLoop();
    });
</script>
<?= $this->endSection() ?>
