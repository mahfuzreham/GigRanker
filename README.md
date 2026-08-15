# GigRanker

**Turn Your Fiverr Gig Into an SEO Marketing Website.**

GigRanker is a Laravel SaaS for freelancers and agencies who want to build SEO-focused marketing websites around Fiverr services. A seller provides their gig information, seller country, target buyer markets, branding and keywords; GigRanker can generate service pages, blog-style content, SEO metadata, internal-link-friendly pages and conversion CTAs.

> **Important:** GigRanker is a marketing/content tool. It does not guarantee Google rankings, Fiverr orders, or search-engine placement.

## Core features

- Fiverr gig URL validation and project setup
- Seller-country vs buyer-market targeting
- USA/EU/international buyer-market content targeting
- AI-generated SEO pages and blog-style guides
- SEO title and meta description generation
- Internal-link-friendly slugs
- Live preview and export workflow
- Fiverr CTA / outbound click tracking
- Subscription plans and AI credits
- Manual bKash payments
- Optional official bKash merchant checkout/verification
- BEP20 USDT payment verification
- Binance account integration for supported admin operations
- Discord, Telegram and email order notifications
- Admin AI provider manager
- OpenAI, Anthropic, Gemini, OpenRouter, Groq and custom OpenAI-compatible providers
- Primary/fallback AI routing
- Server-side encrypted AI credentials
- AI credit usage and provider-cost accounting
- Public **New Features & Updates** page
- Admin feature-update publisher with **Free / Paid / By Request** labels

## AI provider setup

Admin configures providers from **Admin → AI Provider Manager**. Users do not need to supply their own API keys.

Supported provider types:

- OpenAI
- Claude / Anthropic
- Google Gemini
- OpenRouter (including currently available free models)
- Groq
- Custom OpenAI-compatible endpoints

API keys must be supplied by the account owner. Never commit shared, leaked or third-party API keys to the repository.

Example routing:

```text
Primary: OpenRouter
Fallback: Groq → Gemini → OpenAI
```

Free provider availability and rate limits can change. Treat free-model routing as best-effort, not guaranteed unlimited capacity.

## User feature updates

Users can visit `/updates` to see new releases and feature announcements. Each announcement can be marked:

- **Free** — available to eligible users without an extra feature charge
- **Paid** — requires the relevant paid plan/add-on
- **By Request** — user can request access or a custom implementation

Admins publish updates from **Admin → Feature Updates**. This is intentionally separate from the Git commit history: code can be updated through GitHub while the product owner controls the user-facing release announcement.

For future releases, add a feature announcement after the code is deployed so users can see what changed without exposing internal implementation details.

## cPanel installation

Production target:

```text
cPanel user: gigranker
Document root: /home/gigranker/public_html
Repository: mahfuzreham/GigRanker
```

### 1. Open cPanel Terminal / SSH

Log in as the `gigranker` cPanel account (or use the account's SSH access) and enter:

```bash
cd /home/gigranker
```

### 2. Clone the private repository

If the repository is private, use a GitHub deploy key, SSH key or an authenticated Git method. Do **not** put a personal access token directly into shell history.

```bash
git clone git@github.com:mahfuzreham/GigRanker.git app
cd /home/gigranker/app
```

If the repository is already present:

```bash
cd /home/gigranker/app
git pull --ff-only origin feature/admin-payment-verification
```

Use your actual production branch when you promote the project to `main`.

### 3. Point the cPanel domain to Laravel `public`

Recommended document root:

```text
/home/gigranker/app/public
```

If your cPanel domain is already fixed to `/home/gigranker/public_html`, use a deployment layout that keeps the Laravel `public` directory as the web-facing directory. Never expose the Laravel project root, `.env`, storage internals or Git metadata to the browser.

### 4. Install PHP dependencies

From the Laravel project directory:

```bash
composer install --no-dev --optimize-autoloader
```

Use the PHP/Composer version supported by the application's `composer.json`.

### 5. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Set production values in `.env` for:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gigranker.cheap
DB_CONNECTION=mysql
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Configure mail, bKash, BEP20/BSC, Binance, Discord, Telegram and any AI provider credentials only through secure server configuration/Admin Settings as appropriate. Never commit `.env`.

### 6. Run migrations and optimize

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If queues are used for your production workload, configure a cPanel Supervisor/daemon equivalent or the hosting provider's supported queue process.

### 7. Permissions

The web/PHP process needs write access to Laravel's runtime directories, especially:

```text
storage/
bootstrap/cache/
```

Do not make the entire project world-writable.

### 8. Updating after new GitHub code

Before updating production, back up the database and confirm the target commit/branch.

```bash
cd /home/gigranker/app
git fetch origin
git status
git pull --ff-only origin feature/admin-payment-verification
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If a future release changes frontend assets, also run the project's documented build command before cache generation.

**Do not run `git reset --hard` on production unless you intentionally understand and accept the data/configuration impact.**

### Recommended release workflow

```text
Local development
      ↓
Private GitHub branch / PR
      ↓
CI + tests + security review
      ↓
Merge/release
      ↓
Production git pull --ff-only
      ↓
composer install + migrations
      ↓
clear/cache Laravel
      ↓
Smoke test
      ↓
Publish user-facing Feature Update
```

## Production secrets

Never commit:

- `.env`
- OpenAI/Anthropic/Gemini/OpenRouter/Groq keys
- bKash secrets
- Binance API secrets
- Telegram bot tokens
- Discord webhooks
- database passwords
- wallet private keys

For Binance, use the minimum required permissions and **do not enable withdrawals** unless a future feature explicitly requires it and has been separately security-reviewed.

For BEP20 payments, only configure a receiving address. Never store a wallet private key in GigRanker.

## Security principles

- Never commit secrets.
- Keep AI/API credentials server-side.
- Encrypt sensitive application settings at rest.
- Validate and authorize every project/resource request.
- Rate-limit authentication, payment and generation endpoints.
- Treat AI output as untrusted data.
- Sanitize generated HTML before preview/export where applicable.
- Use HTTPS in production.
- Keep dependencies updated.
- Run tests and security checks before releases.
- Back up the database before migrations or production updates.

## Plans

The application currently includes Free, Starter, Pro and Agency concepts with plan-specific credits/project/page limits. Final pricing and entitlements should be verified in the application's plan configuration before production launch.

## Repository

`mahfuzreham/GigRanker`

## Status

Active development. Production deployment should only happen after CI, database migration, payment credentials, mail, AI provider configuration and security checks are verified for the target environment.
