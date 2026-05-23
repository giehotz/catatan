# Checklist Proyek Koperasi & Kotak Masuk - REVISI ULTIMATE

## 📥 Bagian 1: Penyempurnaan UI Kotak Masuk & Perbaikan Konflik CSS
- [ ] Modifikasi `resources/css/app.css` untuk menghapus `--color-base` dan memindahkannya ke `@utility` kustom guna menghindari konflik `.text-base`.
- [ ] Modifikasi `app/Views/user/inbox/index.php` untuk merombak tampilan unread/read state dengan aksen premium (border kiri, dot glowing, dynamic text colors).
- [ ] Jalankan `npm run build` untuk menguji hasil kompilasi Tailwind CSS.
- [ ] Verifikasi halaman Kotak Masuk secara visual pada mode terang & gelap.

## 🤝 Bagian 2: Fitur Pengunduran Diri Anggota Koperasi (Membership Resignation)
- [ ] Pembuatan berkas migrasi `2026_05_22_100000_CreateCooperativeResignationTable` untuk tabel `kop_pengunduran_diri` dengan status `cancelled` dan `expired`.
- [ ] Pembuatan Model `KopPengunduranDiriModel`.
- [ ] Pembuatan Spark CLI Command `CooperativeResignCleanup` untuk auto-expiry terjadwal.
- [ ] Implementasi `ResignController` (Sisi Anggota - Pengajuan, Otorisasi Pembatalan Mandiri, Riwayat Historis, & Verifikasi Publik Minimalis dengan Salt Kriptografi).
- [ ] Implementasi `ResignApprovalController` (Sisi Admin - Peninjauan, Manual DB Transaction, Pessimistic locking FOR UPDATE nomor_urut tahun kalender, & Penolakan).
- [ ] Modifikasi UI `hub.php` untuk menambahkan opsi menu pengunduran diri di bagian bawah.
- [ ] Pembuatan view formulir resign `form.php` (dilengkapi ringkasan sisa tabungan dan utang berjalan).
- [ ] Pembuatan view detail status pengajuan `status.php` (menampilkan progres pending, expired, approved, rejected, serta tabel riwayat pengajuan lama).
- [ ] Pembuatan template digital formal `letter_template.php` untuk Surat Pernyataan Pengunduran Diri Resmi terverifikasi QR Code.
- [ ] Pembuatan panel admin `resign_requests.php` dengan badge angka merah antrean pending.
