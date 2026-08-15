# GigRanker cPanel deployment

Production path: `/home/gigranker/public_html`

## First install

```bash
cd /home/gigranker
mkdir -p public_html
cd public_html
git clone git@github.com:mahfuzreham/GigRanker.git .
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
```

Configure `.env` with the database, mail, payment, notification and AI settings. Never commit `.env`.

## SSH deploy key

Create a dedicated cPanel deployment SSH key. Add the public key to cPanel SSH access and the private key as the GitHub Actions secret `CPANEL_SSH_KEY`.

Add these GitHub repository secrets:

- `CPANEL_HOST`
- `CPANEL_USER` = `gigranker`
- `CPANEL_SSH_PORT` (optional)
- `CPANEL_SSH_KEY`

The workflow `.github/workflows/deploy-production.yml` deploys every push to `main`.

## Manual update from cPanel terminal

```bash
cd /home/gigranker/public_html
git fetch origin main
git reset --hard origin/main
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If queue workers are configured, restart them after code changes:

```bash
php artisan queue:restart
```

## Safety

Do not run `git reset --hard` if the production directory contains manually edited tracked files. Keep production configuration in `.env` only.

Take a database backup before migrations. Run CI/security checks before merging to `main`.

## How updates reach users

A GitHub push to `main` triggers the deployment workflow. After a successful deployment, the running site uses the new code. Customer-facing feature announcements should be published separately through the Admin feature/update system so a code change is not automatically advertised as a customer feature.

Feature requests are submitted from `/feature-requests` and appear in **Admin → Feature Requests**. Admin can mark a request Pending, Planned, In Progress, Completed or Rejected and classify it as Free, Paid or Request.
