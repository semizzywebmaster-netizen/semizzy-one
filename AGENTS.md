# AGENTS.md — SEMIZZY ONE CORE

**Single source of truth for all AI coding agents working on this repository.**

Read this file completely before writing any code. Where this file and any
other instruction disagree, **this file wins**.

Product: SEMIZZY ONE CORE · Parent company: SEMIZZY WEBMASTER
Tagline: Everything You Need. One Platform.
Core version: **2.0.0** · License: none — this software has no license system.

---

## 0. The one-paragraph brief

SEMIZZY ONE CORE is a single integrated Laravel 13 + React 19 application
providing a platform *foundation*: authentication, users, RBAC, admin, settings,
security, notifications, storage/queue/cache/cron abstraction, an API
foundation, a provider framework, an addon engine, PWA/offline/sync, audit
logging, system health, backups, an installation wizard, and tests.

**It contains no business features.** No VTU, wallet, transfers, payments, KYC,
savings, loans, investments, insurance, gift cards, virtual cards, marketplace,
escrow, P2P, crypto, betting, POS, merchant, agent, reseller, travel,
advertising, affiliate, or WhatsApp transaction services. Those arrive later as
independent addons.

---

## 1. Architecture rules

- **One integrated application.** Never create a `frontend/` or `backend/`
  directory. Never split the app into separately deployed halves.
- React + TypeScript live **inside** Laravel at `resources/js/`.
- Vite compiles the frontend; Laravel serves the production app.
- There is exactly **one production deployment**.
- Web server document root is `public/`. Nothing outside `public/` is reachable.
- Directory layout is fixed:

```
semizzy-one/
├── app/            PHP application code
├── addons/         independent addons (business logic lives here)
├── bootstrap/
├── config/
├── database/       migrations + seeders (MySQL)
├── public/         document root, .htaccess, PWA assets, built assets
├── resources/
│   ├── js/         React + TypeScript
│   ├── css/
│   └── views/      Blade templates
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json / composer.lock
├── package.json / package-lock.json
├── vite.config.ts / tsconfig.json
└── AGENTS.md       ← this file
```

- **Core owns:** authentication, users, RBAC, settings, notifications, audit,
  storage, providers, addon engine, system infrastructure, PWA, offline and sync
  infrastructure.
- **Addons own:** business-specific functionality.
- Do not mix these responsibilities. If a change puts business logic in Core,
  it is wrong.

---

## 2. MySQL only — mandatory

- `DB_CONNECTION=mysql`. Always.
- **Never** use SQLite, PostgreSQL, MongoDB, SQL Server, Firebase, or Supabase as
  the primary database.
- Never develop against SQLite and assume it behaves like MySQL.
- No config file may fall back to `sqlite`. All defaults are `mysql`.
- No migration, seeder, model, relationship, index, foreign key, constraint,
  transaction, query, or test may assume a non-MySQL engine.
- The database must be `utf8mb4` / `utf8mb4_unicode_ci`.
- Never hardcode credentials. Everything comes from `.env`.
- Target **MySQL 8.4 LTS**; minimum **8.0**. MariaDB is not a supported target.

---

## 3. cPanel / shared-hosting rules — primary target

cPanel shared hosting is the **primary** deployment target. Assume the host may
have **no** root access, Docker, Redis, RabbitMQ, Supervisor, systemd, Nginx, a
permanently running Node process, or generous cron access.

Never make any of these mandatory:

```
Docker · Redis · RabbitMQ · Supervisor · systemd · Nginx · PM2 · GPU · local AI
```

Rules that follow from this:

- **Queue:** `QUEUE_CONNECTION=database`. Cron-driven `schedule:run`.
- **Cache:** `CACHE_STORE=database` (or `file`). The app must work with **no
  Redis at all**.
- **Node/npm** are for development and building only. Production must never
  require a running Node process. Assets are pre-built into `public/build/`.
- **Storage abstraction** defaults to `local`. S3-compatible adapters are
  optional and must never be a hard dependency.
- All Artisan commands must run as the unprivileged cPanel user. No `sudo`, no
  root-only paths.
- Provide and maintain `.htaccess` for Apache/LiteSpeed. No Nginx-only config.

---

## 4. Coding standards

### PHP / Laravel

- Follow Laravel conventions. Use `php artisan make:*` to scaffold.
- Strict types where the file already uses them; match the file you edit.
- Every mutating request is validated with `$request->validate()`.
- Every model declares `$fillable` (or `$guarded`). Never mass-assign blindly.
- Use Eloquent. Raw SQL only with parameter binding — never string
  interpolation.
- Wrap multi-step writes in `DB::transaction()`.
- Use `Str::studly()`, `Str::` facades, and `Arr::` helpers. **Never** use the
  removed global helpers (`studly_case`, `str_slug`, `array_get`,
  `array_first`, `camel_case`, `snake_case`, `title_case`, `str_limit`).
- Never leave `dd()`, `dump()`, `var_dump()`, `ray()`, or `console.log()` behind.
- No `TODO`/`FIXME`/`HACK` comments without an accompanying issue reference.
- Run `vendor/bin/pint` before committing PHP.

### TypeScript / React

- TypeScript `strict`. No `any` unless unavoidable; no `@ts-ignore` without a
  comment explaining why.
- Functional components with hooks. No class components.
- Tailwind CSS for styling. Use the brand palette (§9).
- Mobile-first, responsive, accessible (semantic HTML, labels, focus states).
- Every async surface has **loading, error, empty, and offline** states.
- Permission-aware navigation is UX only — it never replaces backend checks.

### Blade

- Extend `layouts.app`. Use `@section('page-title', ...)` for the page title.
- Never use `@vite()` to reference a file that does not exist. The entry points
  are exactly `resources/css/app.css` and `resources/js/app.tsx`.
- Keep Blade logic thin. Business logic belongs in controllers/services.

---

## 5. Security rules — non-negotiable

Implement and preserve:

- CSRF protection (never disable it)
- XSS protection and security headers
- SQL injection protection (parameter binding)
- Request validation on every input
- Server-side authorization on every route
- Rate limiting on auth endpoints
- Secure password hashing (`config/hashing.php`, honour `BCRYPT_ROUNDS`)
- Secure sessions and cookies (`http_only`, `same_site`, `secure` in production)
- Upload validation: extension **and** MIME **and** size
- Path traversal protection on any filename from user input
- Mass assignment protection
- Secret protection — never log or expose credentials
- Debug protection — `APP_DEBUG=false` in production

**Never expose, in any response or log:**

```
passwords · API keys · database credentials · session secrets ·
stack traces · internal server paths
```

### RBAC rules (absolute)

- Roles: `ADMIN`, `STAFF`, `SUPPORT`, `USER`.
- **`SUPER_ADMIN` must never exist.** No hidden administrator role.
- **No `Gate::before()`.** No `['*']` wildcard permission. No global bypass.
- Tables: `users`, `roles`, `permissions`, `role_user`, `permission_role`,
  `permission_overrides`.
- **`permission_role` does NOT contain `user_id`.** Never model a direct
  `permission ↔ user` relationship through that table.
- Backend authorization is always authoritative. Frontend permission checks are
  UX only.

---

## 6. Addon architecture

Addons live in `addons/<slug>/` and must be self-contained:

```
addons/my-addon/
├── addon.json       manifest: name, slug, version, description,
│                    requires, permissions, settings
├── src/             *ServiceProvider.php — namespace Addons\<StudlySlug>
├── routes/
├── migrations/
├── resources/
├── config/
└── tests/
```

Rules:

- The provider class namespace must be `Addons\<StudlySlug>` and its filename
  must contain `ServiceProvider`.
- Addons **must** use Core services: authentication, RBAC, notifications, audit
  logging, storage, settings, API infrastructure. Never duplicate them.
- An addon must **never** bypass Core authorization.
- An addon must **never** create a hidden administrator.
- An addon must declare its permissions, dependencies, and version.
- Core discovers and registers providers only for addons with
  `status = 'active'`, inside `AppServiceProvider::boot()` with a DB guard.
- Only **one** sample addon exists (`addons/example-addon/`). Do not create
  additional business addons.

---

## 7. Offline / PWA requirements

SEMIZZY ONE must be a **real** progressive web app, not a website with a
manifest.

- Ship `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html`,
  and icons at 72/96/128/144/192/512 (PNG + SVG).
- Cache versioning, cache cleanup, and an update mechanism are required.
- On connection loss, show the branded SEMIZZY ONE offline page — **never** a
  raw browser network error.
- Offline records in IndexedDB (`resources/js/lib/offlineDb.ts`) must carry:
  local ID, server ID, operation ID, created timestamp, updated timestamp,
  sync status, retry count, conflict status.
- Sync statuses: `PENDING`, `SYNCING`, `SYNCED`, `FAILED`, `CONFLICT`.
- Sync flow: `OFFLINE → LOCAL SAVE → PENDING SYNC → INTERNET RETURNS →
  SERVER VALIDATION → SERVER PROCESSING → SYNCED`, with `FAILED` on rejection
  and `CONFLICT` when both sides changed.
- Global network status: `ONLINE`, `OFFLINE`, `CONNECTING`, `SYNCING`,
  `SYNCED`, `SYNC ERROR`.
- **Never silently overwrite server data.**
- **Never store sensitive secrets in browser storage.**

### Data-loss protection

- Idempotency keys on every retryable submission.
- Unique operation IDs, echoed to the server (`X-Operation-Id` header).
- Retry-safe APIs.
- Local draft persistence and a `beforeunload` guard for unsaved forms.
- Never blindly repeat an operation.

### The critical rule

**Never pretend a server-side operation succeeded while offline.** Only
server-confirmed operations may be marked successful. The UI must say:

> "You're offline. This operation cannot be submitted until your connection
> returns."

---

## 8. Testing requirements

Testing is mandatory, not optional.

- Backend: unit, feature, authorization, MySQL database, and API tests.
- Frontend: `tsc --noEmit`, ESLint, and a successful production build.
- PWA: manifest validation, service-worker validation, offline fallback.
- Addon: discovery, install, activate, deactivate, uninstall.
- Deployment: fresh install, cPanel compatibility, production build, MySQL
  connection, HTTPS, PWA.

**Database tests MUST run against MySQL.** `phpunit.xml` targets
`semizzy_one_test`. Do not rely on SQLite.

The §39 security matrix must be exercised: USER→ADMIN, USER→STAFF,
STAFF→ADMIN, SUPPORT→ADMIN, unauthorized permission access, direct API access,
direct URL access, disabled-addon access, uninstalled-addon access, expired
session, invalid CSRF, rate-limited login, unauthorized file access, path
traversal, mass assignment, SQL injection, XSS, permission escalation, and
addon permission bypass.

**Never fabricate test results. Never claim a test passed that was not run.**
If PHP or MySQL is unavailable, say so explicitly.

---

## 9. Brand and UI

| Token | Value |
|---|---|
| Primary Blue | `#155EEF` |
| Teal | `#00B8A9` |
| Navy | `#071A33` |
| White | `#F8FAFC` |
| Gray | `#64748B` |
| Gradient | `#155EEF → #00B8A9` |

Professional modern SaaS/fintech styling. Build responsive sidebar, mobile
navigation, top navigation, dashboard cards, tables, forms, dialogs,
notifications, profile menu, and system/offline/sync status indicators.
Keep Core professional and clean.

---

## 10. Phase execution rules

Build in small, controlled phases. After **every** phase:

1. Inspect the existing code.
2. Implement the phase.
3. Run relevant tests.
4. Run PHP syntax checks.
5. Run TypeScript checks.
6. Run lint.
7. Run the production build where applicable.
8. Run MySQL tests.
9. Check routes.
10. Check authorization.
11. Check database changes.
12. Check for regressions.
13. Fix all discovered failures.
14. Update documentation.
15. Create a Git commit.
16. Report exactly what passed and failed.

**Do not proceed with known failures.**

### Stop conditions — halt and fix immediately if

- the application fails to boot
- a MySQL connection fails
- migrations fail
- tests fail
- the build fails
- TypeScript fails
- PHP syntax fails
- authorization can be bypassed
- the PWA fails
- the offline page fails
- sync duplicates operations
- data can be silently lost
- secrets are exposed
- cPanel deployment fails

**Never hide failures. Never claim success without testing.**

### Phase status

Phases 01–29 are complete. Phases 30–33 (fresh shared-hosting installation,
offline/online stress testing, full forensic audit, final core certification)
are **not** complete. See `docs/certification-report.md` for the current,
honest state.

---

## 11. Core first, business addons later

**Until FINAL CORE CERTIFICATION is complete, add none of these:**

```
VTU · Airtime · Data · Bills · Wallet · Transfers · Payments · KYC ·
Savings · Loans · Investments · Insurance · Gift Cards · Virtual Cards ·
Marketplace · Escrow · P2P · Crypto · Sports · Betting · Education · AI ·
POS · Merchant · Agent · Reseller · Travel · Advertising · Affiliate ·
WhatsApp transaction services
```

**None.** Core first. Business addons only after core certification.

Also: do not create fake business data — no fake wallet balances, payments, VTU
transactions, bank transfers, financial records, KYC records, or business
transactions. Development fixtures must be clearly identified as test data.

---

## 12. Git and environment

- Commit every completed phase. Clean, descriptive commit messages.
- **Never commit:** `.env`, secrets, API keys, passwords, production database
  dumps, or `node_modules`.
- Dependencies must have a documented installation process.
- Document required PHP extensions and the required MySQL version.

### Build commands

```sh
composer install --no-dev --optimize-autoloader
npm install --legacy-peer-deps     # --legacy-peer-deps is REQUIRED
npm run build                      # tsc && vite build
php artisan test                   # runs against MySQL (semizzy_one_test)
```

---

## 13. Documentation

Keep current: `README.md`, `docs/architecture.md`,
`docs/installation-guide.md`, `docs/cpanel-deployment.md`,
`docs/certification-report.md`.

Document: architecture, installation, development, environment, MySQL setup,
cPanel deployment, Apache configuration, cron, queue, cache, PWA, offline
system, synchronization, addon development, provider development, backup,
security, and troubleshooting.

---

## 14. Before you finish any task

- [ ] No business logic leaked into Core
- [ ] MySQL only — no SQLite fallback anywhere
- [ ] No Docker/Redis/Supervisor/systemd/Nginx/PM2 made mandatory
- [ ] No license code added
- [ ] RBAC intact: no SUPER_ADMIN, no `Gate::before()`, no `['*']`,
      `permission_role` has no `user_id`
- [ ] Authorization enforced server-side
- [ ] No secrets, stack traces, or credentials exposed
- [ ] `APP_DEBUG=false` in production
- [ ] Offline flows never fake server success
- [ ] Tests written (and honestly reported if not executed)
- [ ] Documentation updated
- [ ] Committed
