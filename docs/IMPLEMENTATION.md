# Implementation record

## Phase 1 — environment and architecture

- Workspace was empty; no existing source was replaced.
- PHP 8.2.4 with PDO MySQL/SQLite, Composer 2.7.7, Node 24.21.0, npm 11.19.0, Git 2.45.2.
- MySQL is reachable at 127.0.0.1:3306. Credentials and schema access still need verification.
- Laravel 12 is the latest framework line compatible with this PHP runtime. Use PHP 8.2 syntax; PHP 8.3-only lessons must be explicitly labeled.
- npm's PowerShell launcher is misconfigured on this machine. Use `node "C:/Program Files/nodejs/node_modules/npm/bin/npm-cli.js"` in its place.
- Read the Sites skill. This is an explicitly local Laravel/MySQL project, so no Sites hosting is created.
- Commands: environment/version inspection, TCP probe, Composer create-project. The sandbox blocked Composer networking; retried with native escalation.
- Tests: none yet; no application existed.

### Implementation plan

1. Install Laravel, official Breeze Vue authentication scaffolding, Inertia, Tailwind, PHPUnit, Pint, and Larastan.
2. Build normalized curriculum/content models, policy authorization, progress/bookmark/goal records and quiz grading.
3. Build a responsive Vue learning dashboard, library, lesson reader, playground, quizzes, profile, and content administration.
4. Seed a curated starter curriculum across all eleven requested modules. Track remaining curriculum depth honestly.
5. Run migrations, seeders, feature/security tests, frontend build, formatting, and static analysis. Document actual results and limitations.
6. Deliver installation, architecture, content, database, security, deployment, OOP, SOLID and test documentation.

### Risks and decisions

- Never execute user PHP; only display documented output with an explicit label.
- Quiz answer keys remain on the server until submission. Validate complete question/option ownership and grade transactionally.
- Public registration never accepts an administrator flag. Use policies on every content mutation and preview.
- Published queries exclude drafts and lessons in unpublished modules.
- Content is escaped text and code, not trusted HTML. Avoid user-controlled HTML rendering.
- Use uniqueness constraints, eager loading and bounded/paginated library/admin queries.
- MySQL for development; in-memory SQLite for isolated tests. A separate MySQL integration verification is required.

## Phase 2 — working foundation

- Created the Laravel app, Breeze Vue authentication scaffold, Inertia 3 adapters, Tailwind 4/Vite 8 setup, theme/navigation components, and database-backed dashboard.
- Created normalized learning schema/models, user/lesson/module factories, curriculum seeder, policies, and server-side admin/demo-account commands. `.env.example` uses a safe MySQL template.
- Commands: Composer create-project/require/update; Breeze install; npm install via its underlying CLI; `php scripts/provision-local-mysql.php`; `php artisan migrate`; `php artisan db:seed`; `php artisan academy:demo`.
- MySQL migrations and seeding passed. The scaffold's automatic SQLite migrations also ran before local MySQL configuration. The initial scaffold test pass was 24 passed / 1 failed because the original welcome-page test expected 200; corrected it to the intended login redirect. Default tests now force in-memory SQLite.
- Known environment issues: global npm launcher is broken; `scripts/npm.cjs` calls the installed CLI directly. The scaffold's default multi-worker development setting is unsupported on Windows and is commented out locally. Port 8000 cannot bind even with escalation; verified port 8873 works.
- Next: author content and connect the learning/admin workflows.

## Phase 3 — curated starter curriculum

- Added seven JSON content source files and `CurriculumSeeder`, importing 39 lessons, examples, exercises, short quizzes, and five project guides across all eleven modules.
- Covered core PHP and OOP, all five SOLID principles, Laravel foundations/advanced topics, grouped patterns, defensive security, testing, performance, deployment, and project blueprints.
- `php artisan db:seed` passed; the seeder idempotence test confirms existing editorial content survives re-seeding.
- `php scripts/verify-examples.php` ran 23 standalone PHP examples and compared their output: 23 verified, 0 failures. Framework snippets and blueprints are explicitly skipped and labeled.
- Known scope gap: this is a substantive starter course, not the entire exhaustive syllabus. See `CURRICULUM_ROADMAP.md` for expansion and complete project application work.
- Next: interactive learning and administration.

## Phase 4 — interactive workflows

- Added searchable/paginated library with filters; user-owned progress/bookmarks/goals/history; server-graded quizzes and retakes; lesson prerequisites/objectives, table of contents, previous/next navigation, highlighted local code editor with copy/reset and documented output.
- Added protected module/lesson editing, draft preview, publication, section and lesson ordering, code/output/exercise editing, quiz management, tag create/rename/remove, and aggregate learner statistics.
- Added transactional `QuizGrader` and `LessonEditor`; attempted quizzes are protected from revision. Quiz grading tests include multiple questions, partial input, and cross-question option rejection.
- Added regression fixes for preserved completion on revisits, eager loading in quiz editing, safe malformed-input handling, and changed-slug redirects. Vue's editor observes the current saved lesson so subsequent saves update the created/renamed record.
- Commands: route inspection, incremental feature tests, frontend builds, real HTTP smoke.
- Known limits: code output is documented rather than executed, project guides are seeded/readable rather than editable in the UI, and no visual browser was available.
- Next: final quality checks and documentation.

## Phase 5 — verified checks

Final checks were run against Laravel 12.70.0 and Inertia's PHP adapter 3.5.1. Frontend resolves Vue 3.5.44, Inertia Vue 3.9.1, Tailwind 4.3.3, and Vite 8.3.4. PHPUnit is 11.5.57, Pint 1.30.4, Larastan 3.13.0, and PHPStan 2.3.1.

| Check actually executed | Result |
| --- | --- |
| `php artisan migrate` / `migrate:status` on local MySQL connection | All four migrations ran |
| `php artisan db:seed` | 39 lessons across 11 modules; normalized child records |
| `php artisan test --compact` | 46 passed, 211 assertions |
| `php scripts/verify-examples.php` | 23 standalone PHP examples verified, 0 failures |
| `php vendor/bin/pint --test` | Passed |
| `php vendor/bin/phpstan analyse --no-progress --memory-limit=512M` | No errors at level 5 |
| `node scripts/npm.cjs run build` | Vite production build passed |
| `php C:/composer/composer.phar validate --strict` | composer.json valid; lock current |
| Composer dependency update audit / npm install audit | No vulnerability advisories reported at installation/update time |
| `node --check scripts/http-smoke.mjs` / `scripts/npm.cjs` | Passed |
| `node scripts/http-smoke.mjs` against local server | Passed: login/logout, real CSRF 419, Inertia protocol, database learning flows, quiz answer hiding/grading, bookmark/progress/history, learner admin denial, admin edit preserving attempted quiz |
| Git ignore checks | Environment, demo credentials, logs, generated assets, SQLite runtime DB ignored |

Earlier checks found and fixed the scaffold redirect expectation, missing model relationship generic annotations, an eager-loading omission when comparing attempted quiz content, changed-slug redirect behavior, and malformed nested-input handling. No suppressed Larastan errors or baseline were added. Pint was run to fix formatting, then its check passed. An initial Pint check caught the newly added example-verification script; it was formatted and rechecked successfully.

HTTP verification runs retain real quiz attempts and lesson-view history on the local learner demo account. They restore its original completion/bookmark flags; statistics are calculated from those actual records. The HTTP helper uses native Node HTTP because this installed Node build's fetch/Undici path failed internally. The helper handles Inertia 3 bootstrap data and asset-version headers.

No visual browser was available. Responsive/theme/clipboard/keyboard interactions are implemented but not visually certified. No production hosting, load benchmark, real SMTP/payment, PDF, broadcasting, or complete inventory/ERP application was verified. MySQL lock concurrency for future exercise applications needs its own disposable integration suite.

## Phase 6 — documentation and handoff

- Added/replaced README, `.env.example`, and docs for architecture, database, content authoring, OOP, SOLID, testing, security, deployment, implementation history, and remaining curriculum. Architecture/database/class relationships include Mermaid diagrams. Added an Nginx example.
- Added local npm wrapper and explicit server startup instructions for port 8873. Generated demo access lives in `storage/app/demo-access.txt`; it is ignored and no password is committed.
- Git repository initialized. Git status/ignore inspection used a command-local safe.directory setting for this workspace because the sandbox account differs from the workspace owner; no global Git configuration was changed. No commit, remote, or publication was performed.
- Delivered a working starter platform with real learner/admin functionality and meaningful content. The exhaustive requested course, full exercise applications, richer question banks, guide editing, and production/browser verification remain explicitly listed in `CURRICULUM_ROADMAP.md`.
- Next implementation milestone: expand PHP/OOP lesson depth and pattern-specific runnable examples/tests, then build the guided inventory and ERP applications as separate exercise projects. Keep the academy runnable and repeat relevant checks as those milestones are added.

### Database identification follow-up

`php artisan academy:status` confirmed the preinstalled service is **MariaDB 10.4.28**, not MySQL 8. Actual counts are 11 modules, 39 lessons, 39 quizzes, and 5 project guides. The machine-specific helper now detects MariaDB and selects Laravel's `mariadb` driver; the portable `.env.example` retains the requested MySQL configuration. A direct MySQL `db:show --database=mysql` diagnostic failed because this MariaDB service lacks MySQL's performance_schema.session_status table; this was a diagnostic compatibility issue, not a failed application query. Local database version is disclosed in README/DATABASE rather than represented as a MySQL 8 installation.

After selecting `mariadb`, the database diagnostic and full HTTP smoke passed again. Pint formatted the new status command and its check passed again. The local smoke server is only for verification; use the README startup command for an interactive session.
