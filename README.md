# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, blogs, SEO metadata, schema markup, sitemap/robots output, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest Admin Settings update

The Admin Settings system is now connected to runtime AI generation and billing configuration.

- Gemini, Groq and OpenAI API keys can be saved from Admin → Settings.
- API keys are encrypted at rest with Laravel application encryption.
- AI provider selection is read from the database at generation time.
- AI model selection is read from the database at generation time.
- BEP20 USDT receiving wallet and network are read from the database at checkout time.
- `.env` remains the safe fallback for initial/bootstrap configuration.
- No API key, wallet secret or password is committed to GitHub.

## Admin Settings

```text
/admin/settings
```

Available controls:

### AI

- Primary provider: Gemini / Groq / OpenAI
- Gemini API key + model
- Groq API key + model
- OpenAI API key + model

### Payments

- BEP20 USDT receiving address
- BSC / BEP20 network

Existing secrets are masked. Leave an API-key field blank to keep the existing encrypted value.

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

Admin authorization is based on the server-side admin email allowlist. Admin passwords are never committed to GitHub.

## Billing and BEP20 USDT payments

Paid checkout uses **BEP20 USDT on BNB Smart Chain only**.

Flow:

1. User selects a paid plan.
2. GigRanker reads the current admin-configured wallet and displays the USDT amount.
3. User sends USDT on BSC/BEP20.
4. User submits the blockchain TXID.
5. Payment remains `pending`.
6. Authorized admin verifies the transaction.
7. Subscription activates only after approval.

The current application records the submitted TXID for administrator verification. It does **not** claim automatic on-chain verification.

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
4. Dashboard
5. Project creation
6. Admin Settings → save AI provider/key/model
7. AI generation using the saved Admin Settings key
8. Logout/login session lifecycle
9. Free → paid plan selection
10. Admin Settings → update BEP20 wallet
11. BEP20 USDT payment instructions show the updated wallet
12. TXID submission
13. Admin payment approve/reject
14. Subscription activation after approval
15. Mobile/responsive view
16. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
