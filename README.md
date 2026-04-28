# RT/RW Net Billing

Multi-tenant SaaS billing untuk operator ISP RT/RW Net. Tiap owner yang mendaftar mendapat dashboard billing terpisah (data pelanggan, paket, invoice, pembayaran) dengan trial **3 hari → otomatis di-suspend** kalau tidak upgrade.

> **README ini ditulis agar bisa dieksekusi langsung oleh AI agent / developer baru tanpa konteks tambahan.** Ikuti urutan section dari atas ke bawah.

---

## 1. TL;DR untuk AI Agent

```bash
# Prasyarat: PHP 8.2+ (CI pakai 8.3), Composer 2.x, Node 20+, npm 10+, SQLite3.
git clone https://github.com/dilescendol/rtrw-billing.git
cd rtrw-billing
composer install --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate
mkdir -p database && touch database/database.sqlite
php artisan migrate:fresh --seed
npm ci && npm run build
php artisan serve            # http://127.0.0.1:8000
```

Login demo: **`demo@rtrw.test` / `password`** (dibuat oleh `DemoTenantSeeder`).

Lint + test wajib lulus sebelum commit:

```bash
./vendor/bin/pint --test     # static format check (CI gating)
php artisan test             # PHPUnit / Laravel test
```

CI workflow yang harus dilewati: `.github/workflows/ci.yml` (Pint test + `php artisan test` di PHP 8.3 + SQLite).

---

## 2. Stack & Versi

| Komponen        | Versi / Catatan                                                 |
| --------------- | ---------------------------------------------------------------- |
| PHP             | `^8.2` (CI: 8.3) dengan ekstensi `mbstring, sqlite, pdo_sqlite, gd, xml, bcmath` |
| Framework       | Laravel `^11.31`                                                |
| DB              | SQLite (default lokal & CI) atau MySQL (produksi)               |
| Frontend        | Bootstrap 5 (AdminKit-style) + Tailwind 3 + Vite 6              |
| Node            | 20+ (untuk Vite build)                                          |
| PDF             | `barryvdh/laravel-dompdf ^3.1`                                  |
| MikroTik        | `evilfreelancer/routeros-api-php ^1.6`                          |
| Payment gateway | Pakasir (HTTP API via Guzzle)                                   |
| WhatsApp        | Fonnte (HTTP API)                                               |
| Linter          | Laravel Pint (`./vendor/bin/pint`)                              |
| Test            | PHPUnit 11                                                       |

---

## 3. Fitur Utama

- **Public**: landing page (12 fitur produk + roadmap), pricing, login, register, lupa password.
- **Role / RBAC** (lihat konstanta di `App\Models\User`):
  - `superadmin` — operator platform, lihat semua tenant + paket di `/superadmin`.
  - `admin` / `owner` — admin tenant (pemilik bisnis RT/RW Net), akses penuh dashboard.
  - `teknisi` — staf teknis: lihat dashboard, pelanggan, invoice; tidak bisa ubah Settings / Subscription.
  - `kolektor` — staf penagihan: sama dengan teknisi, fokus ke invoice & payments.
  - `customer` — pelanggan akhir, login ke `/portal`. Diproteksi middleware alias `customer.portal`.
- **Owner Dashboard**:
  - Manajemen pelanggan (CRUD, status `aktif`/`diisolir`/`berhenti`, kredensial PPPoE).
  - Manajemen paket internet (harga, kecepatan, MikroTik profile name).
  - Invoice otomatis bulanan (scheduler) + manual.
  - PDF invoice via DomPDF.
  - Pembayaran via **Pakasir** (auto, webhook callback) atau **manual transfer** + upload bukti.
  - Statistik: total pelanggan, pendapatan bulanan, tunggakan.
- **Customer Portal (`/portal`)**: pelanggan login mandiri, lihat tagihan, riwayat pembayaran, dan ubah profil/password.
- **Super Admin Console (`/superadmin`)**: read-only listing tenant (statistik trial / active / suspended) dan paket subscription.
- **NAS / Router catalog (`/nas`)**: 1 tenant bisa punya banyak router MikroTik. CRUD + tes koneksi, password disimpan terenkripsi. Dibatasi `Plan.max_nas`.
- **Hotspot users (`/hotspot`)**: CRUD user hotspot (mac, profile, expired). Saat NAS di-set dan status aktif, push otomatis ke `/ip/hotspot/user`. Dibatasi `Plan.allow_hotspot`.
- **Voucher hotspot (`/vouchers`)**: generate batch (mass-create dengan kode unik 5–16 char), filter per status (`available`/`sold`/`used`/`expired`), halaman print friendly. Dibatasi `Plan.allow_voucher`.
- **Notifikasi in-app**: bell di navbar (per-user), basis tabel `notifications` Laravel + `App\Notifications\GenericNotification`. Mark-as-read individual atau semua.
- **Mode terang / gelap**: toggle di navbar, persist via cookie `theme=light|dark`. Memakai atribut `data-bs-theme` Bootstrap 5.3 + override custom di `public/css/adminkit.css`.
- **Multi-tenant**: data pelanggan/invoice/pembayaran terisolasi otomatis lewat global scope `App\Scopes\TenantScope`. Setiap query model ber-tenant otomatis di-filter `tenant_id = auth()->user()->tenant_id`.
- **Trial 3 hari → SUSPEND**: setelah trial habis, akun otomatis di-`suspend` (bukan auto-charge). Owner harus upgrade manual via halaman `subscription.plans`.
- **Pakasir 2 scope**:
  - **Platform-level** (`PAKASIR_PLATFORM_*` di `.env`) — untuk subscription owner ke platform kami.
  - **Per-tenant** (di Settings, dienkripsi di DB pakai cast `encrypted`) — untuk billing pelanggan langsung ke rekening Pakasir owner. **Tidak** masuk laporan keuangan platform.
- **MikroTik PPPoE**: auto-create PPP secret saat customer dibuat, auto-isolir saat status diubah ke `diisolir`/`berhenti`.
- **RADIUS / FreeRADIUS (`/radius`)**: 1 tenant bisa punya banyak server. Konfigur host + secret + SQL backend (rlm_sql); customer otomatis di-push ke `radcheck`/`radreply`/`radusergroup`/`radgroupreply` saat create/update; dihapus saat customer dihapus. Dibatasi `Plan.allow_radius`.
- **GenieACS / TR-069 (`/genieacs`)**: konfigur NBI URL + Basic Auth, list devices, halaman detail, tombol refresh & reboot. Dibatasi `Plan.allow_genieacs`.
- **WhatsApp auto-billing (`/whatsapp/templates`, `/whatsapp/logs`)**: template per event (`invoice_created`, `invoice_due_soon`, `invoice_overdue`, `payment_received`, `customer_isolated`, `voucher_created`) dengan placeholder `@{{nama}}` dst. Pesan otomatis dikirim saat invoice dibuat & saat pembayaran diterima. Command `invoice:remind` (scheduler harian 09:00) mengirim pengingat H-3 / H-1 dan H+1 / H+3 / H+7. Semua percobaan tercatat di `whatsapp_logs`. Dibatasi `Plan.allow_whatsapp`.
- **Notifikasi WhatsApp** via Fonnte (per-tenant token).
- **Anti-abuse register**: cooldown email 30 hari + rate-limit IP (3/hari) + device fingerprint (2/hari) — lihat `App\Services\AntiAbuse`.

> Roadmap (akan menyusul di PR berikutnya): monitoring on/off perangkat realtime,
> remote IP customer, WhatsApp auto-billing reminder, dan chat realtime.

---

## 4. Setup Lokal — Step by Step

### 4.1 Prasyarat

```bash
php -v       # >= 8.2
composer -V  # >= 2.5
node -v      # >= 20
npm -v       # >= 10
sqlite3 -version
```

Install ekstensi PHP yang dibutuhkan (Ubuntu/Debian):

```bash
sudo apt-get install -y php-cli php-mbstring php-xml php-sqlite3 php-bcmath php-gd php-curl unzip
```

### 4.2 Clone & install

```bash
git clone https://github.com/dilescendol/rtrw-billing.git
cd rtrw-billing
composer install --no-interaction --prefer-dist --no-progress
npm ci
```

### 4.3 Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Variabel **wajib** untuk operasional dasar (sudah ada di `.env.example`):

```env
APP_NAME="RT/RW Net Billing"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost
DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
TRIAL_DAYS=3
```

Variabel **opsional** (boleh kosong di lokal, isi sebelum produksi):

```env
PAKASIR_BASE_URL=https://pakasir.zone.id
PAKASIR_PLATFORM_PROJECT=
PAKASIR_PLATFORM_API_KEY=
PAKASIR_PLATFORM_SIGNATURE=
```

> Kredensial **per-tenant** (Pakasir/MikroTik/Fonnte) **tidak** ditaruh di `.env`. Tiap owner mengatur lewat halaman `Settings` setelah login (di-encrypt di DB).

### 4.4 Database

Default: SQLite (paling cepat untuk dev & CI).

```bash
mkdir -p database
touch database/database.sqlite
php artisan migrate:fresh --seed
```

Mau MySQL? Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rtrw_billing
DB_USERNAME=root
DB_PASSWORD=secret
```

Lalu jalankan `php artisan migrate:fresh --seed`.

### 4.5 Build asset & jalankan

Mode dev (concurrent server + queue + log + Vite):

```bash
composer run dev
# alias dari: php artisan serve & php artisan queue:listen & php artisan pail & npm run dev
```

Mode satu-perintah (asset di-build sekali, server jalan):

```bash
npm run build
php artisan serve
```

Buka <http://127.0.0.1:8000>.

### 4.6 Akun demo

Seeder `Database\Seeders\DemoTenantSeeder` membuat 1 super admin platform, 1 tenant demo lengkap dengan owner / teknisi / kolektor / customer-portal user, 3 paket internet, 8 pelanggan, dan dua bulan invoice contoh.

Semua password: `password`.

| Email | Role | Login redirect |
| --- | --- | --- |
| `super@rtrw.test` | `superadmin` | `/superadmin/tenants` |
| `demo@rtrw.test` | `admin` (owner tenant demo) | `/dashboard` |
| `teknisi@rtrw.test` | `teknisi` | `/dashboard` (tanpa menu Settings / Subscription) |
| `kolektor@rtrw.test` | `kolektor` | `/dashboard` (tanpa menu Settings / Subscription) |
| `pelanggan@rtrw.test` | `customer` (terhubung ke Pelanggan 1) | `/portal` |

---

## 5. Struktur Direktori (yang relevan)

```
app/
├── Console/Commands/
│   ├── AutoSuspendExpired.php       # `billing:auto-suspend`
│   └── GenerateMonthlyInvoices.php  # `billing:generate-monthly`
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                    # login/register/email-verify/password-reset
│   │   ├── Webhooks/PakasirWebhookController.php
│   │   ├── CustomerController.php
│   │   ├── DashboardController.php
│   │   ├── InvoiceController.php
│   │   ├── PackageController.php
│   │   ├── PaymentController.php
│   │   ├── SettingsController.php
│   │   └── SubscriptionController.php
│   └── Middleware/EnsureTenantUsable.php   # alias: `tenant.usable`
├── Models/                          # Tenant, User, Customer, Package, Invoice, Payment, Plan, …
├── Scopes/TenantScope.php           # global scope untuk multi-tenancy
└── Services/
    ├── AntiAbuse.php                # cooldown register, rate-limit, fingerprint
    ├── FonnteService.php            # WhatsApp
    ├── InvoiceGenerator.php         # bulk generate invoice bulanan
    ├── MikrotikService.php          # PPP secret CRUD, isolir
    └── PakasirService.php           # invoice & callback signature
database/
├── migrations/                      # urut by tanggal
└── seeders/{DatabaseSeeder,DemoTenantSeeder,PlanSeeder}.php
resources/views/                     # Blade: dashboard, customers, invoices, payments, settings, …
routes/
├── web.php                          # semua route HTTP (lihat section 6)
└── console.php                      # schedule
tests/
├── Feature/AuthFlowTest.php
└── Unit/ExampleTest.php
.github/workflows/ci.yml             # pipeline lint + test
```

---

## 6. Routing — Ringkasan

Group route di `routes/web.php`:

1. **Public** — `GET /`, `GET /pricing`.
2. **Guest auth** (`middleware: guest`) — login, register, forgot password, reset password.
3. **Email verification** (`middleware: auth`) — `verification.notice|verify|send`.
4. **Subscription** (`middleware: auth`, **tanpa** `tenant.usable`) — halaman suspended, plans, checkout, return.
5. **App** (`middleware: auth + verified + tenant.usable`):
   - `dashboard`
   - `customers` (resource), `packages` (resource kecuali show)
   - `invoices`: index/create/store/generate-month/show/pdf/pay/destroy
   - `payments`: index/create/store/verify/reject
   - `settings.*`: index, business, pakasir, mikrotik, whatsapp
6. **Webhook** — `POST /webhooks/pakasir/{tenant}` (tanpa CSRF, signature dicek di controller).

---

## 7. Scheduler / Cron

Tambahkan ke crontab server produksi:

```cron
* * * * * cd /path/to/rtrw-billing && php artisan schedule:run >> /dev/null 2>&1
```

Yang otomatis dijalankan (`routes/console.php`):

| Command                       | Jadwal                  | Fungsi                                                                |
| ----------------------------- | ----------------------- | --------------------------------------------------------------------- |
| `billing:auto-suspend`        | hourly                  | Suspend trial/paid expired + tandai invoice overdue.                  |
| `billing:generate-monthly`    | 1st of month, 02:00     | Generate invoice bulanan untuk semua pelanggan aktif tiap tenant.     |

Manual run untuk testing:

```bash
php artisan billing:auto-suspend
php artisan billing:generate-monthly
```

---

## 8. Konfigurasi Integrasi

### 8.1 Pakasir

- **Platform** (operator SaaS): isi `PAKASIR_PLATFORM_PROJECT`, `PAKASIR_PLATFORM_API_KEY`, `PAKASIR_PLATFORM_SIGNATURE` di `.env`.
- **Owner** (RT/RW Net): masuk ke `Settings → tab Pakasir`, isi project + API key + signature milik akun Pakasir owner. Webhook URL tampil di halaman tersebut (`/webhooks/pakasir/{tenant}`).

### 8.2 MikroTik

`Settings → tab MikroTik`: host, port API (8728), user, password. Disimpan dengan cast `encrypted`. Service `App\Services\MikrotikService` digunakan saat:
- Customer dibuat → auto-create PPP secret.
- Status customer berubah → auto-isolir / aktifkan kembali.

### 8.3 Fonnte (WhatsApp)

`Settings → tab WhatsApp`: token Fonnte per tenant. Notifikasi pelanggan dikirim via `App\Services\FonnteService`.

---

## 9. Testing & Lint

Lokal:

```bash
./vendor/bin/pint           # auto-format
./vendor/bin/pint --test    # cek tanpa modifikasi (CI gating)
php artisan test            # PHPUnit
php artisan test --filter=AuthFlowTest
```

CI (GitHub Actions, `.github/workflows/ci.yml`) — wajib hijau sebelum merge:

1. Setup PHP 8.3 + ekstensi.
2. `composer install`.
3. `cp .env.example .env && php artisan key:generate`.
4. `touch database/database.sqlite && php artisan migrate --force`.
5. `./vendor/bin/pint --test`.
6. `php artisan test`.

---

## 10. Konvensi Kode

- **Lint**: Laravel Pint (preset Laravel default). Selalu jalankan `./vendor/bin/pint` sebelum commit.
- **Multi-tenant**: model ber-tenant **wajib** memakai trait/scope `TenantScope` (lihat `app/Scopes/TenantScope.php` & `app/Models/Concerns/`). Jangan query langsung tanpa scope kecuali super admin / cron.
- **Encrypted credentials**: cast `'encrypted'` di model untuk field credential per-tenant (Pakasir, MikroTik, Fonnte).
- **Form Request**: validasi form pakai Form Request bila non-trivial (lihat controller existing untuk pola).
- **Blade**: layout utama `resources/views/layouts/`, AdminKit-style.
- **Bahasa**: copy/UI default Bahasa Indonesia (`APP_LOCALE=id`); pesan error/log boleh English.

---

## 11. Deployment (ringkas)

1. Server: Ubuntu 22.04+, PHP 8.3 + ekstensi seperti CI, MySQL 8 (atau Postgres), Nginx, supervisor.
2. `composer install --no-dev --optimize-autoloader`.
3. Set `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, DB MySQL, Pakasir platform key).
4. `php artisan migrate --force`.
5. `npm ci && npm run build`.
6. `php artisan config:cache route:cache view:cache`.
7. Crontab: `* * * * * php artisan schedule:run`.
8. Queue worker (supervisor): `php artisan queue:work --tries=3 --sleep=3`.
9. Webhook Pakasir harus reachable publik, signature diverifikasi di `PakasirWebhookController`.

---

## 12. Troubleshooting

| Gejala                                                       | Solusi                                                                                       |
| ------------------------------------------------------------ | -------------------------------------------------------------------------------------------- |
| `SQLSTATE… unable to open database file`                     | `mkdir -p database && touch database/database.sqlite` lalu `php artisan migrate:fresh`.      |
| `Class "Redis" not found`                                    | `.env`: pastikan `CACHE_STORE=database` & `SESSION_DRIVER=database` (default).               |
| Vite tidak load                                              | Jalankan `npm run dev` (mode HMR) atau `npm run build` (produksi).                           |
| Login bilang akun "suspended"                                | Trial habis. Jalankan `php artisan tinker` lalu reset `tenants.status = 'active'` & `trial_ends_at` di masa depan. |
| Webhook Pakasir ditolak                                      | Cek `PAKASIR_*_SIGNATURE` cocok, payload `application/json`, dan webhook URL pakai HTTPS publik. |
| Test gagal di CI tapi lulus lokal                            | CI selalu pakai SQLite + PHP 8.3. Reproduksi: `DB_CONNECTION=sqlite php artisan test`.       |

---

## 13. Roadmap

Lihat **[`NEXT_PROJECT.md`](NEXT_PROJECT.md)** untuk daftar fitur lanjutan & prioritasnya.

---

## 14. License

MIT.
