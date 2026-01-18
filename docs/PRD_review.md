# PRD Review — Website Pengadilan Agama Penajam (Laravel 12 + Inertia v2 + Filament v5)

Tanggal review: 2026-01-18  
Referensi: `docs/PRD.md`

## 1) Ringkasan Eksekutif
Secara **dokumen PRD** dan **fondasi skema database (migrations)**, proyek sudah mencakup modul inti (Pages, Menus, News, Documents, PPID, Transparency, SIPP). Namun, secara **implementasi yang bisa dijalankan end-to-end**, repo saat ini memiliki **blocker besar**: banyak kelas inti berada di folder yang salah (di luar `app/`), menyebabkan runtime error, dan ada bug fatal pada `User::can()` yang membuat **test suite tidak bisa jalan**.

Kesimpulan status: **prototype + fondasi skema**, dengan beberapa implementasi “ada” tetapi **tidak terhubung** ke aplikasi yang berjalan.

---

## 2) Temuan Kritis (Blocker) — Prioritas P0

### P0.1 “Shadow code” di folder `home/` (tidak ter-autoload)
Ada banyak kode inti (Models/Controllers/Services) di:
- `home/moohard/dev/work/Models/*`
- `home/moohard/dev/work/web-papenajam/Http/Controllers/Api/Sipp/*`
- `home/moohard/dev/work/web-papenajam/Services/Sipp/*`
- `home/moohard/dev/work/web-papenajam/Models/Sipp/*`

Dampak:
- Kelas yang dirujuk `routes/*` dan Filament tidak ditemukan di runtime karena Composer PSR-4 hanya memetakan `App\\` ke `app/`.
- Contoh nyata: `php artisan route:list` gagal karena controller tidak ada.

### P0.2 Test suite (Pest) gagal total karena konflik signature `User::can()`
Hasil run lokal:
- `./vendor/bin/pest --compact` gagal.
- Akar masalah: `app/Traits/HasPermissions.php` mendefinisikan `can(string $action, string $resource): bool` yang **tidak kompatibel** dengan `Illuminate\Foundation\Auth\User::can($abilities, $arguments = [])`.

Dampak:
- Semua test & CI tests gagal sejak bootstrap.

### P0.3 `php artisan route:list` gagal (controller SIPP tidak ditemukan)
Hasil run lokal:
- `php artisan route:list` gagal dengan error `Class "App\Http\Controllers\Api\Sipp\SippScheduleController" does not exist`.

Akar masalah:
- `routes/api.php` merujuk controller di namespace `App\Http\Controllers\Api\Sipp\...` tetapi file controller yang sesuai berada di folder `home/...`, bukan `app/Http/Controllers/...`.

### P0.4 Frontend TypeScript & ESLint gagal
Hasil run lokal:
- `npm run types` gagal: `resources/js/pages/news-detail.tsx` → `Cannot find name 'WhatsApp'`.
- `npm run lint` gagal: banyak `no-unused-vars` (contoh `footer.tsx`, `header.tsx`, `main-layout.tsx`, `documents.tsx`, `news.tsx`, `pages/[slug].tsx`, `schedules.tsx`).

### P0.5 CI workflow lint kemungkinan rusak
- `.github/workflows/lint.yml` memakai `actions/checkout@v5` (umumnya versi stabil/valid adalah `@v4`).

---

## 3) Gap Analysis terhadap PRD (per Functional Requirement)

### FR-01: Page Builder System
Status: **Belum terimplementasi (baru fondasi skema)**
- Migrations `pages`, `page_blocks`, `page_templates` ada.
- Tetapi belum ada:
  - UI drag-and-drop builder
  - Template save/load yang usable di admin
  - Real-time preview builder
  - Versioning & revision history (FR-01.06)
- Catatan: `PageResource` saat ini menggunakan `RichEditor` untuk `content`, sementara kolom `content` di DB adalah JSON → indikasi masih placeholder.

### FR-02: Dynamic Menu Management
Status: **Partial (DB ada, render & rules belum)**
- Migrations `menus` & `menu_items` ada.
- Frontend masih hard-coded (`resources/js/components/header.tsx` dan `footer.tsx`) → belum mengambil menu dari DB.
- Conditional display rules (FR-02.03) belum ada evaluator/penerapannya.
- Mismatch dengan PRD:
  - PRD menuliskan `route_name`, `custom_url`, dan pemisahan `url_type`
  - Schema sekarang memakai `type` + `url` + `page_id` (belum ada `route_name`).

### FR-03: Content Management (News/Documents/Schedule/Transparency/PPID)
Status: **Partial**
- Migrations untuk News/Documents/PPID/Budget/CaseStatistics ada.
- Namun public pages masih mock data:
  - `resources/js/pages/news.tsx`, `news-detail.tsx`, `documents.tsx`, `schedules.tsx`, `pages/[slug].tsx`.
- Belum terlihat Filament Resources untuk News/Documents/PPID/Transparency/CaseStatistics (baru Page/Menu/User/JoomlaMigration, itupun masih banyak mismatch).
- Document version control (FR-03.02) belum ada di schema/logic.

### FR-04: Admin Panel (Filament)
Status: **Partial / rawan runtime error**
- Filament panel provider ada: `app/Filament/AdminPanelProvider.php`.
- Tetapi ada indikasi resource/model belum lengkap/nyambung:
  - `CategoryResource` direferensikan tetapi file `app/Filament/Resources/CategoryResource.php` tidak ada.
  - Beberapa resource merujuk model yang tidak tersedia di `app/Models` (karena ada di `home/...`).
  - Ada indikasi penggunaan API Filament yang salah (contoh `Section::name(...)`).

### FR-05: API Integration (SIPP)
Status: **Partial (design + kode ada, wiring gagal)**
- Config `config/sipp.php` ada.
- Migrations SIPP ada: `sipp_cases`, `sipp_*`, `sipp_sync_logs`.
- Routes API `routes/api.php` ada.
- Namun implementasi controller/service SIPP yang lebih lengkap ada di `home/...` sehingga runtime gagal.
- Mismatch kontrak endpoint:
  - Dokumen desain mengarah ke `/api/web/...`
  - Client di code menggunakan endpoint seperti `/schedules`, `/cases`, `/statistics`.
  - Perlu finalisasi kontrak API.

### FR-06: User Management & Security
Status: **Partial (Fortify OK, roles/permissions bermasalah)**
- Fortify + inertia views sudah ada (`app/Providers/FortifyServiceProvider.php`).
- Kolom role/permissions/2FA ada di schema.
- Namun implementasi role/permission bermasalah:
  - Override `can()` mematikan test suite.
  - `app/Traits/HasRoles.php` tidak konsisten dengan enum cast `role` pada `app/Models/User.php`.
  - Policies memanggil method yang tidak ada/berbeda (contoh `canEditOwn`).

---

## 4) Review Arsitektur & Struktur Repo

### 4.1 Duplikasi / “mirror” implementasi
Ada dua “versi aplikasi”:
- Versi yang dipakai runtime (harusnya) di `app/...`
- Versi lebih lengkap di `home/...` tetapi tidak dipakai oleh autoload

Rekomendasi P0:
- Jadikan `app/` satu-satunya sumber kode aplikasi, dan bersihkan/migrasikan/hapus folder `home/...` dari repo.

### 4.2 Konsistensi schema vs PRD
Ada beberapa kolom/entitas di PRD yang belum tercermin di migrations/logika (contoh: `categories.type`, `menu_items.route_name`, SIPP schedule revised fields, document versioning). Ini perlu diputuskan:
- Apakah PRD adalah kontrak final, atau PRD masih high-level dan schema sekarang adalah MVP.

---

## 5) Review Frontend (Inertia + React)

### 5.1 Banyak halaman masih mock
Sebagian besar halaman public masih menggunakan data statis/mock, belum mengambil dari DB via controller/props Inertia.

### 5.2 Navigasi hard-coded & banyak route tidak tersedia
Header/Footer punya link yang tidak ada di `routes/web.php` (contoh `/services`, `/announcements`, dll).

### 5.3 Sanitasi HTML manual (regex) berisiko
Beberapa page punya fungsi `sanitizeHtml` berbasis regex yang tidak robust untuk XSS/HTML sanitization.

---

## 6) Testing, CI, Quality

### 6.1 Status saat ini (hasil run lokal)
- `./vendor/bin/pest --compact` ❌ (fatal signature `User::can`)
- `php artisan route:list` ❌ (controller SIPP tidak ditemukan)
- `npm run types` ❌ (missing `WhatsApp`)
- `npm run lint` ❌ (unused vars/imports)
- `.github/workflows/lint.yml` berpotensi ❌ (`actions/checkout@v5`)

### 6.2 Rekomendasi baseline “CI hijau”
Jadikan definisi siap-kembang:
- [ ] `php artisan route:list` sukses
- [ ] `./vendor/bin/pest --compact` sukses
- [ ] `npm run types` sukses
- [ ] `npm run lint` sukses
- [ ] Filament `/admin` minimal User/Page/Menu CRUD bisa dibuka
- [ ] Tidak ada “shadow code” di luar `app/` yang mempengaruhi runtime

---

## 7) Rekomendasi Perbaikan (Prioritas & Urutan)

### P0 (harus beres dulu; target 1–2 hari)
1. Rapikan struktur repo: migrasikan kode inti dari `home/...` ke `app/...` atau hapus arsipnya.
2. Perbaiki roles/permissions:
   - Jangan override `User::can()` dengan signature berbeda.
   - Selaraskan `HasRoles` dengan enum cast `role`.
3. Perbaiki routes: pastikan semua controller yang dirujuk benar-benar ada di `app/Http/Controllers/...`.
4. Bereskan TypeScript + ESLint:
   - Fix `WhatsApp` dan semua unused imports/vars sampai `npm run types` & `npm run lint` hijau.
5. Perbaiki workflow lint CI (checkout action).

### P1 (fondasi fitur PRD; target 1–2 minggu)
1. Lengkapi domain layer:
   - Models + relationships + casts untuk Page/Menu/News/Document/PPID/Transparency/SIPP.
   - Tambahkan factories minimal untuk model inti (Pest).
2. Filament:
   - Lengkapi/memperbaiki resource yang missing (CategoryResource dkk).
   - Tambahkan resources untuk News/Documents/PPID/Transparency/CaseStatistics.
3. Joomla migration:
   - Pastikan jalur import/rollback/validate berjalan end-to-end.

### P2 (fitur utama PRD; target 2–6 minggu)
1. Page Builder:
   - Putuskan “source of truth”: `pages.content` JSON builder vs `page_blocks`.
   - Implement builder UI + renderer + preview + revisioning.
2. Dynamic Menu:
   - Render menu dari DB (Inertia shared props atau endpoint).
   - Implement conditional display rules.
3. SIPP:
   - Finalisasi kontrak endpoint sesuai desain.
   - Wiring sync schedule + monitoring + notifikasi.

