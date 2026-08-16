<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductionHealthService
{
    public function check(): array
    {
        return [
            'application' => $this->checkApplication(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'configuration' => $this->checkConfiguration(),
        ];
    }

    private function checkApplication(): array
    {
        return [
            'status' => app()->environment('production') && ! config('app.debug') ? 'ok' : 'warning',
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'url' => config('app.url'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'driver' => DB::getDriverName()];
        } catch (Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'gigranker:health:'.bin2hex(random_bytes(8));
            Cache::put($key, 'ok', 10);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);
            return ['status' => $ok ? 'ok' : 'failed'];
        } catch (Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $path = 'health-check-'.bin2hex(random_bytes(8)).'.tmp';
            $disk->put($path, 'ok');
            $ok = $disk->get($path) === 'ok';
            $disk->delete($path);
            return ['status' => $ok ? 'ok' : 'failed'];
        } catch (Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    private function checkConfiguration(): array
    {
        $required = [
            'app_key' => (string) config('app.key'),
            'app_url' => (string) config('app.url'),
            'database' => (string) config('database.default'),
        ];

        $missing = array_keys(array_filter($required, static fn (string $value): bool => $value === ''));
        return [
            'status' => $missing === [] ? 'ok' : 'failed',
            'missing' => $missing,
        ];
    }
}
