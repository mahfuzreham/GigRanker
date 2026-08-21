<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'gigranker:admin:create
        {email : Admin email address}
        {--password= : Admin password (minimum 12 characters)}
        {--name=GigRanker Admin : Admin display name}';

    protected $description = 'Create or update a GigRanker administrator account and allowlist its email';

    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');
            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?: $this->secret('Admin password'));
        if (strlen($password) < 12) {
            $this->error('Admin password must be at least 12 characters.');
            return self::FAILURE;
        }

        $name = trim((string) $this->option('name')) ?: 'GigRanker Admin';
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
            $this->info('Admin user created.');
        } else {
            $user->forceFill([
                'name' => $name,
                'password' => $password,
            ])->save();
            $this->info('Existing user updated as admin.');
        }

        $this->allowlistEmail($email);

        $this->info('Admin email: '.$email);
        $this->info('Admin login: '.config('app.url').'/admin/login');

        return self::SUCCESS;
    }

    private function allowlistEmail(string $email): void
    {
        $path = base_path('.env');
        if (! is_file($path) || ! is_writable($path)) {
            $this->warn('Could not update .env automatically. Add this manually: ADMIN_EMAILS='.$email);
            return;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            $this->warn('Could not read .env. Add this manually: ADMIN_EMAILS='.$email);
            return;
        }

        preg_match('/^ADMIN_EMAILS=(.*)$/m', $contents, $matches);
        $emails = [];
        if (isset($matches[1])) {
            $emails = array_values(array_filter(array_map('trim', explode(',', trim($matches[1], " \t\"'")))));
        }

        if (! in_array($email, array_map('strtolower', $emails), true)) {
            $emails[] = $email;
        }

        $value = implode(',', $emails);
        if (isset($matches[0])) {
            $contents = preg_replace('/^ADMIN_EMAILS=.*$/m', 'ADMIN_EMAILS='.$value, $contents, 1);
        } else {
            $contents = rtrim($contents).PHP_EOL.'ADMIN_EMAILS='.$value.PHP_EOL;
        }

        file_put_contents($path, $contents, LOCK_EX);
    }
}
