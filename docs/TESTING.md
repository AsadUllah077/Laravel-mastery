# Testing and verification

## Commands

```sh
php artisan test
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --no-progress --memory-limit=512M
php scripts/verify-examples.php
npm run build
```

On this Windows machine use `node scripts/npm.cjs run build` if the npm launcher fails. `composer test`, `composer analyse`, and `composer format` are configured aliases. Pint formats PHP; Larastan checks `app/` at level 5 without a baseline or suppressed errors. Scripts/fixtures have separate verification, not a claim of full static typing.

## Database isolation

`phpunit.xml` forces `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. Tests use `RefreshDatabase`, factories, and explicit nested fixtures. They must not be pointed at production. SQLite feature tests do not establish MySQL lock/concurrency semantics; the local HTTP smoke separately verifies real application reads and writes against the configured development database.

## What is covered

- Breeze login/logout, registration, password reset/update, email verification, profile update/deletion.
- Authentication/verification requirements and privilege escalation rejection.
- Published-only discovery, draft preview authorization, and progress preservation.
- Search, literal wildcard handling, user-owned goals/progress/bookmarks, duplicate prevention.
- Multi-question quiz scoring, persisted answers, retakes, invalid membership/partial submissions.
- Authorized content create/edit/publish, nested validation, transaction rollback, locked attempted quizzes.
- Lesson membership/order validation, slug redirects, protected tag management, malformed nested input.
- Library query count stays bounded when lesson count grows from one to twelve.
- Seed idempotence preserves editorial changes.

`php scripts/verify-examples.php` launches only trusted repository-authored standalone PHP fixtures in separate CLI subprocesses, with a timeout, and compares actual output. It does not execute framework snippets or project blueprints. It is not a web sandbox and must never be connected to user code input.

## Real HTTP smoke

```sh
php artisan academy:demo
php artisan serve --host=127.0.0.1 --port=8873 --no-reload
# Separate terminal:
node scripts/http-smoke.mjs
```

The helper only accepts loopback and reads generated local credentials from the ignored `storage/app/demo-access.txt`. It checks login/logout, an actual 419 CSRF response, Inertia asset versioning, MySQL-backed learning flows, hidden answer keys, quiz feedback, history, learner denial, and a valid admin edit preserving attempted quiz history. It restores completion/bookmark flags but retains real quiz attempts and lesson-view history on the local learner account. It is a development smoke test, not a production script.

## Verification limits

No automated visual browser was available in this session. Responsive CSS, keyboard focus, reduced-motion support, and light/dark themes are implemented, but viewport interaction, clipboard permissions, mobile navigation, screen-reader behavior, and visual contrast still need browser review. No production load benchmark, real email delivery, external payment, PDF renderer, broadcasting server, or complete ERP was verified. Exact command results are recorded in [IMPLEMENTATION.md](IMPLEMENTATION.md).
