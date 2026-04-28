# NEXT_PROJECT.md — Roadmap Fitur Lanjutan

Dokumen ini berisi **rencana iterasi berikutnya** untuk RT/RW Net Billing.
Format setiap item: **Nama fitur · prioritas · estimasi · acceptance criteria · technical hints**.

Format prioritas: **P0** (blocker bisnis), **P1** (high value), **P2** (nice-to-have).

---

## A. Production Readiness (P0)

### A1. CI extended: matrix PHP 8.2 + 8.3, MySQL job
- **Why**: Repo support `^8.2` tapi CI cuma 8.3 + SQLite. Bug DB-spesifik tidak ketangkep.
- **Acceptance**:
  - `.github/workflows/ci.yml` punya `strategy.matrix` PHP `[8.2, 8.3]`.
  - Tambah job `mysql` (service container `mysql:8`) yang jalanin migrasi + test.
  - README di-update menyebut matrix.
- **Files**: `.github/workflows/ci.yml`.

### A2. Pre-commit hook (Pint + composer audit)
- **Why**: Mencegah PR merah karena format / dependency rentan.
- **Acceptance**:
  - Husky atau git hook native otomatis run `./vendor/bin/pint --test` sebelum commit.
  - Dokumentasi setup di README section 4.
- **Files**: `.husky/pre-commit` atau `package.json` scripts, README.

### A3. Health-check endpoint + structured logging
- **Why**: Memudahkan probe Kubernetes / load balancer.
- **Acceptance**:
  - `GET /healthz` mengembalikan `{status:"ok",db:"ok",queue:"ok"}` 200.
  - Logging pakai JSON formatter di `production`.
- **Files**: `routes/web.php`, `config/logging.php`, controller baru ringan.

### A4. Backup otomatis DB & storage
- **Why**: SaaS billing → data finansial tidak boleh hilang.
- **Acceptance**:
  - `spatie/laravel-backup` ter-install & terjadwal harian via scheduler.
  - Target S3 / disk lokal dapat di-toggle via env.
- **Files**: `config/backup.php`, `routes/console.php`.

---

## B. Fitur Bisnis (P1)

### B1. Multi-currency & i18n full
- **Why**: Saat ini hard-coded IDR + locale `id`. Operator regional ingin bahasa Inggris atau MYR.
- **Acceptance**:
  - Field `currency` di tabel `tenants` & `invoices`.
  - Helper `Money::format($amount, $currency)`.
  - File `lang/en/*.php` mirroring `lang/id/*.php`.
- **Files**: migrasi baru, `App\Helpers\Money`, view invoice.

### B2. Customer self-service portal
- **Why**: Pelanggan akhir saat ini tidak bisa login lihat tagihan sendiri.
- **Acceptance**:
  - Route group `/portal/*` dengan guard `customer`.
  - Pelanggan bisa: lihat invoice aktif, download PDF, upload bukti transfer, lihat status PPPoE.
  - Magic-link login via WhatsApp (Fonnte) — token sekali pakai 30 menit.
- **Files**: `routes/web.php`, `App\Http\Controllers\Portal\*`, guard baru di `config/auth.php`.

### B3. Bulk operations
- **Why**: Owner punya ratusan customer; CRUD satu-satu lambat.
- **Acceptance**:
  - Import customer via CSV (preview + dry-run + commit).
  - Bulk re-generate invoice untuk subset customer (filter paket / area).
  - Bulk send WA reminder.
- **Files**: `App\Http\Controllers\CustomerController` (import action), Livewire/JS table dengan checkbox.

### B4. Reporting & export
- **Why**: Owner butuh laporan keuangan bulanan untuk pajak.
- **Acceptance**:
  - Halaman `/reports` dengan filter periode & status.
  - Export Excel (`maatwebsite/excel`) & PDF.
  - Grafik: pendapatan bulanan, churn customer, aging tunggakan.
- **Files**: controller baru, view, package excel.

### B5. Payment gateway tambahan
- **Why**: Pakasir saja membatasi adopsi.
- **Acceptance**:
  - Driver pluggable: interface `PaymentGateway` di `app/Services/Payments/`.
  - Implementasi: Pakasir (existing), Midtrans, Xendit.
  - Per-tenant pilih gateway di Settings.
- **Files**: refactor `PakasirService` → `PaymentGatewayContract`, tambah Midtrans/Xendit driver, Settings UI.

### B6. PPPoE monitoring real-time
- **Why**: Saat ini MikroTik service hanya CRUD; owner tidak tahu siapa online.
- **Acceptance**:
  - Halaman `/dashboard` menampilkan jumlah PPPoE active.
  - Detail customer menampilkan IP last seen, traffic 24h.
  - Polling tiap 5 menit via job queue.
- **Files**: `App\Services\MikrotikService`, `App\Jobs\SyncMikrotikActiveSessions`.

---

## C. UX / Quality (P2)

### C1. Dark mode + responsive polish
- **Acceptance**: toggle dark mode tersimpan di local storage, semua halaman utama (dashboard, invoice, customer) lulus mobile audit Lighthouse > 90.

### C2. Audit log UI
- **Why**: Tabel `activity_logs` sudah ada (model `ActivityLog`) tapi tidak ada UI.
- **Acceptance**: halaman `/settings/activity` dengan filter user, action, period.

### C3. 2FA owner login (TOTP)
- **Acceptance**: integrasi `pragmarx/google2fa-laravel`, opsional di Settings → Security.

### C4. Rate-limit & captcha login
- **Acceptance**: `Login` attempt > 5/menit per IP → captcha (hCaptcha free tier).

### C5. Notifikasi email + WhatsApp template editor
- **Acceptance**: owner bisa edit template invoice/reminder/welcome di Settings → Templates dengan placeholder `{customer.name}`, `{invoice.amount}`.

---

## D. Tech Debt / Refactor (P2)

### D1. Coverage test naik > 60 %
- **Why**: Saat ini hanya `AuthFlowTest` + 2 example test.
- **Target**: feature test untuk `CustomerController`, `InvoiceController`, `PaymentController`, webhook Pakasir, scheduler command.

### D2. Service Pattern konsisten
- **Why**: Ada controller yang langsung query model, ada yang via service. Standardisasi.
- **Action**: pindahkan logic bisnis ke `App\Services\*`, controller hanya request → response.

### D3. Remove default Tailwind + Bootstrap dual
- **Why**: Asset bundle dobel. Pilih satu (Bootstrap, karena AdminKit). Hapus Tailwind kalau tidak terpakai.

### D4. Static analysis dengan PHPStan / Larastan level 6
- **Action**: tambah `larastan/larastan`, baseline, tambahkan ke CI.

---

## E. Saran "Next Project" Terpisah (untuk dipisah ke repo baru)

Beberapa ide bisa berdiri sendiri sebagai produk turunan dengan basis kode yang sama:

1. **`rtrw-monitoring`** — Dashboard real-time MikroTik (PPP active, traffic, alert) sebagai add-on terpisah, integrasi via API ke `rtrw-billing`.
2. **`rtrw-customer-app`** — Aplikasi mobile (Flutter / React Native) untuk pelanggan akhir: lihat tagihan, bayar, kontak teknisi.
3. **`rtrw-reseller`** — Layer reseller di atas multi-tenant: 1 reseller bisa kelola banyak owner & ambil margin otomatis dari subscription Pakasir platform.
4. **`rtrw-public-api`** — REST/GraphQL API publik (OAuth2 / Sanctum token) supaya integrator pihak ketiga (CRM, accounting) bisa baca data tenant.

---

## F. Definition of Done untuk setiap item

Sebuah item baru bisa di-merge kalau:

1. Ada **test** (unit / feature) yang menutup happy-path & 1 edge case.
2. **Pint** lulus (`./vendor/bin/pint --test`).
3. **CI** hijau di GitHub Actions.
4. **Migration** reversible (`down()` benar) bila ada perubahan schema.
5. Dokumentasi:
   - README di-update kalau ada env baru / command baru.
   - `NEXT_PROJECT.md` item dicoret (strikethrough) atau dipindah ke `CHANGELOG.md`.
6. **Backward-compatible** untuk tenant existing — kalau tidak, tulis upgrade note.

---

## G. Cara Pakai Dokumen Ini (untuk AI Agent / Devin)

1. Pilih satu item di section A → C → D.
2. Buka issue / PR baru dengan judul prefix `[<kode-item>]`, contoh: `[A1] CI matrix PHP 8.2/8.3 + MySQL`.
3. Ikuti **Definition of Done** (section F).
4. Setelah merge, update item di file ini menjadi `~~strikethrough~~` lalu pindahkan ke `CHANGELOG.md` di rilis berikutnya.
