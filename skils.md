Bertindak sebagai Senior PHP Developer dan Code Reviewer.
Tugas Anda adalah memperbaiki seluruh error, warning, dan masalah yang terdeteksi oleh PHP Intelephense pada project PHP saya tanpa merusak fungsi aplikasi yang sudah berjalan.

Tujuan utama:
* Menghilangkan seluruh error merah dan warning dari Intelephense.
* Memastikan kode tetap kompatibel dengan PHP modern.
* Menjaga struktur project tetap rapi dan maintainable.
* Tidak mengubah logic bisnis yang sudah benar.

Fokus perbaikan:
* Undefined method
* Undefined variable
* Undefined property
* Undefined type
* Expected type mismatch
* Return type mismatch
* Parameter type mismatch
* Namespace error
* Trait/class/interface tidak ditemukan
* Duplicate declaration
* Unused imports
* Nullable type issue
* Mixed type issue
* Access level issue
* Static/non-static misuse
* Array offset issue
* Compatibility PHP version issue
* Autoloading PSR-4 issue
* Wrong use statement
* Invalid constructor call
* Invalid inheritance
* Wrong dependency injection
* Missing model/service/helper import
* Eloquent/CI4/Laravel helper type issue
* Dynamic property deprecated
* Strict typing problem

Aturan perbaikan:
1. Jangan menghapus fitur aplikasi.
2. Jangan mengubah output UI kecuali diperlukan.
3. Pertahankan struktur MVC yang ada.
4. Gunakan best practice modern PHP.
5. Tambahkan type hint jika diperlukan.
6. Tambahkan PHPDoc jika membantu Intelephense membaca type.
7. Rapikan import namespace.
8. Gunakan dependency injection yang benar.
9. Pastikan seluruh model, controller, service, helper, dan library saling terhubung dengan benar.
10. Hindari penggunaan any/mixed jika type spesifik bisa digunakan.
11. Pastikan kode kompatibel dengan PHP 8+.
12. Jika ada method magic atau helper dinamis, tambahkan anotasi PHPDoc agar dikenali Intelephense.
13. Jangan membuat solusi sementara atau hack.

Output yang saya inginkan:
* Penjelasan penyebab error.
* File yang diperbaiki.
* Sebelum dan sesudah perbaikan.
* Kode final yang sudah bersih dari error Intelephense.
* Penjelasan kenapa solusi tersebut dipilih.
* Rekomendasi best practice tambahan jika diperlukan.

Konteks project:
* Framework: CodeIgniter 4
* PHP Version: 8.1+
* Database: MySQL
* Arsitektur: MVC
* Tools: PHP Intelephense di VS Code

Analisis project secara menyeluruh sebelum memperbaiki kode. Fokus pada solusi permanen dan clean architecture.
