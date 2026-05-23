<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Backup Log Audit - <?= date('d M Y') ?></title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #555; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #777; }
    </style>
</head>
<body>

    <h1>Log Audit Sistem Koperasi (Transparansi)</h1>
    <p class="subtitle">Waktu Backup: <?= date('d M Y, H:i:s') ?><br>Oleh: <?= esc($clearedBy) ?></p>

    <table>
        <thead>
            <tr>
                <th width="15%">Waktu Kejadian</th>
                <th width="15%">Pelaku (User)</th>
                <th width="20%">Jenis Aksi</th>
                <th width="35%">Rincian Aktivitas</th>
                <th width="15%">IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                    <td><?= esc($log['username'] ?? 'Sistem') ?></td>
                    <td><?= esc($log['action']) ?></td>
                    <td><?= esc($log['details']) ?></td>
                    <td><?= esc($log['ip_address'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada data log.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh sistem Catatan Keuangan Koperasi pada <?= date('d M Y, H:i:s') ?>.
    </div>

</body>
</html>
