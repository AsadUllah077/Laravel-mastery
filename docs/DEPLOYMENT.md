# Deployment checklist

This session builds and verifies a local application; it does not publish a production service. The Sites skill's local workflow is used because PHP/MySQL is explicitly requested. Cloudflare Worker Sites hosting is not a Laravel PHP host.

## Before release

- Use a patched PHP runtime compatible with Laravel 12 and its extensions, MySQL, a web server/PHP-FPM, Node for asset builds, and Composer.
- Run the tests, formatting check, static analysis, trusted example verification, and frontend build from locked dependencies.
- Provision a dedicated database account with only needed permissions. Preserve the production APP_KEY and configure secrets outside source control.
- Set APP_ENV=production, APP_DEBUG=false, APP_URL to the HTTPS origin, and SESSION_SECURE_COOKIE=true. Set the intended APP_TIMEZONE deliberately.
- Configure real mail delivery and verify reset/verification links. Do not use generated local demo accounts in production.
- Set document root to `public/`; allow writes only where required under storage and bootstrap/cache. Configure backups, retention, restore checks, monitoring, and error reporting.

## Release steps

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Build Node assets in CI if production does not carry Node/dev dependencies. Never run composer update during a release; changes to locks belong in reviewed development work. Seed a new installation deliberately with `php artisan db:seed --force`; seeders insert missing lessons and preserve existing edits. Create a production admin using `php artisan academy:create-admin admin@example.com` and its hidden password prompt.

For the current academy, learner/admin operations are synchronous and no custom recurring jobs or live broadcasting services are required. If mail/exports are queued later, supervise `php artisan queue:work`, configure retry_after/timeout/backoff coherently, monitor failed jobs, and restart workers during releases. Configure the scheduler's cron when actual scheduled tasks are added; do not advertise a scheduler feature that has no jobs.

Use [deploy/nginx.conf.example](../deploy/nginx.conf.example) as a reviewed starting point, replacing paths, hostnames, and the PHP-FPM socket. Add real TLS certificates and redirect HTTP to HTTPS. The example alone is not a complete hardened server configuration.

## Verification and recovery

Check `/up`, sign-in, a published lesson, quiz persistence, and denied admin access. `/up` alone is not a complete database/cache/queue readiness probe. Inspect logs and asset responses, then monitor error and latency trends. Test supported viewports and accessibility with a real browser before release.

Use backward-compatible schema expansion, controlled backfills, and later contraction for rolling changes. Keep old/new code compatible during a switch. A code rollback cannot automatically undo new data, and migrate:rollback can lose data. Test restore into a separate schema and document recovery duration and acceptable data loss. Retain a known-good release or image and a clear routing-switch procedure.

No automated CI deployment, Docker image, TLS service, load test, restore drill, or production rollback has been performed in this workspace. Those are follow-on operational milestones.
