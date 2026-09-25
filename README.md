# SEMIZZY ONE CORE v2.0.0

**Everything You Need. One Platform.**

SEMIZZY ONE is an integrated web platform built with Laravel 13, React 19, TypeScript, and MySQL. It is designed as a core foundation with an addon engine for future business features.

## Technology Stack

| Component | Version |
|-----------|---------|
| PHP | 8.4.26 |
| Laravel | 13.33.0 |
| React | 19.x |
| TypeScript | 5.x |
| Vite | 8.3.1 |
| Tailwind CSS | 4.x |
| MySQL | 8.0+ (MariaDB 11.8 tested) |
| Sanctum | 4.3 |

## Quick Start

### Requirements

- PHP 8.2+ with extensions: bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml, zip, gd, intl
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 20+ & npm

### Installation

```bash
# Clone repository
git clone <repository-url> semizzy-one
cd semizzy-one

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Edit .env with your MySQL credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=semizzy_one
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seed
php artisan migrate --seed

# Build frontend
npm run build

# Create storage link
php artisan storage:link

# Start development server
php artisan serve
```

### Default Admin Login

- **Email:** admin@semizzy.com
- **Password:** admin123

⚠️ **Change this password immediately in production!**

## Project Structure

```
semizzy-one/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin panel controllers
│   │   │   ├── Api/V1/         # REST API controllers
│   │   │   └── Auth/           # Authentication controllers
│   │   └── Middleware/          # Custom middleware
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # Service providers
│   └── Services/                # Business services
├── addons/                      # Addon packages
│   └── example-addon/           # Sample addon
├── database/
│   ├── migrations/              # MySQL migrations
│   └── seeders/                 # Database seeders
├── public/
│   ├── manifest.webmanifest     # PWA manifest
│   ├── sw.js                    # Service worker
│   └── offline.html             # Offline fallback page
├── resources/
│   ├── css/                     # Tailwind CSS
│   ├── js/                      # React + TypeScript
│   │   ├── components/          # React components
│   │   ├── hooks/               # Custom hooks
│   │   ├── lib/                 # Utilities
│   │   ├── stores/              # State stores
│   │   └── types/               # TypeScript types
│   └── views/                   # Blade templates
├── routes/
│   ├── web.php                  # Web routes
│   ├── api.php                  # API routes
│   └── console.php              # Artisan commands
└── tests/
    ├── Feature/                 # Feature tests
    └── Unit/                    # Unit tests
```

## Core Features

### Authentication
- Registration, login, logout
- Rate limiting (5 attempts, 15-minute lockout)
- Session management
- Password reset architecture
- 2FA architecture (ready)
- Audit logging on all auth events

### RBAC (Role-Based Access Control)
- Roles: Admin, Staff, Support, User
- 30 granular permissions across 10 modules
- Per-user permission overrides
- No super admin bypass — all access is explicit

### Admin Dashboard
- System status (PHP, Laravel, MySQL, storage, cache, queue)
- User management (CRUD, role assignment, status management)
- Role & permission management
- Settings management (app, security, notifications, PWA, brand)
- Addon management (install, activate, deactivate, uninstall)
- Provider management
- Audit log viewer
- Security overview
- System health checks
- Backup management

### API Foundation (`/api/v1/`)
- Sanctum token authentication
- Standard JSON response format
- Request validation
- Rate limiting
- Request ID tracking

### Addon Engine
- Auto-discovery from `addons/` directory
- Manifest validation (`addon.json`)
- Full lifecycle: install → activate → deactivate → uninstall
- Permission registration
- Settings registration
- Migration support
- Service provider auto-registration

### PWA (Progressive Web App)
- Web manifest with icons
- Service worker with cache-first strategy for assets
- Network-first for API calls
- Offline fallback page
- Online/offline detection
- Background sync support
- Push notification architecture

### Security
- CSRF protection
- XSS protection (content security headers)
- SQL injection protection (Eloquent ORM + parameterized queries)
- Rate limiting on login
- Secure password hashing (bcrypt)
- Encrypted credentials storage
- Mass assignment protection
- Path traversal protection
- Debug mode protection

## cPanel Deployment

1. Upload project to server
2. Set document root to `public/`
3. Set PHP version to 8.2+
4. Create MySQL database and user in cPanel
5. Configure `.env` with database credentials
6. Run `composer install --no-dev`
7. Run `npm install && npm run build`
8. Run `php artisan migrate --seed`
9. Run `php artisan storage:link`
10. Set permissions: `storage/` and `bootstrap/cache/` writable
11. Configure cron: `* * * * * php artisan schedule:run >> /dev/null 2>&1`

### Apache `.htaccess`

Included in `public/.htaccess` with:
- URL rewriting for Laravel
- Security headers
- Sensitive file protection
- Directory listing disabled

## Testing

```bash
# Run all tests against MySQL
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# TypeScript check
npx tsc --noEmit

# Production build
npm run build
```

## Cron Configuration

Add to cPanel cron:
```
* * * * * cd /path/to/semizzy-one && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Processing (Shared Hosting)

Process queues via cron (no Supervisor needed):
```
* * * * * cd /path/to/semizzy-one && php artisan queue:work --stop-when-empty --max-time=60
```

## Addon Development

Create an addon in `addons/your-addon/`:

```
addons/your-addon/
├── addon.json           # Manifest
├── src/
│   └── YourAddonServiceProvider.php
├── routes/
├── migrations/
├── resources/
├── config/
└── tests/
```

See `addons/example-addon/` for a complete working example.

## Brand Colors

| Color | Hex |
|-------|-----|
| Primary Blue | #155EEF |
| Teal | #00B8A9 |
| Navy | #071A33 |
| White | #F8FAFC |
| Gray | #64748B |

## Contact

**SEMIZZY WEBMASTER**
- Phone/WhatsApp: 08112464226, 08162476945
- Email: semizzywebmaster@gmail.com

## License

Proprietary — SEMIZZY WEBMASTER. All rights reserved.