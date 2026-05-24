# deprecated/

Berisi file yang tidak lagi digunakan tapi disimpan sebagai referensi.

## agent.php
- **Purpose:** Debug tool untuk menampilkan User-Agent dan deteksi mobile
- **Alasan di-archive:** Kode broken (undefined variables `$userAgent`, `$isMobile`, `$mobile`;
  unconditional `header("Location: /")` di baris 2 membuat semua echo tidak pernah dieksekusi)
- **Tidak ada reference** dari file lain di project
- **Di-archive tanggal:** 2026-05-24
