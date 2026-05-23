# Rencana Implementasi Pemisahan Frontend Desktop & Mobile (Premium v2)

Dokumen ini merupakan hasil penyempurnaan rencana kerja yang telah menggabungkan seluruh poin ulasan komprehensif Anda. Fokus utama rancangan ini adalah kestabilan, pemeliharaan tinggi, performa maksimal, dan pengalaman pengguna premium yang sangat ergonomis pada perangkat seluler.

---

## User Review Required

Berikut adalah poin-poin krusial hasil penyempurnaan yang perlu Anda ketahui:

> [!TIP]
> **1. Pendeteksian Cerdas (Static Cache & Tablet Excluded)**
> Fungsi global override `view()` akan dilengkapi dengan variabel `static` untuk melakukan caching hasil deteksi User Agent per request agar tidak membebani performa. Tablet (`isTablet()`) secara bawaan akan dikecualikan sehingga tablet tetap mendapatkan kenyamanan tampilan desktop penuh, sedangkan hanya ponsel cerdas (`isMobile()`) yang mendapatkan tampilan mobile premium ini.
> 
> **2. Scroll Lock & Accessibility pada Bottom Sheet**
> Saat Bottom Sheet "Menu Lainnya" atau Modal Transaksi Cepat (FAB) terbuka, sistem akan mengunci gulir halaman latar belakang (`overflow: hidden` pada `body`) dan menyertakan atribut aksesibilitas (`role="dialog"`, `aria-hidden`, dsb) agar berstandar premium.
> 
> **3. Penataan Ruang Konten & Karusel**
> Mengatasi penutupan konten oleh Bottom Nav dengan padding bottom minimum `80px` di layout utama. Karusel dompet/rekening akan dilengkapi dengan **CSS Scroll Snap** (`scroll-snap-type: x mandatory`) dan **indikator titik (dots)** penanda halaman dompet di bawahnya.

---

## Proposed Changes

### 1. Core System & Routing

#### [MODIFY] [Common.php](file:///c:/laragon/www/catatan/app/Common.php)
Mendefinisikan ulang fungsi global `view()` dengan pengaman `if (!function_exists('view'))` dan caching performa statis:

```php
use Config\Services;

if (!function_exists('view')) {
    /**
     * Grabs the current RendererInterface and renders the view.
     * Overridden to serve mobile-specific views automatically with dynamic fallbacks.
     */
    function view(string $name, array $data = [], array $options = []): string
    {
        static $isMobilePhone = null;

        if ($isMobilePhone === null) {
            $agent = Services::userAgent();
            // Hanya layani tampilan mobile untuk smartphone, tablet mendapatkan tampilan desktop
            $isMobilePhone = $agent->isMobile() && !$agent->isTablet();
        }

        if ($isMobilePhone) {
            $mobileView = 'mobile/' . $name;
            $viewPath = APPPATH . 'Views/' . $mobileView . '.php';
            
            if (file_exists($viewPath)) {
                $name = $mobileView;
            }
        }

        $renderer = Services::renderer();
        $saveData = $options['saveData'] ?? true;
        unset($options['saveData']);

        return $renderer->setData($data, 'raw')
            ->render($name, $options, $saveData);
    }
}
```

---

### 2. Layouts & Base Shell

#### [NEW] [mobile_base.php](file:///c:/laragon/www/catatan/app/Views/layouts/mobile_base.php)
Tata letak dasar seluler premium yang independen dari desktop layout:
- **Viewport Meta tag**: `<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">` untuk presisi rendering skala.
- **Glassmorphic Sticky Bottom Nav**: Sits fixed at the bottom (`z-50 backdrop-blur-lg bg-slate-950/80 border-t border-slate-900/80 h-16 flex items-center justify-around px-4`).
- **Linear Gradient Floating Action Button (FAB)**: Tombol aksi melayang berbentuk bulat berpijar di posisi tengah-bawah yang mengambang di atas navigasi bawah (`bg-linear-to-tr from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/20 active:scale-95 transition-all`). Mengetuk FAB akan membuka **Quick Transaction Bottom Sheet** secara instan.
- **Slide-up Bottom Sheet (Menu Lainnya & FAB Modal)**: 
  Menggunakan CSS Transitions & vanilla JS murni yang super ringan untuk meluncurkan modal dari bawah ke atas. Ketika aktif, scroll halaman di belakang dikunci secara terprogram (`document.body.classList.add('overflow-hidden')`).
- **Anti-Overlap Content Wrapper**: Mengaplikasikan `pb-24` (`padding-bottom: 96px`) pada elemen `<main>` agar tidak ada konten yang tertutup oleh bar navigasi bawah.

---

### 3. Mobile Views Translation

#### [NEW] [dashboard.php](file:///c:/laragon/www/catatan/app/Views/mobile/user/dashboard.php)
- **Snap Karusel Dompet**: Menggunakan `flex overflow-x-auto snap-x snap-mandatory gap-4 no-scrollbar scroll-smooth` dengan penanda titik di bawah karusel yang memperbarui status aktifnya lewat pemantauan sensor geser sederhana (JS IntersectionObserver/ScrollListener).
- **Indikator Transaksi Cepat**: Menampilkan statistik ringkas pemasukan/pengeluaran dalam 4 kartu grid mikro.
- **Daftar Transaksi Kompak**: Mengganti tabel horizontal desktop menjadi barisan kartu transaksi bertumpuk. Setiap kartu memiliki ikon badge kategori, detail waktu, nama dompet sumber, dan nilai transaksi yang diformat dengan warna yang kontras (hijau/merah).

#### [NEW] [index.php](file:///c:/laragon/www/catatan/app/Views/mobile/user/transactions/index.php)
- Daftar riwayat transaksi lengkap yang digulir vertikal dengan filter berupa deretan chip tombol (*scrollable pill-chips*).
- Form entri transaksi yang menggunakan inputan ukuran besar, sangat responsif, dan mudah diketuk oleh ujung jari jempol.

#### [NEW] [index.php](file:///c:/laragon/www/catatan/app/Views/mobile/user/reports/index.php)
- Konfigurasi bagan grafik ApexCharts yang dikecilkan dimensinya agar tampil sempurna di resolusi mobile (lebar 100%, tinggi 260px).
- Penempatan tombol filter rentang tanggal dan unduh PDF premium yang ringkas dan ramah sentuhan.

---

### 4. Developer Documentation

#### [NEW] [README_MOBILE.md](file:///c:/laragon/www/catatan/README_MOBILE.md)
Dokumentasi internal lengkap untuk pemeliharaan masa depan:
- Penjelasan cara kerja deteksi global `view()`.
- Petunjuk cara mem-bypass tampilan mobile untuk keperluan pengujian (misalnya menambahkan query parameter `?preview=desktop`).
- Panduan pembuatan halaman mobile baru (cukup membuat berkas baru dengan nama yang identik di dalam folder `mobile/`).

---

## Verification Plan

### Automated Tests
- Menulis skrip uji integrasi sederhana untuk menyimulasikan *request* dengan `User-Agent` Mobile (seperti iPhone) dan memeriksa apakah respon HTML mengandung elemen `mobile-base-nav` untuk memastikan sistem perutean otomatis di `Common.php` berfungsi secara presisi.

### Manual Verification
1. **Uji Lintas Perangkat**:
   - Membuka mode simulasi di Chrome Developer Tools (resolusi 360px - 414px) untuk memvalidasi interaksi Bottom Sheet, FAB, Karusel dompet dengan titik indikatornya.
   - Menguji langsung pada perangkat fisik (ponsel Android & iOS Safari/Chrome) untuk memastikan kehalusan gulir, efek sentuh active state, dan penguncian scroll latar belakang.
2. **Kesesuaian Tablet & Desktop**:
   - Memastikan saat dibuka lewat browser Tablet (iPad) atau Laptop/Desktop biasa, aplikasi tetap memuat view desktop orisinal dengan layout orisinal tanpa ada kecacatan gaya atau fungsionalitas.
3. **Validasi OPCache**:
   - Memastikan tidak terjadi galat *Cannot redeclare view()* pada server web lokal saat pemuatan berulang.
