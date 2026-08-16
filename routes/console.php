<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('gigranker:status', function (): void {
    $this->info('GigRanker application scaffold is ready.');
})->purpose('Check GigRanker application status');
