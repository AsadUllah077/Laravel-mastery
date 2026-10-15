# Requested scope and remaining work

The academy workflows are implemented with real database persistence. The original brief also requests a comprehensive beginner-to-expert course and complete example applications; that educational scope remains larger than this starter delivery. This file prevents confusing a working platform with a finished exhaustive curriculum.

## Seeded content

| Module | Lessons | Current focus |
| --- | ---: | --- |
| PHP Fundamentals | 4 | Syntax/types, arrays/transforms, closures/exceptions, Composer/namespaces/enums/generators |
| Object-Oriented PHP | 11 | Classes, visibility, inheritance, abstraction, interfaces, traits, polymorphism, composition, static binding, values/entities, magic/cloning |
| SOLID Principles | 5 | All five dedicated principles with concrete examples and trade-offs |
| Laravel Fundamentals | 3 | Request lifecycle, routing/validation/controllers, Eloquent/migrations/queries |
| Advanced Laravel | 3 | Container/providers, queues/retries, caching/invalidation |
| Design Patterns | 2 | Strategy/factory/adapter and decorator/observer/state group lessons |
| Laravel Security | 3 | Query/input boundaries, XSS/CSRF, resource authorization |
| Testing & Code Quality | 1 | Test boundaries, fakes, database isolation, quality tools |
| Performance & Scalability | 1 | Measurement, query growth, bounded iteration |
| Deployment & DevOps | 1 | Reproducible releases and recovery checklist |
| Real-World Projects | 5 | Payment, inventory, notifications, invoicing/reporting, mini ERP blueprints |
| **Total** | **39** | **23 standalone PHP examples verified; 39 short quizzes and exercises** |

The starter quizzes have one focused multiple-choice question each; the application supports multi-question grading, tested with two questions. Included lessons combine some advanced topics rather than providing a dedicated in-depth lesson for every subheading of the request.

## Next curriculum milestones

1. Expand PHP into dedicated lessons for all 25 requested topics: operators/control flow, detailed scalar/array APIs, annotations/PSRs, references/scope, modern language features, and additional exercises/tests. Expand OOP into deeper covariance/contravariance, object lifecycle, domain exceptions, aggregate boundaries, and identity/immutability case studies.
2. Add complete bad/refactored PHP and Laravel code pairs, runnable unit tests, multiple quiz questions, and graded exercise milestones for each SOLID principle; finish a complete combined SOLID example application.
3. Expand Laravel fundamentals: all route forms/constraints/signatures/caching, Blade layouts/components/security, controllers/responses, conditional/nested/file validation, middleware ordering, every Eloquent relationship/cast/scope/event/factory, SQL/index/deadlock/isolation examples, and safe schema evolution.
4. Expand advanced Laravel: API CRUD with Sanctum/policies/resources and tests, authorization roles/permissions, events/listeners/observers, email/notifications, secure storage/uploads, scheduling/commands, broadcasting/Reverb/Echo, advanced SQL/locks, DTO/domain architecture and justified repositories. Current snippets are not complete deployed services.
5. Give all eighteen requested patterns dedicated implementations, diagrams, when-not-to-use discussion, Laravel adapters, and tests. Add Builder, Command, Abstract Factory, Repository, Service Layer, DI, Singleton lifetime concerns, Specification, Chain of Responsibility, DTO, and Value Object depth beyond current grouped coverage.
6. Expand defensive security examples and regression tests for every requested vulnerability, including file/path controls, SSRF, deserialization, token/session security, webhook signatures, privacy/encryption, headers, and dependency review. Do not expose deliberately vulnerable routes in the academy.
7. Expand testing into dedicated unit/integration/HTTP/API/queue/storage/event/notification/authorization/coverage/refactoring lessons. Add browser tests and a MySQL-specific concurrency suite using a separate disposable schema.
8. Add reproducible load benchmarks, monitoring scenarios, Octane lifecycle, Redis/scaling/CDN trade-offs, production checklists, Docker/CI examples, and a tested backup/restore drill. No invented performance results.
9. Implement the five guided applications as separate runnable exercise projects with their migrations, factories, policies, controllers/actions, tests, and documentation. Inventory API, live notification delivery, PDF generation, and the full ERP are currently blueprints, not finished application code.

## Product refinements still open

- Real browser review of desktop/mobile layout, keyboard and screen-reader flows, themes, and clipboard permissions.
- Richer quiz banks, exercise submissions/progress, learning-path selection, content-version migration/export, and arbitrary prose/code interleaving if desired.
- Project-guide editing in the admin UI (guides are currently seeded/readable; prose/code/exercises/quizzes are editable).
- Production hosting, real mail configuration, security-header/CSP integration, CI/CD, monitoring, restore tests, and independent operational/security review.

No core learner or content-admin operation is a static-only mockup. The code playground is intentionally a local editor with documented output, as permitted by the request; it is not an isolated PHP runtime. Project blueprints are explicitly described as guided learning plans rather than completed ERP/payment services.
