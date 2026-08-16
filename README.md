# GigRanker

**Turn Your Freelance Gig Into an SEO Marketing Website.**

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

Every push and pull request runs the GitHub Actions quality workflow against **PHP 8.2 and PHP 8.3**. It validates Composer configuration, installs dependencies, audits locked Composer dependencies, runs `php -l` against every tracked PHP source file, boots Laravel, checks routes and verifies the deployment/rollback/backup/health Artisan commands. Application tests run against an isolated SQLite in-memory database.

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

AI provider resilience is covered by automated tests for retrying rate-limit and temporary-server responses. The end-to-end project flow uses a fake AI provider in tests, so CI never requires real AI credentials.

The latest quality workflow is required to pass on **PHP 8.2 and PHP 8.3**, including Composer validation, dependency auditing, PHP syntax checks, Laravel boot/route checks, Artisan command discovery and application tests.

## Security principles

- Never commit `.env` or API secrets.
- AI/API credentials stay server-side.
- Payment secrets stay server-side.
- Validate and authorize every project/resource request.
- Rate-limit generation and authentication endpoints.
- Treat AI output as untrusted data.
- Sanitize generated HTML before preview/export where applicable.
- Normalize authentication identifiers before lookup.
- Regenerate the session after authentication and invalidate it on logout.
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

Deployment history/logging, safe rollback, pre-deployment database backups, production health checks, admin failure alerts, the recurring health-check schedule, PHP/cPanel requirements, automated syntax/quality checks, security auditing, isolated feature testing and the complete project user-flow testing are implemented. Production should still be validated on the actual cPanel server with real environment credentials before public launch.
