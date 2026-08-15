# GigRanker

**Turn Your Freelance Gig Into an SEO Marketing Website.**

GigRanker is a planned Laravel SaaS platform for creating SEO-focused marketing websites around freelance service listings. Users provide their gig/service information, target market and branding; GigRanker generates structured SEO content, service pages, blog content, internal links, schema markup, sitemap/robots files and conversion-focused CTAs.

## Product goals

- Gig-focused SEO marketing websites
- AI-assisted content generation
- Structured JSON output + controlled HTML templates
- Service pages and blog generation
- SEO metadata, canonical URLs, Open Graph and Schema.org
- Internal linking and sitemap generation
- Live preview and ZIP export
- Click tracking for outbound gig CTAs
- Subscription and usage-credit system
- bKash and BEP20 payment support
- Admin-controlled AI providers and usage limits

## Planned stack

- Laravel / PHP
- MySQL or MariaDB
- Blade + Tailwind CSS
- Queue workers for AI generation
- Server-side AI provider abstraction
- cPanel production deployment

## Security principles

- Never commit `.env` or API secrets.
- AI/API credentials stay server-side.
- Payment secrets stay server-side.
- Validate and authorize every project/resource request.
- Rate-limit generation and authentication endpoints.
- Treat AI output as untrusted data.
- Sanitize generated HTML before preview/export where applicable.
- Keep dependencies updated and run security checks before production releases.

## Deployment target

Production cPanel account: `gigranker`

Production path: `/home/gigranker/public_html`

Repository: `mahfuzreham/GigRanker`

## Deployment history / logging

GigRanker now records deployment lifecycle events in the `deployments` table. Each record includes a UUID, environment, release version, Git commit SHA, status, start/finish timestamps, duration, trigger/source information, message and optional metadata.

The `DeploymentLogger` service supports starting a deployment and marking it successful or failed. The Artisan command below can be used by cPanel deployment scripts, CI jobs or administrators:

```bash
php artisan gigranker:deployment start --environment=production --version=2026.08.16 --triggered-by=github-actions --source=main
php artisan gigranker:deployment success --id=<deployment-uuid> --message="Deployment completed"
php artisan gigranker:deployment fail --id=<deployment-uuid> --message="Deployment failed"
php artisan gigranker:deployment list
```

The application can also pick up the Git commit SHA from `GIT_COMMIT` or `GITHUB_SHA` when a commit is not explicitly supplied. Deployment history is intended to become the audit source for the upcoming pre-deployment backup and rollback features.

## Status

Deployment history/logging is implemented. The project remains under development and is not production-ready yet.
