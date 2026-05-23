# 📱 Panduan Pengembangan & Integrasi Mobile Views (Premium v3)

Dokumen ini menjelaskan arsitektur, cara kerja, dan petunjuk pemeliharaan untuk sistem **Frontend Mobile** yang sepenuhnya terpisah dari **Desktop** pada aplikasi Catatan Keuangan.

---

## 🚀 Prinsip Utama: "Murni Presentasi / Hanya Memuat View"

Sistem ini dirancang dengan prinsip pemisahan total pada lapisan presentasi tanpa memengaruhi logika bisnis backend:
1. **100% Kontroler Bersama**: Logika di dalam `Controller`, `Model`, `Entities`, `Filters/Middleware`, maupun `Config` tetap **100% asli** dan digunakan bersama.
2. **Tidak Ada Duplikasi Kode Logika**: Seluruh penanganan kueri database, autentikasi, otorisasi, dan validasi formulir didefinisikan sekali di controller asal.
3. **Pemuatan Berkas Dinamis**: Sistem hanya melakukan penukaran berkas `.php` di dalam fungsi `view()` secara transparan.

---

## 🛠️ Cara Kerja Redireksi Tampilan

Fungsi global `view()` telah didefinisikan ulang secara aman di dalam `app/Common.php` (di dalam blok `!function_exists('view')`) untuk melakukan hal berikut:

1. **Deteksi Perangkat Seluler**:
   Menggunakan `Services::userAgent()` bawaan CodeIgniter 4 untuk mendeteksi apakah permintaan berasal dari perangkat seluler (`$agent->isMobile()`).
2. **Pengecualian Tablet**:
   Untuk kenyamanan pengguna, perangkat Tablet (seperti iPad) dikecualikan (`!$agent->isTablet()`) sehingga tablet tetap mendapatkan antarmuka desktop yang kaya informasi.
3. **Mekanisme Fallback Anggun (Graceful Fallback)**:
   Saat perangkat seluler terdeteksi, sistem memeriksa keberadaan berkas view mobile di `app/Views/mobile/{name}.php`.
   - **Jika berkas ada**: Sistem memuat berkas mobile tersebut.
   - **Jika berkas belum ada**: Sistem otomatis memuat berkas desktop aslinya (`app/Views/{name}.php`). Ini memungkinkan migrasi bertahap per halaman.
4. **Bypass Pengujian**:
   Pengembang dapat memaksa pemuatan tampilan desktop di perangkat seluler dengan menambahkan parameter kueri `?preview=desktop` pada URL.

### Diagram Alir Deteksi:

```
                    ┌───────────────────────────────┐
                    │      Controller Bersama       │
                    │ (Kueri data, validasi, dll.)  │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                      Panggilan view('user/dashboard')
                                    │
                          [ Common.php Override ]
                                    │
                  ┌──────────────────┴──────────────────┐
                  │                                     │
         Perangkat Seluler?                    Bukan Perangkat Seluler?
         (Tanpa Tablet / Bypass)               (Atau ?preview=desktop)
                  │                                     │
                  ▼                                     ▼
     Apakah Views/mobile/... ada?                       │
            ┌─────┴──────┐                              │
            │            │                              │
         [ Ada ]      [ Tidak ]                         │
            │            │                              │
            ▼            ▼                              ▼
    [ Views/mobile/... ] ➔ Fallback ➔ ➔ ➔ ➔ ➔ [ Views/Desktop Asli ]
```

---

## 📂 Struktur Direktori Mobile Views

Tampilan seluler disimpan di bawah folder `app/Views/mobile/` dengan hirarki nama berkas yang mencerminkan struktur view desktop secara identik:

```text
app/
└── Views/
    ├── layouts/
    │   ├── base.php               # Shell layout utama desktop
    │   └── mobile_base.php        # Shell layout utama mobile (Premium Glassmorphic)
    │
    ├── user/                      # Folder views desktop
    │   ├── dashboard.php
    │   ├── reports/index.php
    │   └── transactions/index.php
    │
    └── mobile/                    # Folder views mobile
        └── user/
            ├── dashboard.php      # Dasbor Mobile (Karusel & Snap dots)
            ├── reports/
            │   └── index.php      # Laporan Mobile (ApexCharts 260px)
            └── transactions/
                └── index.php      # Transaksi Mobile (Bottom Sheets & Large Input)
```

---

## 💎 Fitur Premium Antarmuka Seluler (Tailwind CSS v4)

* **Glassmorphic Bottom Navigation**: Bilah navigasi bawah berpijar yang menempel secara *fixed* di bawah layar ponsel (`backdrop-blur-md bg-slate-950/80`).
* **Circular Accent FAB (Floating Action Button)**: Tombol melayang linear-gradient di tengah bawah untuk aksi pencatatan cepat (membuka quick-add sheet).
* **Slide-up Bottom Sheets**: Panel aksi meluncur dari bawah yang interaktif menggunakan CSS Transitions & JavaScript murni tanpa membebani browser.
* **Wallet CSS Scroll Snap Carousel**: Daftar rekening berbentuk kartu geser mewah yang diselaraskan dengan titik indikator kebulatan aktif di bawahnya secara presisi.
* **Large Touch Elements**: Seluruh target sentuhan (termasuk filter chip, input nominal, dan tombol) memiliki tinggi minimal `44px` hingga `56px` untuk kenyamanan jempol.
* **Lock Background Scroll**: Guliran latar belakang dikunci (`overflow-hidden` pada `body`) saat bottom sheet sedang aktif.
* **Safe Padding**: Penambahan kelas `pb-24` (`padding-bottom: 96px`) pada elemen utama `<main>` guna menghindari konten terpotong atau tertutup bilah menu bawah.

---

## 👩‍💻 Petunjuk Pengujian & Debugging

1. **Uji Menggunakan Browser (Google Chrome)**:
   - Tekan `F12` untuk membuka *Developer Tools*.
   - Klik ikon *Toggle Device Toolbar* (atau tekan `Ctrl+Shift+M`).
   - Pilih perangkat simulasi seperti **iPhone 12 Pro** atau **Pixel 7**.
   - Muat ulang halaman (`F5`) agar pendeteksian `User-Agent` terbaca sebagai mobile.
2. **Bypass ke Versi Desktop**:
   - Untuk membandingkan fungsionalitas dengan versi desktop pada emulator seluler, muat halaman dengan menambahkan parameter kueri: `http://localhost:8080/transaction?preview=desktop`
3. **Uji di Ponsel Fisik**:
   - Hubungkan ponsel Anda ke jaringan Wi-Fi yang sama dengan laptop.
   - Buka alamat IP laptop Anda (misal `http://192.168.1.15:8080`).

---

## ⚠️ Pedoman Penting untuk Pengembang Masa Depan

> [!IMPORTANT]
> **ATURAN MUTLAK**: Jangan pernah mengubah logika apa pun di dalam `app/Controllers/` atau file backend lainnya saat melakukan pengembangan atau revisi antarmuka seluler.
>
> Jika Anda memerlukan data baru pada halaman seluler, tambahkan data tersebut di Controller utama sehingga data tersebut tersedia di versi desktop maupun seluler secara bersamaan.

* **Selalu Pertahankan Variabel**: Pastikan setiap berkas `.php` di dalam `Views/mobile/` mendukung dan menggunakan variabel yang dikirimkan oleh controller aslinya secara lengkap.
* **Gunakan Tailwind v4 secara Konsisten**: Gunakan kelas visual modern, gradien lembut (`bg-linear-to-tr`), blur (`backdrop-blur-md`), transisi (`transition-all duration-300`), dan state aktif (`active:scale-95`) agar antarmuka terus terasa hidup dan premium layaknya aplikasi perbankan modern.
