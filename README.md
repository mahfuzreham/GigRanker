# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, SEO metadata, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest UI / CMS update

- GoSaaS-inspired modern SaaS public homepage layout.
- TailAdmin-inspired white admin control center.
- Homepage hero/site text can be changed from Admin → Settings.
- Homepage CMS can manage **Features, Pricing, FAQ, Testimonials and Footer Links** without editing Blade/PHP files.
- CMS entries support active/inactive status and sort order.
- CMS item data is stored as structured JSON in the database and rendered at runtime.
- Public homepage automatically reads active CMS content.
- Existing defaults remain available when CMS content has not yet been configured.

## Homepage CMS

Admin page:

```text
/admin/homepage
```

Manage:

- Features — icon, title and description
- Pricing — plan name, price, currency, features, badge, featured state and CTA
- FAQ — question and answer
- Testimonials — quote, name and role
- Footer links — label and URL
- Section title/subtitle/description
- Sort order
- Active/inactive state

Example feature item:

```json
[
  {
    "icon": "✦",
    "title": "AI content generation",
    "description": "Create targeted SEO content."
  }
]
```

Example pricing item:

```json
[
  {
    "name": "Pro",
    "price": "19",
    "currency": "USDT",
    "features": ["More AI credits", "More projects"],
    "featured": true,
    "badge": "Popular",
    "cta": "Choose Pro"
  }
]
```

The CMS uses the `homepage_sections` table and is loaded on each homepage request, so an admin content change does not require a code deployment.

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
8. Admin Homepage CMS → add/edit/delete feature
9. Admin Homepage CMS → add/edit pricing
10. Admin Homepage CMS → add/edit FAQ
11. Admin Homepage CMS → add/edit testimonial
12. Homepage reflects CMS changes immediately
13. Free → paid plan selection
14. Admin Settings → update BEP20 wallet
15. BEP20 USDT payment instructions show updated wallet
16. TXID submission
17. Admin payment approve/reject
18. Subscription activation after approval
19. Mobile admin/user/public views
20. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
