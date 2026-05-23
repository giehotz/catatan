# Review Project — Catatan Keuangan + Koperasi

**Tanggal:** 2026-05-23
**Tech Stack:** CodeIgniter 4 · PHP 8.1+ · MySQL · Tailwind CSS v4 · CodeIgniter Shield
**Lokasi:** `C:\laragon\www\catatan`

---

## Ringkasan Eksekutif

Aplikasi **Catatan Keuangan** adalah sistem pencatatan keuangan pribadi + modul **Koperasi Simpan Pinjam** yang dibangun di atas CodeIgniter 4. Proyek ini memiliki arsitektur yang cukup terstruktur dengan pemisahan routing modular, service layer, dan dokumentasi yang baik. Namun, terdapat sejumlah kelemahan serius di sisi **keamanan**, **integritas data finansial**, **testing**, dan **konsistensi kode** yang perlu segera ditangani sebelum digunakan di production.

**Skor keseluruhan:** 6.5/10 — Layak dikembangkan lebih lanjut dengan prioritas pada data integrity dan testing.

---

## KELEBIHAN (Strengths)

### 1. Arsitektur Service Layer yang Baik

Beberapa controller sudah mendelegasikan logika bisnis ke service layer:

| Service | Fungsi | Kualitas |
|---------|--------|----------|
| `BudgetService.php` | Enkapsulasi logika budget + alert system | Baik |
| `LoanAmortizationService.php` | Perhitungan amortisasi pinjaman (flat/efektif) | Sangat baik — PHPDoc lengkap, 6 method terstruktur |
| `ChartService.php` | Data distribusi + tren bulanan | Bersih |
| `RecurringService.php` | Transaksi berulang dengan catch-up logic | Baik |
| `ExportService.php` | Ekspor CSV/Excel/PDF | Fungsional, tapi ada catatan |

**Contoh:** `BudgetController` hanya 108 lines karena delegasi ke `BudgetService`.
**Contoh:** `LoanAmortizationService` memiliki komentar yang jelas tentang formula dan pencegahan *formula drift* (lines 8-10).

### 2. Modular Route Organization

Routes dipisah ke **13 file** di `app/Config/Routes/`:
```
web.php, auth.php, transaction.php, category.php, debt-receivable.php,
profile.php, report.php, budget.php, recurring.php, wallet.php,
savings.php, admin.php, cooperative-admin.php, cooperative-member.php, inbox.php
```
Masing-masing dengan filter auth yang sesuai (`user_auth`, `admin_auth`, `coop_auth`).

### 3. Cache Invalidation Hooks di Model Layer

Beberapa model cooperative menggunakan event hooks untuk cache management:
- `KopPinjamanModel` — `$afterUpdate`, `$afterDelete` untuk invalidate cache pinjaman
- `KopAngsuranModel` — `$afterInsert`, `$afterUpdate`, `$afterDelete` untuk invalidate parent loan
- `KopSettingModel` — Double-checked locking dengan `flock()` + cache 1 jam (pattern advanced untuk CI4)

### 4. Escaping Output Konsisten

Semua output user di view menggunakan `esc()`:
```php
<?= (string) esc($tx['description']) ?>  <!-- dashboard.php:229 -->
```
Ini memberikan perlindungan XSS yang baik.

### 5. Comprehensive Migration Set

23 file migrasi dengan nama deskriptif dan foreign key yang menggunakan `CASCADE`/`SET NULL` secara konsisten. Semua nilai moneter menggunakan `DECIMAL(15,2)` — tidak ada float abuse.

### 6. Auth Multi-Role dengan Custom Filters

- **superadmin** — akses penuh
- **admin** — manajemen user + cooperative
- **manager** — cooperative management
- **developer** — settings + user create
- **user** — financial tracking
- **beta** — akses beta features

3 custom filters: `UserAuthFilter`, `AdminAuthFilter`, `CoopAuthFilter` + `CsrfHeaderFilter` untuk AJAX.

### 7. Mobile-Responsive Views

Dedicated mobile views di `app/Views/mobile/` dengan glassmorphic UI, auto-detection device, dan graceful fallback ke desktop views. Preview override via `?preview=mobile|desktop`.

### 8. Dokumentasi Arsitektur

`architecture_map.md` — 270 lines dengan Mermaid diagrams (ERD, sequence flow, system architecture). Dilengkapi `db/AI_BOOT_CONTEXT.md` untuk konteks AI agent.

### 9. Dark/Light Mode Infrastructure

Tailwind CSS v4 semantic color tokens (`bg-base`, `bg-surface`, `text-tx-primary`), FOWT prevention via inline blocking script. Phase 1-2 sudah selesai.

### 10. Refactoring Kontroller Monolitik

`CooperativeAdmin` (~800+ lines, 22 methods) telah berhasil dipecah menjadi **18 controller** terpisah di `Cooperative/` dan `Cooperative/Member/`. `CooperativeMember` (~600 lines) juga sudah dipecah dengan `MemberTrait` untuk DRY.

### 11. Dual-Sync Pattern untuk Pinjaman

Pinjaman koperasi otomatis membuat entri `debts` (untuk anggota) dan `receivables` (untuk koperasi) — menjaga konsistensi data personal finance dengan cooperative module.

### 12. PHP 8.1+ Required

Proyek ***membutuhkan*** PHP 8.1+, yang berarti infrastruktur modern tersedia — meskipun fitur-fitur 8.1 belum dimanfaatkan (lihat kekurangan).

---

## KEKURANGAN (Weaknesses)

### 🔴 KRITIS — Harus Segera Diperbaiki

#### 1. ❌ Tidak Ada Database Transaction di Loan Approval

**Lokasi:** `app/Controllers/Cooperative/LoanController.php:86-178`

`approveLoan()` melakukan **5+ operasi database terpisah** tanpa wrapping transaction:
1. Insert debt
2. Insert receivable
3. Update loan status
4. Insert kas internal (pencairan)
5. Insert upfront bunga/jasa

Jika salah satu gagal di tengah, data menjadi **inkonsisten** — utang tercatat tapi dana tidak cair, atau sebaliknya.

**Kontras:** `DirectLoanController::storeDirectLoan()` sudah menggunakan `transStart()`/`transComplete()` dengan benar.

**Rekomendasi:** Bungkus semua operasi dalam `$db->transStart()` / `$db->transComplete()` dengan rollback di catch block.

#### 2. ❌ Race Condition pada Balance Check

**Lokasi:** `LoanController.php:75-78` → `getSaldo()` check lalu 40 lines kemudian `insert()` kas

Tidak ada `SELECT ... FOR UPDATE` atau row-level locking antara pengecekan saldo dan pengurangan. Dua request concurrent bisa menyetujui pinjaman dari sumber dana yang sama, menyebabkan **overdraft**.

**Rekomendasi:** Gunakan `$db->table('kop_kas_internal')->where('sumber', $sumber)->forUpdate()->get()` atau pindahkan pengecekan + pengurangan ke dalam satu transaksi.

#### 3. ❌ Auto-Promote User ID 1 ke Superadmin

**Lokasi:** `app/Controllers/BaseController.php:46-51`

```php
if ($user->id === 1 && !$user->inGroup('superadmin')) {
    // auto-promote
}
```

**Severity: HIGH** — Jika user ID 1 adalah user reguler (misal setelah restore DB dari backup), atau di shared hosting, ini memberikan akses admin penuh ke user yang salah.

**Rekomendasi:** Hapus auto-promote. Gunakan seeder atau migration untuk assign group superadmin.

#### 4. ❌ Test Coverage Mendekati Nol

**Lokasi:** `tests/`

Hanya ada 3 test file — semuanya boilerplate CI4 default:
- `HealthTest.php` (2 assertions — cek APPPATH + baseURL)
- `ExampleDatabaseTest.php` (4 assertions — test example model)
- `ExampleSessionTest.php` (1 assertion)

**Tidak ada test untuk:**
- Controller (Transaction, Budget, Wallet, LoanController, dll — 0 coverage)
- Model aplikasi (TransactionModel, KopPinjamanModel, dll — 0 coverage)
- Service (BudgetService, ExportService, LoanAmortizationService — 0 coverage)
- Integration / HTTP endpoint tests
- Security tests (CSRF, XSS, authorization)

**Coverage estimate:** < 1%

**Rekomendasi:** Mulai dengan integration test untuk operasi finansial kritis (create transaction, approve loan), kemudian unit test untuk service.

---

### 🟡 SEDANG — Prioritas Tinggi

#### 5. ❌ Tidak Ada Transaction untuk Installment Approval

**Lokasi:** `InstallmentController.php:130-156`

Status angsuran di-update di luar transaction, lalu insert kas dan debt_payments terpisah. Crash di tengah → angsuran approved tapi kas tidak tercatat.

#### 6. ❌ Exception Messages Terekspos ke User

**Lokasi:** `Transaction.php:186, 278, 330, 432`

```php
catch (\Exception $e) {
    $db->transRollback();
    return redirect()->back()->withInput()
        ->with('error', 'Gagal: ' . $e->getMessage());
}
```

Detail teknis database bocor ke user. Di production, ini information disclosure.

#### 7. ❌ CSRF Tidak Diaktifkan Global

**Lokasi:** `app/Config/Filters.php:87`

```php
// 'csrf',   // commented out
```

CSRF protection tidak aktif secara global. Beberapa form menggunakan `csrf_field()` manual, tapi tidak konsisten.

#### 8. ❌ View Files Terlalu Besar

**Lokasi:** `app/Views/user/transactions/index.php` — **782 lines**

File ini menangani: daftar transaksi, modal add income, modal add expense, modal adjust balance, filter form. Ini SRP violation di view layer. Juga ada duplikasi logika antara desktop dan mobile views.

#### 9. ❌ Tidak Ada PHP 8.1+ Features

Proyek membutuhkan PHP ^8.1 tapi **tidak menggunakan**:
- `readonly` properties
- `enum` (masih pakai string constants / hardcoded)
- `match` expression (masih `if/elseif`)
- Named arguments
- `declare(strict_types=1)` — hanya beberapa file
- Return type hints — banyak method tanpa tipe return

#### 10. ❌ Model Layer Sangat Tipis

Hampir semua model hanya berisi konfigurasi tabel + allowed fields. **Tidak ada:**
- Validation rules (`$validationRules`)
- Relationship definitions (`$hasMany`, `$belongsTo`)
- Custom query methods
- Model events (kecuali Kop* models)

Akibatnya, logika query berulang **tersebar di controller-controller**.

#### 11. ❌ N+1 Query Problem

**Lokasi:** `Transaction.php:87-92`

Di dalam loop transaksi, kategori dicari satu per satu:
```php
$this->incomeCategoryModel->find($tx['income_category_id'])  // N+1!
```

Ini akan menjadi masalah performa dengan ribuan transaksi.

---

### 🟢 RENDAH — Perbaikan Jangka Menengah

#### 12. ❌ Duplicate Code Tersebar

| Pattern | Lokasi |
|---------|--------|
| Cek kepemilikan wallet diulang 4+ kali | `Transaction.php`, `Wallet.php` |
| Join anggota+user diulang 5+ controller | `LoanController`, `ArrearsReportController`, `MemberController`, dll |
| Upload file pattern diulang 3 controller | `InstallmentController`, `Member/LoansController`, `Member/BillsController` |
| Settings key list didefinisikan 3x dengan isi berbeda | `SettingController.php:22-54, 87-115, 299-309` |
| Duplikasi view logic desktop vs mobile | `user/dashboard.php` vs `mobile/user/dashboard.php` |

#### 13. ❌ HTML PDF Tersemat di PHP

**Lokasi:** `ExportService.php:261-510`

283 lines HTML + inline CSS sebagai string PHP untuk `exportToPDF()`. Harusnya jadi view file terpisah.

#### 14. ❌ ENUM vs Lookup Table

Beberapa ENUM di schema (status pinjaman, status angsuran, jenis simpanan) sebaiknya menjadi lookup table. Terbukti merepotkan — migration `2026-05-20-120000` harus mengubah `jenis_simpanan` dari ENUM ke VARCHAR.

#### 15. ❌ Dead Code

**Lokasi:** `LoanAmortizationService.php:57-59`

Variable `$bungaPerBulanFlatTotal` dideklarasikan tapi **tidak pernah digunakan**. Perhitungan aktual dilakukan di loop (line 75).

#### 16. ❌ SHU Distribution Route Tidak Ditemukan

View `shu.php:129` mereferensikan `admin/cooperative/shu/distribute` tapi controller untuk endpoint ini tidak ada — mungkin hilang saat refactoring.

#### 17. ❌ Rounding Drift Risk

Semua nilai di-`round(..., 2)` secara individual di amortization service. Akumulasi error rounding bisa terjadi untuk tenor panjang (24-60 bulan).

#### 18. ❌ Tidak Ada Named Routes

Semua route menggunakan hardcoded URL di view (`base_url('admin/cooperative/loans')`) — tidak ada yang menggunakan `route('nama_route')`. Ini menyulitkan perubahan struktur URL.

#### 19. ❌ Inconsistent Internationalization

Campuran Bahasa Indonesia dan Inggris di:
- Kode: method names Inggris, komentar Indonesia
- View strings: hardcoded Indonesia tanpa lang file
- Variabel: `$angsuran`, `$pinjaman` vs `$transaction`, `$wallet`

#### 20. ❌ Tidak Ada Version Control (Git)

Tidak ada `.git` directory. Tidak ada riwayat perubahan, tidak ada branching, tidak ada code review.

---

## MATRIKS PRIORITAS REKOMENDASI

| # | Rekomendasi | Severity | Effort | Impact | Priority |
|---|------------|----------|--------|--------|----------|
| 1 | Wrapping transaction di approveLoan() | 🔴 KRITIS | Rendah (beberapa jam) | Mencegah data inkonsistensi finansial | **HIGH** |
| 2 | Atomic balance check + deduction | 🔴 KRITIS | Rendah (pindahkan ke dalam transaksi) | Mencegah overdraft | **HIGH** |
| 3 | Hapus auto-promote superadmin | 🔴 KRITIS | Rendah (hapus 5 lines) | Keamanan akun | **HIGH** |
| 4 | Tambah integration test untuk alur kritis | 🔴 KRITIS | Tinggi (2-3 hari) | Mencegah regression | **HIGH** |
| 5 | Transaction di installment approval | 🟡 SEDANG | Rendah | Integritas data angsuran | **HIGH** |
| 6 | Aktifkan CSRF global | 🟡 SEDANG | Rendah | Keamanan form | **HIGH** |
| 7 | Refactor view 782 lines → partials | 🟡 SEDANG | Sedang | Maintainability | **MEDIUM** |
| 8 | Extract shared queries ke model methods | 🟡 SEDANG | Sedang | DRY + maintainability | **MEDIUM** |
| 9 | Gunakan PHP 8.1 features (readonly, enum, match) | 🟡 SEDANG | Tinggi | Code quality | **MEDIUM** |
| 10 | Extract PDF HTML ke view file | 🟢 RENDAH | Rendah | Maintainability | **MEDIUM** |
| 11 | Init Git repository | 🟢 RENDAH | Rendah | Version control | **MEDIUM** |
| 12 | ENUM → lookup tables | 🟢 RENDAH | Tinggi | Schema flexibility | **LOW** |
| 13 | Named routes | 🟢 RENDAH | Sedang | URL flexibility | **LOW** |
| 14 | Internationalization | 🟢 RENDAH | Tinggi | i18n readiness | **LOW** |
| 15 | Remove dead code | 🟢 RENDAH | Rendah | Cleanup | **LOW** |

---

## CATATAN TAMBAHAN

### Arsitektur — Overall
Aplikasi ini memiliki fondasi yang cukup baik dengan pemisahan routing, service layer, dan dokumentasi. Refactoring controller monolithic sudah dilakukan dan menghasilkan struktur yang lebih terurus. Namun, **data integrity untuk operasi finansial adalah kelemahan paling serius** — dalam aplikasi yang menangani uang, transaction wrapping adalah **non-negotiable**.

### Performa
- N+1 query di Transaction::index() perlu diperbaiki dengan eager loading
- `LOWER()` search di LoanController::directory() akan slow pada dataset besar (no functional index)
- KopKasInternalModel.getSaldo() menghitung ulang dari awal setiap kali — perlu running balance column
- RecurringService jalan sinkronus — heavy user dengan banyak schedule akan alami slow page load

### Security
- Masalah paling serius: auto-promote superadmin
- Kedua: exception messages bocor ke user
- Ketiga: CSRF tidak aktif global
- Tidak ada rate limiting
- Tidak ada CSP headers
- Tidak ada security test

### Testing
Ini adalah area yang paling memprihatinkan. Proyek sebesar ini dengan operasi finansial **harus** memiliki test suite. Minimum:
- Integration test untuk create/update/delete transaction
- Integration test untuk loan approval flow
- Unit test untuk LoanAmortizationService
- Unit test untuk BudgetService.getBudgetReport()

### Dark Mode
Infrastructure sudah bagus (Tailwind tokens, FOWT prevention). Phase 3 (DB theme_preference) dan Phase 4 (UI switcher + refactoring) masih pending dan perlu diselesaikan agar fitur benar-benar usable.

---

*Review ini disusun berdasarkan analisis kode pada 2026-05-23. Beberapa temuan mungkin sudah diperbaiki sebagian.*
