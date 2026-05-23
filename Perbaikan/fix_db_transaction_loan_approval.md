# Fix: DB Transaction di approveLoan() + Auto-Promote + Tests

## Ringkasan

Tiga perbaikan prioritas tinggi berdasarkan review project (`Perbaikan/review_project.md`).

---

## 1. DB Transaction Wrapping di approveLoan()

**File:** `app/Controllers/Cooperative/LoanController.php`

**Sebelum:**
- `approveLoan()` melakukan **5+ operasi DB** tanpa transaction:
  1. Insert debt
  2. Insert receivable
  3. Update loan status
  4. Insert kas (pencairan)
  5. Insert upfront bunga/jasa (0-2x)
- Jika salah satu gagal di tengah → data inkonsisten (utang tercatat tapi dana tidak cair)

**Sesudah:**
- Semua operasi DB dibungkus dalam `$db->transStart()` / `$db->transComplete()`
- Try/catch dengan `$db->transRollback()` di catch block
- Pattern mengikuti `DirectLoanController::storeDirectLoan()` yang sudah benar

**Kode relevan:**
```php
$db = \Config\Database::connect();
$db->transStart();
try {
    // ... semua insert/update ...
    $db->transComplete();
    if ($db->transStatus() === false) {
        return redirect()->back()->with('error', 'Pencairan gagal karena kesalahan sistem.');
    }
    return redirect()->back()->with('message', '...');
} catch (\Exception $e) {
    $db->transRollback();
    return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
}
```

---

## 2. Hapus Auto-Promote Superadmin

**File:** `app/Controllers/BaseController.php`

**Sebelum:**
```php
// Auto-promote first user (ID 1) to superadmin
if (auth()->loggedIn()) {
    $user = auth()->user();
    if ($user->id === 1 && !$user->inGroup('superadmin')) {
        $user->addGroup('superadmin');
    }
}
```

**Sesudah:** Dihapus. Tidak ada user yang otomatis mendapat akses superadmin.

**Reason:**
- Jika user ID 1 adalah user reguler (misal setelah restore DB), dia dapat superadmin
- Security risk di shared hosting
- Assign group superadmin harus manual via seeder atau admin panel

---

## 3. Unit Tests untuk Service Layer

### 3a. LoanAmortizationServiceTest

**File:** `tests/unit/LoanAmortizationServiceTest.php`

**Test cases (15 tests):**

| Test | Verifikasi |
|------|------------|
| `testBuildScheduleFlatRateCicil` | Pokok/bunga/jasa per bulan benar, due dates |
| `testBuildScheduleFlatRateDiAwal` | Bunga = 0 karena dibayar upfront |
| `testBuildScheduleEffectiveRate` | Bunga menurun setiap bulan (declining balance) |
| `testBuildScheduleEffectiveBungaDeclinesOverTime` | Strict decline: month N > month N+1 |
| `testBuildScheduleEmptyForZeroTenor` | Empty array untuk tenor 0 |
| `testBuildScheduleWithRecords` | Status 'approved' vs 'Belum Dibayar', record attachment |
| `testGetInstallmentNumberForMonth` | Mapping due date → angsuran_ke |
| `testGetInstallmentNumberForMonthNullWhenNoApprovedAt` | Null safety |
| `testGetArrearsForMonthNoDueInstallment` | has_due = false |
| `testGetArrearsForMonthPaidInstallment` | has_due = true, bunga = 0 (sudah bayar) |
| `testGetArrearsForMonthUnpaidInstallment` | has_due = true, bunga dihitung |
| `testGetArrearsForMonthLoanPaidBeforeMonth` | Loan paid → no arrears |
| `testCalculateLoanSummaryNoPayments` | grand_total benar, progress 0% |
| `testCalculateLoanSummaryPartialPayment` | progress 25%, 3 angsuran dibayar |
| `testCalculateLoanSummaryFullPayment` | progress 100%, 3/3 dibayar |

### 3b. BudgetServiceTest

**File:** `tests/unit/BudgetServiceTest.php`

**Mocking strategy:**
- CI4 Model pakai `__call()` untuk fluent methods (`where`, `selectSum`, dll)
- Pakai `addMethods()` + `onlyMethods()` via `getMockBuilder()`
- Service dibuat via `ReflectionClass::newInstanceWithoutConstructor()` (karena SQLite3 tidak terinstall)
- Model mocks di-inject via `ReflectionProperty::setValue()`

**Test cases (9 tests):**

| Test | Verifikasi |
|------|------------|
| `testGetBudgetReportReturnsAllCategories` | 4 categories returned |
| `testGetBudgetReportParentCategoryCalculatesRollupSpending` | Parent spending = direct + children (3.3M), 110% = Overbudget |
| `testGetBudgetReportChildCategoryNoLimit` | Child 800K, limit 0, percent 0 |
| `testGetBudgetReportCategoryWithinBudget` | 50% = hijau Aman |
| `testGetSmartAlertsReturnsOnlyCategoriesAbove80Percent` | Hanya ≥80% yang masuk alert |
| `testGetSmartAlertsExcludesCategoriesUnder80Percent` | 50% tidak masuk alert |
| `testSetLimitInsertsWhenNoExistingBudget` | Insert dipanggil dengan data benar |
| `testSetLimitUpdatesWhenExistingBudget` | Update dipanggil dengan data benar |
| `testDeleteLimit` | Delete dipanggil |

### 3c. TransactionFlowTest (database integration)

**File:** `tests/database/TransactionFlowTest.php`

**Test cases (8 tests):** create income, create expense, zero amount (expect error), negative amount (expect error), update, delete, date range filter, user scoping.

**Catatan:** Membutuhkan SQLite3 PHP extension atau MySQL test database yang dikonfigurasi di `phpunit.xml`:
```xml
<env name="database.tests.hostname" value="localhost"/>
<env name="database.tests.database" value="catatan_test"/>
<env name="database.tests.username" value="root"/>
<env name="database.tests.DBDriver" value="MySQLi"/>
```

---

## Cara Menjalankan Tests

```bash
# Semua unit tests
vendor/bin/phpunit tests/unit/ --no-coverage

# Service test spesifik
vendor/bin/phpunit tests/unit/LoanAmortizationServiceTest.php --no-coverage
vendor/bin/phpunit tests/unit/BudgetServiceTest.php --no-coverage

# Database test (butuh SQLite3 atau MySQL test DB)
vendor/bin/phpunit tests/database/TransactionFlowTest.php --no-coverage
```

## Hasil

```
OK (26 tests, 96 assertions)  # unit tests
# Database tests: skip sampai SQLite3 terinstall atau MySQL test DB dikonfigurasi
```

---

## Perbaikan Belum Dilakukan (Masih Open)

Dari review project, prioritas selanjutnya:
4. **Transaction wrapping di InstallmentController::approveInstallment()** — update status angsuran di luar transaction
5. **Aktifkan CSRF global** — `app/Config/Filters.php:87` masih komentar
6. **Exception messages jangan bocor ke user** — `Transaction.php` lines 186, 278, 330, 432
7. **N+1 query di Transaction::index()** — category lookup dalam loop
8. **ENUM → VARCHAR** — migration `2026-05-20-120000` sudah mulai, lanjutkan untuk tabel lain
