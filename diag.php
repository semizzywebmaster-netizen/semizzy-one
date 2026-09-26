<?php

/**
 * SEMIZZY ONE - route diagnostic.
 *
 * Upload this file to the project ROOT (the directory that contains artisan)
 * and open it in a browser, or run it over SSH with: php diag.php
 *
 * DELETE THIS FILE as soon as you are done (rm -f diag.php). It prints
 * internal absolute paths, environment values and installation state, so it
 * must never be left on a live server.
 *
 * If your document root is public/ (which it should be), this file at the
 * project root is NOT reachable over HTTP and must be run via SSH:
 *     php diag.php
 * If your document root is the project root by mistake, this file IS reachable
 * over HTTP - fix the document root to public/ and delete this file.
 */

if (PHP_SAPI !== 'cli') {
    echo "<!doctype html><meta charset=utf-8><title>SEMIZZY ONE diagnostic</title>";
    echo "<style>body{font:14px/1.6 ui-monospace,Menlo,Consolas,monospace;background:#101014;color:#e8e8ec;padding:28px;max-width:900px;margin:auto}"
        ."h1{font-size:19px;color:#fff}h2{font-size:15px;color:#9fe870;margin-top:26px;border-bottom:1px solid #2a2a33;padding-bottom:6px}"
        .".k{color:#8b8b96}.ok{color:#7ee07e}.bad{color:#ff7a7a}.warn{color:#ffcf6a}"
        ."table{border-collapse:collapse;width:100%;margin-top:8px}td,th{padding:5px 9px;border:1px solid #2a2a33;text-align:left;font-size:13px}"
        ."th{background:#1a1a20}.mono{white-space:pre-wrap;word-break:break-all}</style>";
}

function h2($t) { echo "\n<h2>$t</h2>\n"; }
function ok($t) { echo "<div class='ok'>  [OK]   $t</div>"; }
function bad($t) { echo "<div class='bad'>  [FAIL] $t</div>"; }
function warn($t) { echo "<div class='warn'>  [WARN] $t</div>"; }
function info($t) { echo "<div class='k'>         $t</div>"; }
function row($k, $v) { echo "<tr><td class='k'>$k</td><td class='mono'>".htmlspecialchars((string) $v)."</td></tr>"; }

$root = __DIR__;

echo "<h1>SEMIZZY ONE &mdash; route diagnostic</h1>";

if (PHP_SAPI !== 'cli') {
    echo "<div class='warn' style='border:2px solid #ffcf6a;padding:12px;margin:14px 0'>"
        ."<b>SECURITY WARNING.</b> This page is publicly reachable and leaks internal"
        ." paths and configuration. <b>Delete diag.php from the server as soon as you"
        ." have finished reading this.</b> Your document root should be <code>public/</code>;"
        ." if it is not, fix that too.</div>";
}

// ---------------------------------------------------------------- 1. caches
h2('1. Bootstrap caches (the usual culprit)');

$cacheFiles = glob($root.'/bootstrap/cache/*.php') ?: [];
$expected = ['.gitignore'];
$cached = array_values(array_filter($cacheFiles, function ($f) use ($expected) {
    return !in_array(basename($f), $expected, true);
}));

if (empty($cached)) {
    ok('No route/config/view cache files present. Laravel is reading routes/web.php directly.');
} else {
    foreach ($cached as $f) {
        $size = @filesize($f);
        $age  = time() - (int) @filemtime($f);
        bad(basename($f).' exists ('.number_format($size).' bytes, modified '.$age.'s ago)');
        info('This file overrides routes/web.php. Delete it: rm -f '.$f);
    }
}

// ------------------------------------------------------------ 2. writability
h2('2. Writable directories');

foreach (['bootstrap/cache', 'storage', 'storage/framework', 'storage/logs', 'storage/app'] as $d) {
    $p = $root.'/'.$d;
    if (!is_dir($p)) {
        bad($d.' does not exist');
    } elseif (!is_writable($p)) {
        bad($d.' is NOT writable (chmod 755, or 775 if that fails)');
    } else {
        ok($d.' is writable');
    }
}

// --------------------------------------------------------------- 3. install
h2('3. Installation state');

$marker = $root.'/storage/app/.installed';
if (file_exists($marker)) {
    warn('storage/app/.installed EXISTS - the wizard is locked and every /install/* request is redirected to /');
    info('If you are trying to install: rm -f '.$marker);
} else {
    ok('No .installed marker - the wizard is unlocked and /install/* should be reachable');
}

// ------------------------------------------------------------------ 4. env
h2('4. Environment');

if (!file_exists($root.'/.env')) {
    bad('.env is MISSING - copy .env.example to .env first');
} else {
    ok('.env is present');
    $env = [];
    foreach (file($root.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }

    row('APP_ENV', $env['APP_ENV'] ?? '(unset)');
    row('APP_DEBUG', $env['APP_DEBUG'] ?? '(unset)');
    row('APP_URL', $env['APP_URL'] ?? '(unset)');
    row('DB_CONNECTION', $env['DB_CONNECTION'] ?? '(unset)');

    if (($env['DB_CONNECTION'] ?? 'mysql') !== 'mysql') {
        bad('DB_CONNECTION must be mysql');
    } else {
        ok('DB_CONNECTION is mysql');
    }

    if (!empty($env['APP_KEY']) && strpos($env['APP_KEY'], 'base64:') === 0) {
        ok('APP_KEY is set');
    } else {
        bad('APP_KEY is missing or malformed - run: php artisan key:generate');
    }
}

// ------------------------------------------------------------------ 5. PHP
h2('5. PHP runtime');

row('PHP version', PHP_VERSION);
info('Required: 8.2 or higher (8.3+ recommended)');

$ext = ['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'curl', 'zip', 'bcmath'];
$missing = [];
foreach ($ext as $e) {
    if (!extension_loaded($e)) {
        $missing[] = $e;
    }
}
if ($missing) {
    bad('Missing PHP extensions: '.implode(', ', $missing));
} else {
    ok('All required PHP extensions loaded ('.implode(', ', $ext).')');
}

if (function_exists('opcache_get_status')) {
    $st = @opcache_get_status(false);
    if (is_array($st) && !empty($st['opcache_enabled'])) {
        warn('OPcache is ENABLED. If a route was just added and still 404s after route:clear,');
        info('OPcache may be serving stale compiled PHP. Fix: touch the files, or restart PHP.');
        info('  touch routes/web.php app/Http/Controllers/InstallController.php');
    } else {
        ok('OPcache is not active');
    }
} else {
    info('OPcache not available');
}

// --------------------------------------------------------------- 6. vendor
h2('6. Dependencies');

if (!is_dir($root.'/vendor')) {
    bad('vendor/ is MISSING - run: composer install --no-dev --optimize-autoloader');
} elseif (!file_exists($root.'/vendor/autoload.php')) {
    bad('vendor/autoload.php is MISSING - run: composer install --no-dev --optimize-autoloader');
} else {
    ok('vendor/autoload.php is present');
}

if (!file_exists($root.'/public/build/manifest.json')) {
    warn('public/build/manifest.json is MISSING - run: npm ci && npm run build');
} else {
    ok('Frontend build manifest is present');
}

// ------------------------------------------------------------- 7. routes
h2('7. Route registration');

$web = file_get_contents($root.'/routes/web.php');
$expectedRoutes = [
    'install.check.server'  => "/install/check/server",
    'install.check.php'     => "/install/check/php",
    'install.check.mysql'   => "/install/check/mysql",
    'install.environment'   => "/install/environment",
    'install.database.connect'   => "/install/database/connect",
    'install.database.validate'  => "/install/database/validate",
    'install.application'   => "/install/application",
    'install.admin'         => "/install/admin",
    'install.migrate'       => "/install/migrate",
    'install.seed'          => "/install/seed",
    'install.check.storage' => "/install/check/storage",
    'install.check.cache'   => "/install/check/cache",
    'install.check.pwa'     => "/install/check/pwa",
    'install.check.security' => "/install/check/security",
    'install.finalize'      => "/install/finalize",
];

echo "<table><tr><th>Route name</th><th>URL in routes/web.php</th></tr>";
$missingInFile = [];
foreach ($expectedRoutes as $name => $url) {
    $present = strpos($web, "name('".$name."')") !== false;
    if (!$present) {
        $missingInFile[] = $name;
    }
    row($name, ($present ? 'FOUND  ' : 'MISSING ').$url);
}
echo "</table>";

if ($missingInFile) {
    bad('These routes are NOT in routes/web.php - your deployed copy of the file is outdated.');
    info('Fix: git pull origin main   (then check git log shows commit 930b258 or newer)');
} else {
    ok('All 15 install routes are present in routes/web.php on disk.');
}

// ------------------------------------------------------- 8. controller
h2('8. Controller methods');

$ctrl = file_get_contents($root.'/app/Http/Controllers/InstallController.php');
$methods = ['index', 'checkServer', 'checkPhp', 'checkMysql', 'environment',
    'connectDatabase', 'validateDatabase', 'application', 'admin',
    'migrate', 'seed', 'checkStorage', 'checkCache', 'checkPwa',
    'checkSecurity', 'finalize'];

$missingMethods = [];
foreach ($methods as $m) {
    if (strpos($ctrl, 'function '.$m.'(') === false) {
        $missingMethods[] = $m;
    }
}
if ($missingMethods) {
    bad('Missing controller methods: '.implode(', ', $missingMethods));
    info('Your deployed InstallController.php is outdated. Run: git pull origin main');
} else {
    ok('All '.count($methods).' controller methods exist.');
}

// ------------------------------------------------------------ 9. verdict
h2('9. What to do');

if ($missingInFile || $missingMethods) {
    echo "<div class='bad'><b>Your deployed code is outdated.</b> Run:</div>";
    echo "<div class='mono'>cd ".htmlspecialchars($root)."
git fetch origin
git reset --hard origin/main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
rm -f storage/app/.installed
php artisan route:clear && php artisan config:clear && php artisan view:clear</div>";
} elseif (!empty($cached)) {
    echo "<div class='bad'><b>A stale cache is blocking the routes.</b> Run:</div>";
    echo "<div class='mono'>rm -f bootstrap/cache/routes*.php bootstrap/cache/config.php bootstrap/cache/*.php
php artisan route:clear && php artisan config:clear && php artisan view:clear</div>";
    info('If the files come straight back, bootstrap/cache is not writable or is owned by another user.');
} elseif (file_exists($marker)) {
    echo "<div class='warn'><b>The wizard is locked.</b> Run: rm -f storage/app/.installed</div>";
} else {
    echo "<div class='ok'><b>Everything on disk looks correct.</b> If routes still 404, the cause is outside this file:</div>";
    info('1. Apache mod_rewrite / .htaccess - open https://your-domain/up (the health endpoint).');
    info('   If that 404s too, the problem is .htaccess, not the routes.');
    info('2. Document root must be public/, not the project root.');
    info('3. OPcache - run: touch routes/web.php app/Http/Controllers/InstallController.php');
}

echo "\n<div class='k' style='margin-top:28px;border-top:1px solid #2a2a33;padding-top:12px'>";
echo "Delete this file when finished: rm -f diag.php</div>\n";
