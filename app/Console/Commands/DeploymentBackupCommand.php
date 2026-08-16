<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Services\DeploymentBackupService;
use Illuminate\Console\Command;
use Throwable;

class DeploymentBackupCommand extends Command
{
    protected $signature = 'gigranker:backup
        {action : create|list}
        {--deployment= : Deployment UUID to associate with the backup}
        {--environment=production : Deployment environment}';

    protected $description = 'Create or inspect pre-deployment database backups';

    public function handle(DeploymentBackupService $service): int
    {
        return match ($this->argument('action')) {
            'create' => $this->create($service),
            'list' => $this->listBackups(),
            default => $this->invalidAction(),
        };
    }

    private function create(DeploymentBackupService $service): int
    {
        $deployment = null;
        if (is_string($this->option('deployment')) && $this->option('deployment') !== '') {
            $deployment = Deployment::query()->where('deployment_id', $this->option('deployment'))->first();
            if ($deployment === null) {
                $this->error('Deployment not found.');
                return self::INVALID;
            }
        }

        try {
            $backup = $service->createDatabaseBackup($deployment, (string) $this->option('environment'));
        } catch (Throwable $exception) {
            $this->error('Backup failed: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Backup completed: '.$backup->backup_id);
        $this->line('Path: '.($backup->path ?? '-'));
        $this->line('SHA-256: '.($backup->checksum ?? '-'));
        return self::SUCCESS;
    }

    private function listBackups(): int
    {
        $backups = \App\Models\DeploymentBackup::query()->latest('id')->limit(20)->get();
        if ($backups->isEmpty()) {
            $this->line('No deployment backups found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Environment', 'Type', 'Status', 'Size', 'Started', 'Finished'],
            $backups->map(fn ($backup): array => [
                $backup->backup_id,
                $backup->environment,
                $backup->type,
                $backup->status,
                $backup->size_bytes !== null ? number_format((int) $backup->size_bytes).' bytes' : '-',
                (string) $backup->started_at,
                (string) ($backup->finished_at ?? '-'),
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function invalidAction(): int
    {
        $this->error('Unknown action. Use create or list.');
        return self::INVALID;
    }
}
