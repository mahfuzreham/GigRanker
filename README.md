# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, SEO metadata, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest homepage stability and UI fix

The public homepage now uses a dedicated `layouts/public.blade.php` layout instead of the authenticated application layout. This prevents the dashboard/workspace header from rendering above the public homepage and removes the duplicate-header issue.

The public UI now includes:

- Dedicated public navigation with one header only.
- Responsive mobile navigation styling.
- SaaS-style hero, features, workflow, pricing, testimonials and FAQ sections.
- Admin-managed CMS content with safe defaults.
- A redesigned multi-column footer with Product, Company and Resources links.
- Responsive footer and current-year display.
- No authenticated sidebar/topbar on the public homepage.

If the homepage previously showed an old header after deployment, clear compiled views with `php artisan optimize:clear` before testing.

## Homepage CMS

Admin page:

```text
/admin/homepage
```

The public homepage supports structured settings for:

- Features — icon, title and description
- Pricing — plan name, price, currency, features, badge, featured state and CTA
- FAQ — question and answer
- Testimonials — quote, name and role
- Footer links — label and URL

The homepage uses safe built-in defaults when a CMS JSON setting is empty or invalid.

## Admin Settings

```text
/admin/settings
```

Available controls:

- Site name/tagline
- Hero kicker/title/description
- Hero CTA labels
- Feature and workflow headings/descriptions
- Final CTA content
- Footer text
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

git fetch origin main
git checkout main
git pull --ff-only origin main

export PATH="/home/gigranker/bin:$PATH"
composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
php artisan view:cache
php artisan gigranker:health --json
```

If the homepage previously showed a 500 error or duplicate header, the `optimize:clear` step is required to remove old compiled Blade views.

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

After migration, AI credentials, homepage content and the BEP20 wallet can be maintained from the admin panel.

## Admin control center

Admin login:

```text
/admin/login
```

Admin dashboard:

```text
/admin
```

Homepage CMS:

```text
/admin/homepage
```

Settings:

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
- Validate and authorize admin settings and CMS actions.
- Rate-limit authentication, generation, payment and CMS endpoints.
- Treat CMS JSON as untrusted input and validate it before storage.
- Never activate paid subscriptions from a client-side payment claim alone.
- Verify the BEP20 network, receiving address, USDT token, amount and transaction status before payment approval.

## Database and health check

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan view:cache
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
2. Public header appears once only
3. Public footer and footer links
4. Registration
5. Login
6. User dashboard
7. Project creation
8. Admin Settings → save AI provider/key/model
9. AI generation using saved Admin Settings key
10. Admin Homepage CMS → add/edit feature
11. Admin Homepage CMS → add/edit pricing
12. Admin Homepage CMS → add/edit FAQ
13. Admin Homepage CMS → add/edit testimonial
14. Homepage reflects CMS changes immediately
15. Free → paid plan selection
16. Admin Settings → update BEP20 wallet
17. BEP20 USDT payment instructions show updated wallet
18. TXID submission
19. Admin payment approve/reject
20. Subscription activation after approval
21. Mobile admin/user/public views
22. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
