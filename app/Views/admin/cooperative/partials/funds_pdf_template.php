<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Kas Koperasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        
        /* HEADER (Kop Surat) */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #000;
            margin-bottom: 2px;
            padding-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-cell {
            width: 80px;
            text-align: center;
        }
        .logo {
            max-width: 70px;
            max-height: 70px;
        }
        .kop-text {
            text-align: center;
        }
        .kop-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .kop-address {
            font-size: 11px;
            margin: 0 0 3px 0;
        }
        .kop-contact {
            font-size: 10px;
            margin: 0;
        }
        .double-line {
            border-top: 1px solid #000;
            margin-bottom: 20px;
        }

        /* DOCUMENT TITLE */
        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 20px;
        }

        /* TABLE */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        table.data-table td.num {
            text-align: center;
        }
        table.data-table td.nominal {
            text-align: right;
        }

        /* COLORS */
        .text-green { color: #10b981; }
        .text-red { color: #ef4444; }

        /* SIGNATURE */
        .signature-wrapper {
            width: 100%;
            display: table;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-right {
            display: table-cell;
            width: 38%;
            vertical-align: top;
            text-align: center;
        }
        .signature-right .sig-city {
            font-size: 11px;
            color: #333;
            margin-bottom: 2px;
        }
        .signature-right .sig-space {
            height: 60px;
        }
        .signature-right .sig-name {
            font-size: 11px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 4px;
        }
        .signature-right .sig-role {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <?php if (!empty($kop['logo_url'])): ?>
                    <?php 
                    $logoPath = parse_url($kop['logo_url'], PHP_URL_PATH);
                    $fullPath = FCPATH . ltrim($logoPath, '/');
                    if (file_exists($fullPath)) {
                        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                        $data = file_get_contents($fullPath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        echo '<img src="'.$base64.'" class="logo" alt="Logo">';
                    } else {
                        echo '<img src="'.$kop['logo_url'].'" class="logo" alt="Logo">';
                    }
                    ?>
                <?php endif; ?>
            </td>
            <td class="kop-text">
                <h1 class="kop-name"><?= htmlspecialchars($kop['nama_koperasi'] ?? 'KOPERASI SIMPAN PINJAM', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="kop-address"><?= htmlspecialchars($kop['alamat_koperasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="kop-contact">
                    Telp: <?= htmlspecialchars($kop['telepon_koperasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?> | 
                    Email: <?= htmlspecialchars($kop['email_koperasi'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </td>
            <td class="logo-cell"></td> <!-- Empty cell to balance the layout -->
        </tr>
    </table>
    <div class="double-line"></div>

    <!-- TITLE -->
    <div class="doc-title">LAPORAN RIWAYAT MUTASI KAS (AUDIT TRAIL)</div>
    <div class="doc-subtitle">Periode: <?= htmlspecialchars($periode, ENT_QUOTES, 'UTF-8') ?></div>

    <!-- TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Kas (Kategori)</th>
                <th width="15%">Transaksi</th>
                <th width="15%">Nominal (Rp)</th>
                <th width="35%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($riwayatDana as $row): ?>
                <tr>
                    <td class="num"><?= $no++ ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td>
                    <td><?= strtoupper(str_replace('_', ' ', htmlspecialchars($row['kategori_dana']))) ?></td>
                    
                    <?php 
                    $jenisLabel = 'Mutasi';
                    $colorClass = '';
                    if ($row['jenis_transaksi'] === 'pemasukan') {
                        $jenisLabel = 'Masuk';
                        $colorClass = 'text-green';
                    } elseif ($row['jenis_transaksi'] === 'pengeluaran') {
                        $jenisLabel = 'Keluar';
                        $colorClass = 'text-red';
                    }
                    ?>
                    
                    <td><strong class="<?= $colorClass ?>"><?= strtoupper($jenisLabel) ?></strong></td>
                    <td class="nominal <?= $colorClass ?>"><?= number_format($row['nominal'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['keterangan'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- SIGNATURE -->
    <div class="signature-wrapper">
        <div style="display:table-cell; width:62%;"></div>
        <div class="signature-right">
            <div class="sig-city">
                <?= htmlspecialchars($kop['work_region'] ?? 'Jakarta', ENT_QUOTES, 'UTF-8') ?>, <?= date('d F Y') ?>
            </div>
            <div>Pengurus Koperasi,</div>
            <div class="sig-space"></div>
            <div class="sig-name"><?= htmlspecialchars($signer['name'] ?? 'Pengurus KSP', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="sig-role"><?= htmlspecialchars($signer['role'] ?? 'Ketua Dewan Pengurus', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

</body>
</html>
