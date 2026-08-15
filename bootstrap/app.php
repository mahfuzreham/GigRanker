<?php

declare(strict_types=1);

use App\Console\Commands\DeploymentLogCommand;
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
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Add application middleware here.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Add application exception configuration here.
    })->create();
