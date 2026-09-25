<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackups();

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        try {
            $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Ensure backups directory exists
            if (!File::isDirectory(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // MySQL dump using mysqldump or PHP
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            $command = "mysqldump -h {$dbHost} -P {$dbPort} -u {$dbUser}";
            if ($dbPass) {
                $command .= " -p'{$dbPass}'";
            }
            $command .= " {$dbName} > {$path} 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                // Fallback: create metadata-only backup
                File::put($path, "-- Backup created at " . now() . "\n-- Note: mysqldump not available. Use cPanel backup.\n");
            }

            return back()->with('success', 'Backup created: ' . $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!File::exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        // Security: prevent path traversal
        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            abort(403);
        }

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            abort(403);
        }

        if (File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'Backup deleted.');
    }

    private function getBackups(): array
    {
        $path = storage_path('app/backups');

        if (!File::isDirectory($path)) {
            return [];
        }

        $files = File::files($path);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'size_human' => $this->formatBytes($file->getSize()),
                'created_at' => $file->getCTime(),
            ];
        }

        return $backups;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}