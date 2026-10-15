// HTTP checks against this local application only. Does not print credentials or cookies.
import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import http from 'node:http';

const base = new URL(process.argv[2] || 'http://127.0.0.1:8873');
assert.equal(base.hostname, '127.0.0.1', 'Only the local application may receive demo credentials.');
const access = readFileSync(new URL('../storage/app/demo-access.txt', import.meta.url), 'utf8');
const password = access.match(/Learner: learner@academy\.test\r?\nPassword: ([^\r\n]+)/)?.[1];
assert.ok(password, 'Create local demo accounts with php artisan academy:demo first.');
const cookies = new Map();
let version = '';
async function request(path, { method = 'GET', data, csrf = true, inertia = false } = {}) {
    const headers = { Accept: 'application/json', Cookie: [...cookies].map(([key, value]) => key + '=' + value).join('; ') };
    if (inertia) { headers['X-Inertia'] = 'true'; headers['X-Inertia-Version'] = version; }
    if (csrf && cookies.has('XSRF-TOKEN')) headers['X-XSRF-TOKEN'] = decodeURIComponent(cookies.get('XSRF-TOKEN'));
    if (data) headers['Content-Type'] = 'application/json';
    const response = await new Promise((resolve, reject) => {
        const body = data ? JSON.stringify(data) : undefined;
        if (body) headers['Content-Length'] = Buffer.byteLength(body);
        const req = http.request(new URL(path, base), { method, headers }, res => {
            const chunks = [];
            res.on('data', chunk => chunks.push(chunk));
            res.on('end', () => { const text = Buffer.concat(chunks).toString(); resolve({ status: res.statusCode, headers: res.headers, text, json: () => JSON.parse(text) }); });
        });
        req.on('error', reject);
        req.setTimeout(20000, () => req.destroy(new Error('Local HTTP request timed out.')));
        req.end(body);
    });
    for (const value of response.headers['set-cookie'] || []) {
        const pair = value.split(';')[0], separator = pair.indexOf('=');
        cookies.set(pair.slice(0, separator), pair.slice(separator + 1));
    }
    return response;
}
async function page(path) {
    const response = await request(path, { inertia: true });
    assert.equal(response.status, 200, 'Expected successful page: ' + path);
    return response.json();
}

const initial = await request('/login');
assert.equal(initial.status, 200);
const initialPage = initial.text.match(/<script data-page="app" type="application\/json">([\s\S]*?)<\/script>/)?.[1];
assert.ok(initialPage, 'Initial HTML contains the Inertia 3 bootstrap page.');
version = JSON.parse(initialPage).version;
assert.equal((await request('/login', { method: 'POST', csrf: false, data: { email: 'learner@academy.test', password } })).status, 419, 'Real HTTP requests require CSRF.');
await request('/login');
assert.equal((await request('/login', { method: 'POST', data: { email: 'learner@academy.test', password } })).status, 302);
const dashboard = await page('/dashboard');
assert.equal(dashboard.component, 'Dashboard');
assert.equal(dashboard.props.stats.modules, 11);
assert.equal(dashboard.props.stats.lessons, 39);
const library = await page('/lessons?q=interfaces');
assert.ok(library.props.lessons.total > 0);
const lesson = await page('/lessons/interfaces-contracts-and-dependency-injection');
assert.equal(lesson.component, 'Lessons/Show');
assert.ok(lesson.props.lesson.code_examples.length);
const question = lesson.props.lesson.quiz.questions[0];
assert.equal('is_correct' in question.options[0], false);
assert.equal('explanation' in question, false);
assert.equal((await request('/admin')).status, 403);
const quiz = await request('/lessons/interfaces-contracts-and-dependency-injection/quiz', { method: 'POST', data: { answers: { [question.id]: question.options[0].id } } });
assert.equal(quiz.status, 200);
assert.equal((await quiz.json()).attempt.score, 100);
const bookmarkPath = '/lessons/interfaces-contracts-and-dependency-injection/bookmark';
assert.equal((await request(bookmarkPath, { method: 'PUT', data: { bookmarked: true } })).status, 302);
assert.equal((await page('/lessons?bookmarked=1')).props.lessons.total > 0, true);
assert.equal((await request(bookmarkPath, { method: 'PUT', data: { bookmarked: lesson.props.bookmarked } })).status, 302);
const progressPath = '/lessons/interfaces-contracts-and-dependency-injection/progress';
assert.equal((await request(progressPath, { method: 'PUT', data: { completed: true } })).status, 302);
assert.ok((await page('/dashboard')).props.stats.completed > 0);
assert.equal((await request(progressPath, { method: 'PUT', data: { completed: !!lesson.props.progress?.completed_at } })).status, 302);
assert.equal((await page('/history')).component, 'History');
assert.equal((await request('/logout', { method: 'POST' })).status, 302);
assert.equal((await request('/dashboard')).status, 401);
const adminPassword = access.match(/Administrator: admin@academy\.test\r?\nPassword: ([^\r\n]+)/)?.[1];
assert.ok(adminPassword);
await request('/login');
assert.equal((await request('/login', { method: 'POST', data: { email: 'admin@academy.test', password: adminPassword } })).status, 302);
assert.equal((await page('/admin')).component, 'Admin/Index');
const editor = await page('/admin/lessons/interfaces-contracts-and-dependency-injection/edit');
assert.equal(editor.component, 'Admin/Edit');
assert.equal(editor.props.lesson.quiz_locked, true);
const editable = editor.props.lesson;
const fields = ['module_id', 'title', 'slug', 'summary', 'difficulty', 'estimated_minutes', 'objectives', 'prerequisites', 'position', 'is_published', 'sections', 'code_examples', 'exercises', 'tags', 'quiz'];
const payload = Object.fromEntries(fields.map(key => [key, editable[key]]));
assert.equal((await request('/admin/lessons/interfaces-contracts-and-dependency-injection', { method: 'PUT', inertia: true, data: payload })).status, 303);
assert.deepEqual((await page('/admin/lessons/interfaces-contracts-and-dependency-injection/edit')).props.errors, {});
assert.equal((await request('/logout', { method: 'POST' })).status, 302);
console.log('HTTP smoke passed: login/logout, CSRF, MySQL dashboard/library/reader, hidden quiz keys, grading, bookmarks, progress, history, admin denial, and admin editing with preserved quiz history.');
console.log('The local learner account retains the quiz attempt and lesson-view history from this check.');
