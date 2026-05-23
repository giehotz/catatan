<?php
/**
 * @var string $title
 * @var array  $activeMembers  List of active cooperative members with username & email
 * @var int    $currentYear
 */
?>
<?= $this->extend('layouts/admin_cooperative_base') ?>

<?= $this->section('koprasi_content') ?>

<!-- Download Overlay (hidden by default, shown during export) -->
<div id="download_overlay" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center flex-col gap-4">
    <div class="w-16 h-16 rounded-full border-4 border-teal-500/30 border-t-teal-400 animate-spin"></div>
    <p class="text-white font-bold text-sm">Sedang memproses dokumen…</p>
    <p class="text-slate-400 text-xs">Harap tunggu, ekspor sedang berjalan</p>
</div>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Tunggakan Angsuran</h1>
            <p class="text-slate-400 text-sm">Filter anggota, pilih tahun &amp; bulan, lalu pratinjau dan ekspor laporan resmi PDF atau Excel.</p>
        </div>
        <a href="<?= base_url('admin/cooperative') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-400 hover:text-white border border-slate-800 hover:border-slate-700 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
            Dashboard
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs flex items-center gap-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <?= (string) esc(session()->getFlashdata('error')) ?>
    </div>
    <?php endif; ?>

    <!-- Filter Panel -->
    <div class="bg-slate-900/60 border border-slate-900 rounded-2xl p-5 space-y-5">
        <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" /></svg>
            Filter Laporan
        </h2>

        <!-- Row 1: Member + Year -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Pilih Anggota Aktif</label>
                <select id="filter_anggota" class="w-full px-3 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white text-xs font-semibold outline-none focus:border-teal-500 cursor-pointer transition-colors">
                    <option value="">-- Pilih Anggota --</option>
                    <?php foreach ($activeMembers as $m): ?>
                    <option value="<?= (string) esc((string) $m['id']) ?>">
                        <?= (string) esc((string) $m['username']) ?> (<?= (string) esc((string) $m['nomor_anggota']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Tahun Buku</label>
                <select id="filter_tahun" class="w-full px-3 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-white text-xs font-bold font-mono outline-none focus:border-teal-500 cursor-pointer transition-colors">
                    <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                    <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button id="btn_preview" onclick="runPreview()" class="w-full px-4 py-2.5 bg-teal-500/10 border border-teal-500/30 hover:bg-teal-500/20 text-teal-400 hover:text-teal-300 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    Pratinjau Laporan
                </button>
            </div>
        </div>

        <!-- Row 2: Month checkboxes (max 12) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-400">Pilih Bulan (Maks. 12 Bulan)</label>
                <div class="flex gap-2">
                    <button type="button" onclick="selectAllMonths()" class="text-[10px] font-bold text-teal-400 hover:text-teal-300 underline transition-colors cursor-pointer">Pilih Semua</button>
                    <span class="text-slate-700">|</span>
                    <button type="button" onclick="clearAllMonths()" class="text-[10px] font-bold text-slate-500 hover:text-slate-300 underline transition-colors cursor-pointer">Hapus Semua</button>
                </div>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2" id="month_checkboxes">
                <?php
                $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                foreach ($monthNames as $idx => $mName):
                    $mNum = $idx + 1;
                ?>
                <label class="flex items-center gap-2 p-2.5 bg-slate-950/40 border border-slate-800 rounded-xl cursor-pointer hover:border-teal-500/40 transition-colors group month-checkbox-label" data-month="<?= $mNum ?>">
                    <input type="checkbox" class="month-cb accent-teal-500 cursor-pointer" value="<?= $mNum ?>" id="month_<?= $mNum ?>" checked onchange="onMonthChange()">
                    <span class="text-xs font-semibold text-slate-400 group-hover:text-white transition-colors"><?= $mName ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <p id="month_limit_notice" class="text-[10px] text-rose-400 font-semibold hidden">Batas maksimal 12 bulan telah tercapai. Batalkan pilihan lain sebelum menambah.</p>
        </div>
    </div>

    <!-- Preview Result Area -->
    <div id="preview_area" class="hidden space-y-4">

        <!-- Member info badge -->
        <div id="preview_member_info" class="flex items-center gap-3 p-4 bg-teal-500/5 border border-teal-500/20 rounded-xl">
            <svg class="w-5 h-5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            <div>
                <p id="preview_member_name" class="text-sm font-bold text-white"></p>
                <p id="preview_member_meta" class="text-xs text-slate-400"></p>
            </div>
        </div>

        <!-- No arrears notice -->
        <div id="preview_no_arrears" class="hidden p-5 bg-emerald-500/5 border border-emerald-500/20 rounded-xl text-center">
            <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm font-bold text-emerald-400">Tidak Ada Tunggakan</p>
            <p class="text-xs text-slate-400 mt-1">Anggota tidak memiliki tunggakan pada periode yang dipilih.</p>
        </div>

        <!-- Arrears data table -->
        <div id="preview_table_wrapper" class="hidden overflow-x-auto border border-slate-900 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-teal-900/30 text-[10px] font-bold text-teal-300 uppercase tracking-wider border-b border-teal-900/40">
                        <th class="py-3 px-4 text-center">No</th>
                        <th class="py-3 px-4">Bulan</th>
                        <th class="py-3 px-4 text-right">Tunggakan Wajib</th>
                        <th class="py-3 px-4 text-right">Tunggakan Sosial</th>
                        <th class="py-3 px-4 text-right">Tunggakan Jasa</th>
                        <th class="py-3 px-4 text-right">Jumlah</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="preview_tbody" class="divide-y divide-slate-900/60 text-xs text-slate-300">
                    <!-- AJAX populated -->
                </tbody>
                <tfoot>
                    <tr id="preview_total_row" class="bg-teal-900/40 text-xs font-bold text-teal-300 border-t border-teal-900/40">
                        <td colspan="2" class="py-3 px-4 text-right">TOTAL TUNGGAKAN</td>
                        <td id="total_wajib" class="py-3 px-4 text-right"></td>
                        <td id="total_sosial" class="py-3 px-4 text-right"></td>
                        <td id="total_jasa" class="py-3 px-4 text-right"></td>
                        <td id="total_grand" class="py-3 px-4 text-right text-rose-400"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Export buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <form id="form_pdf" method="POST" action="<?= base_url('admin/cooperative/reports/arrears/pdf') ?>" target="_blank">
                <?= csrf_field() ?>
                <input type="hidden" name="anggota_id" id="pdf_anggota_id">
                <input type="hidden" name="tahun" id="pdf_tahun">
                <input type="hidden" name="download_token" id="pdf_download_token">
                <div id="pdf_bulan_inputs"></div>
                <button type="button" id="btn_pdf" onclick="submitExport('pdf')"
                    class="px-5 py-2.5 bg-rose-500/10 border border-rose-500/25 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Cetak Laporan PDF
                </button>
            </form>

            <form id="form_excel" method="POST" action="<?= base_url('admin/cooperative/reports/arrears/excel') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="anggota_id" id="excel_anggota_id">
                <input type="hidden" name="tahun" id="excel_tahun">
                <input type="hidden" name="download_token" id="excel_download_token">
                <div id="excel_bulan_inputs"></div>
                <button type="button" id="btn_excel" onclick="submitExport('excel')"
                    class="px-5 py-2.5 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Ekspor Lembar Excel
                </button>
            </form>
        </div>
    </div>

</div>

<script>
// ── Helpers ──────────────────────────────────────────────────────────────────
function fmtRp(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// ── Month checkbox enforcement (max 12) ──────────────────────────────────────
function getCheckedMonths() {
    return [...document.querySelectorAll('.month-cb:checked')].map(el => parseInt(el.value));
}
function onMonthChange() {
    const checked = getCheckedMonths();
    const limitNotice = document.getElementById('month_limit_notice');
    if (checked.length >= 12) {
        limitNotice.classList.remove('hidden');
        document.querySelectorAll('.month-cb:not(:checked)').forEach(cb => cb.disabled = true);
    } else {
        limitNotice.classList.add('hidden');
        document.querySelectorAll('.month-cb').forEach(cb => cb.disabled = false);
    }
}
function selectAllMonths() {
    document.querySelectorAll('.month-cb').forEach(cb => { cb.disabled = false; cb.checked = true; });
    onMonthChange();
}
function clearAllMonths() {
    document.querySelectorAll('.month-cb').forEach(cb => { cb.checked = false; cb.disabled = false; });
    document.getElementById('month_limit_notice').classList.add('hidden');
}

// ── AJAX Preview ─────────────────────────────────────────────────────────────
async function runPreview() {
    const anggotaId = document.getElementById('filter_anggota').value;
    const tahun     = document.getElementById('filter_tahun').value;
    const months    = getCheckedMonths();

    if (!anggotaId) { alert('Silakan pilih anggota terlebih dahulu.'); return; }
    if (months.length === 0) { alert('Pilih minimal satu bulan.'); return; }

    const btn = document.getElementById('btn_preview');
    btn.disabled = true;
    btn.textContent = 'Memproses…';

    const body = new URLSearchParams();
    body.append('anggota_id', anggotaId);
    body.append('tahun', tahun);
    months.forEach(m => body.append('bulan[]', m));

    try {
        const resp = await fetch('<?= base_url('admin/cooperative/reports/arrears/preview') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            },
            body: body.toString()
        });

        const data = await resp.json();
        if (!data.success) {
            alert('Error: ' + data.error);
            return;
        }

        renderPreview(data.member, data.rows, anggotaId, tahun, months);
    } catch (err) {
        alert('Kesalahan jaringan: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg> Pratinjau Laporan`;
    }
}

function renderPreview(member, rows, anggotaId, tahun, months) {
    document.getElementById('preview_area').classList.remove('hidden');

    // Member info
    document.getElementById('preview_member_name').textContent = member.username || '-';
    document.getElementById('preview_member_meta').textContent =
        'No. Anggota: ' + (member.nomor_anggota || '-') + ' | Email: ' + (member.email || '-');

    const grandTotal = rows.reduce((s, r) => s + Number(r.jumlah), 0);

    if (grandTotal === 0) {
        document.getElementById('preview_no_arrears').classList.remove('hidden');
        document.getElementById('preview_table_wrapper').classList.add('hidden');
        document.getElementById('btn_pdf').disabled   = true;
        document.getElementById('btn_excel').disabled = true;
        return;
    }

    document.getElementById('preview_no_arrears').classList.add('hidden');
    document.getElementById('preview_table_wrapper').classList.remove('hidden');
    document.getElementById('btn_pdf').disabled   = false;
    document.getElementById('btn_excel').disabled = false;

    // Render rows
    const tbody = document.getElementById('preview_tbody');
    tbody.innerHTML = '';
    let totWajib = 0, totSosial = 0, totJasa = 0, totGrand = 0;

    rows.forEach((r, i) => {
        const hasArrears = Number(r.jumlah) > 0;
        let statusBadge = hasArrears
            ? '<span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Menunggak</span>'
            : '<span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Lunas</span>';

        const tr = document.createElement('tr');
        tr.className = (i % 2 === 0 ? 'bg-slate-900/20' : 'bg-slate-950/10') + (hasArrears ? ' border-l-2 border-rose-500/40' : '');
        tr.innerHTML = `
            <td class="py-3 px-4 text-center">${i + 1}</td>
            <td class="py-3 px-4 font-semibold text-white">${r.bulan_nama}</td>
            <td class="py-3 px-4 text-right ${Number(r.tunggakan_wajib) > 0 ? 'text-rose-400 font-bold' : 'text-emerald-500'}">${fmtRp(r.tunggakan_wajib)}</td>
            <td class="py-3 px-4 text-right ${Number(r.tunggakan_sosial) > 0 ? 'text-rose-400 font-bold' : 'text-emerald-500'}">${fmtRp(r.tunggakan_sosial)}</td>
            <td class="py-3 px-4 text-right ${Number(r.tunggakan_jasa) > 0 ? 'text-rose-400 font-bold' : 'text-emerald-500'}">${fmtRp(r.tunggakan_jasa)}</td>
            <td class="py-3 px-4 text-right ${hasArrears ? 'text-rose-300 font-extrabold' : 'text-emerald-400 font-bold'}">${fmtRp(r.jumlah)}</td>
            <td class="py-3 px-4 text-center">${statusBadge}</td>
        `;
        tbody.appendChild(tr);

        totWajib  += Number(r.tunggakan_wajib);
        totSosial += Number(r.tunggakan_sosial);
        totJasa   += Number(r.tunggakan_jasa);
        totGrand  += Number(r.jumlah);
    });

    document.getElementById('total_wajib').textContent  = fmtRp(totWajib);
    document.getElementById('total_sosial').textContent = fmtRp(totSosial);
    document.getElementById('total_jasa').textContent   = fmtRp(totJasa);
    document.getElementById('total_grand').textContent  = fmtRp(totGrand);

    // Sync export form hidden fields
    syncExportForms(anggotaId, tahun, months);
}

function syncExportForms(anggotaId, tahun, months) {
    ['pdf', 'excel'].forEach(type => {
        document.getElementById(type + '_anggota_id').value = anggotaId;
        document.getElementById(type + '_tahun').value      = tahun;

        const container = document.getElementById(type + '_bulan_inputs');
        container.innerHTML = '';
        months.forEach(m => {
            const inp  = document.createElement('input');
            inp.type   = 'hidden';
            inp.name   = 'bulan[]';
            inp.value  = m;
            container.appendChild(inp);
        });
    });
}

// ── Export with cookie-based loading overlay ──────────────────────────────────
function submitExport(type) {
    const token   = 'dl_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    const overlay = document.getElementById('download_overlay');

    document.getElementById(type + '_download_token').value = token;

    // Show loading overlay
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');

    // Submit the form
    document.getElementById('form_' + type).submit();

    // Poll for the cookie every 500ms with a 30-second timeout fallback
    const started   = Date.now();
    const MAX_WAIT  = 30000; // 30 seconds
    const interval  = setInterval(() => {
        const cookieVal = getCookie('downloadToken');
        const elapsed   = Date.now() - started;

        if (cookieVal === token || elapsed > MAX_WAIT) {
            clearInterval(interval);
            // Delete the cookie so it doesn't interfere with next export
            document.cookie = 'downloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }, 500);
}

function getCookie(name) {
    const value = '; ' + document.cookie;
    const parts = value.split('; ' + name + '=');
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}
</script>

<?= $this->endSection() ?>
