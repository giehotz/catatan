<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Angsuran - <?= $submission['id'] ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .logo { max-width: 60px; max-height: 60px; float: left; }
        .kop-text { margin-left: 70px; text-align: left; }
        .kop-title { font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .kop-subtitle { font-size: 10px; margin: 2px 0 0 0; color: #666; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin-bottom: 20px; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px; vertical-align: top; }
        .info-table .label { width: 120px; font-weight: bold; }
        .info-table .colon { width: 10px; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #999; padding: 8px; text-align: left; }
        .details-table th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .details-table .right { text-align: right; }
        .details-table .center { text-align: center; }

        .footer { width: 100%; margin-top: 30px; }
        .signature-box { float: right; width: 250px; text-align: center; }
        .signature-title { margin-bottom: 50px; font-size: 11px; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .signature-role { font-size: 10px; color: #666; }
        
        .clear { clear: both; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-admin { background-color: #fef08a; color: #854d0e; border: 1px solid #eab308; }
        .badge-user { background-color: #e0f2fe; color: #0369a1; border: 1px solid #38bdf8; }
    </style>
</head>
<body>

    <div class="header">
        <?php if (!empty($kop['logo_url'])): ?>
            <img src="<?= base_url('uploads/settings/' . $kop['logo_url']) ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <div class="kop-text" <?= empty($kop['logo_url']) ? 'style="margin-left:0; text-align:center;"' : '' ?>>
            <h1 class="kop-title"><?= htmlspecialchars($kop['nama_koperasi']) ?></h1>
            <p class="kop-subtitle">
                <?= htmlspecialchars($kop['alamat_koperasi']) ?><br>
                Telp: <?= htmlspecialchars($kop['telepon_koperasi']) ?> | Email: <?= htmlspecialchars($kop['email_koperasi']) ?>
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="title">TANDA TERIMA ANGSURAN PINJAMAN</div>

    <table class="info-table">
        <tr>
            <td class="label">No. Referensi</td>
            <td class="colon">:</td>
            <td><strong>INV-ANG-<?= str_pad($submission['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
            
            <td class="label">Tanggal Validasi</td>
            <td class="colon">:</td>
            <td><?= date('d/m/Y H:i', strtotime($submission['approved_at'])) ?></td>
        </tr>
        <tr>
            <td class="label">Nama Anggota</td>
            <td class="colon">:</td>
            <td><?= htmlspecialchars($username) ?></td>
            
            <td class="label">Tanggal Transfer</td>
            <td class="colon">:</td>
            <td><?= date('d/m/Y H:i', strtotime($submission['created_at'])) ?></td>
        </tr>
        <tr>
            <td class="label">No. Anggota</td>
            <td class="colon">:</td>
            <td><?= htmlspecialchars($nomor_anggota) ?></td>
            
            <td class="label">Sumber Dana</td>
            <td class="colon">:</td>
            <td>
                <?php if ($submission['source'] === 'admin'): ?>
                    <span class="badge badge-admin">Setoran Langsung (Kantor)</span>
                <?php else: ?>
                    <span class="badge badge-user">Transfer Bank (Anggota)</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="35%">Keterangan Angsuran</th>
                <th width="20%">Status</th>
                <th width="35%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $totalDistribusi = 0;
            ?>
            <?php if (!empty($angsuranLines)): ?>
                <?php foreach ($angsuranLines as $line): ?>
                    <tr>
                        <td class="center"><?= $no++ ?></td>
                        <td>Angsuran Ke-<?= $line['angsuran_ke'] ?> (Pinjaman #<?= $loan['id'] ?>)</td>
                        <td class="center">Terbayar</td>
                        <td class="right"><?= number_format($line['nominal_bayar'], 2, ',', '.') ?></td>
                    </tr>
                    <?php $totalDistribusi += $line['nominal_bayar']; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="center">1</td>
                    <td>Angsuran Pinjaman Fasilitas Kredit #<?= $loan['id'] ?></td>
                    <td class="center">Terbayar</td>
                    <td class="right"><?= number_format($submission['nominal_pengajuan'], 2, ',', '.') ?></td>
                </tr>
                <?php $totalDistribusi = $submission['nominal_pengajuan']; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="right">Total Nominal Validasi</th>
                <th class="right">Rp <?= number_format($totalDistribusi, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 10px; color: #666; margin-bottom: 20px;">
        * Kuitansi ini adalah bukti pembayaran angsuran yang sah dan diterbitkan secara digital oleh sistem <?= htmlspecialchars($kop['nama_koperasi']) ?>.<br>
        * Total disetujui mungkin sedikit berbeda dari yang ditransfer apabila terjadi pembulatan (overpayment batas sisa hutang).
    </div>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-title">Disahkan oleh,</div>
            <div class="signature-name"><?= htmlspecialchars($signer['name'] ?? '') ?></div>
            <div class="signature-role"><?= htmlspecialchars($signer['role'] ?? '') ?></div>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
