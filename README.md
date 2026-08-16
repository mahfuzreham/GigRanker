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

GigRanker records deployment lifecycle events in the `deployments` table. Each record includes a UUID, environment, release version, Git commit SHA, status, start/finish timestamps, duration, trigger/source information, message and optional metadata.

Use the deployment logger command from cPanel deployment scripts, CI jobs or administrators:

```bash
php artisan gigranker:deployment start --environment=production --version=2026.08.16 --triggered-by=github-actions --source=main
php artisan gigranker:deployment success --id=<deployment-uuid> --message="Deployment completed"
php artisan gigranker:deployment fail --id=<deployment-uuid> --message="Deployment failed"
php artisan gigranker:deployment list
```

## Rollback

Rollback targets must reference a previously successful deployment. By default the command only validates the target and creates an audit record; it does not modify the working tree.

```bash
php artisan gigranker:rollback <deployment-uuid>
```

To execute the code rollback on a Git working tree, an explicit confirmation is required:

```bash
php artisan gigranker:rollback <deployment-uuid> --execute --yes
```

The rollback service fetches repository refs, verifies the target commit, and resets the working tree to that commit. It records the rollback as a new deployment-history entry, including the previous and resulting commit SHA. **Database migrations are never reversed automatically**; schema rollback must be handled separately and deliberately.

## Status

Deployment history/logging and safe rollback are implemented. Pre-deployment backups and production health checks remain before production readiness.
