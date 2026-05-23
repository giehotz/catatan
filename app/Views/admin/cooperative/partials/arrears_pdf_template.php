<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tunggakan Angsuran</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.5;
        }

        /* ── KOP HEADER ── */
        .kop-wrapper {
            border-bottom: 3px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .kop-inner {
            display: table;
            width: 100%;
        }
        .kop-logo-cell {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }
        .kop-logo-cell img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }
        .kop-text-cell {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }
        .kop-coop-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-legal {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .kop-address {
            font-size: 9px;
            color: #475569;
            margin-top: 1px;
        }
        .kop-second-line {
            border-top: 1px solid #0f766e;
            margin-top: 6px;
            padding-top: 4px;
        }

        /* ── REPORT HEADER ── */
        .report-heading {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-heading h2 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            letter-spacing: 0.4px;
        }
        .report-heading p {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── MEMBER INFO BOX ── */
        .member-info-box {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background-color: #f8fafc;
            padding: 7px 10px;
            margin-bottom: 14px;
            font-size: 10px;
        }
        .member-info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .member-info-box td {
            padding: 1px 0;
            vertical-align: top;
        }
        .member-info-box .lbl {
            width: 110px;
            color: #64748b;
            font-weight: bold;
        }

        /* ── DATA TABLE ── */
        .arrears-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .arrears-table thead tr {
            background-color: #0f766e;
            color: #ffffff;
        }
        .arrears-table thead th {
            padding: 7px 8px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #0d9488;
        }
        .arrears-table tbody tr:nth-child(even) {
            background-color: #f0fdfa;
        }
        .arrears-table tbody tr.has-arrears {
            background-color: #fff7ed;
        }
        .arrears-table tbody td {
            padding: 5.5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .arrears-table tfoot tr {
            background-color: #134e4a;
            color: #ffffff;
        }
        .arrears-table tfoot td {
            padding: 7px 8px;
            border: 1px solid #0d9488;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-red    { color: #dc2626; font-weight: bold; }
        .text-green  { color: #059669; }
        .text-bold   { font-weight: bold; }
        .text-muted  { color: #94a3b8; font-size: 9px; }

        /* ── EMPTY STATE ── */
        .no-arrears-notice {
            text-align: center;
            padding: 18px;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            color: #15803d;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 14px;
        }

        /* ── SIGNATURE BLOCK ── */
        .signature-wrapper {
            width: 100%;
            display: table;
            margin-top: 16px;
        }
        .signature-right {
            display: table-cell;
            width: 38%;
            vertical-align: top;
            text-align: center;
        }
        .signature-right .sig-city {
            font-size: 10px;
            color: #475569;
            margin-bottom: 2px;
        }
        .signature-right .sig-space {
            height: 50px;
        }
        .signature-right .sig-name {
            font-size: 10.5px;
            font-weight: bold;
            border-top: 1px solid #334155;
            padding-top: 4px;
        }
        .signature-right .sig-role {
            font-size: 9px;
            color: #64748b;
        }

        /* ── FOOTER ── */
        .doc-footer {
            position: fixed;
            bottom: 12px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

<?php
/**
 * @var array  $kopData  Cooperative identity array
 * @var array  $member   Member info (username, nomor_anggota, email)
 * @var array  $rows     Arrears rows from computeArrearsRows()
 * @var int    $year
 * @var int[]  $months
 * @var array  $signer
 * @var string $printed
 */
$totalWajib  = 0.0;
$totalSosial = 0.0;
$totalJasa   = 0.0;
$grandTotal  = 0.0;
foreach ($rows as $r) {
    $totalWajib  += $r['tunggakan_wajib'];
    $totalSosial += $r['tunggakan_sosial'];
    $totalJasa   += $r['tunggakan_jasa'];
    $grandTotal  += $r['jumlah'];
}

function fmt(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
?>

<!-- Fixed footer on every page -->
<div class="doc-footer">
    Laporan ini dibuat secara otomatis oleh Sistem KSP &bull; Dicetak: <?= esc($printed) ?> &bull; Dokumen Resmi
</div>

<!-- KOP -->
<div class="kop-wrapper">
    <div class="kop-inner">
        <?php if (!empty($kopData['logo_path'])): ?>
        <div class="kop-logo-cell">
            <img src="<?= base_url(esc($kopData['logo_path'])) ?>" alt="Logo KSP">
        </div>
        <?php endif; ?>
        <div class="kop-text-cell">
            <div class="kop-coop-name"><?= esc($kopData['cooperative_name'] ?? 'KOPERASI SIMPAN PINJAM') ?></div>
            <?php if (!empty($kopData['legal_id'])): ?>
            <div class="kop-legal"><?= esc($kopData['legal_id']) ?> &bull; <?= esc($kopData['work_region'] ?? '') ?></div>
            <?php endif; ?>
            <div class="kop-address">
                <?= esc($kopData['address'] ?? '') ?>
                <?php if (!empty($kopData['phone'])): ?> &bull; Telp: <?= esc($kopData['phone']) ?><?php endif; ?>
                <?php if (!empty($kopData['email'])): ?> &bull; <?= esc($kopData['email']) ?><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="kop-second-line"></div>
</div>

<!-- Report Heading -->
<div class="report-heading">
    <h2>LAPORAN TUNGGAKAN ANGSURAN ANGGOTA</h2>
    <p>Periode Tahun Buku <?= esc((string) $year) ?></p>
</div>

<!-- Member Info -->
<div class="member-info-box">
    <table>
        <tr>
            <td class="lbl">Nama Anggota</td>
            <td>: <strong><?= esc($member['username'] ?? '-') ?></strong></td>
            <td class="lbl">No. Anggota</td>
            <td>: <strong><?= esc($member['nomor_anggota'] ?? '-') ?></strong></td>
        </tr>
        <tr>
            <td class="lbl">Email</td>
            <td>: <?= esc($member['email'] ?? '-') ?></td>
            <td class="lbl">Status</td>
            <td>: <?= esc(ucfirst($member['status_keaktifan'] ?? 'aktif')) ?></td>
        </tr>
    </table>
</div>

<!-- Arrears Table -->
<?php if ($grandTotal <= 0): ?>
<div class="no-arrears-notice">
    ✓ Anggota tidak memiliki tunggakan pada periode yang dipilih.
</div>
<?php else: ?>
<table class="arrears-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:14%">Bulan</th>
            <th style="width:18%">Tunggakan Wajib</th>
            <th style="width:18%">Tunggakan Sosial</th>
            <th style="width:18%">Tunggakan Jasa</th>
            <th style="width:16%">Jumlah</th>
            <th style="width:12%">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $r): ?>
        <?php $hasArrears = $r['jumlah'] > 0; ?>
        <tr class="<?= $hasArrears ? 'has-arrears' : '' ?>">
            <td class="text-center"><?= $i + 1 ?></td>
            <td><?= esc($r['bulan_nama']) ?></td>
            <td class="text-right <?= $r['tunggakan_wajib'] > 0 ? 'text-red' : 'text-green' ?>">
                <?= fmt($r['tunggakan_wajib']) ?>
            </td>
            <td class="text-right <?= $r['tunggakan_sosial'] > 0 ? 'text-red' : 'text-green' ?>">
                <?= fmt($r['tunggakan_sosial']) ?>
            </td>
            <td class="text-right <?= $r['tunggakan_jasa'] > 0 ? 'text-red' : 'text-green' ?>">
                <?= fmt($r['tunggakan_jasa']) ?>
            </td>
            <td class="text-right <?= $hasArrears ? 'text-red' : 'text-green' ?>">
                <?= fmt($r['jumlah']) ?>
            </td>
            <td class="text-center">
                <?php
                if ($hasArrears) {
                    $pending = ($r['record_wajib'] && $r['record_wajib']['status'] === 'pending')
                            || ($r['record_sosial'] && $r['record_sosial']['status'] === 'pending');
                    echo $pending ? '<span style="color:#d97706;font-size:9px">Menunggu</span>' : '<span class="text-red" style="font-size:9px">Menunggak</span>';
                } else {
                    echo '<span class="text-green" style="font-size:9px">Lunas</span>';
                }
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="text-right">TOTAL TUNGGAKAN</td>
            <td class="text-right"><?= fmt($totalWajib) ?></td>
            <td class="text-right"><?= fmt($totalSosial) ?></td>
            <td class="text-right"><?= fmt($totalJasa) ?></td>
            <td class="text-right"><?= fmt($grandTotal) ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<?php endif; ?>

<!-- Signature -->
<div class="signature-wrapper">
    <div style="display:table-cell; width:62%;"></div>
    <div class="signature-right">
        <div class="sig-city">
            <?= esc($kopData['work_region'] ?? 'Jakarta') ?>, <?= date('d F Y') ?>
        </div>
        <div>Pengurus Koperasi,</div>
        <div class="sig-space"></div>
        <div class="sig-name"><?= esc($signer['name'] ?? 'Pengurus KSP') ?></div>
        <div class="sig-role"><?= esc($signer['role'] ?? 'Ketua Dewan Pengurus') ?></div>
    </div>
</div>

</body>
</html>
