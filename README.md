# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, blogs, SEO metadata, schema markup, sitemap/robots output, outbound click tracking, subscriptions, payment intake, deployment history, backups, rollback and production health checks.

## Stack

- Laravel 12
- PHP 8.2+
- Recommended cPanel PHP: 8.3.x
- MySQL/MariaDB
- Blade + Tailwind CSS
- Composer 2.x

## UI / UX

The current application uses a clean white, responsive UI for both user and administrator areas. Shared navigation, cards, forms, tables, alerts, buttons and authentication screens use the same light visual system.

Admin UI includes a dedicated white admin sign-in screen, responsive control-center dashboard, SaaS plan overview, platform metrics, recent orders and payment-verification navigation. User UI includes a responsive dashboard, project list, AI credit indicator, project actions and mobile-friendly tables.

## cPanel deployment

Production path:

```text
/home/gigranker/public_html
```

The repository keeps the Laravel application root in `public_html` while the web entry point is `public/`. The repository includes root and `public/.htaccess` rules for cPanel deployments where the domain document root cannot be changed to `public/`.

### Fresh deployment

```bash
cd /home/gigranker
rm -rf /tmp/GigRanker-deploy
git clone --branch main --single-branch https://github.com/mahfuzreham/GigRanker.git /tmp/GigRanker-deploy
cp -a /tmp/GigRanker-deploy/. /home/gigranker/public_html/
rm -rf /tmp/GigRanker-deploy
cd /home/gigranker/public_html

composer install --no-dev --optimize-autoloader --no-interaction
[ -f .env ] || cp .env.example .env
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

Never overwrite an existing production `.env` with `.env.example`.

### Existing deployment update — recommended

Use this procedure whenever new code is pushed to GitHub `main`:

```bash
cd /home/gigranker/public_html

git status
git fetch origin main
git checkout main
git pull --ff-only origin main

export PATH="/home/gigranker/bin:$PATH"
composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
php artisan gigranker:health --json
```

The final health check must report `application`, `database`, `cache`, `storage` and `configuration` as `ok` before considering the update production-ready.

If `git status` shows intentional production changes, stop and review them before pulling. Do not overwrite `.env` or other server-only configuration.

Do not use these destructive commands as a normal update method:

```bash
git reset --hard
php artisan migrate:fresh
rm -rf .env
```

`git reset --hard` can discard intentional production changes and `migrate:fresh` destroys database tables/data.

### Account-local Composer

On cPanel accounts where `composer` is not globally available, install Composer in the account's `bin` directory:

```bash
cd /home/gigranker
mkdir -p bin
cd bin
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/home/gigranker/bin --filename=composer
rm composer-setup.php
export PATH="/home/gigranker/bin:$PATH"
composer --version
```

For the verified production setup, Composer 2.10.2 runs with PHP 8.3.30.

## cPanel web routing

If the domain document root is `/home/gigranker/public_html`, the repository root `.htaccess` routes requests into Laravel's `public/` directory and disables directory indexing. `public/.htaccess` sends non-file/non-directory requests to `public/index.php`.

Preferred configuration, when cPanel allows it, is still:

```text
Document Root: /home/gigranker/public_html/public
```

Do not type Apache `RewriteRule` directives directly into Terminal; they belong inside `.htaccess` files.

## Production environment

Configure `.env` on the server only:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

ADMIN_EMAIL=your-admin@example.com
ADMIN_EMAILS=your-admin@example.com

BKASH_NUMBER=your-bkash-number
BEP20_USDT_ADDRESS=your-bep20-usdt-address
BEP20_NETWORK=BSC
```

AI provider credentials and mail settings must also remain server-side. Never commit `.env`, API keys, payment credentials or wallet secrets.

## Admin control center

GigRanker has a dedicated admin entry point and protected control center.

Admin login:

```text
/admin/login
```

Admin dashboard:

```text
/admin
```

Payment verification:

```text
/admin/payments
```

Admin authorization is based on the server-side `ADMIN_EMAILS` / `ADMIN_EMAIL` allowlist. The dedicated admin login uses the authorized user's account password; no admin password is committed to GitHub.

### First-time admin setup

1. Register a normal GigRanker account using `/register`.
2. On the server, add that account email to `.env`:

```env
ADMIN_EMAILS=admin@example.com
```

Multiple admins can be comma-separated:

```env
ADMIN_EMAILS=admin@example.com,owner@example.com
```

3. Clear Laravel configuration cache:

```bash
php artisan optimize:clear
php artisan optimize
```

4. Open `/admin/login` and sign in with that account's existing password.

The admin dashboard provides SaaS overview metrics, plan information, recent orders and a direct link to payment verification. Payment approval/rejection remains protected by the same server-side admin allowlist.

## PHP / extension checklist

Required/expected extensions include:

- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `mbstring`
- `openssl`
- `pdo`
- `pdo_mysql`
- `session`
- `tokenizer`
- `xml`
- `zip`

`zip` / `ZipArchive` is required for ZIP/static-site export. `intl`, `opcache` and `redis` are recommended/optional depending on deployment configuration.

Verify the environment:

```bash
php -v
php -m | sort
composer --version
composer check-platform-reqs
php -r 'echo class_exists("ZipArchive") ? "ZipArchive: OK\n" : "ZipArchive: MISSING\n";'
php -r 'echo extension_loaded("pdo_mysql") ? "pdo_mysql: OK\n" : "pdo_mysql: MISSING\n";'
```

## Database and health check

After configuring the database:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan gigranker:health --json
```

The production health command checks application configuration, database connectivity, cache read/write, storage and required configuration. A failed health check exits non-zero and production should not be considered ready until it passes.

Expected healthy result:

```text
application     ok
database        ok
cache           ok
storage         ok
configuration   ok
```

## Scheduler / recurring health check

Add this cPanel cron:

```cron
* * * * * cd /home/gigranker/public_html && php artisan schedule:run >> /dev/null 2>&1
```

The application schedules the production health check for 03:00 on the 1st and 16th of each month and can notify `ADMIN_EMAIL` on failure.

## Deployment history / logging

Deployment lifecycle events are stored in the `deployments` table.

```bash
php artisan gigranker:deployment start --environment=production --version=2026.08.16 --triggered-by=github-actions --source=main
php artisan gigranker:deployment success --id=<deployment-uuid> --message="Deployment completed"
php artisan gigranker:deployment fail --id=<deployment-uuid> --message="Deployment failed"
php artisan gigranker:deployment list
```

## Rollback

Validate a previous successful deployment:

```bash
php artisan gigranker:rollback <deployment-uuid>
```

Execute an explicit code rollback only with confirmation:

```bash
php artisan gigranker:rollback <deployment-uuid> --execute --yes
```

Rollback refuses a dirty Git working tree and records the rollback in deployment history. Database migrations are not automatically reversed.

## Pre-deployment backups

Create and list production backups:

```bash
php artisan gigranker:backup create --deployment=<deployment-uuid> --environment=production
php artisan gigranker:backup list
```

Backups are tracked with status, path, size, duration and SHA-256 checksum. A failed backup returns a non-zero exit code so deployment automation can stop before changing production.

## Billing and payments

The subscription foundation provides Free, Starter, Pro and Agency plans. Paid checkout supports bKash and BEP20 payment submission. Payment submissions remain `pending` until an authorized administrator verifies them.

Admin payment review:

```text
/admin/payments
```

`ADMIN_EMAILS` is the server-side allowlist. Client input cannot grant admin access. Approval is transactional and activates the selected paid plan/credits only after manual verification.

Automatic bKash/provider or on-chain verification is not claimed.

## Security

- Never commit `.env` or secrets.
- Keep AI and payment credentials server-side.
- Validate and authorize project/resource requests.
- Rate-limit authentication, generation and payment endpoints.
- Treat AI output as untrusted data.
- Escape/sanitize generated content before preview/export.
- Normalize authentication identifiers and regenerate sessions after login.
- Invalidate sessions and regenerate CSRF tokens on logout.
- Never activate paid subscriptions from a client-side payment claim alone.
- Keep dependencies updated and run security checks before production releases.

## Quality / validation

The repository includes automated PHP/Laravel validation, syntax checks, Composer validation/security auditing, application tests, deployment/rollback/backup/health command checks and PHP 8.2/8.3 CI coverage.

## Current production validation

The cPanel deployment was validated with:

- PHP 8.3.30
- Composer 2.10.2
- MySQL database connection
- All current migrations applied
- Database cache tables applied
- `php artisan optimize:clear` successful
- `php artisan optimize` successful
- `php artisan gigranker:health --json` successful

The latest production health result reported `application`, `database`, `cache`, `storage` and `configuration` as `ok`.

## Live functional test checklist

Before public launch, test on the actual production domain:

1. Homepage
2. Registration
3. Login
4. Dashboard
5. Project creation
6. AI generation with configured provider credentials
7. Logout/login session lifecycle
8. Deployment and backup section
9. Dedicated admin login
10. Admin dashboard
11. Admin payment approve/reject
12. Mobile/responsive view

## Repository

```text
mahfuzreham/GigRanker
```

Production account/path:

```text
cPanel account: gigranker
/home/gigranker/public_html
```
