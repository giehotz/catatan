<?php
use App\Models\KopSettingModel;

$syaratKetentuan = KopSettingModel::getSetting('kop_syarat_ketentuan');

if (empty(trim($syaratKetentuan))) {
    // Default text if setting is empty
    $syaratKetentuan = "
<p><strong>1. Ketentuan Umum</strong></p>
<p>Dengan menjadi anggota Koperasi, Anda bersedia mematuhi segala peraturan dan ketentuan yang berlaku di dalam Koperasi.</p>
<br>
<p><strong>2. Keanggotaan</strong></p>
<p>Keanggotaan bersifat mengikat dan wajib membayar simpanan pokok serta simpanan wajib sesuai ketentuan yang disepakati.</p>
<br>
<p><strong>3. Pinjaman & Angsuran</strong></p>
<ul>
    <li>Pencairan pinjaman tunduk pada persetujuan dan ketersediaan kas.</li>
    <li>Anggota wajib membayar angsuran tepat waktu. Keterlambatan dapat dikenakan sanksi sesuai kebijakan koperasi.</li>
</ul>
<br>
<p><strong>4. Pengunduran Diri</strong></p>
<p>Pengunduran diri hanya dapat diproses apabila anggota telah melunasi seluruh kewajiban dan hutang yang ada.</p>
";
}
?>
<div class="prose prose-invert max-w-none text-slate-300">
    <?= $syaratKetentuan ?>
</div>
