# SEMIZZY ONE — cPanel Deployment Guide

## Prerequisites

- cPanel shared hosting with PHP 8.2+ support
- MySQL 8.0+ or MariaDB 10.6+
- SSH access (optional but recommended)
- Composer available on server

## Step-by-Step Deployment

### 1. Upload Project

Upload the entire `semizzy-one/` directory to your hosting account via:
- **File Manager** (cPanel) — zip and upload
- **FTP/SFTP** — upload all files
- **SSH** — `git clone` if available

Place files in your desired directory (e.g., `/home/username/semizzy-one/`).

### 2. Set PHP Version

In cPanel → **MultiPHP Manager**:
- Select your domain
- Set PHP version to **8.2** or higher (8.3/8.4 recommended)

### 3. Create MySQL Database

In cPanel → **MySQL Databases**:
1. Create database: `semizzy_one`
2. Create user: `semizzy_user` with strong password
3. Add user to database with **ALL PRIVILEGES**

### 4. Configure Document Root

In cPanel → **Domains** or **Subdomains**:
- Set document root to: `/home/username/semizzy-one/public`
- This is CRITICAL — Laravel's `public/` directory must be the web root

If you cannot change the document root, create a symbolic link:
```bash
ln -s /home/username/semizzy-one/public/* /home/username/public_html/
ln -s /home/username/semizzy-one/public/.htaccess /home/username/public_html/.htaccess
```

### 5. Configure Environment

SSH into the server (or use File Manager):
```bash
cd /home/username/semizzy-one
cp .env.example .env
```

Edit `.env`:
```
APP_NAME="SEMIZZY ONE"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_username_semizzy_one
DB_USERNAME=your_cpanel_username_semizzy_user
DB_PASSWORD=your_strong_password

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### 6. Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Node dependencies and build
npm install
npm run build
```

### 7. Run Migrations

```bash
php artisan migrate --seed --force
```

This creates all database tables and seeds the default admin account.

### 8. Create Storage Link

```bash
php artisan storage:link
```

### 9. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 10. Configure Cron

In cPanel → **Cron Jobs**:
- Add new cron job, run every minute:
```
* * * * * cd /home/username/semizzy-one && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Enable HTTPS

In cPanel → **SSL/TLS** or use **Let's Encrypt**:
- Install SSL certificate
- Force HTTPS redirect (add to `.htaccess` if needed):
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 12. Verify Installation

1. Visit `https://yourdomain.com`
2. You should be redirected to the login page
3. Login with: `admin@semizzy.com` / `admin123`
4. **CHANGE THE DEFAULT PASSWORD IMMEDIATELY**

### 13. Post-Deployment Checklist

- [ ] Application loads correctly
- [ ] Login works
- [ ] Dashboard shows system status
- [ ] Admin panel accessible
- [ ] HTTPS is active
- [ ] PWA manifest loads (`/manifest.webmanifest`)
- [ ] Service worker registers
- [ ] Offline page works (disconnect internet)
- [ ] Cron job is running (check System Health → Cron)
- [ ] Default admin password changed
- [ ] APP_DEBUG is set to `false`

## Troubleshooting

### 500 Error
- Check `storage/logs/laravel.log`
- Verify `.env` database credentials
- Ensure `storage/` and `bootstrap/cache/` are writable

### White Screen
- Set `APP_DEBUG=true` temporarily
- Check PHP version is 8.2+
- Check required PHP extensions are loaded

### Database Connection Failed
- Verify database name includes cPanel prefix: `username_dbname`
- Verify user has ALL PRIVILEGES on the database
- Check host is `localhost` (most cPanel setups)

### Routes Return 404
- Ensure document root points to `public/`
- Check `.htaccess` exists in `public/`
- Verify mod_rewrite is enabled

### Assets Not Loading
- Run `npm run build` to compile frontend assets
- Check `public/build/` directory exists with built files

## Recommended PHP Extensions

```
bcmath, ctype, curl, dom, fileinfo, gd, intl, json, mbstring,
openssl, pdo_mysql, tokenizer, xml, zip, opcache, readline
```

## Server Requirements Summary

| Requirement | Minimum |
|-------------|---------|
| PHP | 8.2+ |
| MySQL/MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x |
| Node.js | 20+ (build only) |
| Apache | 2.4+ |
| mod_rewrite | Required |
| Disk Space | 500MB+ |
| Memory | 256MB+ (512MB recommended) |