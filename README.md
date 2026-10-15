# Laravel Mastery Academy

A working learning platform for PHP, OOP, SOLID, and Laravel: authenticated dashboards, searchable lessons, local code editing, saved bookmarks, persistent progress, server-graded quizzes, goals, history, and protected content administration.

This delivery is a functional starter application with **39 authored lessons across eleven modules**, including five guided project blueprints. It is **not the complete exhaustive course or a finished ERP** described in the original brief. [Remaining scope](docs/CURRICULUM_ROADMAP.md) and [actual verification results](docs/IMPLEMENTATION.md) are documented explicitly.

## Stack

Laravel 12, PHP 8.2-compatible syntax, official Breeze session authentication, Vue 3 Composition API, Inertia 3, Tailwind 4, Vite 8, MySQL, PHPUnit, Pint, and Larastan level 5. The installed PHP is 8.2.4, so Laravel 12 is used instead of Laravel 13/PHP 8.3+. Exact resolved dependencies are recorded in `composer.lock` and `package-lock.json`.

## This workspace

The local XAMPP service is MariaDB 10.4.28 (MySQL-compatible), using Laravel's `mariadb` connection. Schema `laravel_mastery_academy` is provisioned and seeded; `.env` contains this machine's local configuration and is ignored. The safe `.env.example` targets MySQL for clean installations. Assets have been built. Start the app from this directory:

```powershell
php artisan serve --host=127.0.0.1 --port=8873 --no-reload
```

Open **http://127.0.0.1:8873**. Port 8000 was unavailable on this machine. Generated learner/admin credentials are in [storage/app/demo-access.txt](storage/app/demo-access.txt), an ignored local file. The HTTP smoke test retained actual lesson-view history and quiz attempts on the learner account; completion and bookmark flags were restored.

The machine's npm launcher is misconfigured. A local wrapper calls its underlying CLI without changing the global installation:

```powershell
node scripts/npm.cjs run build
node scripts/npm.cjs run dev
```

Use the production asset build with `artisan serve`, or keep the Vite dev process running in a second terminal while editing frontend code.

## Clean installation

Requirements: PHP 8.2+ with Laravel-required extensions and PDO MySQL, Composer, MySQL, and a Node version supported by Vite 8 (this workspace uses Node 24). Use a patched supported runtime for production.

```sh
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
```

On PowerShell, use `Copy-Item .env.example .env`. Configure DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD in `.env`. Create a dedicated schema and least-privilege user first; never overwrite an existing configured `.env` blindly.

```sh
php artisan migrate
php artisan db:seed
php artisan academy:create-admin admin@example.com --name="Academy Admin"
php artisan serve --host=127.0.0.1 --port=8873
```

The admin command prompts for a password of at least 12 characters without echoing it. Public registration creates learners only. Configure SMTP for real email verification/password resets; local `MAIL_MAILER=log` writes links to `storage/logs/laravel.log`. Seeded/demo admin accounts are explicitly verified for local use.

Optional: `php artisan academy:demo` creates local learner/admin accounts with generated passwords and records them in ignored storage. It refuses non-local environments and never resets an existing account password. `scripts/provision-local-mysql.php` is a machine-specific convenience for a local passwordless XAMPP root service; do not use it in production or as a general credential configuration tool.

For convenient local access, `DatabaseSeeder` creates or resets the verified administrator `admin@academy.test` with password `Admin@123456` before seeding the curriculum. This runs only when `APP_ENV=local`; each local `php artisan db:seed` resets that administrator password.

## Use the academy

- Start at Overview: resume a lesson, inspect real progress, adjust daily/weekly goals, and follow the learning path.
- Search the library by title, concept, lesson text, tag, or code. Filter by module, difficulty, tag, or bookmarks.
- Read a lesson, edit/copy/reset examples, reveal documented output, attempt exercises and quizzes, then mark completion. Edited PHP is never executed by the application.
- Bookmark lessons and inspect progress/quiz history. Each account owns its records.
- Admins open Content administration to manage modules, lesson order, prose, examples/output, exercises, tags, quizzes, draft previews, publication, and aggregate completion statistics.
- A lesson is discoverable only if both it and its module are published. Quizzes with attempts are locked against changes to protect history; create a new lesson to revise the assessment.

## Verification

```sh
php artisan test
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --no-progress --memory-limit=512M
php scripts/verify-examples.php
npm run build
```

Tests force in-memory SQLite. An additional `node scripts/http-smoke.mjs` check runs against the local server/MySQL using generated local credentials; it records real learner history. See [TESTING.md](docs/TESTING.md) for coverage and side effects. Visual browser/viewport and clipboard interaction review remains outstanding; no production deployment or external provider delivery is claimed.

## Documentation

- [Architecture and lifecycle](docs/ARCHITECTURE.md)
- [Database schema and relationships](docs/DATABASE.md)
- [Content format and authoring](docs/LEARNING_CONTENT.md)
- [OOP guide](docs/OOP_GUIDE.md) and [SOLID guide](docs/SOLID_GUIDE.md)
- [Testing](docs/TESTING.md), [security](docs/SECURITY.md), and [deployment](docs/DEPLOYMENT.md)
- [Implementation record](docs/IMPLEMENTATION.md) and [remaining curriculum](docs/CURRICULUM_ROADMAP.md)

Git is initialized; no commit or remote publication is performed. `.env`, vendor/node_modules, generated assets, runtime data, logs, and local credentials are ignored. Re-running the curriculum seeder adds missing slugs and preserves editorial changes. Back up the database before schema or editorial maintenance.
