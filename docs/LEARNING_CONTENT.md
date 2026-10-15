# Educational content and contribution guide

The starter course contains 39 authored lessons across eleven modules. Definitions, substantive explanations, analogies, common mistakes, Laravel applications, interview answers, code walkthroughs, exercises with revealed solutions, quizzes, and takeaways are included. This is a curated starter, not the entire exhaustive syllabus in the request.

## Sources and storage

Authoring fixtures live in `database/content/*.json`. `CurriculumSeeder` imports them into normalized database records. Every JSON lesson includes module index, title, definition, why/how, analogy, code, expected output, steps, bad practices, Laravel application, exercise, solution, question, options, correct option index, interview answer, takeaways, and tags. Optional fields include objectives, prerequisites, estimated minutes, language, security notes, and a project blueprint.

The fixtures are repository content, not application code to execute. Only explicitly standalone examples are executed by the CLI verification helper. Mark framework snippets and blueprints `"runnable": false`. The web playground never executes any PHP and does not recalculate output after edits.

## Lesson reader order

1. Title, difficulty, time, and prerequisites.
2. Learning objectives and definition.
3. Explanation, analogy, common mistakes, Laravel application, interview answer, and takeaways.
4. Syntax/example, numbered walkthrough, and documented output.
5. Exercise and optional revealed solution.
6. Optional guided project milestones and acceptance criteria.
7. Short server-graded quiz and lesson completion.
8. Previous/next recommended lesson.

Admins can reorder prose sections and lessons. The reader groups examples after prose; it does not yet provide arbitrary interleaving of code among sections. Expanded lessons should add dedicated bad/better code pairs and executable unit tests where appropriate, rather than assuming an explanatory warning replaces them.

## Editorial checks

- State the PHP/framework version. Baseline is PHP 8.2 and Laravel 12; label later-language features rather than inserting syntax the installed runtime cannot parse.
- Explain why, when, and trade-offs, including cases where an interface, repository, trait, or pattern adds no value.
- Keep units, failure behavior, ownership, and output explicit.
- Distinguish standalone examples, framework snippets requiring other classes, and project blueprints.
- Use safe text, not trusted arbitrary HTML. Do not add v-html for stored prose.
- Include a concrete exercise, a solution approach, and an unambiguous quiz answer with feedback.
- Do not invent performance results or claim live provider integrations.
- Run `php scripts/verify-examples.php` for standalone PHP and the application checks after content changes.

## Admin workflow

Sign in as an admin, open Content administration, create/edit modules, create a lesson, add sections/code/output/exercises/tags/quiz, save a draft, preview, then publish. A lesson appears to learners only when its module is also published. `Manage tags` supports create/rename/remove; removing a tag detaches it from lessons without deleting prose.

Quizzes may have up to 30 questions and 2–6 options per question through validated content data; the visual editor starts questions with four options. The starter has one focused question per lesson. Multi-question grading is covered by tests. After the first attempt, its quiz configuration is locked; create a new lesson for a revised assessment. Other lesson content stays editable.

Admin changes live in the database. Export/reconcile them into reviewed seed fixtures separately; no automatic export is implemented. Re-running seeders inserts missing slugs and deliberately preserves existing editorial changes.

See [remaining curriculum](CURRICULUM_ROADMAP.md) before describing the entire requested syllabus as finished.
