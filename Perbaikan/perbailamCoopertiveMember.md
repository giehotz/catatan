## Analisis: Apakah `CooperativeMember.php` Perlu Di-split?

**Ya, perlu di-split.** Meskipun controller ini berorientasi pada anggota biasa (bukan admin), ukurannya sudah mencapai **~600 baris** dan menangani setidaknya **5 tanggung jawab berbeda**:

| Domain | Method | Jumlah baris |
|--------|--------|---------------|
| Keanggotaan & Aktivasi | `index()`, `getMemberOrRedirect()`, `join()` | ~100 |
| Simpanan (setoran/penarikan) | `savings()`, `deposit()`, `withdraw()`, `paySavingBill()` | ~150 |
| Pinjaman & Pengajuan | `loans()`, `requestLoan()` | ~80 |
| Angsuran (pembayaran cicilan) | `payInstallment()` | ~60 |
| Tagihan Dinamis (bulanan wajib & sosial) | `bills()` | ~130 |

Selain itu, controller ini juga memiliki **duplikasi logika** dengan `CooperativeAdmin` (misalnya perhitungan saldo simpanan, amortisasi pinjaman). Pemisahan akan memudahkan:
- **Testing** – setiap domain diuji terpisah.
- **Maintenance** – perubahan aturan simpanan tidak mengganggu tagihan.
- **Kolaborasi** – developer berbeda bisa mengerjakan fitur anggota dan fitur admin tanpa konflik merge.

---

## Prompt AI untuk Memecah `CooperativeMember.php`

Saya akan memberikan **Prompt AI** yang dapat Anda gunakan untuk melakukan refactoring controller `CooperativeMember.php` menjadi beberapa controller terpisah di folder `app/Controllers/Member/` (atau `User/Cooperative/`). Struktur akan mengikuti pola yang sama seperti pada `CooperativeAdmin`, namun kali ini untuk sisi anggota.

```text
Saya memiliki file controller CodeIgniter 4 bernama `CooperativeMember.php` (path: `app/Controllers/CooperativeMember.php`). Controller ini menangani berbagai fitur untuk anggota koperasi yang sudah login: dashboard anggota, aktivasi keanggotaan dengan kode undangan, mutasi simpanan (setoran/penarikan), pengajuan pinjaman, pembayaran angsuran, serta tagihan bulanan (simpanan wajib & dana sosial). Ukuran file saat ini sekitar 600 baris dan melanggar Single Responsibility Principle.

Saya ingin Anda melakukan **refactoring** dengan memecah controller ini menjadi beberapa controller terpisah yang lebih kecil, masing-masing hanya menangani satu domain fitur.

### Detail Controller Saat Ini

**Namespace:** `App\Controllers`  
**Method yang ada:**
- `getMemberOrRedirect()` – helper private
- `index()` – dashboard anggota (menampilkan saldo simpanan, sisa pinjaman, dll)
- `join()` – aktivasi keanggotaan dengan kode undangan
- `savings()` – daftar mutasi simpanan
- `deposit()` – unggah bukti setoran
- `withdraw()` – ajukan penarikan (hanya sukarela)
- `loans()` – daftar pinjaman & form pengajuan
- `requestLoan()` – simpan pengajuan pinjaman
- `payInstallment($loanId)` – unggah bukti angsuran
- `bills()` – tagihan simpanan wajib & sosial per bulan
- `paySavingBill()` – bayar tagihan simpanan (bulanan atau tahunan)

**Model yang digunakan:**
`KopInvitationModel`, `KopAnggotaModel`, `KopSimpananModel`, `KopPinjamanModel`, `KopAngsuranModel`, `AuditLogModel`, `KopSettingModel`

### Tujuan Refactoring

1. **Pisahkan controller** ke dalam folder `app/Controllers/Member/Cooperative/` (atau `User/Cooperative/` – pilih yang sesuai dengan struktur proyek Anda).
2. **Buat 5 controller baru** seperti di bawah ini.
3. **Hapus method `getMemberOrRedirect()`** – setiap controller akan memiliki helper sendiri atau menggunakan trait.
4. **Pertahankan semua logika bisnis** tanpa perubahan.
5. **Gunakan namespace `App\Controllers\Member\Cooperative`**.
6. **Sertakan contoh routes** yang mengarah ke controller baru (dengan asumsi sebelumnya route `cooperative/*` mengarah ke `CooperativeMember::method`).

### Struktur Controller Baru

| Controller | Method yang dipindahkan | Model yang dibutuhkan |
|------------|------------------------|------------------------|
| `MemberDashboardController` | `index()`, `getMemberOrRedirect()` (private) | `KopAnggotaModel`, `KopSimpananModel`, `KopPinjamanModel`, `KopAngsuranModel` |
| `MemberActivationController` | `join()` | `KopInvitationModel`, `KopAnggotaModel`, `AuditLogModel` |
| `MemberSavingsController` | `savings()`, `deposit()`, `withdraw()`, `paySavingBill()` | `KopSimpananModel`, `KopAnggotaModel`, `AuditLogModel`, `KopSettingModel` |
| `MemberLoansController` | `loans()`, `requestLoan()`, `payInstallment()` | `KopPinjamanModel`, `KopAnggotaModel`, `KopAngsuranModel`, `KopSettingModel`, `AuditLogModel` |
| `MemberBillsController` | `bills()`, `paySavingBill()` (jika sudah dipindah ke Savings, maka bills sendiri tetap) | `KopAnggotaModel`, `KopSimpananModel`, `KopPinjamanModel`, `KopAngsuranModel`, `KopSettingModel` |

**Catatan:** Method `paySavingBill()` saat ini berada di `CooperativeMember`. Dalam pemecahan, method ini sebaiknya ditempatkan di `MemberSavingsController` karena menangani pembayaran simpanan. Namun method `bills()` membutuhkan data tagihan dan juga menyediakan form untuk `paySavingBill`. Jadi lebih baik `paySavingBill()` tetap di `MemberSavingsController`, dan `bills()` di `MemberBillsController` akan redirect ke form pembayaran di Savings.

Alternatif: Gabungkan `MemberSavingsController` dan `MemberBillsController` karena tagihan adalah turunan dari simpanan. Tapi untuk menjaga SRP, lebih baik dipisah.

### Ketentuan Kode

Setiap controller harus:

1. **Namespace:** `namespace App\Controllers\Member\Cooperative;`
2. **Extends:** `use App\Controllers\BaseController;`
3. **Constructor:** hanya menginisialisasi model yang diperlukan.
4. **Helper `getMemberOrRedirect()`** – buat sebagai **private method** di setiap controller yang membutuhkan (atau buat **Trait** `MemberTrait` jika ingin menghindari duplikasi). Saya sarankan Trait.
5. **Hapus semua logika otorisasi manual** (tidak perlu karena sudah dipastikan login oleh filter umum `auth`). Namun tetap cek status keanggotaan aktif di method yang memerlukan.

### Opsional: Membuat Trait `MemberTrait`

Untuk menghindari duplikasi method `getMemberOrRedirect()`, buat Trait di `app/Traits/MemberTrait.php`:

```php
<?php

namespace App\Traits;

use App\Models\KopAnggotaModel;

trait MemberTrait
{
    protected function getMemberOrRedirect()
    {
        $userId = auth()->id();
        $anggotaModel = new KopAnggotaModel();
        $member = $anggotaModel->where('user_id', $userId)->first();
        
        if (!$member) {
            // Redirect ke halaman aktivasi dengan pesan
            return redirect()->to(base_url('cooperative/join'))->with('error', 'Anda belum terdaftar sebagai anggota koperasi. Silakan gunakan kode undangan.');
        }
        
        if ($member['status_keaktifan'] !== 'aktif') {
            return redirect()->to(base_url('cooperative'))->with('error', 'Keanggotaan Anda sedang ditangguhkan. Hubungi pengelola.');
        }
        
        return $member;
    }
}
```

Kemudian setiap controller yang membutuhkan member aktif dapat menggunakan `use \App\Traits\MemberTrait;` dan memanggil `$member = $this->getMemberOrRedirect();` – method ini akan mengembalikan array member atau redirect.

### Contoh Routes Baru (di `Config/Routes.php`)

Asumsikan sebelumnya route `cooperative/*` langsung ke `CooperativeMember`. Setelah refactor:

```php
// Group untuk cooperative member (tanpa filter admin)
$routes->group('cooperative', ['namespace' => 'App\Controllers\Member\Cooperative'], function($routes) {
    $routes->get('/', 'MemberDashboardController::index');
    $routes->post('join', 'MemberActivationController::join');
    
    $routes->get('savings', 'MemberSavingsController::savings');
    $routes->post('deposit', 'MemberSavingsController::deposit');
    $routes->post('withdraw', 'MemberSavingsController::withdraw');
    $routes->post('pay-saving-bill', 'MemberSavingsController::paySavingBill');
    
    $routes->get('loans', 'MemberLoansController::loans');
    $routes->post('request-loan', 'MemberLoansController::requestLoan');
    $routes->post('pay-installment/(:num)', 'MemberLoansController::payInstallment/$1');
    
    $routes->get('bills', 'MemberBillsController::bills');
});
```

### Output yang Diharapkan

Hasilkan kode lengkap untuk:
1. Trait `MemberTrait.php` (jika menggunakan pendekatan trait).
2. 5 controller di folder `app/Controllers/Member/Cooperative/`:
   - `MemberDashboardController.php`
   - `MemberActivationController.php`
   - `MemberSavingsController.php`
   - `MemberLoansController.php`
   - `MemberBillsController.php`
3. Instruksi penghapusan file `CooperativeMember.php` yang lama setelah semua berfungsi.
4. Catatan tentang perubahan yang mungkin diperlukan pada view (jika ada yang memanggil `url_to('CooperativeMember::method')`).

Pastikan semua logika bisnis tetap identik, tidak mengubah perilaku, hanya memindahkan kode.

---

**Silakan jalankan prompt ini.** Hasilkan kode yang bersih, terstruktur, dan siap digunakan.
```

