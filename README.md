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
- Admin **Deployment / Update Center**
- GitHub branch/commit update checking
- cPanel deployment configuration on secure API port **2083**
- Explicit Admin approval before production deployment

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

Admins publish updates from **Admin → Feature Updates**. This is intentionally separate from Git commit history: code can be updated through GitHub while the product owner controls the user-facing release announcement.

## Admin Deployment / Update Center

The production update flow is approval-based:

```text
GitHub new commit
       ↓
Admin → Deployment Center
       ↓
Check GitHub Update
       ↓
Review commit SHA/message
       ↓
Approve & Deploy
       ↓
cPanel UAPI over HTTPS :2083
       ↓
VersionControl update
       ↓
VersionControlDeployment
       ↓
Production
```

Configure from the Admin deployment screen:

- cPanel host/IP
- cPanel API port (**2083 only**)
- cPanel username
- cPanel API secret/token (preferred)
- repository root
- GitHub repository
- GitHub branch
- GitHub token for private repository access

Sensitive cPanel/GitHub credentials are stored encrypted where the application stores them. Prefer a restricted cPanel API token/secret instead of an account password. Never commit credentials to Git.

The current deployment center intentionally does **not** auto-deploy merely because GitHub has a new commit. Admin approval is required before production changes.

## cPanel installation

Production target:

```text
cPanel user: gigranker
Document root: /home/gigranker/public_html
Repository: mahfuzreham/GigRanker
```

### 1. Open cPanel Terminal / SSH

Log in as the `gigranker` cPanel account (or use the account's SSH access) and enter the deployment directory.

### 2. Clone the private repository

If the repository is private, use a GitHub deploy key, SSH key or an authenticated Git method. Do **not** put a personal access token directly into shell history.

```bash
cd /home/gigranker
mkdir -p app
git clone git@github.com:mahfuzreham/GigRanker.git app
cd /home/gigranker/app
```

For an existing checkout:

```bash
cd /home/gigranker/app
git fetch origin
git checkout feature/admin-payment-verification
git pull --ff-only origin feature/admin-payment-verification
```

Use the actual production branch when promoting the project to `main`.

### 3. Point the cPanel domain to Laravel `public`

Recommended document root:

```text
/home/gigranker/app/public
```

If the cPanel domain must remain `/home/gigranker/public_html`, use a deployment layout that keeps Laravel's `public` directory as the web-facing directory. Never expose the Laravel project root, `.env`, storage internals or Git metadata to the browser.

### 4. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

Use the PHP/Composer version supported by `composer.json`.

### 5. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Set production values in `.env` and keep `APP_DEBUG=false`.

### 6. Run migrations and optimize

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Configure queue workers/cron using the hosting provider's supported process manager.

### 7. Permissions

The PHP/web process needs write access to:

```text
storage/
bootstrap/cache/
```

Do not make the entire project world-writable.

## Manual production update

Always back up the database and verify the target commit before a production update.

```bash
cd /home/gigranker/app
git fetch origin
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run `git reset --hard` on production unless the rollback procedure has been reviewed and the database/configuration impact is understood.

## Customer release workflow

Code deployment and customer announcements are separate on purpose. Internal bug fixes/security patches should not automatically appear as commercial features.

```text
New code/feature
    ↓
CI + review
    ↓
Admin approves deployment
    ↓
Production deploy + smoke test
    ↓
Admin → Feature Updates
    ↓
Free / Paid / By Request
    ↓
Publish
    ↓
Users see What's New
```

## Production secrets

Never commit:

- `.env`
- AI provider keys
- bKash secrets
- Binance API secrets
- Telegram bot tokens
- Discord webhooks
- database passwords
- wallet private keys

For Binance, use minimum required permissions and keep withdrawals disabled unless separately reviewed. For BEP20 payments, configure only the receiving address; never store a wallet private key in GigRanker.

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

## Remaining release checklist

- [ ] Deployment history persistence and audit log
- [ ] One-click rollback with backup protection
- [ ] Pre-deployment backup + restore test
- [ ] Post-deployment health check
- [ ] Discord/Telegram deployment notifications
- [ ] Full AI credit/token accounting validation
- [ ] Free/Paid/By Request entitlement enforcement tests
- [ ] bKash production credential test
- [ ] BEP20 production test
- [ ] Binance API production connection test
- [ ] Payment edge-case tests
- [ ] Full automated test suite
- [ ] Security audit
- [ ] Queue/cron configuration
- [ ] Final cPanel smoke test

## Repository

`mahfuzreham/GigRanker`

## Status

Active development. Production deployment should only happen after CI, database migration, payment credentials, mail, AI provider configuration and security checks are verified for the target environment.
