<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProductionHealthCommand extends Command
{
    protected $signature = 'gigranker:health {--json : Output JSON instead of a table} {--notify-admin : Email the configured admin when checks fail}';

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

            if ($this->option('notify-admin')) {
                $this->notifyAdmin($checks);
            }

            return self::FAILURE;
        }

        $this->info('Production health checks passed.');
        return self::SUCCESS;
    }

    private function notifyAdmin(array $checks): void
    {
        $recipient = trim((string) env('ADMIN_EMAIL', ''));
        if ($recipient === '') {
            $this->warn('ADMIN_EMAIL is not configured; health failure was not emailed.');
            return;
        }

        $failed = collect($checks)
            ->filter(fn (array $check): bool => ($check['status'] ?? 'failed') === 'failed')
            ->map(fn (array $check, string $name): string => $name.': '.((string) ($check['message'] ?? 'failed')))
            ->implode("\n");

        try {
            Mail::raw(
                "GigRanker production health check failed.\n\nEnvironment: ".app()->environment()."\nURL: ".config('app.url')."\n\nFailed checks:\n".$failed,
                function ($message) use ($recipient): void {
                    $message->to($recipient)->subject('GigRanker Production Health Alert');
                },
            );
            $this->warn('Admin health alert sent to '.$recipient.'.');
        } catch (Throwable $exception) {
            $this->warn('Unable to send admin health alert: '.$exception->getMessage());
        }
    }
}
