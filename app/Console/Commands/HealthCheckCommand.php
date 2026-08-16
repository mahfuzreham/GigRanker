<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckCommand extends Command
{
    protected $signature = 'gigranker:health
        {--json : Return machine-readable JSON}
        {--notify-admin : Email ADMIN_EMAIL when the check fails}';

    protected $description = 'Check production application readiness and dependencies';

    public function handle(): int
    {
        $checks = [];

        $checks['app_key'] = $this->check('Application key', static fn (): bool => is_string(config('app.key')) && config('app.key') !== '');
        $checks['debug'] = $this->check('Debug disabled', static fn (): bool => config('app.debug') === false);
        $checks['url'] = $this->check('Application URL', static fn (): bool => is_string(config('app.url')) && preg_match('#^https?://#i', config('app.url')) === 1);
        $checks['database'] = $this->check('Database connection', static function (): bool {
            DB::connection()->getPdo();
            DB::select('select 1');
            return true;
        });
        $checks['cache'] = $this->check('Cache read/write', static function (): bool {
            $key = 'gigranker:health:'.bin2hex(random_bytes(8));
            Cache::put($key, 'ok', 60);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);
            return $ok;
        });
        $checks['storage'] = $this->check('Local storage read/write', static function (): bool {
            $disk = Storage::disk('local');
            $path = 'health-check/'.bin2hex(random_bytes(8)).'.txt';
            $disk->put($path, 'ok');
            $ok = $disk->get($path) === 'ok';
            $disk->delete($path);
            return $ok;
        });
        $checks['zip'] = $this->check('ZipArchive extension', static fn (): bool => class_exists(\ZipArchive::class));

        $failed = array_keys(array_filter($checks, static fn (array $check): bool => $check['status'] !== 'ok'));
        $healthy = $failed === [];
        $payload = [
            'healthy' => $healthy,
            'checked_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'checks' => $checks,
            'failed_checks' => $failed,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info($healthy ? 'GigRanker health check: PASS' : 'GigRanker health check: FAIL');
            foreach ($checks as $name => $check) {
                $this->line(sprintf('%s: %s%s', $name, $check['status'], $check['message'] !== '' ? ' - '.$check['message'] : ''));
            }
        }

        if (! $healthy && $this->option('notify-admin')) {
            $this->notifyAdmin($payload);
        }

        return $healthy ? self::SUCCESS : self::FAILURE;
    }

    /** @return array{status:string,message:string} */
    private function check(string $name, callable $callback): array
    {
        try {
            return ['status' => $callback() ? 'ok' : 'failed', 'message' => $callback() ? '' : $name.' check returned false'];
        } catch (Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    /** @param array<string,mixed> $payload */
    private function notifyAdmin(array $payload): void
    {
        $recipient = config('mail.admin_email', env('ADMIN_EMAIL'));
        if (! is_string($recipient) || $recipient === '') {
            return;
        }

        try {
            Mail::raw((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), static function ($message) use ($recipient): void {
                $message->to($recipient)->subject('GigRanker production health check failed');
            });
        } catch (Throwable $exception) {
            $this->warn('Health alert email failed: '.$exception->getMessage());
        }
    }
}
