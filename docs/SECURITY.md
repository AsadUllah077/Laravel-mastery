# Security practices

## Implemented boundaries

Authentication comes from Laravel Breeze's generated session flow, including hashed passwords, session regeneration, login throttling, secure logout, password reset, and signed email verification. Learning routes require verified accounts. Registration accepts only name, email, and password; it cannot set administrator privilege. Admin creation is a server-side command with a hidden password prompt; local demo credentials are generated and stored in ignored storage.

All admin routes require a gate. Lesson policies govern create/update/preview; learners cannot access drafts through direct URLs. User progress/bookmarks/goals derive ownership from the authenticated relationship rather than a submitted user ID. Database uniqueness and foreign keys reinforce application rules.

Quiz answer keys and explanations are hidden before submission. The grader validates the entire answer set and question-option membership; it persists attempts and answers in a transaction. Submissions are rate-limited. Content editing shares a quiz lock and protects attempted question history from revision/deletion.

Form Requests validate nested content and allowed languages, difficulties, sizes, and slugs. Complex edits run transactionally. Eloquent fillable fields exclude privileged user attributes. Search values are bound and LIKE wildcards escaped for literal matching; no user-selected SQL syntax is accepted.

Vue text interpolation escapes stored prose, expected output, and exercise content. Code highlighting inserts highlight.js-generated escaped markup only. There is no arbitrary HTML content renderer, PHP execution route, eval, or user-controlled subprocess. Browser code editing remains local and output is explicitly documented. The CLI fixture verifier accepts only repository-authored content and must not become an application endpoint.

## Environment and operations

- Keep `.env`, APP_KEY, database credentials, demo-access files, logs, and backups outside commits and public web access.
- Use HTTPS and secure cookies in production; disable APP_DEBUG and serve only `public/`.
- Use patched, supported PHP/packages and review Composer/npm audit output. A successful audit is not a complete security assessment.
- Use a least-privilege MySQL account; the machine-specific provisioning helper targets a local passwordless XAMPP root account and must never run in production.
- Mail defaults to the log transport for local development. Reset/verification links in logs are sensitive; configure delivery and retention before production.
- Do not cache or log tokens, passwords, or cross-user data. Scope any future cache by relevant owner/tenant and authorization dimensions.
- No uploads/private downloads/webhooks/broadcasting are currently exposed by the academy. Add file access policies, safe generated names, limits, signed links, webhook verification, SSRF controls, and tests when those features are introduced.
- An app-specific CSP/nonce rollout, security headers, production access logging, backup/restore automation, and independent security review are deployment work. Do not describe the starter as production-audited.

See `LearningAppTest` for denial, ownership, validation, and escalation regression tests. The HTTP smoke verifies CSRF outside Laravel's normal test bypass. Report an actual security problem privately to the project owner; do not put secrets in a public issue.
