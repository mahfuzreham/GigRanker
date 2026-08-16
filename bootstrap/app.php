<?php

declare(strict_types=1);

use App\Console\Commands\DeploymentBackupCommand;
use App\Console\Commands\DeploymentLogCommand;
use App\Console\Commands\DeploymentRollbackCommand;
use App\Console\Commands\ProductionHealthCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        DeploymentLogCommand::class,
        DeploymentRollbackCommand::class,
        DeploymentBackupCommand::class,
        ProductionHealthCommand::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        // Run on the 1st and 16th of each month at 03:00. This provides a
        // roughly 15-day recurring production health-check cadence.
        $schedule->command('gigranker:health --json --notify-admin')
            ->cron('0 3 1,16 * *')
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Add application middleware here.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Add application exception configuration here.
    })->create();
