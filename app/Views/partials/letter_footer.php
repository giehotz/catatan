<?php
/**
 * @var array $signer
 * @var string $hash
 * @var string $processed_at
 * @var string $member_name
 * @var string $member_role
 */
?>
<div class="signature-section" style="margin-top: 40px; display: table; width: 100%; font-size: 13px;">
    <div style="display: table-row;">
        <!-- Left signature: Member -->
        <div style="display: table-cell; width: 50%; text-align: center; vertical-align: bottom;">
            <div style="height: 20px;"></div>
            <div style="color: #0f172a; font-weight: 600; margin-bottom: 60px;">Hormat Saya,</div>
            <div style="font-weight: 750; color: #0f172a; text-decoration: underline;"><?= esc($member_name) ?></div>
            <div style="font-size: 11px; color: #64748b; font-weight: 600;"><?= esc($member_role) ?></div>
        </div>
        
        <!-- Right signature: Board Signer -->
        <div style="display: table-cell; width: 50%; text-align: center; vertical-align: bottom;">
            <div style="color: #475569; margin-bottom: 10px;">Jakarta, <?= date('d F Y', strtotime($processed_at)) ?></div>
            <div style="font-weight: 600; color: #0f172a; margin-bottom: 10px;">Dewan Pengurus KSP,</div>
            
            <div class="digital-stamp" style="border: 2px dashed #10b981; padding: 6px 12px; border-radius: 8px; background-color: #f0fdf4; color: #15803d; font-size: 10px; font-weight: 600; display: inline-block; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 0.5px;">
                Verified Digital Signature
            </div>
            
            <div style="font-weight: 750; color: #0f172a; text-decoration: underline;"><?= esc($signer['name']) ?></div>
            <div style="font-size: 11px; color: #64748b; font-weight: 600;"><?= esc($signer['role']) ?></div>
        </div>
    </div>
</div>

<!-- Verification Footer / QR Mockup -->
<div class="verification-footer" style="margin-top: 50px; border-top: 1px dashed #cbd5e1; padding-top: 20px; display: table; width: 100%; font-size: 11px; color: #64748b;">
    <div style="display: table-row;">
        <!-- QR Mockup Column -->
        <div style="display: table-cell; width: 90px; vertical-align: middle; padding-right: 15px;">
            <div class="qr-mockup" onclick="window.open('<?= base_url('cooperative/verify-resign/' . $hash) ?>', '_blank')" style="width: 80px; height: 80px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 5px; box-sizing: border-box; cursor: pointer;">
                <svg viewBox="0 0 24 24" style="width: 100%; height: 100%; fill: #0f172a;">
                    <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm12-2h6v6h-6V3zm2 2v2h2V5h-2zM3 15h6v6H3v-6zm2 2v2h2v-2H5zm14-2h6v6h-6v-6zm2 2v2h2v-2h-2zm-3-4h2v2h-2v-2zm-2 2h2v2h-2v-2zm2-2h2v-2h2v2h-2v2h-2v-2zm-4 0h2v2H10v-2zm2-2h2v2h-2v-2zm-4-4h2v2H8V7zm2-2h2v2h-2V5zm-2 6h2v2H8v-2zm2 2h2v2h-2v-2zm-6-2h2v2H4v-2zm2-2h2v2H6V9z"/>
                </svg>
            </div>
        </div>
        
        <!-- Details Column -->
        <div style="display: table-cell; vertical-align: middle; line-height: 1.5; text-align: left;">
            <strong style="color: #334155; font-size: 11px;">Verifikasi Keaslian Dokumen:</strong><br/>
            Surat Keputusan ini diterbitkan secara resmi melalui sistem terenkripsi Portal Koperasi. Untuk memverifikasi keabsahan stempel digital dan keaslian isi dokumen ini, scan QR Code atau kunjungi:<br/>
            <a href="<?= base_url('cooperative/verify-resign/' . $hash) ?>" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                <?= base_url('cooperative/verify-resign/' . substr($hash, 0, 16)) ?>...
            </a>
        </div>
    </div>
</div>
