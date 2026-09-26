<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * SEMIZZY ONE CORE — 16-step first-run installation wizard (§30, §31).
 *
 * The wizard is intentionally stateless: the client holds the accumulated
 * configuration and sends the relevant slice with each request. This avoids
 * depending on a session table that does not exist yet on a fresh install.
 */
class InstallController extends Controller
{
    /** Steps in the wizard, in order. */
    public const STEPS = [
        1  => 'Welcome',
        2  => 'Server Requirements',
        3  => 'PHP Requirements',
        4  => 'MySQL Requirements',
        5  => 'Environment',
        6  => 'MySQL Connection',
        7  => 'Database Validation',
        8  => 'Application Settings',
        9  => 'Admin Account',
        10 => 'Migrations',
        11 => 'Core Data',
        12 => 'Storage Checks',
        13 => 'Cache Checks',
        14 => 'PWA Checks',
        15 => 'Security Checks',
        16 => 'Finalization',
    ];

    /** PHP extensions required by Core (§30 step 3). */
    private const REQUIRED_EXTENSIONS = [
        'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring',
        'openssl', 'pdo_mysql', 'tokenizer', 'xml', 'zip', 'gd', 'intl', 'sodium',
    ];

    // ─── Guards ────────────────────────────────────────────────

    private function guard(): ?JsonResponse
    {
        if (file_exists(storage_path('app/.installed'))) {
            return response()->json([
                'success' => false,
                'message' => 'Application is already installed.',
            ], 403);
        }

        return null;
    }

    private function respond(array $checks, ?string $message = null): JsonResponse
    {
        // A check marked 'warn' is advisory: it is shown but does not block the
        // wizard. Production hardening (SSL, APP_ENV) is the operator's call and
        // can be applied after installation, so it must not trap them here.
        $blocking = collect($checks)->filter(
            static fn ($c) => $c['passed'] === false && empty($c['warn'])
        );
        $failed = $blocking->count();
        $warned = collect($checks)->filter(
            static fn ($c) => $c['passed'] === false && !empty($c['warn'])
        )->count();

        return response()->json([
            'success' => $failed === 0,
            'checks'  => $checks,
            'failed'  => $failed,
            'warned'  => $warned,
            'message' => $message ?? match (true) {
                $failed > 0 && $warned > 0 => "{$failed} check(s) failed, {$warned} warning(s).",
                $failed > 0               => "{$failed} check(s) failed.",
                $warned > 0               => "All required checks passed, {$warned} warning(s).",
                default                   => 'All checks passed.',
            },
        ]);
    }

    private function fail(string $message, int $code = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    // ─── Step 1: Welcome ───────────────────────────────────────

    public function index()
    {
        if (file_exists(storage_path('app/.installed'))) {
            return redirect('/');
        }

        // Self-heal: a previous install (or an older build of this installer)
        // may have left a cached route table that no longer matches
        // routes/web.php. Laravel then serves routes exclusively from
        // bootstrap/cache/routes-*.php and every newly added route 404s,
        // which would make this wizard unusable. Clear it before rendering.
        $this->clearStaleCaches();

        return view('install.index');
    }

    /**
     * Remove stale bootstrap caches so the wizard can never be blocked by a
     * route/config/view table left behind by an earlier install or build.
     *
     * Only ever runs while the application is NOT installed, so it has no
     * effect on a live production app.
     */
    private function clearStaleCaches(): void
    {
        foreach (['route', 'config', 'view'] as $type) {
            try {
                Artisan::call($type.':clear');
            } catch (\Throwable $e) {
                // A non-writable bootstrap/cache must not stop the wizard.
                // The operator can still clear it manually via SSH.
            }
        }
    }

    // ─── Step 2: Server Requirements ───────────────────────────

    public function checkServer(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $freeBytes = disk_free_space(base_path()) ?: 0;

        $checks = [
            ['label' => 'Storage directory writable', 'passed' => is_writable(storage_path()), 'detail' => storage_path()],
            ['label' => 'Bootstrap cache writable', 'passed' => is_writable(base_path('bootstrap/cache')), 'detail' => base_path('bootstrap/cache')],
            ['label' => 'Free disk space >= 250 MB', 'passed' => $freeBytes >= 262144000, 'detail' => $this->formatBytes($freeBytes) . ' free'],
            ['label' => 'PHP memory limit >= 128M', 'passed' => $this->memoryLimitBytes() >= 134217728, 'detail' => ini_get('memory_limit') ?: 'unlimited'],
            ['label' => 'Vendor dependencies installed', 'passed' => file_exists(base_path('vendor/autoload.php')), 'detail' => file_exists(base_path('vendor/autoload.php')) ? 'vendor/ present' : 'run: composer install'],
            ['label' => '.env file present', 'passed' => file_exists(base_path('.env')), 'detail' => file_exists(base_path('.env')) ? '.env found' : 'copy .env.example to .env'],
        ];

        return $this->respond($checks);
    }

    // ─── Step 3: PHP Requirements ──────────────────────────────

    public function checkPhp(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $loaded = get_loaded_extensions();
        $missing = array_values(array_diff(self::REQUIRED_EXTENSIONS, $loaded));

        // Functions this application cannot run without. disable_functions only
        // matters when it removes one of these - a host may safely disable
        // exec, system and friends without affecting the installer.
        $requiredFunctions = [
            'symlink', 'parse_ini_file', 'putenv', 'getenv', 'mail',
            'file_put_contents', 'file_get_contents', 'is_writable',
            'scandir', 'mkdir', 'realpath', 'ini_set', 'ini_get',
        ];
        $disabledFunctions = array_values(array_filter(
            array_map('trim', explode(',', (string) ini_get('disable_functions'))),
            static fn ($f) => $f !== ''
        ));
        $blockedFunctions = array_values(array_intersect($requiredFunctions, $disabledFunctions));

        $checks = [
            ['label' => 'PHP version >= 8.3', 'passed' => version_compare(PHP_VERSION, '8.3.0', '>='), 'detail' => 'PHP ' . PHP_VERSION],
            ['label' => 'PHP version < 8.6', 'passed' => version_compare(PHP_VERSION, '8.6.0', '<'), 'detail' => 'PHP ' . PHP_VERSION],
            ['label' => 'Required PHP extensions', 'passed' => empty($missing), 'detail' => empty($missing) ? count(self::REQUIRED_EXTENSIONS) . ' loaded' : 'Missing: ' . implode(', ', $missing)],
            ['label' => 'Required functions not disabled',
             'passed' => empty($blockedFunctions),
             'detail' => empty($blockedFunctions)
                ? (empty($disabledFunctions)
                    ? 'disable_functions: none'
                    : count($disabledFunctions) . ' disabled, none required by this app')
                : 'Disabled: ' . implode(', ', $blockedFunctions)],
        ];

        return $this->respond($checks);
    }

    // ─── Step 4: MySQL Requirements ────────────────────────────

    public function checkMysql(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $hasPdo = extension_loaded('pdo_mysql');
        $hasMysqli = extension_loaded('mysqli');
        $hasMysqlnd = extension_loaded('mysqlnd');

        $checks = [
            ['label' => 'pdo_mysql extension loaded', 'passed' => $hasPdo, 'detail' => $hasPdo ? 'loaded' : 'REQUIRED — enable pdo_mysql'],
            ['label' => 'mysqli extension loaded', 'passed' => $hasMysqli, 'detail' => $hasMysqli ? 'loaded' : 'recommended for mysqldump'],
            ['label' => 'mysqlnd driver available', 'passed' => $hasMysqlnd, 'detail' => $hasMysqlnd ? 'loaded' : 'recommended'],
            ['label' => 'PDO drivers include mysql', 'passed' => in_array('mysql', $this->pdoDrivers(), true), 'detail' => implode(', ', $this->pdoDrivers())],
        ];

        return $this->respond($checks);
    }

    // ─── Step 5: Environment ───────────────────────────────────

    public function environment(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url'  => 'required|url',
            'app_env'  => 'required|in:local,production',
        ]);

        $appKey = config('app.key');

        if (empty($appKey)) {
            Artisan::call('key:generate', ['--force' => true]);
            $appKey = config('app.key');
        }

        $this->updateEnv([
            'APP_NAME' => '"' . $request->app_name . '"',
            'APP_ENV'  => $request->app_env,
            'APP_URL'  => $request->app_url,
            'APP_KEY'  => $appKey,
            'APP_DEBUG' => $request->app_env === 'production' ? 'false' : 'true',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Environment file written.',
            'app_key' => $appKey,
        ]);
    }

    // ─── Step 6: MySQL Connection ──────────────────────────────

    public function connectDatabase(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer|min:1|max:65535',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $pdo = $this->connect($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();

            $this->applyDatabaseConfig($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));

            return response()->json([
                'success' => true,
                'message' => 'Connected successfully.',
                'version' => $version,
            ]);
        } catch (\Exception $e) {
            return $this->fail('Connection failed: ' . $e->getMessage());
        }
    }

    // ─── Step 7: Database Validation ───────────────────────────

    public function validateDatabase(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $this->applyDatabaseConfig($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));
            DB::purge('mysql');

            $version = DB::selectOne('SELECT VERSION() as v')->v ?? 'unknown';
            $versionOk = version_compare($version, '8.0.0', '>=');

            // Required privileges for the installer to work on shared hosting.
            $grants = [];
            try {
                $rows = DB::select('SHOW GRANTS FOR CURRENT_USER()');
                $grants = implode('; ', array_map(fn ($r) => (string) (array_values((array) $r)[0] ?? ''), $rows));
            } catch (\Exception $e) {
                $grants = '';
            }

            $hasAll = stripos($grants, 'ALL PRIVILEGES') !== false;
            $hasCreate = $hasAll || stripos($grants, 'CREATE') !== false;
            $hasIndex = $hasAll || stripos($grants, 'INDEX') !== false;
            $hasAlter = $hasAll || stripos($grants, 'ALTER') !== false;
            $hasDrop = $hasAll || stripos($grants, 'DROP') !== false;
            $hasInsert = $hasAll || stripos($grants, 'INSERT') !== false;
            $hasSelect = $hasAll || stripos($grants, 'SELECT') !== false;

            $charset = DB::selectOne("SELECT @@character_set_database AS cs, @@collation_database AS col");
            $charsetOk = stripos((string) ($charset->cs ?? ''), 'utf8') === 0;

            $existing = count(DB::select('SHOW TABLES'));

            $checks = [
                ['label' => 'MySQL server version >= 8.0', 'passed' => $versionOk, 'detail' => 'MySQL ' . $version],
                ['label' => 'CREATE privilege', 'passed' => $hasCreate, 'detail' => $hasCreate ? 'granted' : 'required to create tables'],
                ['label' => 'INDEX privilege', 'passed' => $hasIndex, 'detail' => $hasIndex ? 'granted' : 'required for indexes'],
                ['label' => 'ALTER privilege', 'passed' => $hasAlter, 'detail' => $hasAlter ? 'granted' : 'required for migrations'],
                ['label' => 'DROP privilege', 'passed' => $hasDrop, 'detail' => $hasDrop ? 'granted' : 'required for migrations'],
                ['label' => 'INSERT privilege', 'passed' => $hasInsert, 'detail' => $hasInsert ? 'granted' : 'required for seed data'],
                ['label' => 'SELECT privilege', 'passed' => $hasSelect, 'detail' => $hasSelect ? 'granted' : 'required'],
                ['label' => 'Database charset is utf8mb4', 'passed' => $charsetOk, 'detail' => ($charset->cs ?? 'unknown') . ' / ' . ($charset->col ?? 'unknown')],
            ];

            return response()->json([
                'success' => collect($checks)->where('passed', false)->count() === 0,
                'checks'  => $checks,
                'failed'  => collect($checks)->where('passed', false)->count(),
                'message' => $existing > 0
                    ? "Database contains {$existing} existing table(s). Migrations will be applied on top."
                    : 'Database is empty and ready.',
                'existing_tables' => $existing,
            ]);
        } catch (\Exception $e) {
            return $this->fail('Database validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Convert the database (and every table in it) to utf8mb4.
     *
     * cPanel creates databases with MySQL's historical default of
     * latin1/latin1_swedish_ci, which cannot store 4-byte characters such as
     * emoji and silently truncates them. Rather than only reporting the
     * problem, the installer offers to fix it, because on shared hosting the
     * operator often cannot easily drop and recreate the database.
     */
    public function convertCharset(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $this->applyDatabaseConfig($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));
            DB::purge('mysql');

            $database = $request->db_database;
            $collation = 'utf8mb4_unicode_ci';

            // 1. Change the database default so tables created later inherit it.
            DB::statement("ALTER DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE {$collation}");

            // 2. Convert every existing table. latin1 -> utf8mb4 is a widening
            //    conversion: no data is lost for valid latin1 content.
            $tables = array_map(
                static fn ($r) => (string) (array_values((array) $r)[0] ?? ''),
                DB::select('SHOW TABLES')
            );

            $converted = 0;
            foreach ($tables as $table) {
                if ($table === '') {
                    continue;
                }
                DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE {$collation}");
                $converted++;
            }

            return response()->json([
                'success'   => true,
                'converted' => $converted,
                'message'   => "Database and {$converted} table(s) converted to utf8mb4. Re-run validation to confirm.",
            ]);
        } catch (\Exception $e) {
            return $this->fail('Charset conversion failed: ' . $e->getMessage(), 500);
        }
    }

    // ─── Step 8: Application Settings ──────────────────────────

    public function application(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'app_name'    => 'required|string|max:255',
            'app_url'     => 'required|url',
            'timezone'    => 'required|string|timezone',
            'locale'      => 'required|string|in:en',
            'date_format' => 'nullable|string|in:Y-m-d,d/m/Y,m/d/Y',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application settings accepted.',
        ]);
    }

    // ─── Step 9: Admin Account ─────────────────────────────────

    public function admin(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'admin_name'                 => 'required|string|max:255',
            'admin_email'                => 'required|email',
            'admin_password'             => 'required|string|min:8|confirmed',
            'admin_password_confirmation' => 'required|string',
        ]);

        $weak = ['password', '12345678', 'admin123', 'qwerty123'];
        if (in_array(strtolower($request->admin_password), $weak, true)) {
            return $this->fail('Please choose a stronger administrator password.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Administrator account accepted.',
        ]);
    }

    // ─── Step 10: Migrations ───────────────────────────────────

    public function migrate(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $this->applyDatabaseConfig($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));
            DB::purge('mysql');

            Artisan::call('migrate', ['--force' => true]);

            $tables = count(DB::select('SHOW TABLES'));

            return response()->json([
                'success' => true,
                'message' => "Migrations complete. {$tables} tables present.",
                'tables'  => $tables,
            ]);
        } catch (\Exception $e) {
            return $this->fail('Migration failed: ' . $e->getMessage(), 500);
        }
    }

    // ─── Step 11: Seed Core Data ───────────────────────────────

    public function seed(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $this->applyDatabaseConfig($request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']));
            DB::purge('mysql');

            Artisan::call('db:seed', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Core data seeded: roles, permissions, settings.',
            ]);
        } catch (\Exception $e) {
            return $this->fail('Seeding failed: ' . $e->getMessage(), 500);
        }
    }

    // ─── Step 12: Storage Checks ───────────────────────────────

    public function checkStorage(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        // Create the symlink here rather than only in finalize(). This check
        // runs at step 12 and finalize() runs at step 16, so demanding an
        // already-existing link made this row permanently red and blocked the
        // wizard. Creating it is idempotent and gives a real pass/fail signal.
        $linkExists = is_link(public_path('storage'));
        $linkDetail = $linkExists ? 'linked' : 'not linked';
        if (!$linkExists) {
            if (File::exists(public_path('storage'))) {
                // Something that is not a symlink occupies the path.
                $linkDetail = 'a non-symlink file/dir exists at public/storage - remove it';
            } else {
                try {
                    Artisan::call('storage:link');
                    $linkExists = is_link(public_path('storage'));
                    $linkDetail = $linkExists ? 'created' : 'could not be created';
                } catch (\Throwable $e) {
                    $linkDetail = 'could not be created: ' . $e->getMessage();
                }
            }
        }

        $checks = [
            ['label' => 'storage/ writable', 'passed' => is_writable(storage_path()), 'detail' => storage_path()],
            ['label' => 'storage/framework writable', 'passed' => is_writable(storage_path('framework')), 'detail' => storage_path('framework')],
            ['label' => 'storage/logs writable', 'passed' => is_writable(storage_path('logs')), 'detail' => storage_path('logs')],
            ['label' => 'storage/app writable', 'passed' => is_writable(storage_path('app')), 'detail' => storage_path('app')],
            ['label' => 'public/storage symlink', 'passed' => $linkExists, 'detail' => $linkDetail],
        ];

        return $this->respond($checks);
    }

    // ─── Step 13: Cache Checks ─────────────────────────────────

    public function checkCache(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $driver = config('cache.default');
        $writeOk = false;
        $detail = 'driver: ' . $driver;

        try {
            cache()->put('install_probe', 'ok', 10);
            $writeOk = cache()->get('install_probe') === 'ok';
            cache()->forget('install_probe');
        } catch (\Exception $e) {
            $detail = 'driver: ' . $driver . ' — ' . $e->getMessage();
        }

        $checks = [
            ['label' => 'Cache store configured', 'passed' => in_array($driver, ['database', 'file', 'array'], true), 'detail' => 'driver: ' . $driver],
            ['label' => 'Cache read/write works', 'passed' => $writeOk, 'detail' => $detail],
            ['label' => 'No Redis required', 'passed' => $driver !== 'redis', 'detail' => $driver === 'redis' ? 'Redis is optional, not required' : 'using ' . $driver],
        ];

        return $this->respond($checks);
    }

    // ─── Step 14: PWA Checks ───────────────────────────────────

    public function checkPwa(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $manifest = public_path('manifest.webmanifest');
        $sw = public_path('sw.js');
        $offline = public_path('offline.html');
        $icon192 = public_path('icons/icon-192x192.png');
        $icon512 = public_path('icons/icon-512x512.png');

        $manifestOk = false;
        $manifestDetail = 'not found';
        if (File::exists($manifest)) {
            $decoded = json_decode(File::get($manifest), true);
            $manifestOk = is_array($decoded)
                && isset($decoded['name'], $decoded['short_name'], $decoded['start_url'], $decoded['display'], $decoded['icons'])
                && is_array($decoded['icons']) && count($decoded['icons']) > 0;
            $manifestDetail = $manifestOk ? 'valid, ' . count($decoded['icons']) . ' icons' : 'invalid JSON or missing fields';
        }

        $checks = [
            ['label' => 'manifest.webmanifest present & valid', 'passed' => $manifestOk, 'detail' => $manifestDetail],
            ['label' => 'Service worker present', 'passed' => File::exists($sw), 'detail' => File::exists($sw) ? 'sw.js' : 'missing'],
            ['label' => 'Offline fallback page present', 'passed' => File::exists($offline), 'detail' => File::exists($offline) ? 'offline.html' : 'missing'],
            ['label' => '192x192 icon present', 'passed' => File::exists($icon192), 'detail' => File::exists($icon192) ? 'present' : 'missing'],
            ['label' => '512x512 icon present', 'passed' => File::exists($icon512), 'detail' => File::exists($icon512) ? 'present' : 'missing'],
        ];

        return $this->respond($checks);
    }

    // ─── Step 15: Security Checks ──────────────────────────────

    public function checkSecurity(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $appDebug = env('APP_DEBUG') === true || env('APP_DEBUG') === 'true';
        $appEnv = env('APP_ENV', 'production');
        $https = $request->isSecure() || $request->header('X-Forwarded-Proto') === 'https';
        $htaccess = File::exists(public_path('.htaccess'));

        $checks = [
            ['label' => 'APP_DEBUG is false', 'passed' => !$appDebug, 'detail' => $appDebug ? 'APP_DEBUG=true — disable in production' : 'APP_DEBUG=false'],
            // Advisory only: the operator chooses APP_ENV at step 5. Blocking
            // here would contradict their own choice and trap the install.
            ['label' => 'APP_ENV is production', 'passed' => $appEnv === 'production', 'warn' => true, 'detail' => 'APP_ENV=' . $appEnv . ($appEnv === 'production' ? '' : ' - set to production when you go live')],
            ['label' => '.env is git-ignored', 'passed' => $this->isGitIgnored('.env'), 'detail' => $this->isGitIgnored('.env') ? 'ignored' : 'NOT ignored — fix .gitignore'],
            ['label' => 'Document root .htaccess present', 'passed' => $htaccess, 'detail' => $htaccess ? 'public/.htaccess' : 'missing'],
            ['label' => 'Sensitive files blocked from web', 'passed' => $this->sensitiveFilesBlocked(), 'detail' => '.env, composer.*, artisan blocked'],
            // Advisory only. isSecure() is false behind many cPanel proxies and
            // load balancers even on real HTTPS sites, and SSL can be enabled
            // (cPanel AutoSSL) after installation.
            ['label' => 'HTTPS in use', 'passed' => $https, 'warn' => true, 'detail' => $https ? 'secure' : 'enable an SSL certificate (can be done after install)'],
            ['label' => 'Password hashing configured', 'passed' => config('hashing.driver') !== null, 'detail' => 'driver: ' . config('hashing.driver', 'bcrypt')],
        ];

        return $this->respond($checks);
    }

    // ─── Step 16: Finalization ─────────────────────────────────

    public function finalize(Request $request): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }

        $request->validate([
            // Database
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
            // Application
            'app_name'    => 'required|string|max:255',
            'app_url'     => 'required|url',
            'app_env'     => 'required|in:local,production',
            'timezone'    => 'required|string',
            'locale'      => 'required|string',
            'date_format' => 'nullable|string|in:Y-m-d,d/m/Y,m/d/Y',
            // Admin
            'admin_name'  => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_password_confirmation' => 'required|string',
        ]);

        try {
            $db = $request->only(['db_host', 'db_port', 'db_database', 'db_username', 'db_password']);

            // 1. Persist final .env
            $this->updateEnv([
                'APP_NAME'      => '"' . $request->app_name . '"',
                'APP_ENV'       => $request->app_env,
                'APP_DEBUG'     => $request->app_env === 'production' ? 'false' : 'true',
                'APP_URL'       => $request->app_url,
                'APP_TIMEZONE'  => $request->timezone,
                'APP_LOCALE'    => $request->locale,
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'       => $db['db_host'],
                'DB_PORT'       => $db['db_port'],
                'DB_DATABASE'   => $db['db_database'],
                'DB_USERNAME'   => $db['db_username'],
                'DB_PASSWORD'   => $db['db_password'] ?? '',
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE'    => 'database',
                'QUEUE_CONNECTION' => 'database',
            ]);

            // 2. Apply to the running process (config was loaded at bootstrap)
            $this->applyDatabaseConfig($db);
            Artisan::call('config:clear');
            DB::purge('mysql');

            // 3. Migrate + seed
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            // 4. Create / update the administrator (§31)
            $admin = User::where('email', 'admin@semizzy.com')->first();

            if ($admin) {
                $admin->update([
                    'name'     => $request->admin_name,
                    'email'    => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'status'   => 'active',
                ]);
            } else {
                $admin = User::create([
                    'name'     => $request->admin_name,
                    'email'    => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'status'   => 'active',
                ]);
                $admin->roles()->sync([1]);
            }

            // 5. Persist application settings
            Setting::set('app', 'name', $request->app_name, 'string');
            Setting::set('app', 'timezone', $request->timezone, 'string');
            Setting::set('app', 'locale', $request->locale, 'string');
            Setting::set('app', 'date_format', $request->date_format ?: 'Y-m-d', 'string');
            Setting::set('app', 'version', '2.0.0', 'string');

            // 6. Storage link
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // 7. Production caches
            //
            // Clear first: a previous install (or an older build) may have left
            // a cached route table that no longer matches routes/web.php, which
            // would make newly added routes return 404 until manually cleared.
            if ($request->app_env === 'production') {
                // Each call is individually guarded. Migrations and seeding have
                // already run by this point, so an unguarded failure here would
                // leave a half-installed application with no .installed marker
                // and no way to re-run the wizard. A cache that could not be
                // built is a performance detail, not a reason to abort.
                $cacheNotes = [];
                foreach (['route:clear', 'view:clear', 'config:cache', 'view:cache'] as $cmd) {
                    try {
                        Artisan::call($cmd);
                    } catch (\Throwable $e) {
                        $cacheNotes[] = $cmd . ' failed: ' . $e->getMessage();
                    }
                }
            }

            // NOTE: route:cache is deliberately NOT run here. Once a route cache
            // file exists, Laravel serves routes exclusively from it and ignores
            // routes/web.php entirely, so every future "git pull" that adds or
            // changes a route would 404 until someone runs "route:clear". The
            // route table for this application is small enough that caching it
            // is not worth the deployment footgun.

            // 8. Mark installed (§30 "prevent unauthorized re-running")
            File::put(storage_path('app/.installed'), json_encode([
                'installed_at' => now()->toIso8601String(),
                'version'      => '2.0.0',
                'admin_email'  => $request->admin_email,
            ], JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'SEMIZZY ONE CORE installed successfully.',
                'login_url' => rtrim($request->app_url, '/') . '/login',
            ]);
        } catch (\Exception $e) {
            return $this->fail('Installation failed: ' . $e->getMessage(), 500);
        }
    }

    // ─── Helpers ───────────────────────────────────────────────

    private function pdoDrivers(): array
    {
        return class_exists('PDO') ? \PDO::getAvailableDrivers() : [];
    }

    private function memoryLimitBytes(): int
    {
        $limit = ini_get('memory_limit');

        if ($limit === false || $limit === '' || $limit === '-1') {
            return PHP_INT_MAX;
        }

        return (int) preg_replace_callback('/^(-?\d+)(.?)/', function ($m) {
            return $m[1] * ['k' => 1024, 'm' => 1048576, 'g' => 1073741824][strtolower($m[2])] ?? 1;
        }, strtolower(trim($limit)));
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1024, 2) . ' KB';
    }

    /**
     * Open a standalone PDO connection for testing (never touches app config).
     */
    private function connect(array $db): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['db_host'],
            $db['db_port'],
            $db['db_database']
        );

        return new \PDO($dsn, $db['db_username'], $db['db_password'] ?? '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 5,
        ]);
    }

    /**
     * Push the user-supplied DB credentials into the running process so that
     * subsequent Artisan calls target the newly configured database rather
     * than whatever was loaded from .env at bootstrap.
     */
    private function applyDatabaseConfig(array $db): void
    {
        config([
            'database.connections.mysql.host'     => $db['db_host'],
            'database.connections.mysql.port'     => $db['db_port'],
            'database.connections.mysql.database' => $db['db_database'],
            'database.connections.mysql.username' => $db['db_username'],
            'database.connections.mysql.password' => $db['db_password'] ?? '',
        ]);
    }

    private function isGitIgnored(string $path): bool
    {
        if (!file_exists(base_path('.gitignore'))) {
            return false;
        }

        foreach (file(base_path('.gitignore'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if ($line === $path || $line === '/' . $path) {
                return true;
            }
        }

        return false;
    }

    private function sensitiveFilesBlocked(): bool
    {
        if (!File::exists(public_path('.htaccess'))) {
            return false;
        }

        $htaccess = File::get(public_path('.htaccess'));

        return str_contains($htaccess, '^\\.env')
            && str_contains($htaccess, 'composer\\.')
            && str_contains($htaccess, 'artisan');
    }

    private function updateEnv(array $values): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            File::put($envPath, '');
        }

        $env = File::get($envPath);
        $appended = [];

        foreach ($values as $key => $value) {
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $key . '=' . $value, $env, 1);
            } else {
                $appended[] = $key . '=' . $value;
            }
        }

        if (!empty($appended)) {
            $env = rtrim($env, "\n") . "\n" . implode("\n", $appended) . "\n";
        }

        File::put($envPath, $env);
    }
}
