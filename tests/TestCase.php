<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mark application as installed for tests
        $installedFile = storage_path('app/.installed');
        if (!file_exists(dirname($installedFile))) {
            mkdir(dirname($installedFile), 0755, true);
        }
        file_put_contents($installedFile, json_encode([
            'installed_at' => now()->toIso8601String(),
            'version' => '2.0.0',
        ]));
    }

    protected function tearDown(): void
    {
        // Clean up installed marker
        $installedFile = storage_path('app/.installed');
        if (file_exists($installedFile)) {
            @unlink($installedFile);
        }

        parent::tearDown();
    }
}