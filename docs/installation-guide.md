# SEMIZZY ONE — Installation Guide

> **Everything You Need. One Platform.**
> Core Version: 2.0.0

---

## Table of Contents

1. [System Requirements](#1-system-requirements)
2. [Local Development Setup](#2-local-development-setup)
3. [cPanel Shared Hosting Deployment](#3-cpanel-shared-hosting-deployment)
4. [VPS / Dedicated Server Deployment](#4-vps--dedicated-server-deployment)
5. [Post-Installation Configuration](#5-post-installation-configuration)
6. [Cron & Queue Setup](#6-cron--queue-setup)
7. [PWA Setup](#7-pwa-setup)
8. [HTTPS Setup](#8-https-setup)
9. [Default Login](#9-default-login)
10. [Troubleshooting](#10-troubleshooting)
11. [Required PHP Extensions](#11-required-php-extensions)
12. [Server Requirements Summary](#12-server-requirements-summary)

---

## 1. System Requirements

### Minimum

| Requirement | Version |
|-------------|---------|
| PHP | 8.2+ (8.3 or 8.4 recommended) |
| MySQL | 8.0+ |
| MariaDB | 10.6+ (alternative to MySQL) |
| Composer | 2.x |
| Node.js | 20+ (build only, NOT needed in production) |
| Apache | 2.4+ with mod_rewrite |
| Disk Space | 500 MB minimum |
| PHP Memory | 256 MB (512 MB recommended) |

### Required PHP Extensions

```
bcmath, ctype, curl, dom, fileinfo, gd, intl, json,
mbstring, openssl, pdo_mysql, tokenizer, xml, zip,
opcache, readline, session, sodium
```

Check your PHP extensions:
```bash
php -m
```

### What You Do NOT Need

SEMIZZY ONE is designed for shared hosting. You do **NOT** need:

- ❌ Docker
- ❌ Redis
- ❌ RabbitMQ
- ❌ Supervisor
- ❌ systemd
- ❌ Nginx
- ❌ PM2
- ❌ Root access
- ❌ A permanently running Node.js process

---

## 2. Local Development Setup

### 2.1 Clone the Repository

```bash
git clone https://github.com/semizzywebmaster-netizen/semizzy-one.git
cd semizzy-one
```

### 2.2 Install PHP Dependencies

```bash
composer install
```

### 2.3 Install Node Dependencies

```bash
npm install
```

### 2.4 Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your MySQL connection:

```env
APP_NAME="SEMIZZY ONE"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=semizzy_one
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

### 2.5 Generate Application Key

```bash
php artisan key:generate
```

### 2.6 Create the MySQL Database

```sql
CREATE DATABASE semizzy_one CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or via command line:
```bash
mysql -u root -p -e "CREATE DATABASE semizzy_one CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2.7 Run Database Migrations & Seed

```bash
php artisan migrate --seed
```

This creates:
- 24 database tables
- 4 roles (Admin, Staff, Support, User)
- 30 permissions
- 21 system settings
- Default admin account

### 2.8 Create Storage Symlink

```bash
php artisan storage:link
```

### 2.9 Build Frontend Assets

For development (with hot reload):
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 2.10 Start Development Server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

### 2.11 Verify Installation

```bash
# Check application status
php artisan about

# Run health check
php artisan health:check

# Run tests
php artisan test

# Check routes
php artisan route:list
```

---

## 3. cPanel Shared Hosting Deployment

This is the **primary deployment target** for SEMIZZY ONE.

### 3.1 Upload Project Files

**Option A: File Manager (cPanel)**

1. Log into cPanel
2. Open **File Manager**
3. Navigate to your domain's root directory
4. Upload `semizzy-one.zip`
5. Extract the zip file
6. You should now have `/home/username/semizzy-one/`

**Option B: FTP/SFTP**

1. Connect via FTP client (FileZilla, WinSCP, etc.)
2. Upload the entire `semizzy-one/` directory
3. Place it in your home directory

**Option C: SSH (if available)**

```bash
cd /home/your-username
git clone https://github.com/semizzywebmaster-netizen/semizzy-one.git
```

### 3.2 Set PHP Version

1. Go to cPanel → **MultiPHP Manager** (or **PHP Selector**)
2. Select your domain
3. Set PHP version to **8.2** or higher (8.3/8.4 recommended)
4. Ensure these extensions are enabled:
   - bcmath, curl, dom, fileinfo, gd, intl, mbstring, pdo_mysql, xml, zip, opcache

### 3.3 Create MySQL Database

1. Go to cPanel → **MySQL Databases**
2. Create a new database:
   - Database name: `semizzy_one` (will become `username_semizzy_one`)
3. Create a new database user:
   - Username: `semizzy_user` (will become `username_semizzy_user`)
   - Password: Use a strong password (save this!)
4. Add the user to the database:
   - Select the user and database
   - Check **ALL PRIVILEGES**
   - Click **Add**

**Important:** Note the full database name and username (they include your cPanel username prefix).

### 3.4 Configure Document Root

The web server **MUST** point to the `public/` directory.

**Option A: Domain Root (recommended)**

1. Go to cPanel → **Domains** or **Subdomains**
2. Set document root to:
   ```
   /home/your-username/semizzy-one/public
   ```

**Option B: Subdirectory**

If you want SEMIZZY ONE at `yourdomain.com/app/`:

1. Create a symbolic link from `public_html/app` to `semizzy-one/public`:
   ```bash
   ln -s /home/your-username/semizzy-one/public /home/your-username/public_html/app
   ```

**Option C: Cannot change document root**

If you cannot change the document root:
```bash
# Copy public files to public_html
cp -r /home/your-username/semizzy-one/public/* /home/your-username/public_html/
cp /home/your-username/semizzy-one/public/.htaccess /home/your-username/public_html/

# Edit public_html/index.php to point to the correct paths
```

Edit `public_html/index.php`:
```php
require __DIR__.'/../semizzy-one/vendor/autoload.php';
$app = require_once __DIR__.'/../semizzy-one/bootstrap/app.php';
```

### 3.5 Configure Environment File

Via SSH or File Manager:

```bash
cd /home/your-username/semizzy-one
cp .env.example .env
```

Edit `.env` with your production settings:

```env
APP_NAME="SEMIZZY ONE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_username_semizzy_one
DB_USERNAME=your_username_semizzy_user
DB_PASSWORD=your_database_password

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_ENCRYPT=true

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Critical:** Set `APP_DEBUG=false` in production.

### 3.6 Install PHP Dependencies

```bash
cd /home/your-username/semizzy-one
composer install --no-dev --optimize-autoloader
```

### 3.7 Generate Application Key

```bash
php artisan key:generate
```

### 3.8 Install Node Dependencies & Build

```bash
npm install
npm run build
```

**Note:** If Node.js is not available on your hosting, build locally first:
```bash
# On your local machine
npm install
npm run build
# Then upload the public/build/ directory to the server
```

### 3.9 Run Database Migrations

```bash
php artisan migrate --seed --force
```

### 3.10 Create Storage Link

```bash
php artisan storage:link
```

### 3.11 Set File Permissions

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env
```

### 3.12 Configure Cron Job

1. Go to cPanel → **Cron Jobs**
2. Set frequency to **Once Per Minute**
3. Add this command:

```
* * * * * cd /home/your-username/semizzy-one && php artisan schedule:run >> /dev/null 2>&1
```

### 3.13 Enable HTTPS

1. Go to cPanel → **SSL/TLS** or **Let's Encrypt**
2. Install an SSL certificate for your domain
3. Force HTTPS redirect

If automatic redirect is not available, add to `.htaccess` in `public/`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3.14 Verify Installation

1. Visit `https://yourdomain.com`
2. You should see the SEMIZZY ONE login page
3. Login with default credentials (see [Section 9](#9-default-login))
4. **Change the default password immediately**

---

## 4. VPS / Dedicated Server Deployment

### 4.1 Install Prerequisites

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-opcache \
    php8.3-readline mysql-server apache2 libapache2-mod-php8.3 \
    unzip curl git

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js (for building)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

**CentOS/RHEL:**
```bash
sudo dnf install php php-mysqlnd php-pdo php-mbstring php-xml \
    php-zip php-bcmath php-gd php-intl php-opcache \
    mysql-server httpd unzip curl git

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 4.2 Configure Apache

Create `/etc/apache2/sites-available/semizzy.conf` (Ubuntu) or `/etc/httpd/conf.d/semizzy.conf` (CentOS):

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/semizzy-one/public

    <Directory /var/www/semizzy-one/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"

    ErrorLog ${APACHE_LOG_DIR}/semizzy-error.log
    CustomLog ${APACHE_LOG_DIR}/semizzy-access.log combined

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
</VirtualHost>

# HTTP to HTTPS redirect
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

Enable the site:
```bash
sudo a2enmod rewrite headers ssl
sudo a2ensite semizzy.conf
sudo systemctl restart apache2
```

### 4.3 Clone & Configure

```bash
cd /var/www
git clone https://github.com/semizzywebmaster-netizen/semizzy-one.git
cd semizzy-one

composer install --no-dev --optimize-autoloader
npm install && npm run build

cp .env.example .env
# Edit .env with your production settings

php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link

chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
```

### 4.4 Configure Cron

```bash
crontab -e
```

Add:
```
* * * * * cd /var/www/semizzy-one && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Post-Installation Configuration

### 5.1 Change Default Admin Password

1. Login with default credentials
2. Go to Profile → Change Password
3. Set a strong password

### 5.2 Configure Application Settings

Navigate to **Admin → Settings**:

**Application:**
- App Name: Your application name
- Tagline: Your tagline
- Timezone: Your timezone (e.g., `Africa/Lagos`)
- Locale: Your language

**Security:**
- Max Login Attempts: 5 (default)
- Lockout Duration: 900 seconds (default)
- Session Lifetime: 120 minutes (default)

**Brand:**
- Customize colors (Primary Blue, Teal, Navy)

### 5.3 Create Additional Users

Go to **Admin → Users → Add User** and assign appropriate roles.

### 5.4 Verify System Health

Go to **Admin → System Health** or run:
```bash
php artisan health:check
```

All checks should show ✅ healthy.

---

## 6. Cron & Queue Setup

### cPanel Cron (Recommended)

Add in cPanel → Cron Jobs:

```
* * * * * cd /home/your-username/semizzy-one && php artisan schedule:run >> /dev/null 2>&1
```

This automatically handles:
- Queue processing (every minute)
- Cache cleanup (daily)
- Failed job pruning (daily)
- Audit log cleanup (90-day retention)
- Provider health checks (every 15 minutes)
- Cron heartbeat recording

### Manual Queue Processing (Alternative)

If you prefer to process queues separately:

```
* * * * * cd /home/your-username/semizzy-one && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1
```

---

## 7. PWA Setup

SEMIZZY ONE is a Progressive Web App out of the box.

### What's Included

- ✅ `manifest.webmanifest` — App metadata and icons
- ✅ `sw.js` — Service worker with offline caching
- ✅ `offline.html` — Branded offline fallback page
- ✅ PNG icons (72px to 512px)

### How It Works

1. First visit loads normally
2. Service worker caches the app shell and static assets
3. Subsequent visits load from cache (faster)
4. If offline, users see the branded offline page
5. API calls return a structured offline response (never a browser error)

### HTTPS Required

PWAs require HTTPS. The service worker will not register on HTTP.

---

## 8. HTTPS Setup

### cPanel (Let's Encrypt)

1. Go to cPanel → **Let's Encrypt** or **SSL/TLS**
2. Select your domain
3. Click **Issue** or **Install**
4. Enable **Force HTTPS Redirect**

### Manual Certificate

If using a purchased certificate:

1. Go to cPanel → **SSL/TLS** → **Manage SSL Sites**
2. Paste your certificate, private key, and CA bundle
3. Install the certificate

### Verify HTTPS

Visit `https://yourdomain.com` and check for the 🔒 lock icon.

---

## 9. Default Login

After installation, login with:

| Field | Value |
|-------|-------|
| **Email** | `admin@semizzy.com` |
| **Password** | `admin123` |

### Default Roles

| Role | Description |
|------|-------------|
| **Admin** | Full system access (30 permissions) |
| **Staff** | Limited admin capabilities |
| **Support** | User assistance permissions |
| **User** | Basic profile access only |

⚠️ **CHANGE THE DEFAULT PASSWORD IMMEDIATELY AFTER INSTALLATION**

---

## 10. Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel log
tail -50 storage/logs/laravel.log

# Common fixes:
# 1. Ensure .env exists
cp .env.example .env
php artisan key:generate

# 2. Ensure storage is writable
chmod -R 755 storage/ bootstrap/cache/

# 3. Ensure vendor exists
composer install --no-dev
```

### White Screen / No Output

```bash
# Temporarily enable debug mode
# Edit .env: APP_DEBUG=true

# Check PHP version
php -v

# Check required extensions
php -m | grep -E "bcmath|curl|dom|gd|mbstring|pdo_mysql|xml|zip"
```

### Database Connection Failed

```bash
# Test MySQL connection manually
mysql -u your_username -p -h localhost your_database

# Common issues:
# - Database name includes cPanel prefix: username_dbname
# - User not assigned to database
# - Wrong password
# - Host is not 'localhost'
```

### Routes Return 404

```bash
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Ensure .htaccess exists in public/
ls -la public/.htaccess

# Ensure AllowOverride All in Apache config
```

### Frontend Assets Not Loading

```bash
# Build assets
npm install
npm run build

# Verify build output
ls -la public/build/

# If Node.js not available on server:
# Build locally, then upload public/build/ directory
```

### "Vite Manifest Not Found"

```bash
npm run build
```

This creates `public/build/manifest.json` required by Laravel.

### Migrations Fail

```bash
# Check MySQL version
mysql --version

# Check database exists and user has privileges
mysql -u your_user -p -e "SHOW GRANTS;"

# Reset and re-run
php artisan migrate:fresh --seed --force
```

### Permission Denied Errors

```bash
# Set correct ownership (Linux VPS)
sudo chown -R www-data:www-data storage bootstrap/cache

# Set correct permissions
chmod -R 755 storage/ bootstrap/cache/
chmod 644 .env
```

### PWA Not Installing

- Ensure HTTPS is active (PWAs require HTTPS)
- Check browser console for service worker errors
- Verify `/manifest.webmanifest` is accessible
- Verify `/sw.js` is accessible

---

## 11. Required PHP Extensions

```bash
# Check installed extensions
php -m

# Required extensions (must be enabled):
bcmath      # Arbitrary precision math
ctype       # Character type checking
curl        # HTTP client
dom         # Document Object Model
fileinfo    # File type detection
gd          # Image processing
intl        # Internationalization
json        # JSON support (usually built-in)
mbstring    # Multi-byte string handling
openssl     # Encryption and SSL
pdo_mysql   # MySQL database driver
tokenizer   # PHP token parsing
xml         # XML parsing
zip         # ZIP archive handling
opcache     # PHP opcode caching (performance)
readline    # CLI input
session     # Session management
sodium      # Modern encryption
```

### Installing Missing Extensions (Ubuntu/Debian)

```bash
sudo apt install php8.3-gd php8.3-intl php8.3-bcmath php8.3-zip php8.3-curl php8.3-xml php8.3-mbstring php8.3-mysql
sudo systemctl restart apache2
```

### Installing Missing Extensions (cPanel)

1. Go to cPanel → **Select PHP Version** or **PHP Manager**
2. Check the required extensions
3. Save changes

---

## 12. Server Requirements Summary

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.2 | 8.3 or 8.4 |
| MySQL | 8.0 | 8.4 LTS |
| MariaDB | 10.6 | 11.x |
| Composer | 2.x | Latest |
| Node.js | 20 (build only) | 20 LTS |
| Apache | 2.4 | Latest |
| RAM | 256 MB | 512 MB+ |
| Disk | 500 MB | 1 GB+ |
| PHP Memory | 256 MB | 512 MB |
| PHP Max Execution | 30s | 60s |
| mod_rewrite | Required | Required |

---

## Quick Reference Commands

```bash
# Application
php artisan about                    # Application info
php artisan key:generate             # Generate app key
php artisan migrate --seed           # Run migrations + seed
php artisan migrate:fresh --seed     # Reset database
php artisan storage:link             # Create storage symlink

# Cache (clear after updates)
php artisan config:clear             # Clear config cache
php artisan route:clear              # Clear route cache
php artisan view:clear               # Clear compiled views
php artisan cache:clear              # Clear application cache

# Health
php artisan health:check             # Run health checks
php artisan health:check --json      # JSON output

# Backup
php artisan backup:database          # Database backup

# Queue
php artisan queue:work               # Process queue
php artisan queue:work --stop-when-empty  # Process and exit

# Testing
php artisan test                     # Run all tests
npx tsc --noEmit                     # TypeScript check
npm run build                        # Production build
```

---

## Support

**SEMIZZY WEBMASTER**

- Phone/WhatsApp: 08112464226, 08162476945
- Email: semizzywebmaster@gmail.com

---

*SEMIZZY ONE CORE v2.0.0 — Everything You Need. One Platform.*