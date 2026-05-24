<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Tunggakan Anggota KSP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.5;
        }

        /* ── KOP HEADER ── */
        .kop-wrapper {
            border-bottom: 3px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 10px;
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
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-legal {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }
        .kop-address {
            font-size: 8px;
            color: #475569;
            margin-top: 1px;
        }
        .kop-second-line {
            border-top: 1px solid #0f766e;
            margin-top: 4px;
            padding-top: 2px;
        }

        /* ── REPORT HEADER ── */
        .report-heading {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-heading h2 {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            letter-spacing: 0.4px;
        }
        .report-heading p {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
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
            padding: 6px 6px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #0d9488;
        }
        .arrears-table tbody tr:nth-child(even) {
            background-color: #f0fdfa;
        }
        .arrears-table tbody td {
            padding: 5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 9px;
        }
        .arrears-table tfoot tr {
            background-color: #134e4a;
            color: #ffffff;
        }
        .arrears-table tfoot td {
            padding: 6px 6px;
            border: 1px solid #0d9488;
            font-weight: bold;
            font-size: 9px;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .text-bold   { font-weight: bold; }

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
            font-size: 9px;
            color: #475569;
            margin-bottom: 2px;
        }
        .signature-right .sig-space {
            height: 50px;
        }
        .signature-right .sig-name {
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #334155;
            padding-top: 4px;
        }
        .signature-right .sig-role {
            font-size: 8px;
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
 * @var array  $rows     Arrears rows from computeAllMembersArrearsSummary()
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

$monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$firstMonth = $monthNames[min($months)] ?? '';
$lastMonth  = $monthNames[max($months)] ?? '';
$periodText = count($months) > 1 ? "$firstMonth - $lastMonth $year" : "$firstMonth $year";
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
    <h2>REKAPITULASI TUNGGAKAN ANGGOTA KSP</h2>
    <p>Periode: <?= esc($periodText) ?></p>
</div>

<!-- Arrears Table -->
<table class="arrears-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:28%">Nama Anggota</th>
            <th style="width:17%">Tunggakan Wajib</th>
            <th style="width:17%">Tunggakan Sosial</th>
            <th style="width:17%">Tunggakan Jasa</th>
            <th style="width:17%">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $r): ?>
        <tr>
            <td class="text-center"><?= $i + 1 ?></td>
            <td class="text-left font-bold"><?= esc($r['nama_anggota']) ?></td>
            <td class="text-right"><?= fmt($r['tunggakan_wajib']) ?></td>
            <td class="text-right"><?= fmt($r['tunggakan_sosial']) ?></td>
            <td class="text-right"><?= fmt($r['tunggakan_jasa']) ?></td>
            <td class="text-right text-bold"><?= fmt($r['jumlah']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="text-right">GRAND TOTAL</td>
            <td class="text-right"><?= fmt($totalWajib) ?></td>
            <td class="text-right"><?= fmt($totalSosial) ?></td>
            <td class="text-right"><?= fmt($totalJasa) ?></td>
            <td class="text-right"><?= fmt($grandTotal) ?></td>
        </tr>
    </tfoot>
</table>

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
