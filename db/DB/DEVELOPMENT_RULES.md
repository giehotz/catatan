# 📏 Development Rules — Catatan Keuangan

## 1. File Modification Checklist

Sebelum mengubah file apapun, pastikan:

- [ ] Sudah baca `Routes.php` untuk memahami endpoint terkait
- [ ] Sudah baca controller method yang terlibat
- [ ] Sudah baca model yang digunakan
- [ ] Sudah baca view yang akan diubah
- [ ] Sudah cek `kop_settings` untuk nilai yang bisa dikonfigurasi
- [ ] Sudah cek migration terkait untuk memahami schema

## 2. Schema Change Protocol

```
1. Buat migration baru di app/Database/Migrations/
   Format nama: YYYY-MM-DD-HHMMSS_NamaDescriptif.php
2. Jalankan: php spark migrate
3. Update $allowedFields di model terkait
4. Update view yang menampilkan data baru
5. Update controller query (terutama JOIN + SELECT)
```

## 3. Controller Rules

```php
// ✅ BENAR: Controller tipis, panggil model
public function create() {
    $this->validate($rules);
    $this->model->insert($data);
    return redirect()->with('message', 'Berhasil');
}

// ❌ SALAH: Logic berat di controller
public function create() {
    $db = Database::connect();
    $result = $db->query("SELECT ..."); // Jangan query langsung!
    // 50 baris kalkulasi... // Pindah ke model!
}
```

## 4. Model Rules

```php
// Wajib ada:
protected $table = 'nama_tabel';
protected $primaryKey = 'id';
protected $returnType = 'array';
protected $protectFields = true;
protected $allowedFields = [...]; // WAJIB lengkap

// Untuk kop_settings (VARCHAR PK):
protected $useAutoIncrement = false;

// Static helper pattern:
public static function getSetting(string $key, $default = null) { ... }
```

## 5. View Rules

```php
// Layout extension (WAJIB):
<?= $this->extend('layouts/nama_layout') ?>
<?= $this->section('nama_section') ?>
  ...konten...
<?= $this->endSection() ?>

// Form pattern (WAJIB):
<form action="<?= base_url('path') ?>" method="post">
    <?= csrf_field() ?>
    ...
</form>

// Output escaping (WAJIB):
<?= esc($variable) ?>
<?= number_format($angka, 2, ',', '.') ?>

// Flash messages (WAJIB di setiap halaman):
<?php if (session('message')) : ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 ...">
        <?= session('message') ?>
    </div>
<?php endif ?>
```

## 6. CSS Rules

```
Source:  resources/css/app.css
Output:  public/css/app.css
Build:   cmd /c "npm run build"
Watch:   cmd /c "npm run dev"

Versi:   TailwindCSS v4.3 (@tailwindcss/cli)
Config:  CSS-based (BUKAN tailwind.config.js)

Palette:
  - Background: slate-950, slate-900
  - Borders: slate-800, slate-900
  - Text: white, slate-300, slate-400, slate-500
  - Success: emerald-400/500
  - Danger: rose-400/500
  - Info: indigo-300/400
  - Warning: amber-400
  - Accents: selalu pakai opacity (emerald-500/10, rose-500/20)

Radius: rounded-xl, rounded-2xl
Cards: bg-slate-950/40 border border-slate-900 rounded-2xl p-6
Buttons: bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl
Input: bg-slate-950 border border-slate-800 rounded-xl px-4 py-3
```

## 7. JavaScript Rules

```
Framework: TIDAK ADA — Vanilla JS saja
Pattern: <script> inline di view, setelah HTML
Naming: camelCase untuk fungsi dan variabel
DOM: document.getElementById(), querySelector()
Format angka: Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' })
Event: addEventListener('input'/'change'/'click'/'DOMContentLoaded')
```

## 8. Audit Logging

```php
// Semua operasi penting WAJIB di-log:
AuditLogModel::log('action_name', 'Deskripsi detail...');

// Naming convention untuk action:
coop_direct_loan_granted
coop_installment_approved
coop_installment_rejected
coop_loan_approved
coop_loan_rejected
coop_setting_changed
coop_fund_transfer
coop_fund_manual
coop_shu_distributed
```

## 9. Financial Calculation Rules

```php
// SELALU baca dari settings, JANGAN hardcode:
$bunga = KopSettingModel::getSetting('kop_bunga_pinjaman_persen', '1.50');

// SELALU gunakan floatval() untuk konversi:
$nominal = floatval($this->request->getPost('nominal'));

// SELALU pakai number_format() untuk display:
number_format($amount, 2, ',', '.') // Format Indonesia

// SELALU pakai DB transaction untuk operasi multi-tabel:
$db->transStart();
try { ... $db->transComplete(); }
catch (\Exception $e) { $db->transRollback(); }
```

## 10. Security Checklist

- [ ] CSRF: `csrf_field()` di setiap form POST
- [ ] XSS: `esc()` di setiap output variabel
- [ ] SQL Injection: Pakai CI4 Query Builder, bukan raw SQL
- [ ] Auth: Cek filter di `Filters.php` dan `Routes.php`
- [ ] Authorization: Cek `inGroup()` untuk role-based access
- [ ] File Upload: Validasi `is_image`, `max_size`
- [ ] Input Validation: Server-side via CI4 `$this->validate()`
