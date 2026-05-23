<?php
/**
 * @var array $request
 * @var array $member
 * @var array $user
 * @var array|null $admin
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keputusan Pengunduran Diri Resmi - <?= (string) esc($request['nomor_surat']) ?></title>
    <style>
        /* Premium Typography & Styling for Digital + Print layout */
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;855&family=Inter:wght@300;400;600;750&family=Playfair+Display:ital,wght@0,500;0,700;1,400&display=swap');
        
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 40px 20px;
        }

        .letter-container {
            background-color: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 50px 60px;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            position: relative;
            border-top: 8px solid #10b981; /* Emerald branding strip */
        }

        /* Letter Kop */
        .kop-header {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .kop-header h1 {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 750;
            color: #065f46; /* Deep Emerald */
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }

        .kop-header p {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }

        .kop-header .telp {
            font-weight: 600;
            color: #334155;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .doc-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 5px 0;
            text-decoration: underline;
        }

        .doc-title .doc-number {
            font-family: monospace;
            font-size: 12px;
            color: #475569;
            font-weight: 600;
        }

        /* Content Paragraphs */
        .content {
            font-size: 13.5px;
            color: #334155;
            text-align: justify;
        }

        .content p {
            margin-bottom: 15px;
            text-indent: 40px;
        }

        .content .no-indent {
            text-indent: 0 !important;
        }

        /* Member Info Table */
        .info-table {
            width: 85%;
            margin: 20px auto;
            border-collapse: collapse;
            font-size: 13px;
        }

        .info-table td {
            padding: 6px 10px;
            vertical-align: top;
        }

        .info-table td.label {
            width: 35%;
            font-weight: 600;
            color: #475569;
        }

        .info-table td.value {
            width: 65%;
            color: #0f172a;
            font-weight: 750;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 13px;
        }

        .sig-block {
            text-align: center;
            width: 45%;
        }

        .sig-block .date {
            margin-bottom: 15px;
            color: #475569;
        }

        .sig-block .title {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 50px;
        }

        .sig-block .name {
            font-weight: 750;
            color: #0f172a;
            text-decoration: underline;
        }

        .sig-block .role {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        /* Digital Signature Badge */
        .digital-stamp {
            border: 2px dashed #10b981;
            padding: 8px 12px;
            border-radius: 8px;
            background-color: #f0fdf4;
            color: #15803d;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Verification Footer / QR Mockup */
        .verification-footer {
            margin-top: 50px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 11px;
            color: #64748b;
        }

        .qr-mockup {
            width: 80px;
            height: 80px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 5px;
            box-sizing: border-box;
            cursor: pointer;
        }

        .qr-mockup svg {
            width: 100%;
            height: 100%;
            fill: #0f172a;
        }

        .verification-details {
            flex-grow: 1;
        }

        .verification-details a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .verification-details a:hover {
            text-decoration: underline;
        }

        /* Print Controls */
        .print-toolbar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .btn-primary {
            background-color: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 4px rgb(16 185 129 / 0.15);
        }

        .btn-primary:hover {
            background-color: #059669;
        }

        /* Print Specific CSS */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .letter-container {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .print-toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Printable toolbar buttons -->
    <div class="print-toolbar">
        <a href="<?= base_url('cooperative') ?>" class="btn btn-secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Hub
        </a>
        <button onclick="window.print();" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak Dokumen
        </button>
    </div>

    <!-- Letter Body -->
    <div class="letter-container">
        
        <!-- Kop Surat Dinamis (Polymorphic Snapshot atau Fallback Baseline) -->
        <?= view('partials/letter_kop', ['kop' => \App\Helpers\SuratHelper::getKopData('resign', $request['id'])]) ?>

        <!-- Document Identity -->
        <div class="doc-title">
            <h2>Surat Keputusan Pengurus KSP</h2>
            <div class="doc-number">Nomor SK: SK-SKP/RE/<?= (string) esc($request['nomor_surat']) ?></div>
        </div>

        <!-- content of the letter -->
        <div class="content">
            <p class="no-indent">Menimbang permohonan pengunduran diri resmi yang diajukan oleh Anggota Koperasi Simpan Pinjam Catatan Keuangan tertanggal <?= date('d M Y', strtotime($request['created_at'])) ?>, maka Pengurus Koperasi menetapkan hal-hal sebagai berikut:</p>
            
            <p class="no-indent"><strong>PERTAMA:</strong> Menyetujui sepenuhnya pengunduran diri sukarela dari keanggotaan KSP atas nama:</p>
            
            <!-- Member Identity Table -->
            <table class="info-table">
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td>:</td>
                    <td class="value"><?= (string) esc($user['username'] ?? 'Anggota Koperasi') ?></td>
                </tr>
                <tr>
                    <td class="label">Nomor Anggota Resmi</td>
                    <td>:</td>
                    <td class="value font-mono"><?= (string) esc($member['nomor_anggota']) ?></td>
                </tr>
                <tr>
                    <td class="label">Email Terdaftar</td>
                    <td>:</td>
                    <td class="value"><?= (string) esc($user['email'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengesahan</td>
                    <td>:</td>
                    <td class="value"><?= date('d F Y', strtotime($request['processed_at'] ?: 'now')) ?></td>
                </tr>
            </table>

            <p><strong>KEDUA:</strong> Terhitung sejak tanggal pengesahan Surat Keputusan ini, status keaktifan bersangkutan dinyatakan resmi <strong>Nonaktif</strong>. Seluruh hak-hak keanggotaan, wewenang pengambilan keputusan, serta hak partisipasi dalam Rapat Anggota Tahunan (RAT) dicabut penuh.</p>
            
            <p><strong>KETIGA:</strong> Menyatakan bahwa yang bersangkutan telah dibebaskan dari segala kewajiban pinjaman kredit koperasi (sisa utang Rp 0), dan seluruh akumulasi simpanan pokok, wajib, serta sukarela telah dicairkan dan diserahkan kembali sepenuhnya secara tertib sesuai ketentuan finansial yang berlaku.</p>

            <p>Demikian Surat Keputusan ini dibuat dan disahkan untuk dipergunakan sebagaimana mestinya dengan penuh rasa hormat dan tanggung jawab.</p>
        </div>

        <!-- Signatures & Verification Footer Dinamis (Polymorphic Snapshot) -->
        <?= view('partials/letter_footer', [
            'signer'       => \App\Helpers\SuratHelper::getSigner('resign', $request['id']),
            'hash'         => $request['hash_verifikasi'],
            'processed_at' => $request['processed_at'] ?: $request['created_at'],
            'member_name'  => $user['username'] ?? 'Anggota Koperasi',
            'member_role'  => 'Anggota Koperasi (Nonaktif)'
        ]) ?>

    </div>

</body>
</html>
