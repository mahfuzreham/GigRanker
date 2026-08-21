# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, blogs, SEO metadata, schema markup, sitemap/robots output, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest UI / billing update

- Clean white, responsive SaaS interface across public, user and admin areas.
- Conversion-focused homepage with features, workflow, plans and payment messaging.
- Paid checkout accepts **BEP20 USDT only**.
- Users submit the blockchain TXID after sending the exact USDT amount.
- Paid subscriptions remain pending until an authorized administrator verifies the payment.
- The receiving BEP20 wallet and network can now be managed from the admin settings screen; `.env` remains the production fallback.

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

AI credentials and payment destinations can be maintained from **Admin → Settings** after the migration. `.env` values remain safe fallbacks. Never commit `.env`, API keys, payment credentials or wallet secrets.

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

### Admin settings

Authorized administrators can manage:

- Primary AI provider: Gemini, Groq or OpenAI.
- Gemini, Groq and OpenAI model names.
- Gemini, Groq and OpenAI API keys.
- BEP20 USDT receiving wallet.
- BEP20 network (BSC).

API keys are encrypted at rest using Laravel application encryption and are never displayed back in plaintext. Leaving an existing API-key field blank keeps the saved secret unchanged.

The payment checkout reads the admin-managed BEP20 wallet/network at request time, so changing the receiving wallet does not require editing application code or the checkout template.

## Billing and BEP20 USDT payments

The subscription foundation provides Free, Starter, Pro and Agency plans. Paid checkout uses **BEP20 USDT on BNB Smart Chain only**.

Payment flow:

1. User selects a paid plan.
2. GigRanker displays the current admin-configured USDT amount and receiving BEP20 wallet.
3. User sends USDT on BSC/BEP20.
4. User submits the blockchain TXID.
5. Payment remains `pending`.
6. Authorized admin verifies the transaction.
7. Only after approval is the paid subscription activated.

The current application records the submitted TXID for administrator verification. It does **not** claim automatic on-chain verification.

## Security

- Never commit `.env` or secrets.
- Keep AI and payment credentials server-side.
- Admin-managed API keys are encrypted at rest.
- Validate and authorize admin settings and payment actions.
- Rate-limit authentication, generation, payment and settings endpoints.
- Never activate paid subscriptions from a client-side payment claim alone.
- Before approval, verify the BEP20 network, receiving address, USDT token, amount and transaction status.

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
4. Dashboard
5. Project creation
6. AI generation with configured provider credentials
7. Logout/login session lifecycle
8. Free → paid plan selection
9. BEP20 USDT payment instructions
10. TXID submission
11. Admin login
12. Admin dashboard
13. Admin settings update and encrypted key storage
14. Admin payment approve/reject
15. Subscription activation after approval
16. Mobile/responsive view
17. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
