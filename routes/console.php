<?php

declare(strict_types=1);

use App\Console\Commands\DeploymentLogCommand;
use Illuminate\Support\Facades\Artisan;

Artisan::command('gigranker:status', function (): void {
    $this->info('GigRanker application scaffold is ready.');
})->purpose('Check GigRanker application status');

Artisan::starting(function ($artisan): void {
    $artisan->resolveCommands([
        DeploymentLogCommand::class,
    ]);
});
