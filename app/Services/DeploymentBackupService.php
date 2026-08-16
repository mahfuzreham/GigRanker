<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deployment;
use App\Models\DeploymentBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeploymentBackupService
{
    public function createDatabaseBackup(?Deployment $deployment = null, string $environment = 'production'): DeploymentBackup
    {
        $backup = DeploymentBackup::query()->create([
            'backup_id' => (string) str()->uuid(),
            'deployment_id' => $deployment?->id,
            'environment' => $environment,
            'type' => 'database',
            'status' => 'running',
            'storage_disk' => 'local',
            'started_at' => now(),
        ]);

        $started = microtime(true);

        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $database = (string) config('database.connections.'.$driver.'.database');

            if ($driver !== 'mysql' || $database === '') {
                throw new RuntimeException('Automatic deployment backup currently supports a configured MySQL database only.');
            }

            $storage = Storage::disk('local');
            $directory = 'deployment-backups/'.now()->format('Y/m/d');
            $filename = $directory.'/'.$backup->backup_id.'.sql';
            $dump = $this->buildMysqlDump($connection, $database);

            $storage->put($filename, $dump);
            $size = $storage->size($filename);
            $checksum = hash('sha256', $dump);

            $backup->update([
                'status' => 'completed',
                'path' => $filename,
                'size_bytes' => $size,
                'checksum' => $checksum,
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'message' => 'Database backup completed successfully.',
            ]);
        } catch (Throwable $exception) {
            $backup->update([
                'status' => 'failed',
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $backup->fresh();
    }

    private function buildMysqlDump($connection, string $database): string
    {
        $tables = $connection->select('SHOW TABLES');
        $key = 'Tables_in_'.strtolower($database);
        $sql = "-- GigRanker deployment backup\n-- Database: ".str_replace(['\\', "\n", "\r"], ['', ' ', ' '], $database)."\n-- Created: ".now()->toIso8601String()."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableRow) {
            $table = (string) ($tableRow->{$key} ?? array_values((array) $tableRow)[0] ?? '');
            if ($table === '') {
                continue;
            }

            $quoted = '`'.str_replace('`', '``', $table).'`';
            $create = $connection->selectOne('SHOW CREATE TABLE '.$quoted);
            $createSql = (string) (array_values((array) $create)[1] ?? '');
            if ($createSql !== '') {
                $sql .= 'DROP TABLE IF EXISTS '.$quoted.";\n".$createSql.";\n\n";
            }

            $rows = $connection->table($table)->get();
            foreach ($rows as $row) {
                $columns = array_keys((array) $row);
                $values = array_map(fn ($value): string => $value === null ? 'NULL' : $connection->getPdo()->quote((string) $value), array_values((array) $row));
                $sql .= 'INSERT INTO '.$quoted.' (`'.implode('`,`', array_map(fn (string $column): string => str_replace('`', '``', $column), $columns)).'`) VALUES ('.implode(',', $values).");\n";
            }
            $sql .= "\n";
        }

        return $sql."SET FOREIGN_KEY_CHECKS=1;\n";
    }
}
