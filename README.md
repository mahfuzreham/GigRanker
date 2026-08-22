# GigRanker

**Version: 1.0**  
**Status: Production Candidate — live testing**

Turn a freelance gig into an SEO-focused marketing website with AI-assisted content generation, project pages, SEO metadata, outbound click tracking, subscriptions, **BEP20 USDT-only payment intake**, deployment history, backups, rollback and production health checks.

## Latest homepage and client-resource update

The public homepage uses a dedicated public layout so the authenticated workspace header/sidebar is never rendered above the public site. The client dashboard now includes an administrator-managed **Free HTML Sites** resource area.

### Admin-managed Free HTML Sites

Admin page:

```text
/admin/hosted-sites
```

Administrators can add multiple client-facing resources with:

- Site name
- **Site Link** — hosted HTML website URL
- **Setup Link** — optional setup/instructions URL
- Description
- Sort order
- Active/hidden status
- Delete action

Only active resources are shown to clients. Clients see the links in their dashboard under **Free HTML Sites**, with separate **Open Site** and **Setup Link** buttons.

The links are global client resources, so an administrator can publish multiple free hosted HTML sites without editing client accounts individually.

## Homepage UI

The public UI includes:

- Dedicated public navigation with one header only.
- Responsive mobile navigation styling.
- SaaS-style hero, features, workflow, pricing, testimonials and FAQ sections.
- Admin-managed CMS content with safe defaults.
- Redesigned multi-column footer with Product, Company and Resources links.
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

After migration, AI credentials, homepage content, hosted client links and the BEP20 wallet can be maintained from the admin panel.

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

Free HTML Sites:

```text
/admin/hosted-sites
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
- Validate and authorize admin settings, CMS and hosted-site actions.
- Rate-limit authentication, generation, payment and CMS endpoints.
- Treat CMS content as untrusted input and validate it before storage.
- Hosted site and setup links accept only HTTP/HTTPS URLs and are escaped by Blade.
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
7. Free HTML Sites section visible to clients
8. Admin → Free HTML Sites → add multiple site links
9. Admin → Free HTML Sites → hide/show/delete links
10. Project creation
11. Admin Settings → save AI provider/key/model
12. AI generation using saved Admin Settings key
13. Admin Homepage CMS → add/edit feature
14. Admin Homepage CMS → add/edit pricing
15. Admin Homepage CMS → add/edit FAQ
16. Admin Homepage CMS → add/edit testimonial
17. Homepage reflects CMS changes immediately
18. Free → paid plan selection
19. Admin Settings → update BEP20 wallet
20. BEP20 USDT payment instructions show updated wallet
21. TXID submission
22. Admin payment approve/reject
23. Subscription activation after approval
24. Mobile admin/user/public views
25. Production health check

## Repository

```text
mahfuzreham/GigRanker
```
