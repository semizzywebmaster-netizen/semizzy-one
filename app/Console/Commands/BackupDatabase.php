<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--output= : Output filename}';
    protected $description = 'Create a MySQL database backup (shared-hosting compatible)';

    public function handle(): int
    {
        $this->info('Starting database backup...');

        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = $this->option('output') ?: 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = "{$backupDir}/{$filename}";

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Try mysqldump first
        $command = "mysqldump -h {$dbHost} -P {$dbPort} -u {$dbUser}";
        if ($dbPass) {
            $command .= " -p'" . addslashes($dbPass) . "'";
        }
        $command .= " --single-transaction --routines --triggers {$dbName} > " . escapeshellarg($path) . " 2>&1";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Fallback: PHP-based backup
            $this->warn('mysqldump not available, using PHP fallback...');
            return $this->phpBackup($path, $dbName);
        }

        $size = File::size($path);
        $this->info("Backup created: {$filename} (" . $this->formatBytes($size) . ")");

        AuditService::log('backup.created', 'backup', null, 'success', [
            'filename' => $filename,
            'size' => $size,
        ]);

        return self::SUCCESS;
    }

    private function phpBackup(string $path, string $dbName): int
    {
        try {
            $tables = \DB::select("SHOW TABLES");
            $key = "Tables_in_{$dbName}";
            $sql = "-- SEMIZZY ONE Database Backup\n";
            $sql .= "-- Generated: " . now()->toIso8601String() . "\n";
            $sql .= "-- Database: {$dbName}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$key;

                // Get CREATE TABLE statement
                $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTable)) {
                    $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                }

                // Get data
                $rows = \DB::select("SELECT * FROM `{$tableName}`");
                if (!empty($rows)) {
                    $columns = array_keys((array) $rows[0]);
                    $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";

                    $valueRows = [];
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            if ($val === null) return 'NULL';
                            return "'" . addslashes($val) . "'";
                        }, array_values((array) $row));
                        $valueRows[] = '(' . implode(', ', $values) . ')';
                    }
                    $sql .= implode(",\n", $valueRows) . ";\n\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            File::put($path, $sql);

            $size = File::size($path);
            $this->info("PHP backup created (" . $this->formatBytes($size) . ")");

            AuditService::log('backup.created', 'backup', null, 'success', [
                'filename' => basename($path),
                'size' => $size,
                'method' => 'php',
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            AuditService::log('backup.failed', 'backup', null, 'failure', [
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}