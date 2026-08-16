<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('gigranker:status', function (): void {
    $this->info('GigRanker application scaffold is ready.');
})->purpose('Check GigRanker application status');

Schedule::command('gigranker:health --json --notify-admin')
    ->dailyAt('03:00')
    ->when(static fn (): bool => in_array(now()->day, [1, 16], true))
    ->withoutOverlapping(60);
