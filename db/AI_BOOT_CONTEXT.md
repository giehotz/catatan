# 🚀 AI Boot Context — Catatan Keuangan + Koperasi

> Berikan file ini sebagai system prompt / context awal kepada AI baru.

## Quick Start

Kamu sedang mengerjakan proyek **Catatan Keuangan** — aplikasi pencatatan keuangan pribadi + modul **Koperasi Simpan Pinjam (KSP)** berbasis CodeIgniter 4.

### Tech Stack
| Layer     | Technology                       |
|-----------|----------------------------------|
| Backend   | CodeIgniter 4.6.5 + PHP 8.1     |
| Auth      | CodeIgniter Shield v1.3          |
| Database  | MySQL                            |
| Frontend  | TailwindCSS v4.3 (CLI)          |
| JS        | Vanilla JavaScript (inline)      |
| PDF       | DomPDF v3.1                      |
| Excel     | PhpSpreadsheet v5.7              |

### Entry Points
- **Routes:** `app/Config/Routes.php` — baca ini PERTAMA
- **Filters:** `app/Config/Filters.php` — pahami auth gates
- **Schema:** `app/Database/Migrations/` — 18 migration files

### Roles
| Role        | Dashboard                 | Can Access                        |
|-------------|---------------------------|-----------------------------------|
| user        | `/` (Home::index)         | All `/user` modules               |
| manager     | `/` + `/admin/cooperative`| User modules + coop management    |
| admin       | `/admin` + all            | Everything + settings             |
| superadmin  | `/admin` + all            | Everything + role assignment      |

### Critical Files (Read Before Any Change)
1. `app/Config/Routes.php` — route → controller mapping
2. `app/Config/Filters.php` — auth filter → URI binding
3. `app/Controllers/CooperativeAdmin.php` — largest controller (50KB+)
4. `app/Models/KopPinjamanModel.php` — loan calculation engine
5. `app/Models/KopSettingModel.php` — system settings (static helpers)
6. `app/Views/layouts/` — 4 base layouts (NEVER break these)

### How to Build CSS
```bash
cmd /c "npm run build"
# PowerShell memblokir npm langsung, harus via cmd
```

### How to Run Migrations
```bash
php spark migrate
```

### How to Run Dev Server
```bash
php spark serve --port=8080
```

## What NOT to Do
- ❌ Don't rewrite architecture
- ❌ Don't create new service layer (project doesn't use one)
- ❌ Don't use Alpine/Vue/React (project uses vanilla JS)
- ❌ Don't add tailwind.config.js (v4 uses CSS-based config)
- ❌ Don't hardcode financial values (use kop_settings)
- ❌ Don't skip migrations for schema changes
- ❌ Don't put business logic in controllers
