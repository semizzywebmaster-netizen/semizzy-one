<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    /**
     * Block all install actions once the app is installed.
     */
    private function ensureNotInstalled(): ?\Illuminate\Http\JsonResponse
    {
        if (file_exists(storage_path('app/.installed'))) {
            return response()->json([
                'success' => false,
                'message' => 'Application is already installed.',
            ], 403);
        }

        return null;
    }

    public function index()
    {
        if ($guard = $this->ensureNotInstalled()) {
            return redirect('/');
        }

        return view('install.index');
    }

    public function checkRequirements()
    {
        if ($guard = $this->ensureNotInstalled()) {
            return $guard;
        }

        $checks = [
            'php' => [
                'label' => 'PHP Version >= 8.2',
                'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'detail' => 'PHP ' . PHP_VERSION,
            ],
            'extensions' => $this->checkExtensions(),
            'storage_writable' => [
                'label' => 'Storage directory writable',
                'passed' => is_writable(storage_path()),
                'detail' => storage_path(),
            ],
            'cache_writable' => [
                'label' => 'Bootstrap cache writable',
                'passed' => is_writable(base_path('bootstrap/cache')),
                'detail' => base_path('bootstrap/cache'),
            ],
            'env_exists' => [
                'label' => 'Environment file exists',
                'passed' => file_exists(base_path('.env')),
                'detail' => file_exists(base_path('.env')) ? '.env found' : '.env not found',
            ],
            'app_key' => [
                'label' => 'Application key set',
                'passed' => !empty(config('app.key')),
                'detail' => !empty(config('app.key')) ? 'Key set' : 'Key not set',
            ],
        ];

        return response()->json($checks);
    }

    public function testDatabase(Request $request)
    {
        if ($guard = $this->ensureNotInstalled()) {
            return $guard;
        }

        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            config()->set('database.connections.install_test', [
                'driver' => 'mysql',
                'host' => $request->host,
                'port' => $request->port,
                'database' => $request->database,
                'username' => $request->username,
                'password' => $request->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            $pdo = DB::connection('install_test')->getPdo();
            $version = DB::connection('install_test')->selectOne('SELECT VERSION() as version');

            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'version' => $version->version ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function install(Request $request)
    {
        if ($guard = $this->ensureNotInstalled()) {
            return $guard;
        }

        $request->validate([
            // Database
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'required|string',
            // Application
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            // Admin
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Step 1: Update .env
            $this->updateEnv([
                'APP_NAME' => '"' . $request->app_name . '"',
                'APP_URL' => $request->app_url,
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password,
            ]);

            // Step 2: Apply new DB settings to the running process
            // (the config was loaded at bootstrap from the old .env)
            config([
                'database.connections.mysql.host'     => $request->db_host,
                'database.connections.mysql.port'     => $request->db_port,
                'database.connections.mysql.database' => $request->db_database,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);

            Artisan::call('config:clear');
            DB::purge('mysql');

            // Step 3: Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Step 4: Seed database
            Artisan::call('db:seed', ['--force' => true]);

            // Step 5: Update admin credentials
            $admin = User::where('email', 'admin@semizzy.com')->first();
            if ($admin) {
                $admin->update([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                ]);
            }

            // Step 6: Update app settings
            Setting::set('app', 'name', $request->app_name, 'string');

            // Step 7: Create storage link
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // Step 8: Cache config for production
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            // Step 9: Mark as installed
            $installedData = [
                'installed_at' => now()->toIso8601String(),
                'version' => '2.0.0',
                'admin_email' => $request->admin_email,
            ];
            File::put(storage_path('app/.installed'), json_encode($installedData, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Installation complete!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function checkExtensions(): array
    {
        $required = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo_mysql', 'tokenizer', 'xml', 'zip', 'gd', 'intl', 'sodium'];
        $loaded = get_loaded_extensions();
        $missing = array_diff($required, $loaded);

        return [
            'label' => 'Required PHP Extensions',
            'passed' => empty($missing),
            'detail' => empty($missing)
                ? count($required) . ' extensions loaded'
                : 'Missing: ' . implode(', ', $missing),
            'missing' => $missing,
        ];
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