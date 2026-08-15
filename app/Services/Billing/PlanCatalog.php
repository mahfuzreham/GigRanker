<?php

declare(strict_types=1);

namespace App\Services\Billing;

final class PlanCatalog
{
    public static function all(): array
    {
        return [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'currency' => 'USD',
                'credits' => 10,
                'projects' => 1,
                'pages' => 3,
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => 5,
                'currency' => 'USD',
                'credits' => 50,
                'projects' => 3,
                'pages' => 20,
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 15,
                'currency' => 'USD',
                'credits' => 200,
                'projects' => 10,
                'pages' => 100,
            ],
            'agency' => [
                'name' => 'Agency',
                'price' => 39,
                'currency' => 'USD',
                'credits' => 500,
                'projects' => 50,
                'pages' => 500,
            ],
        ];
    }

    public static function get(string $plan): ?array
    {
        return self::all()[$plan] ?? null;
    }
}
