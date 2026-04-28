# RT/RW Net Billing

Multi-tenant SaaS billing untuk operator ISP RT/RW Net. Tiap owner yang mendaftar mendapat dashboard billing terpisah (data pelanggan, paket, invoice, pembayaran) dengan trial 3 hari → otomatis di-suspend kalau tidak upgrade.

Stack: **Laravel 11 · Bootstrap 5 (AdminKit-style) · SQLite/MySQL · Pakasir · MikroTik RouterOS API · Fonnte (WhatsApp) · DomPDF.**

## Fitur Utama

- **Public**: landing page (12 fitur produk + roadmap), login, register, lupa password.
- **Role / RBAC**:
  - `superadmin` — operator platform, lihat semua tenant + paket.
  - `admin` / `owner` — admin tenant (pemilik bisnis RT/RW Net), akses penuh dashboard.
  - `teknisi` — staf teknis: lihat dashboard, pelanggan, invoice; tidak bisa ubah Settings/Subscription.
  - `kolektor` — staf penagihan: sama dengan teknisi, fokus ke invoice & payments.
  - `customer` — pelanggan akhir, login ke `/portal` (panel pelanggan).
- **Owner Dashboard**:
  - Manajemen pelanggan (CRUD, status aktif/diisolir/berhenti, PPPoE creds).
  - Manajemen paket internet (harga, kecepatan, MikroTik profile).
  - Invoice otomatis bulanan (scheduler) + manual.
  - PDF invoice via DomPDF.
  - Pembayaran via Pakasir (auto, webhook callback) atau manual transfer + upload bukti.
  - Statistik: pelanggan, pendapatan, tunggakan.
- **Customer Portal (`/portal`)**: pelanggan login dan melihat tagihan, riwayat pembayaran, dan profil akun.
- **Super Admin Console (`/superadmin`)**: read-only listing tenant (statistik trial/active/suspended) dan paket subscription.
- **Notifikasi in-app**: bell di navbar (per-user), basis tabel `notifications` Laravel + `App\Notifications\GenericNotification`. Bisa mark-as-read individual atau semua.
- **Mode terang / gelap**: toggle di navbar, persist via cookie `theme=light|dark`. Memakai `data-bs-theme` Bootstrap 5.3 + custom override di `public/css/adminkit.css`.
- **Multi-tenant**: data pelanggan/invoice/pembayaran terisolasi otomatis pakai global scope `TenantScope`.
- **Trial 3 hari → SUSPEND**: setelah trial habis, akun otomatis di-suspend (bukan auto-charge). Owner harus upgrade manual.
- **Pakasir 2 scope**:
  - **Platform-level** (`PAKASIR_PLATFORM_*` di `.env`) — untuk subscription owner ke platform kami.
  - **Per-tenant** (di Settings, dienkripsi di DB) — untuk billing pelanggan langsung ke rekening Pakasir owner. Tidak masuk laporan keuangan platform.
- **MikroTik PPPoE**: auto-create user saat customer dibuat, auto-isolir saat status diubah.
- **Notifikasi WhatsApp** via Fonnte (per-tenant token).
- **Anti-abuse register**: cooldown email 30 hari + rate-limit IP (3/hari) + device fingerprint (2/hari).

> Roadmap (akan menyusul di PR berikutnya): NAS/Hotspot/Voucher penuh, RADIUS + GenieACS, monitoring Mikrotik on/off,
> remote IP customer, dan chat realtime.

## Quick Start

```bash
git clone <this-repo>
cd rtrw-billing
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Lalu buka http://127.0.0.1:8000.

**Akun demo (dari seeder)** — semua password: `password`

| Email | Role | Login ke |
| --- | --- | --- |
| `super@rtrw.test` | `superadmin` | `/superadmin/tenants` |
| `demo@rtrw.test` | `admin` (owner tenant demo) | `/dashboard` |
| `teknisi@rtrw.test` | `teknisi` | `/dashboard` (tanpa menu Settings) |
| `kolektor@rtrw.test` | `kolektor` | `/dashboard` (tanpa menu Settings) |
| `pelanggan@rtrw.test` | `customer` (terhubung ke Pelanggan 1) | `/portal` |

## Scheduler

Tambahkan ke crontab server:

```cron
* * * * * cd /path/to/rtrw-billing && php artisan schedule:run >> /dev/null 2>&1
```

Yang dijalankan:

- `billing:auto-suspend` (hourly) → suspend trial/paid expired + tandai overdue invoice.
- `billing:generate-monthly` (1st of month, 02:00) → buat invoice bulanan untuk semua pelanggan aktif tiap tenant.

## Konfigurasi Pakasir

1. **Platform** (operator SaaS): isi `PAKASIR_PLATFORM_PROJECT`, `PAKASIR_PLATFORM_API_KEY`, `PAKASIR_PLATFORM_SIGNATURE` di `.env`.
2. **Owner** (RT/RW Net): masuk ke Settings → tab Pakasir, isi project + API key + signature milik akun Pakasir owner. Webhook URL ditampilkan di halaman tersebut.

## Konfigurasi MikroTik & Fonnte

Settings → tab MikroTik / WhatsApp. Kredensial dienkripsi otomatis di DB (Laravel `encrypted` cast).

## Tests / Lint

```bash
./vendor/bin/pint        # auto-format
./vendor/bin/pint --test # check
php artisan test
```

## License

MIT.
