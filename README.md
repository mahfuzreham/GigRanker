# GigRanker

**Turn Your Freelance Gig Into an SEO Marketing Website.**

## 🚀 cPanel Terminal — Deploy / Update from GitHub

Use the following commands in **cPanel Terminal** to download the complete GigRanker code from GitHub and deploy/update the application. Repository: `mahfuzreham/GigRanker`.

### First deployment — empty `public_html`

If `/home/gigranker/public_html` is empty, clone the `main` branch directly:

```bash
cd /home/gigranker
rm -rf /tmp/GigRanker-deploy

git clone --branch main --single-branch https://github.com/mahfuzreham/GigRanker.git /tmp/GigRanker-deploy

cp -a /tmp/GigRanker-deploy/. /home/gigranker/public_html/
cd /home/gigranker/public_html
rm -rf /tmp/GigRanker-deploy
```

Then install dependencies and prepare Laravel:

```bash
cd /home/gigranker/public_html

composer install --no-dev --optimize-autoloader --no-interaction

# Create .env only if it does not already exist.
[ -f .env ] || cp .env.example .env

php artisan key:generate --force
php artisan storage:link
php artisan optimize:clear
php artisan migrate --force
php artisan optimize
```

> **Important:** Never run `cp .env.example .env` over an existing production `.env`. Keep real database, mail, AI and payment credentials only in `.env` and never commit them to Git.

### Existing installation — update to latest `main`

For an existing Git checkout:

```bash
cd /home/gigranker/public_html

git fetch origin main
git checkout main
git pull --ff-only origin main

composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Before updating production, create a backup and check the working tree:

```bash
cd /home/gigranker/public_html

git status --short
php artisan gigranker:backup create --environment=production
php artisan gigranker:health --json
```

Do **not** use `git reset --hard` on production unless you intentionally want to discard local/uncommitted files. The normal update command uses `git pull --ff-only` to avoid silently overwriting local changes.

### If `public_html` is not currently a Git checkout

If the existing application was uploaded manually and you want Git tracking without deleting the current `.env`, initialize the repository carefully:

```bash
cd /home/gigranker/public_html

git init
git remote add origin https://github.com/mahfuzreham/GigRanker.git
git fetch origin main
git checkout -B main origin/main
```

If Git reports local files would be overwritten, **stop and back up the current directory first**. Do not force-reset until you have confirmed that all required production files are backed up.

### Production environment after code download

Edit the production environment file:

```bash
cd /home/gigranker/public_html
nano .env
```

At minimum, configure:

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

Also configure the selected mail and AI provider credentials in `.env`. **Never paste real secrets into GitHub, README, issues or commits.**

### Verify the cPanel PHP environment

```bash
cd /home/gigranker/public_html
php -v
php -m | sort
composer --version
composer check-platform-reqs
php -r 'echo class_exists("ZipArchive") ? "ZipArchive: OK\n" : "ZipArchive: MISSING\n";'
php -r 'echo extension_loaded("pdo_mysql") ? "pdo_mysql: OK\n" : "pdo_mysql: MISSING\n";'
```

Recommended production PHP: **PHP 8.3.x**, provided the cPanel host has the required packages/extensions available.

### Final live test

After configuration and migrations:

```bash
cd /home/gigranker/public_html
php artisan gigranker:health
php artisan gigranker:health --json
php artisan route:list --except-vendor
```

If the health check returns failure, **do not consider production ready** until the failing check is fixed.

### Laravel scheduler cron on cPanel

Add this cron job in cPanel:

```cron
* * * * * cd /home/gigranker/public_html && php artisan schedule:run >> /dev/null 2>&1
```

This runs the application's scheduled tasks, including the production health check scheduled for the 1st and 16th of each month at 03:00.

---

GigRanker is a planned Laravel SaaS platform for creating SEO-focused marketing websites around freelance service listings. Users provide their gig/service information, target market and branding; GigRanker generates structured SEO content, service pages, blog content, internal links, schema markup, sitemap/robots files and conversion-focused CTAs.

## Product goals

- Gig-focused SEO marketing websites
- AI-assisted content generation
- Structured JSON output + controlled HTML templates
- Service pages and blog generation
- SEO metadata, canonical URLs, Open Graph and Schema.org
- Internal linking and sitemap generation
- Live preview and ZIP export
- Click tracking for outbound gig CTAs
- Subscription and usage-credit system
- bKash and BEP20 payment support
- Admin-controlled AI providers and usage limits

## Planned stack

- Laravel 12 / PHP 8.2+
- MySQL or MariaDB
- Blade + Tailwind CSS
- Queue workers for AI generation
- Server-side AI provider abstraction
- cPanel production deployment

## PHP / cPanel server requirements

GigRanker declares **PHP 8.2 or newer** in `composer.json`. For cPanel PHP Selector, **PHP 8.3.x is the recommended production choice** after verifying the host's Laravel/extension package availability. Keep the production PHP version aligned with the version validated by `composer install` and the application's CI/runtime checks.

### PHP Selector checklist

- [ ] PHP 8.2+ available
- [ ] PHP 8.3.x selected/recommended for production
- [ ] PHP-FPM enabled where available
- [ ] Composer 2.x installed and working
- [ ] MySQL/MariaDB database configured
- [ ] Document root points to the Laravel `public/` directory or the cPanel deployment layout is configured correctly

### Required PHP extensions

Enable these extensions in cPanel PHP Selector / MultiPHP Manager:

- [ ] `bcmath`
- [ ] `ctype`
- [ ] `curl`
- [ ] `dom`
- [ ] `fileinfo`
- [ ] `filter`
- [ ] `hash`
- [ ] `mbstring`
- [ ] `openssl`
- [ ] `pcre`
- [ ] `pdo`
- [ ] `pdo_mysql`
- [ ] `session`
- [ ] `tokenizer`
- [ ] `xml`
- [ ] `zip` — required for GigRanker ZIP/static-site export (`ZipArchive`)

### Recommended production extensions / features

- [ ] `intl`
- [ ] `opcache`
- [ ] `redis` — optional, useful when Redis is selected for cache/queues

### Verify PHP environment

Run these commands on the production server after selecting the PHP version:

```bash
php -v
php -m | sort
php -r 'echo class_exists("ZipArchive") ? "ZipArchive: OK\n" : "ZipArchive: MISSING\n";'
php -r 'echo extension_loaded("pdo_mysql") ? "pdo_mysql: OK\n" : "pdo_mysql: MISSING\n";'
composer --version
composer check-platform-reqs
```

Do not mark the server ready until `composer check-platform-reqs` passes and `ZipArchive` is available for the export feature.

## Automated code quality / syntax checks

Every push and pull request runs the GitHub Actions quality workflow against **PHP 8.2 and PHP 8.3**. It validates Composer configuration, installs dependencies, audits locked Composer dependencies, runs `php -l` against every tracked PHP source file, boots Laravel, checks routes and verifies the deployment/rollback/backup/health Artisan commands. It also executes `gigranker:health --json` in CI and runs application tests against an isolated SQLite in-memory database.

The security workflow separately checks PHP syntax and scans tracked files for common hard-coded secret assignments. A production release should not proceed while either workflow is failing.

## Bug-fix / validation status

A full repository validation pass was performed after the deployment-readiness work. The quality workflow found and fixed real application issues, including:

- Symfony/Laravel command method collision in `DeploymentLogCommand`.
- Missing base HTTP `Controller` class.
- Deployment command registration cleanup in Laravel bootstrap.
- Missing Laravel framework cache/view/log directories in a clean checkout.
- Missing PHPUnit bootstrap/configuration and smoke-test directories.
- PHPUnit application bootstrap corrected so feature tests boot Laravel correctly.
- Test isolation strengthened with SQLite and model factories.
- Project ownership and Fiverr URL validation are now covered by feature tests.
- CI now runs Composer vulnerability auditing.
- Rollback execution now refuses to reset a dirty Git working tree, preventing accidental destruction of uncommitted production changes.
- AI-generated duplicate page slugs are ignored so one generated page cannot silently overwrite another.
- AI request JSON encoding is checked explicitly before sending the provider request.
- Gemini and OpenAI-compatible providers now retry transient `408`, `429`, `500`, `502`, `503`, and `504` failures, while still failing fast on non-transient HTTP errors.
- Static export now rejects unsafe/path-traversal page slugs before creating ZIP entries.
- Export site URLs are restricted to HTTP/HTTPS and checked again at runtime.
- ZIP entry names are validated before being written.
- Exported HTML escapes AI/user content, and JSON-LD encoding now fails safely instead of producing invalid markup.
- Export tests cover path traversal, HTML injection and invalid site URL handling.
- Authentication normalizes email addresses before lookup and regenerates sessions after successful authentication.
- Logout invalidates the session and regenerates the CSRF token.
- Authentication, guest-access and session lifecycle flows are covered by feature tests.
- The complete project flow is covered from project creation through AI generation, preview, ZIP export and outbound Fiverr click tracking.
- Cross-user access to project generation, preview and export is explicitly tested.
- Subscription plan architecture was rebuilt on top of the current `main` code instead of merging the stale conflicting branch.
- Billing plans are validated server-side and paid plans remain inactive until a verified payment flow is implemented.
- Subscription records, user plan state, authenticated billing routes and billing plan UI are covered by feature tests.
- Paid checkout now creates a server-side pending payment ledger entry for bKash or BEP20 submissions.
- Payment transaction references are protected against duplicate reuse.
- Payment destinations are configured through server-side environment variables and are never accepted from the browser.
- Payment intake tests verify that submitted payments remain pending and cannot activate a paid plan by themselves.
- Admin payment verification now uses a server-side email allowlist and never trusts a client-supplied admin flag.
- Payment approval is transactional and row-locked; a reviewed payment cannot be approved or rejected again.
- Approved payments activate the selected paid subscription, add the plan's AI credits and record the credit transaction atomically.
- Rejected payments remain inactive and retain a reviewer audit reference.
- Payment review tests cover non-admin denial, approval, rejection and repeated-approval idempotency.
- The production health command is executed directly in the PHP 8.2/8.3 quality matrix so command registration and runtime health-check wiring are continuously validated.

AI provider resilience is covered by automated tests for retrying rate-limit and temporary-server responses. The end-to-end project flow uses a fake AI provider in tests, so CI never requires real AI credentials.

The latest quality workflow is required to pass on **PHP 8.2 and PHP 8.3**, including Composer validation, dependency auditing, PHP syntax checks, Laravel boot/route checks, Artisan command discovery, the production health command and application tests.

## Billing / subscription plans

The current subscription foundation provides four plans:

| Plan | Price | AI credits | Projects | SEO pages |
|---|---:|---:|---:|---:|
| Free | $0 | 10 | 1 | 3 |
| Starter | $5/month | 50 | 3 | 20 |
| Pro | $15/month | 200 | 10 | 100 |
| Agency | $39/month | 500 | 50 | 500 |

Authenticated users can view plans at `/billing/plans`. Selecting **Free** updates the user's plan. Paid selections open `/billing/payment`, where users select bKash or BEP20 and submit a transaction ID/TXID. The submission is stored as `pending`; it does not activate the paid plan until an authorized administrator approves it.

### Payment configuration

Set these values only in the production `.env` file:

```env
BKASH_NUMBER=your-bkash-number
BEP20_USDT_ADDRESS=your-bep20-usdt-address
BEP20_NETWORK=BSC
ADMIN_EMAILS=admin@example.com,another-admin@example.com
```

`ADMIN_EMAILS` is the allowlist for the built-in payment review screen. `ADMIN_EMAIL` is accepted as a backwards-compatible single-admin fallback.

Never commit real payment numbers, wallet addresses, API keys or provider credentials to Git.

### Admin payment verification

Authorized administrators can review pending payments at:

```text
/admin/payments
```

Available actions:

- **Approve & Activate** — marks the payment approved, activates the paid plan for one month, adds the plan's AI credits, expires any previous active subscription and records the credit/payment audit data in one database transaction.
- **Reject** — marks the payment rejected without changing the user's plan or credits.

The server checks the authenticated user's normalized email against `ADMIN_EMAILS`. A client cannot grant itself admin access. Payment rows are locked during review, and only `pending` payments may be processed, preventing double-credit/double-subscription activation.

The approval flow does **not** perform automatic bKash or blockchain verification. An administrator/provider must independently verify the submitted transaction before approving it.

## Security principles

- Never commit `.env` or API secrets.
- AI/API credentials stay server-side.
- Payment secrets stay server-side.
- Validate and authorize every project/resource request.
- Rate-limit generation and authentication endpoints.
- Rate-limit payment submission and admin review endpoints.
- Treat AI output as untrusted data.
- Sanitize generated HTML before preview/export where applicable.
- Normalize authentication identifiers before lookup.
- Regenerate the session after authentication and invalidate it on logout.
- Never activate a paid subscription from a client-side payment claim alone.
- Keep dependencies updated and run security checks before production releases.

## Deployment target

Production cPanel account: `gigranker`

Production path: `/home/gigranker/public_html`

Repository: `mahfuzreham/GigRanker`

## Deployment history / logging

GigRanker records deployment lifecycle events in the `deployments` table. Each record includes a UUID, environment, release version, Git commit SHA, status, start/finish timestamps, duration, trigger/source information, message and optional metadata.

Use the deployment logger command from cPanel deployment scripts, CI jobs or administrators:

```bash
php artisan gigranker:deployment start --environment=production --version=2026.08.16 --triggered-by=github-actions --source=main
php artisan gigranker:deployment success --id=<deployment-uuid> --message="Deployment completed"
php artisan gigranker:deployment fail --id=<deployment-uuid> --message="Deployment failed"
php artisan gigranker:deployment list
```

## Rollback

Rollback targets must reference a previously successful deployment. By default the command only validates the target and creates an audit record; it does not modify the working tree.

```bash
php artisan gigranker:rollback <deployment-uuid>
```

To execute the code rollback on a Git working tree, an explicit confirmation is required:

```bash
php artisan gigranker:rollback <deployment-uuid> --execute --yes
```

The rollback service fetches repository refs, verifies the target commit, refuses to operate on a dirty working tree, and resets the working tree to that commit. It records the rollback as a new deployment-history entry, including the previous and resulting commit SHA. **Database migrations are never reversed automatically**; schema rollback must be handled separately and deliberately.

## Pre-deployment backups

Before a production deployment, create a database backup and associate it with the deployment record. Backups are stored under Laravel's local storage disk and are tracked in the `deployment_backups` table with status, path, size, duration and SHA-256 checksum.

```bash
php artisan gigranker:backup create --deployment=<deployment-uuid> --environment=production
php artisan gigranker:backup list
```

The automated backup currently supports a configured MySQL database. A failed backup returns a non-zero command exit code so a deployment script can stop before changing production. Backup files and the application storage location must be protected with appropriate server permissions and retention policies.

## Production health checks

Run the production readiness check before and after deployments:

```bash
php artisan gigranker:health
php artisan gigranker:health --json
php artisan gigranker:health --json --notify-admin
```

The check verifies production-safe application configuration, database connectivity, cache read/write, local storage read/write and required core configuration. A failed check exits non-zero. With `--notify-admin`, failures are emailed to `ADMIN_EMAIL`.

### Automatic 15-day check

Production health checks are scheduled for **03:00 on the 1st and 16th of every month** (roughly a 15-day recurring cadence). The scheduler runs `gigranker:health --json --notify-admin` and prevents overlapping runs.

On cPanel, the Laravel scheduler still needs the standard cron entry:

```cron
* * * * * cd /home/gigranker/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Set the production `.env` value:

```env
ADMIN_EMAIL=your-admin@example.com
```

The server must also have a working Laravel mail configuration for alert delivery.

## Status

Deployment history/logging, safe rollback, pre-deployment database backups, production health checks, admin failure alerts, the recurring health-check schedule, PHP/cPanel requirements, automated syntax/quality checks, security auditing, isolated feature testing, complete project-flow testing, the subscription foundation, secure payment intake and admin payment verification are implemented. The quality workflow now executes the production health command on PHP 8.2 and PHP 8.3. Payment verification remains a manual trust step; automatic bKash/provider or on-chain verification is not claimed. Production should still be validated on the actual cPanel server with real environment credentials before public launch.
