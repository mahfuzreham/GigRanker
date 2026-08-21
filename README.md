# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, blogs, SEO metadata, schema markup, sitemap/robots output, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest UI / billing update

- Clean white, responsive SaaS interface across public, user and admin areas.
- Conversion-focused homepage with features, workflow, plans and payment messaging.
- Paid checkout now accepts **BEP20 USDT only**.
- bKash is no longer accepted as a payment method.
- Users submit the blockchain TXID after sending the exact USDT amount.
- Paid subscriptions remain pending until an authorized administrator verifies the payment.
- Configure the receiving wallet with `BEP20_USDT_ADDRESS` and network with `BEP20_NETWORK=BSC`.

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

ADMIN_EMAILS=your-admin@example.com

BEP20_USDT_ADDRESS=your-bep20-usdt-address
BEP20_NETWORK=BSC
```

AI provider credentials and mail settings must remain server-side. Never commit `.env`, API keys, payment credentials or wallet secrets.

## Admin control center

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

Admin authorization is based on the server-side `ADMIN_EMAILS` / `ADMIN_EMAIL` allowlist. The admin password is never committed to GitHub.

## Billing and BEP20 USDT payments

The subscription foundation provides Free, Starter, Pro and Agency plans. Paid checkout uses **BEP20 USDT on BNB Smart Chain only**.

Required server configuration:

```env
BEP20_USDT_ADDRESS=0xYourReceivingWallet
BEP20_NETWORK=BSC
```

Payment flow:

1. User selects a paid plan.
2. GigRanker displays the exact USDT amount and receiving BEP20 wallet.
3. User sends USDT on BSC/BEP20.
4. User submits the blockchain TXID.
5. Payment remains `pending`.
6. Authorized admin verifies the transaction.
7. Only after approval is the paid subscription activated.

The current application records the submitted TXID for administrator verification. It does **not** claim automatic on-chain verification.

## Security

- Never commit `.env` or secrets.
- Keep AI and payment credentials server-side.
- Validate and authorize project/resource requests.
- Rate-limit authentication, generation and payment endpoints.
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
13. Admin payment approve/reject
14. Subscription activation after approval
15. Mobile/responsive view
16. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
