<?php

declare(strict_types=1);

namespace App\Contracts;

interface AiProvider
{
    public function generate(string $systemPrompt, string $userPrompt): string;

    public function name(): string;
}
