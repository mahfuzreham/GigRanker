# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, blogs, SEO metadata, schema markup, sitemap/robots output, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest UI update — TailAdmin-inspired admin design

The supplied **TailAdmin Laravel** template was reviewed and its visual language was applied to GigRanker without replacing GigRanker's application structure or copying the template's unrelated demo pages.

Admin UI now includes:

- Fixed white sidebar with grouped navigation.
- Active route states and quick links.
- Sticky top bar and admin identity badge.
- Responsive mobile sidebar with overlay toggle.
- TailAdmin-style spacing, borders, cards, badges and brand palette.
- Responsive dashboard metric cards.
- Cleaner SaaS plan and recent-order tables.
- Redesigned payment verification table.
- Redesigned AI / USDT settings screen.

Admin layout:

```text
resources/views/layouts/admin.blade.php
```

The public/user application layout and functionality remain separate from the admin control-center layout.

## Admin Settings

```text
/admin/settings
```

Available controls:

- Primary AI provider: Gemini / Groq / OpenAI
- Gemini, Groq and OpenAI API keys + models
- BEP20 USDT receiving address
- BSC / BEP20 network

API keys are encrypted at rest. Leave an existing API-key field blank to keep the saved secret unchanged.

## Billing

Paid checkout accepts **BEP20 USDT only**. The checkout reads the current admin-managed receiving wallet at request time. Payments remain pending until an authorized administrator verifies the submitted TXID.

## Stack

- Laravel 12
- PHP 8.2+
- Recommended cPanel PHP: 8.3.x
- MySQL/MariaDB
- Blade + Tailwind CSS
- Composer 2.x

## cPanel deployment

Production path:

```text
/home/gigranker/public_html
```

### Existing deployment update — recommended

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

Never overwrite the production `.env` with `.env.example` and never commit secrets.

## Production environment

Configure `.env` on the server only for infrastructure/bootstrap values:

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

ADMIN_EMAILS=your-admin@example.com
```

After the app migration is complete, AI credentials and the BEP20 wallet can be maintained from Admin → Settings.

## Admin control center

Admin login:

```text
/admin/login
```

Admin dashboard:

```text
/admin
```

Admin settings:

```text
/admin/settings
```

Payment verification:

```text
/admin/payments
```

## Security

- Never commit `.env` or secrets.
- Admin-managed API keys are encrypted at rest.
- Runtime AI credentials are loaded from encrypted settings when available.
- Payment wallet is loaded from admin settings at checkout time.
- Validate and authorize admin settings and payment actions.
- Rate-limit authentication, generation, payment and settings endpoints.
- Never activate paid subscriptions from a client-side payment claim alone.
- Verify the BEP20 network, receiving address, USDT token, amount and transaction status before payment approval.

## Database and health check

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan gigranker:health --json
```

Expected healthy result:

```text
application     ok
database        ok
cache           ok
storage         ok
configuration   ok
```

## Live functional test checklist

1. Homepage and responsive layout
2. Registration
3. Login
4. User dashboard
5. Project creation
6. Admin Settings → save AI provider/key/model
7. AI generation using saved Admin Settings key
8. Free → paid plan selection
9. Admin Settings → update BEP20 wallet
10. BEP20 USDT payment instructions show updated wallet
11. TXID submission
12. Admin login
13. Admin dashboard responsive layout
14. Admin payment approve/reject
15. Subscription activation after approval
16. Mobile sidebar and responsive views
17. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
