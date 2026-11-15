# Production Deployment

This application can run on Laravel Cloud or another managed Laravel host. A deployment has not been created from this workspace: the directory currently has no Git repository or remote configured. Hosting access, a pushed repository, production environment variables, and a public domain are still required.

## Runtime requirements

- PHP 8.3 or newer (the project currently requires `^8.3`).
- Composer 2.
- Node.js 22 or newer and npm for the frontend build.
- PostgreSQL for production. The application has a configured PostgreSQL connection suitable for Neon.
- A persistent shared filesystem or S3-compatible object storage for public business and product images.

Point the web server's document root at the repository's `public` directory only. Do not expose the repository root, `.env`, `storage`, or `vendor` directly. Laravel Cloud's Laravel application deployment uses the public entry point.

## Repository and hosting setup

1. Put this project in a private GitHub repository and push the deployment branch. The current workspace is not a Git repository, and no public repository matching this project was found during this task.
2. Create a Laravel Cloud account/project (including any required billing), connect the repository, and select the production branch.
3. Use PHP 8.3+ and Node.js 22+; configure the project root and ensure the web root is `public/`.
4. Create or select the production PostgreSQL database. The existing project context identifies Neon; keep its connection secret in the hosting environment, not in source control.
5. Add the environment variables listed below. Set `APP_URL` to the final HTTPS URL and configure the host's domain/DNS and TLS certificate.
6. Configure shared persistent media storage before accepting uploads. If migrating existing local uploads to object storage, copy them while preserving their relative paths (the paths are stored in the database).
7. Deploy, run migrations only after reviewing and backing up the production database, then check the health endpoint and perform the manual browser checks below.

## Environment variables

Set these in the hosting platform's secret/environment settings. The names are listed without secret values.

**Application and security**

- `APP_NAME`
- `APP_ENV` (`production`)
- `APP_KEY` (generate a unique key in the host or with `php artisan key:generate --show`; never commit it)
- `APP_DEBUG` (`false`)
- `APP_URL` (the public HTTPS origin)
- `LOG_CHANNEL`
- `LOG_LEVEL` (normally `warning` or `info`)
- `SESSION_DRIVER`
- `SESSION_SECURE_COOKIE` (`true`)
- `SESSION_LIFETIME`
- `SESSION_DOMAIN` (only when cross-subdomain sessions are needed)
- `SESSION_ENCRYPT`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` (`https://<your-production-domain>/auth/google/callback`)

For local development, set `GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback`. In Google Cloud Console, register that exact URL as an authorized redirect URI for the local OAuth client, and register the production HTTPS callback URL for the production client. Keep client credentials in the environment's secret settings.

**Database**

- `DB_CONNECTION` (`pgsql`)
- `DB_URL` (the PostgreSQL connection string, stored as a secret)
- `DB_SSLMODE` (`require` for Neon)

Use the platform's preferred connection-string format and connection pooling guidance. Do not put database credentials in `APP_URL`, logs, workflow files, or this guide.

**Cache and queue**

- `CACHE_STORE`
- `QUEUE_CONNECTION`

The current application does not dispatch queued jobs or define scheduled tasks. A queue worker and scheduler are not required unless future features add asynchronous work or scheduled commands. The current email/password-reset flow is synchronous.

**Public media storage**

- `MEDIA_DISK` (`s3` for an S3-compatible bucket, or `public` only with persistent shared storage)
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_URL` (optional, if the provider supplies a public base URL)
- `AWS_ENDPOINT` (optional, for S3-compatible providers)
- `AWS_USE_PATH_STYLE_ENDPOINT` (optional; use the provider's required setting)

The filesystem configuration already defines `public` and `s3` disks. Uploaded images use `MEDIA_DISK`; `s3` requires `league/flysystem-aws-s3-v3`, which is not currently installed in this project. Do not select `MEDIA_DISK=s3` until that dependency is explicitly approved and added. As-is, use a durable shared local volume or make adding the adapter the remaining storage setup action. Configure any bucket for public reads of uploaded image objects, or use a CDN/public URL supported by the provider. Keep object deletion enabled for uploaded files.

If `MEDIA_DISK=public`, the host must provide a durable filesystem shared by all web instances and preserve it across releases. Run `php artisan storage:link` so `public/storage` points to `storage/app/public`. An ephemeral deployment filesystem is not suitable: uploaded logos, cover images, and product images would be lost on redeploy or instance replacement.

**Password-reset email**

- `MAIL_MAILER`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `RESEND_API_KEY` (when using the configured Resend mailer)

Registration does not send verification email. Configure mail delivery if users should be able to request password resets.

## Build and deployment commands

Use the following build sequence in the host's build configuration:

```sh
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
```

Do not use `composer setup` in production: that project convenience script runs migrations automatically.

After deployment, the host may run these safe application-cache commands:

```sh
php artisan optimize:clear
php artisan optimize
```

For local-disk media only, create the storage link once for the persistent release/runtime filesystem:

```sh
php artisan storage:link
```

Do not run `storage:link` as a substitute for persistent storage. With `MEDIA_DISK=s3`, image URLs are generated by the configured disk and the public-storage symlink is not needed for uploaded media.

## Database migration procedure

No migrations have been applied to the configured PostgreSQL database by this task. The read-only status check found these six migrations pending at the time of this preparation:

- `2026_11_12_110212_create_products_table`
- `2026_11_12_124033_add_is_active_to_users_table`
- `2026_11_12_124040_create_reviews_table`
- `2026_11_12_124045_create_review_replies_table`
- `2026_11_12_124057_create_business_reports_table`
- `2026_11_12_124126_create_business_profile_views_table`

Before any production migration:

1. Take and verify a restorable PostgreSQL backup.
2. Review the current schema and run `php artisan migrate:status` against the intended production connection.
3. Confirm that the pending migrations and application release are approved.
4. Run this exact command as a controlled deployment step:

```sh
php artisan migrate --force
```

Do not run `migrate:fresh`, `db:wipe`, rollback, reset, truncate, or seed commands against production. The historical email-OTP migration has already run on the inspected database; its unused columns are intentionally retained. No OTP-column removal migration is included.

## Admin access

Public registration only allows customer or business-owner accounts. Register a trusted account normally, then provision the first administrator through the protected deployment console:

```sh
php artisan app:promote-admin trusted-user@example.com
```

Use the real registered account email in the host console; do not expose admin provisioning as a public route.

## Health checks and completion

- Configure the platform health check to `GET /up`; Laravel already provides this endpoint.
- Confirm HTTPS redirects/cookies, login/logout, customer and business-owner registration, role restrictions, image upload/display, directory search, and the admin dashboard in a real browser.
- Obtain the assigned public URL from the hosting application's domain/deployment panel after a successful release. Do not advertise the site as live until that URL responds over HTTPS and the checks above pass.

## Automated checks

The existing GitHub Actions workflow runs Composer checks and tests against in-memory SQLite, then builds frontend assets with `npm ci` and `npm run build`. It does not connect to Neon or run production migrations.
