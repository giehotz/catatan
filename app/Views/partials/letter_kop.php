<?php
/**
 * @var array $kop
 */
?>
<div class="kop-header" style="border-bottom: 3px double #1e293b; padding-bottom: 15px; margin-bottom: 25px; display: table; width: 100%;">
    <div style="display: table-row;">
        <?php if (!empty($kop['logo_path']) && (is_file(FCPATH . $kop['logo_path']) || str_contains($kop['logo_path'], 'default'))) : ?>
            <div style="display: table-cell; width: 90px; vertical-align: middle; text-align: left;">
                <img src="<?= base_url($kop['logo_path']) ?>" alt="Logo Koperasi" style="max-height: 75px; max-width: 75px; object-fit: contain; display: block;" />
            </div>
        <?php endif; ?>
        
        <div style="display: table-cell; vertical-align: middle; text-align: center;">
            <h1 style="font-family: 'Cinzel', serif; font-size: 24px; font-weight: 750; color: #065f46; margin: 0 0 5px 0; letter-spacing: 1px;">
                <?= esc($kop['cooperative_name']) ?>
            </h1>
            <p style="font-size: 11px; color: #64748b; margin: 2px 0; letter-spacing: 0.5px;">
                <?= esc($kop['legal_id']) ?> | <?= esc($kop['work_region']) ?>
            </p>
            <p style="font-size: 11px; color: #334155; margin: 2px 0; letter-spacing: 0.5px; font-weight: 600;">
                <?= esc($kop['address']) ?> <br/> Telp: <?= esc($kop['phone']) ?> | E-mail: <?= esc($kop['email']) ?> | Web: <?= esc($kop['website'] ?? '-') ?>
            </p>
        </div>
    </div>
</div>
