# Rencana Implementasi Revisi: Monitor Pengguna Aktif (`admin/active-users`)

Revisi ini merespons seluruh 10 poin feedback sebelum implementasi.

---

## ✅ Tanggapan atas 10 Poin Feedback

### 1. Ketergantungan pada `$recordActiveDate` — **TERVERIFIKASI AKTIF**
Konfigurasi `app/Config/Auth.php` baris 172 mengonfirmasi:
```php
public bool $recordActiveDate = true;
```
Shield memperbarui `last_active` pada **setiap request** yang melewati filter `session`. Tidak diperlukan migrasi atau perubahan konfigurasi tambahan. Hal ini akan **dicatat sebagai prasyarat** di komentar controller.

### 2. Penanganan NULL `last_active` — **DITANGANI EKSPLISIT**
PHP controller akan menghitung status berdasarkan `last_active` dengan null-safety:
- `null` → ditampilkan sebagai badge **"Belum Login"** (abu-abu)
- JavaScript `timeAgo()` akan memeriksa `dateStr` sebelum parsing, mengembalikan `'Belum pernah login'` jika null/kosong

### 3. Konsistensi statistik — **MUTUALLY EXCLUSIVE**
Statistik 4 kartu dihitung secara **hierarkis eksklusif** di PHP:
```
onlineCount  = last_active >= NOW() - 15 min
todayCount   = last_active >= CURDATE()         MINUS onlineCount
weekCount    = last_active >= 7 hari lalu       MINUS onlineCount - todayCount  
inactiveCount= NULL atau < 7 hari lalu
```
Total ke-4 kartu = jumlah seluruh pengguna. Tidak ada overlap.

### 4. Auto-refresh UX — **DIGANTI AJAX PARTIAL UPDATE**
`window.location.reload()` digantikan dengan **AJAX fetch** ke endpoint khusus yang mengembalikan JSON data pengguna terbaru. JavaScript kemudian mempatch DOM tabel secara parsial. Posisi scroll, filter aktif, dan semua interaksi **tidak terganggu**.
- Endpoint tambahan: `GET admin/active-users/data` → JSON
- Tombol **Pause/Resume** refresh tersedia
- Countdown timer 60 detik dapat di-reset manual

### 5. Kolom "Perangkat Terakhir" — **DIHAPUS DARI SCOPE**
Tabel `users` tidak memiliki kolom user agent. Kolom ini **dihapus** dari spesifikasi view. Gantinya hanya ditampilkan **IP address terakhir** yang tersedia via tabel `auth_logins` (jika dibutuhkan di iterasi berikutnya). Untuk MVP ini, cukup kolom: Username, Email, Role, Status, Terakhir Aktif.

### 6. Mekanisme Filter — **CLIENT-SIDE JAVASCRIPT**
Filter dilakukan **sepenuhnya di sisi klien** (JavaScript). Semua data sudah dikirim dari server sekali, filter tab hanya toggle visibilitas baris. Tidak ada query string `?filter=`. Auto-refresh AJAX tidak memerlukan parameter tambahan karena hanya update data, bukan route.

### 7 & 8. Integrasi Tema Adaptif & `admin_base.php` — **SLATE DARK KONSISTEN**
Admin panel **sengaja menggunakan fixed dark theme** (slate-950) yang sudah ada dan tidak menggunakan sistem tema terang/gelap. Menu navigasi baru **mengikuti konvensi slate yang sudah ada** di `admin_base.php`, konsisten dengan semua menu lain. Tidak ada perubahan tema semantik yang diperlukan di area admin.

### 9. Fallback jika `last_active` tidak terdefinisi — **DITAMBAHKAN GUARD**
Controller akan memiliki null-safe handling:
```php
$lastActive = $u->last_active ?? null;
$status = $lastActive === null ? 'never' : (...);
```
View juga memiliki PHP null check sebelum merender tanggal.

### 10. Pembatasan akses berbasis peran — **EKSPLISIT DI CONTROLLER**
Route tetap di grup `admin_auth`, namun controller juga melakukan **double-check** eksplisit:
```php
if (!auth()->user()->inGroup('admin', 'superadmin')) {
    return redirect()->to(base_url('admin'))->with('error', 'Akses Ditolak.');
}
```
Manager (koperasi) tidak dapat mengakses halaman ini.

---

## 📂 File yang Dimodifikasi/Dibuat

---

### Controller Layer

#### [NEW] `app/Controllers/Admin/ActiveUsers.php`

Dua method:
- `index()` — Render view dengan data lengkap
- `data()` — Endpoint AJAX, return JSON untuk auto-refresh

Logika PHP:
```php
$now = time();
$onlineThreshold = 15 * 60; // 15 menit
$todayStart = strtotime('today midnight');
$weekStart  = $now - (7 * 24 * 3600);

foreach ($users as $u) {
    $la = $u->last_active ? strtotime((string)$u->last_active) : null;
    if ($la === null) {
        $status = 'never';
    } elseif (($now - $la) <= $onlineThreshold) {
        $status = 'online';  // ← onlineCount++
    } elseif ($la >= $todayStart) {
        $status = 'today';   // ← todayCount++ (bukan online)
    } elseif ($la >= $weekStart) {
        $status = 'week';    // ← weekCount++
    } else {
        $status = 'inactive';
    }
}
```

---

### Route Layer

#### [MODIFY] `app/Config/Routes/admin.php`
```php
$routes->get('active-users', 'ActiveUsers::index');
$routes->get('active-users/data', 'ActiveUsers::data');  // AJAX endpoint
```

---

### View Layer

#### [NEW] `app/Views/admin/active_users.php`

**Komponen UI:**
1. **Header** dengan badge Live & timestamp terakhir update
2. **4 Kartu Statistik** (mutually exclusive):
   - 🟢 Online Sekarang (< 15 mnt, emerald)
   - 🟡 Aktif Hari Ini (amber, exclude online)
   - 🔵 Aktif Minggu Ini (indigo, exclude hari ini)
   - ⚫ Tidak Aktif / Belum Login (slate)
3. **Filter Tab Bar** — client-side JS, tidak reload halaman
4. **Search Bar** — filter username/email real-time tanpa server
5. **Tabel Pengguna** dengan kolom: No, Username+Avatar, Email, Role, Status Badge, Terakhir Aktif
6. **Auto-refresh AJAX** dengan Pause/Resume dan countdown indicator
7. **Empty state** per filter jika tidak ada data

**JavaScript null-safe `timeAgo()`:**
```javascript
function timeAgo(dateStr) {
    if (!dateStr || dateStr === 'null') return 'Belum pernah login';
    const diff = Date.now() - new Date(dateStr).getTime();
    if (isNaN(diff)) return 'Tidak diketahui';
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1)  return 'Baru saja';
    if (minutes < 60) return `${minutes} menit lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24)   return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    return `${days} hari lalu`;
}
```

**AJAX partial update (menggantikan `window.location.reload()`):**
```javascript
async function fetchAndUpdateTable() {
    const res = await fetch(ACTIVE_USERS_DATA_URL);
    const { users } = await res.json();
    renderRows(users);              // patch DOM saja
    updateStatCards(users);         // update angka kartu
    updateLastRefreshTime();        // update timestamp di header
    reapplyActiveFilter();          // reapply filter tab yang aktif
}
```

#### [MODIFY] `app/Views/layouts/admin_base.php`

Tambah menu **"Monitor Aktif"** mengikuti konvensi slate yang sudah ada:
- Desktop navbar: setelah "Log Audit" (baris ~75), hanya tampil untuk `admin|superadmin`
- Mobile drawer: setelah item "Log Audit" mobile (baris ~190)
- Active class: `bg-cyan-500/15 text-cyan-400 ring-1 ring-cyan-500/30`

---

## 🧪 Rencana Verifikasi

### Automated
- Route `admin/active-users` accessible → 200
- Route `admin/active-users/data` returns JSON `{users: [...]}`
- Manager login → redirect ke admin dengan error 'Akses Ditolak'

### Manual
1. Login sebagai user biasa → buka beberapa halaman
2. Buka `admin/active-users` → verifikasi muncul badge ONLINE
3. Klik filter tab "Online" → verifikasi hanya user online terlihat
4. Tunggu 60 detik → verifikasi tabel di-update via AJAX (tidak reload)
5. Klik Pause → verifikasi countdown berhenti
6. Verifikasi user dengan `last_active = NULL` tampil badge "Belum Login"
7. Verifikasi total 4 kartu = jumlah total pengguna


Menambahkan halaman monitoring real-time berbasis kolom `last_active` yang sudah ada di tabel `users`, untuk menampilkan aktivitas pengguna kepada administrator dalam panel yang kaya visual dan interaktif.

---

## 📌 Deskripsi Tujuan

Halaman **Active Users Monitor** akan menampilkan:
- Daftar pengguna yang sedang atau baru-baru ini **online** (berdasarkan `last_active` dalam 15 menit terakhir)
- Statistik ringkasan: berapa yang **Online Sekarang**, **Aktif Hari Ini**, **Aktif Minggu Ini**, **Total Terdaftar**
- Tabel lengkap semua pengguna dengan kolom "Terakhir Aktif" yang menampilkan waktu relatif ("5 menit lalu", "2 jam lalu", dsb.)
- Badge status **ONLINE** real-time dengan animasi pulse untuk yang aktif < 15 menit
- Filter berdasarkan status aktivitas (Semua / Online / Aktif Hari Ini / Tidak Aktif)
- **Auto-refresh** setiap 60 detik agar data tetap segar tanpa reload halaman penuh
- Navigasi menu baru di `admin_base.php`

---

## 🛠️ Arsitektur Teknis

### Sumber Data
Kolom `last_active` (DATETIME, nullable) sudah ada di tabel `users` sejak migrasi `CreateUsersTable`. Shield CI4 secara otomatis memperbarui kolom ini setiap request terotentikasi jika config `Auth.php` `$recordActiveDate = true` aktif.

### Definisi Status
| Status | Kondisi `last_active` |
|---|---|
| 🟢 **Online** | `last_active >= NOW() - INTERVAL 15 MINUTE` |
| 🟡 **Aktif Hari Ini** | `last_active >= CURDATE()` dan bukan online |
| 🟠 **Aktif Minggu Ini** | `last_active >= NOW() - INTERVAL 7 DAY` dan bukan hari ini |
| ⚫ **Tidak Aktif** | `last_active < NOW() - INTERVAL 7 DAY` atau NULL |

---

## 📂 Proposed Changes

---

### Controller Layer

#### [NEW] `app/Controllers/Admin/ActiveUsers.php`
Controller baru dengan method `index()`:
- Query semua users dengan kolom `last_active`, `username`, `email`, `active`, `created_at`
- Hitung statistik agregat: `onlineCount`, `todayCount`, `weekCount`, `totalCount`
- Kirim data ke view `admin/active_users`
- Support filter query string `?filter=online|today|week|inactive`

---

### Route Layer

#### [MODIFY] `app/Config/Routes/admin.php`
Tambahkan 1 route GET baru dalam group `admin`:
```php
$routes->get('active-users', 'ActiveUsers::index');
```

---

### View Layer

#### [NEW] `app/Views/admin/active_users.php`
Halaman monitoring dengan komponen:

1. **Header Banner** — gradien indigo-purple dengan title & subtitle
2. **Stats Grid (4 Kartu)**:
   - 🟢 Online Sekarang (pulse hijau)
   - 🟡 Aktif Hari Ini (amber)
   - 🟠 Aktif Minggu Ini (indigo)
   - ⚫ Total Pengguna (slate)
3. **Filter Tab Bar** — Semua / Online / Aktif Hari Ini / Tidak Aktif
4. **Tabel Data Pengguna** dengan kolom:
   - Avatar + Username
   - Email
   - Role/Grup
   - Status Badge (ONLINE / AKTIF / TIDAK AKTIF)
   - Terakhir Aktif (waktu relatif JS)
   - Perangkat Terakhir (User Agent sederhana — jika tersedia)
5. **Auto-refresh indicator** — countdown badge "Refresh dalam X detik"
6. **Empty state** jika tidak ada data sesuai filter

#### [MODIFY] `app/Views/layouts/admin_base.php`
Tambahkan menu item **"Monitor Aktif"** pada:
- Desktop navbar (baris ~71, setelah "Log Audit")
- Mobile drawer (baris ~195, setelah item Log Audit mobile)

---

## 🧩 Detail Implementasi JavaScript

```javascript
// Auto-refresh setiap 60 detik
let countdown = 60;
const autoRefreshTimer = setInterval(() => {
    countdown--;
    updateCountdownBadge(countdown);
    if (countdown <= 0) {
        window.location.reload();
    }
}, 1000);

// Waktu relatif (Indonesian locale)
function timeAgo(dateStr) {
    const diff = Date.now() - new Date(dateStr).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'Baru saja';
    if (minutes < 60) return `${minutes} menit lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    return `${days} hari lalu`;
}
```

---

## 🎨 Visual Design

- **Konsisten dengan admin panel yang ada** (slate-950 dark theme, glow gradients)
- Badge ONLINE: `bg-emerald-500/15 text-emerald-400 border-emerald-500/25` dengan dot animasi `animate-pulse`
- Badge AKTIF HARI INI: `bg-amber-500/15 text-amber-400 border-amber-500/25`
- Badge TIDAK AKTIF: `bg-slate-700/30 text-slate-500 border-slate-700/40`
- Filter tab aktif: highlight indigo dengan ring

---

## 🧪 Verification Plan

### Automated
- Pastikan route `admin/active-users` dapat diakses oleh admin dan superadmin
- Filter query string berfungsi (menampilkan/menyembunyikan baris yang benar)

### Manual
1. Buka browser, login sebagai user biasa → kunjungi beberapa halaman
2. Buka admin panel → `admin/active-users`
3. Verifikasi user tersebut muncul sebagai ONLINE (< 15 menit)
4. Tunggu 15 menit → status berubah ke AKTIF HARI INI
5. Verifikasi auto-refresh countdown berjalan dan halaman refresh otomatis
6. Verifikasi menu navigasi baru muncul di desktop dan mobile drawer
