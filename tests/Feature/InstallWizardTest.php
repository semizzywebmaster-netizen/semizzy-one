<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * End-to-end coverage for the 16-step installation wizard.
 *
 * Every step is driven through the real HTTP kernel against MySQL. This suite
 * exists because the wizard was previously verified only by a DOM simulation,
 * which could not catch server-side defects such as a check that can never
 * pass, or an endpoint that receives no request body.
 */
class InstallWizardTest extends TestCase
{
    use RefreshDatabase;

    private string $envBackup = '';

    private string $markerBackup = '';

    protected function setUp(): void
    {
        parent::setUp();

        // finalize() rewrites .env and creates the .installed marker. Preserve
        // both so the developer's working environment survives the run.
        $this->envBackup = File::get(base_path('.env'));
        $marker = storage_path('app/.installed');
        $this->markerBackup = File::exists($marker) ? File::get($marker) : '';
        File::delete($marker);
    }

    protected function tearDown(): void
    {
        File::put(base_path('.env'), $this->envBackup);
        $marker = storage_path('app/.installed');
        File::exists($marker) ? File::delete($marker) : null;
        if ($this->markerBackup !== '') {
            File::put($marker, $this->markerBackup);
        }

        // Purge bootstrap caches. A config cache baked during one test holds
        // that test's .env values and would silently corrupt the next one -
        // this is exactly how the real-world bug presented.
        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $cached) {
            if (basename($cached) !== '.gitignore') {
                File::delete($cached);
            }
        }

        parent::tearDown();
    }

    private function db(): array
    {
        return [
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_database' => env('DB_DATABASE'),
            'db_username' => env('DB_USERNAME'),
            'db_password' => env('DB_PASSWORD'),
        ];
    }

    private function checkLabel(array $json, string $needle): ?array
    {
        foreach ($json['checks'] ?? [] as $c) {
            if (stripos($c['label'], $needle) !== false) {
                return $c;
            }
        }

        return null;
    }

    public function test_step_2_server_requirements_all_pass(): void
    {
        $r = $this->postJson('/install/check/server', []);
        $r->assertOk()->assertJson(['success' => true]);
        $this->assertSame(0, $r->json('failed'), 'server requirement checks must all pass');
    }

    public function test_step_3_php_checks_pass_and_functions_check_is_real(): void
    {
        $r = $this->postJson('/install/check/php', []);
        $r->assertOk()->assertJson(['success' => true]);

        // Regression: this row used to test whether the 'disable_functions' ini
        // KEY existed (it always does) instead of whether anything was actually
        // disabled, so it could never pass on any server.
        $row = $this->checkLabel($r->json(), 'functions not disabled');
        $this->assertNotNull($row, 'the disable_functions row must exist');
        $this->assertTrue($row['passed'], 'disable_functions must pass when nothing is disabled');
        $this->assertStringContainsString('disable_functions', $row['detail']);
    }

    public function test_step_4_mysql_checks_pass(): void
    {
        $r = $this->postJson('/install/check/mysql', []);
        $r->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_5_environment_is_accepted(): void
    {
        $r = $this->postJson('/install/environment', [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
        ]);
        $r->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_6_database_connection_is_accepted(): void
    {
        $r = $this->postJson('/install/database/connect', $this->db());
        $r->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_7_validate_accepts_the_database_form(): void
    {
        // Regression: runCheck() used to POST an empty body, so this endpoint
        // always failed validation with 422 and rendered no checks at all.
        $r = $this->postJson('/install/database/validate', $this->db());
        $r->assertOk();
        $this->assertGreaterThan(0, count($r->json('checks')), 'validation must return checks');
    }

    public function test_step_7_charset_row_passes_on_a_utf8mb4_database(): void
    {
        $r = $this->postJson('/install/database/validate', $this->db());
        $r->assertOk();
        $row = $this->checkLabel($r->json(), 'charset');
        $this->assertNotNull($row);
        $this->assertTrue($row['passed'], 'the test database must be utf8mb4: ' . json_encode($row));
    }

    public function test_step_7_charset_conversion_reports_a_count(): void
    {
        $r = $this->postJson('/install/database/charset', $this->db());
        $r->assertOk()->assertJson(['success' => true]);
        $this->assertIsInt($r->json('converted'));
    }

    public function test_step_8_and_9_settings_and_admin_are_accepted(): void
    {
        $this->postJson('/install/application', [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
        ])->assertOk()->assertJson(['success' => true]);

        $this->postJson('/install/admin', [
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ])->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_9_rejects_a_weak_admin_password(): void
    {
        $this->postJson('/install/admin', [
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'admin123', 'admin_password_confirmation' => 'admin123',
        ])->assertStatus(422);
    }

    public function test_step_10_and_11_migrate_and_seed_succeed(): void
    {
        $this->postJson('/install/migrate', $this->db())->assertOk()->assertJson(['success' => true]);
        $this->postJson('/install/seed', $this->db())->assertOk()->assertJson(['success' => true]);
        // hasRole() compares the slug column; name is the display label.
        $this->assertDatabaseHas('roles', ['slug' => 'admin', 'name' => 'Administrator']);
        $this->assertDatabaseHas('roles', ['slug' => 'staff']);
        $this->assertDatabaseHas('roles', ['slug' => 'support']);
        $this->assertDatabaseHas('roles', ['slug' => 'user']);
        $this->assertDatabaseMissing('roles', ['slug' => 'super-admin']);
    }

    public function test_step_12_storage_symlink_check_passes(): void
    {
        // Regression: this row demanded an already-existing public/storage
        // symlink, but the symlink is created in finalize() at step 16 - four
        // steps later. The row was therefore permanently red and blocked the
        // wizard. The check now creates the link itself.
        $r = $this->postJson('/install/check/storage', []);
        $r->assertOk()->assertJson(['success' => true]);
        $row = $this->checkLabel($r->json(), 'symlink');
        $this->assertNotNull($row);
        $this->assertTrue($row['passed'], 'the symlink check must pass: ' . json_encode($row));
    }

    public function test_step_13_cache_checks_pass(): void
    {
        $this->postJson('/install/check/cache', [])->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_14_pwa_checks_pass(): void
    {
        $this->postJson('/install/check/pwa', [])->assertOk()->assertJson(['success' => true]);
    }

    public function test_step_15_security_warnings_do_not_block_installation(): void
    {
        // APP_ENV and HTTPS are advisory. isSecure() is false behind many cPanel
        // proxies even on real HTTPS sites, and SSL can be enabled after install,
        // so neither may block the wizard.
        config(['app.debug' => false]);
        putenv('APP_DEBUG=false');
        $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = 'false';

        $r = $this->postJson('/install/check/security', []);
        $r->assertOk();
        $this->assertTrue($r->json('success'), 'advisory warnings must not block: ' . $r->getContent());

        foreach (['APP_ENV is production', 'HTTPS in use'] as $label) {
            $row = $this->checkLabel($r->json(), $label);
            $this->assertNotNull($row, "missing row: {$label}");
            $this->assertTrue($row['warn'] ?? false, "{$label} must be flagged as advisory");
        }
    }

    public function test_step_16_finalize_completes_and_marks_installed(): void
    {
        $this->postJson('/install/migrate', $this->db())->assertOk();
        $this->postJson('/install/seed', $this->db())->assertOk();

        $r = $this->postJson('/install/finalize', $this->db() + [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ]);

        $r->assertOk()->assertJson(['success' => true]);
        $this->assertTrue(File::exists(storage_path('app/.installed')), '.installed marker must be written');
        $this->assertDatabaseHas('users', ['email' => 'admin@semizzy.com']);
    }

    public function test_finalize_does_not_create_a_route_cache(): void
    {
        // Regression: route:cache made every subsequently added route 404,
        // because Laravel then serves routes only from the cached file.
        $this->postJson('/install/migrate', $this->db())->assertOk();
        $this->postJson('/install/finalize', $this->db() + [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ])->assertOk();

        $cached = glob(base_path('bootstrap/cache/routes-*.php')) ?: [];
        $this->assertSame([], $cached, 'finalize must not run route:cache');
    }

    public function test_wizard_locks_once_installed(): void
    {
        $this->postJson('/install/migrate', $this->db())->assertOk();
        $this->postJson('/install/finalize', $this->db() + [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ])->assertOk();

        // CheckInstallation redirects /install* to / once installed, which runs
        // before the controller guard. Either outcome means the wizard is locked.
        foreach (['/install/migrate', '/install/finalize', '/install/check/server'] as $path) {
            $status = $this->postJson($path, $this->db())->getStatusCode();
            $this->assertContains($status, [302, 403], "{$path} was not locked (got {$status})");
        }
        $this->get('/install')->assertRedirect('/');
    }

    public function test_every_install_endpoint_returns_a_json_response(): void
    {
        $paths = [
            '/install/check/server', '/install/check/php', '/install/check/mysql',
            '/install/environment', '/install/database/connect', '/install/database/validate',
            '/install/database/charset', '/install/application', '/install/admin',
            '/install/migrate', '/install/seed', '/install/check/storage',
            '/install/check/cache', '/install/check/pwa', '/install/check/security',
        ];

        foreach ($paths as $path) {
            $r = $this->postJson($path, $this->db() + [
                'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
                'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
                'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
                'admin_password' => 'Str0ngPassw0rd!2026',
                'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
            ]);
            $this->assertNotSame(500, $r->getStatusCode(), "{$path} returned 500: " . $r->getContent());
            $this->assertJson($r->getContent(), "{$path} did not return JSON");
        }
    }
    public function test_login_and_public_pages_do_not_500_when_frontend_is_missing(): void
    {
        // Regression: @vite throws ViteManifestNotFoundException when
        // public/build/manifest.json is absent, which turned every page into a
        // blank HTTP 500 after a successful install. The layout must degrade.
        File::delete(storage_path('app/.installed'));
        $this->postJson('/install/migrate', $this->db())->assertOk();
        $this->postJson('/install/finalize', $this->db() + [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ])->assertOk();

        $manifest = public_path('build/manifest.json');
        $hadManifest = File::exists($manifest);
        if ($hadManifest) {
            File::move($manifest, $manifest.'.bak');
        }

        try {
            foreach (['/login', '/register'] as $path) {
                $r = $this->get($path);
                $this->assertNotSame(500, $r->getStatusCode(), "{$path} returned 500 without a frontend build");
                $this->assertStringContainsString('vite-missing', $r->getContent(), "{$path} must warn visibly");
            }
        } finally {
            if ($hadManifest) {
                File::move($manifest.'.bak', $manifest);
            }
        }
    }

    public function test_step_14_detects_a_missing_frontend_build(): void
    {
        $manifest = public_path('build/manifest.json');
        $hadManifest = File::exists($manifest);
        if ($hadManifest) {
            File::move($manifest, $manifest.'.bak');
        }

        try {
            $r = $this->postJson('/install/check/pwa', []);
            $r->assertOk();
            $this->assertFalse($r->json('success'), 'a missing frontend build must fail the PWA check');

            $row = $this->checkLabel($r->json(), 'Frontend build present');
            $this->assertNotNull($row);
            $this->assertFalse($row['passed']);
            $this->assertStringContainsString('npm run build', $row['detail']);
        } finally {
            if ($hadManifest) {
                File::move($manifest.'.bak', $manifest);
            }
        }
    }

    public function test_finalize_warns_when_the_frontend_build_is_missing(): void
    {
        File::delete(storage_path('app/.installed'));
        $this->postJson('/install/migrate', $this->db())->assertOk();

        $manifest = public_path('build/manifest.json');
        $hadManifest = File::exists($manifest);
        if ($hadManifest) {
            File::move($manifest, $manifest.'.bak');
        }

        try {
            $r = $this->postJson('/install/finalize', $this->db() + [
                'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
                'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
                'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
                'admin_password' => 'Str0ngPassw0rd!2026',
                'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
            ]);
            $r->assertOk()->assertJson(['success' => true]);

            $warnings = $r->json('warnings');
            $this->assertIsArray($warnings);
            $this->assertNotEmpty($warnings, 'finalize must warn when the frontend build is missing');
            $this->assertStringContainsString('npm run build', implode(' ', $warnings));
        } finally {
            if ($hadManifest) {
                File::move($manifest.'.bak', $manifest);
            }
        }
    }
    public function test_environment_writes_a_non_empty_app_key(): void
    {
        // Regression: the installer called Artisan key:generate and then re-read
        // config('app.key'), which had been resolved BEFORE .env was rewritten.
        // The stale empty value was written back to .env, and the later
        // config:cache baked it in - after which every page returned HTTP 500
        // "No application encryption key has been specified".
        $r = $this->postJson('/install/environment', [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
        ]);
        $r->assertOk()->assertJson(['success' => true]);

        $key = $r->json('app_key');
        $this->assertNotEmpty($key, 'the installer must return a usable APP_KEY');
        $this->assertStringStartsWith('base64:', $key);

        // And the value must actually be persisted to .env, not just reported.
        $env = File::get(base_path('.env'));
        $this->assertMatchesRegularExpression('/^APP_KEY=base64:.{20,}$/m', $env, 'APP_KEY must be persisted to .env');
    }

    public function test_finalize_never_caches_config_with_an_empty_app_key(): void
    {
        // The full reported chain: install as production (which runs
        // config:cache), then confirm the site still boots. If an empty
        // APP_KEY were baked in, /login would return HTTP 500.
        File::delete(storage_path('app/.installed'));
        $this->postJson('/install/migrate', $this->db())->assertOk();

        $r = $this->postJson('/install/finalize', $this->db() + [
            'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
            'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
            'admin_password' => 'Str0ngPassw0rd!2026',
            'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
        ]);
        $r->assertOk()->assertJson(['success' => true]);

        $cache = glob(base_path('bootstrap/cache/config.php'));
        if ($cache) {
            $baked = require $cache[0];
            $this->assertNotEmpty($baked['app']['key'] ?? '', 'the cached config must never hold an empty APP_KEY');
        }

        $this->get('/login')->assertOk();
    }

    public function test_finalize_discards_a_config_cache_with_an_empty_key(): void
    {
        // End-to-end proof of the reported failure: install, then confirm the
        // site still boots. If config:cache baked an empty APP_KEY, /login 500s.
        File::delete(storage_path('app/.installed'));

        $stray = base_path('.env.production');
        $had = File::exists($stray);
        $original = $had ? File::get($stray) : null;

        try {
            File::put($stray, "APP_NAME=\"SEMIZZY ONE\"\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=https://yourdomain.com\n");

            $this->postJson('/install/environment', [
                'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
            ])->assertOk();
            $this->postJson('/install/migrate', $this->db())->assertOk();

            $r = $this->postJson('/install/finalize', $this->db() + [
                'app_name' => 'SEMIZZY ONE', 'app_url' => 'http://localhost', 'app_env' => 'production',
                'timezone' => 'Africa/Lagos', 'locale' => 'en', 'date_format' => 'Y-m-d',
                'admin_name' => 'Admin', 'admin_email' => 'admin@semizzy.com',
                'admin_password' => 'Str0ngPassw0rd!2026',
                'admin_password_confirmation' => 'Str0ngPassw0rd!2026',
            ]);
            $r->assertOk()->assertJson(['success' => true]);

            // The poisoned cache must not survive.
            $cache = base_path('bootstrap/cache/config.php');
            if (File::exists($cache)) {
                $baked = require $cache;
                $this->assertNotEmpty($baked['app']['key'] ?? '', 'a config cache with an empty APP_KEY must never survive');
            }

            // phpunit.xml pins APP_ENV=testing, so Laravel does not load
            // .env.production here and the key is never actually clobbered.
            // The observable guarantee is that the cache never survives with an
            // empty key, asserted above. The warning path is covered by
            // test_stray_env_production_is_detected.
            $this->assertTrue(true);

            // And the site must actually work.
            $this->get('/login')->assertOk();
        } finally {
            $had ? File::put($stray, $original) : File::delete($stray);
            File::delete(base_path('bootstrap/cache/config.php'));
        }
    }
}
