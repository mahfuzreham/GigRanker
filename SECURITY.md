# Security Policy

## Reporting a vulnerability

Please do not publish credentials, API keys, payment secrets or exploitable details in a public issue. Report security issues privately to the project maintainer through the repository's available private contact channel.

## Security requirements

- Secrets must remain outside Git history.
- Production must use `APP_DEBUG=false`.
- Authentication and generation endpoints must be rate limited.
- Authorization must be enforced server-side for every project and payment resource.
- Payment callbacks/webhooks must be verified before changing subscription state.
- AI-generated content must be treated as untrusted input.
- User-generated HTML must be sanitized before being rendered in an application preview.
- Outbound URLs should be validated to prevent unsafe redirects where applicable.
- Dependencies should be audited before production deployment.
