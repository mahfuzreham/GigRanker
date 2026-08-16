<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionHealthService;
use Illuminate\Console\Command;

class ProductionHealthCommand extends Command
{
    protected $signature = 'gigranker:health {--json : Output JSON instead of a table}';

    protected $description = 'Run production readiness health checks';

    public function handle(ProductionHealthService $service): int
    {
        $checks = $service->check();
        $failed = collect($checks)->contains(fn (array $check): bool => ($check['status'] ?? 'failed') === 'failed');

        if ($this->option('json')) {
            $this->line((string) json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Check', 'Status', 'Details'],
                collect($checks)->map(fn (array $check, string $name): array => [
                    $name,
                    $check['status'] ?? 'failed',
                    collect($check)->except('status')->map(fn ($value): string => is_array($value) ? implode(', ', $value) : (string) $value)->implode(' | '),
                ])->all(),
            );
        }

        if ($failed) {
            $this->error('Production health checks failed. Do not deploy until the failing checks are resolved.');
            return self::FAILURE;
        }

        $this->info('Production health checks passed.');
        return self::SUCCESS;
    }
}
