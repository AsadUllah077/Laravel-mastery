<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'], 'module' => ['nullable', 'integer'],
            'difficulty' => ['nullable', 'in:beginner,intermediate,advanced'],
            'bookmarked' => ['nullable', 'boolean'], 'tag' => ['nullable', 'integer'],
        ]);
        $lessons = Lesson::published()->with(['module:id,title,slug,icon', 'tags'])
            ->withExists(['progress as completed' => fn ($q) => $q->where('user_id', $request->user()->id)->whereNotNull('completed_at'),
                'bookmarks as bookmarked' => fn ($q) => $q->where('user_id', $request->user()->id)])
            ->when($filters['q'] ?? null, function ($query, $search) {
                // Bound parameters prevent SQL injection; escape wildcard characters for literal search.
                $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
                $query->where(fn ($q) => $q->whereRaw("lessons.title LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("summary LIKE ? ESCAPE '!'", [$term])
                    ->orWhereHas('sections', fn ($s) => $s->whereRaw("body LIKE ? ESCAPE '!'", [$term]))
                    ->orWhereHas('codeExamples', fn ($c) => $c->whereRaw("source_code LIKE ? ESCAPE '!'", [$term]))
                    ->orWhereHas('tags', fn ($t) => $t->whereRaw("name LIKE ? ESCAPE '!'", [$term])));
            })
            ->when($filters['module'] ?? null, fn ($q, $id) => $q->where('module_id', $id))
            ->when($filters['difficulty'] ?? null, fn ($q, $difficulty) => $q->where('difficulty', $difficulty))
            ->when($filters['tag'] ?? null, fn ($q, $tag) => $q->whereHas('tags', fn ($t) => $t->whereKey($tag)))
            ->when($filters['bookmarked'] ?? false, fn ($q) => $q->whereHas('bookmarks', fn ($b) => $b->where('user_id', $request->user()->id)))
            ->orderBy('module_id')->orderBy('position')->orderBy('id')->paginate(12)->withQueryString();

        return Inertia::render('Lessons/Index', [
            'lessons' => $lessons, 'filters' => $filters,
            'modules' => Module::published()->orderBy('position')->get(['id', 'title']),
            'tags' => Tag::whereHas('lessons', fn ($q) => $q->published())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, Lesson $lesson): Response
    {
        $lesson->loadMissing('module');
        Gate::authorize('view', $lesson);
        $lesson->load(['module', 'sections', 'codeExamples', 'exercises', 'tags', 'projectGuide', 'quiz.questions.options']);
        $preview = ! $lesson->is_published || ! $lesson->module->is_published;
        if (! $preview) {
            $request->user()->progress()->upsert([
                'lesson_id' => $lesson->id, 'user_id' => $request->user()->id,
                'last_viewed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ], ['user_id', 'lesson_id'], ['last_viewed_at', 'updated_at']);
        }
        $ordered = Lesson::published()->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->orderBy('modules.position')->orderBy('modules.id')->orderBy('lessons.position')->orderBy('lessons.id')
            ->get(['lessons.id', 'lessons.title', 'lessons.slug', 'lessons.module_id']);
        $index = $ordered->search(fn ($item) => $item->id === $lesson->id);

        return Inertia::render('Lessons/Show', [
            'lesson' => $lesson, 'preview' => $preview,
            'progress' => $request->user()->progress()->where('lesson_id', $lesson->id)->first(),
            'bookmarked' => $request->user()->bookmarks()->where('lesson_id', $lesson->id)->exists(),
            'siblings' => $ordered->where('module_id', $lesson->module_id)->values(),
            'previous' => $index !== false && $index > 0 ? $ordered[$index - 1] : null,
            'next' => $index !== false ? $ordered->get($index + 1) : null,
        ]);
    }
}
