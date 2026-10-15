<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveLessonRequest;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\LessonEditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Index', [
            'modules' => Module::withCount('lessons')->orderBy('position')->get(),
            'lessons' => Lesson::with('module:id,title')->orderByDesc('updated_at')->paginate(20),
            'paths' => LearningPath::all(['id', 'title']),
            'stats' => ['learners' => User::where('is_admin', false)->count(), 'completions' => LessonProgress::whereNotNull('completed_at')->count(), 'attempts' => QuizAttempt::count(), 'average_score' => (int) round(QuizAttempt::avg('score') ?? 0)],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Lesson::class);

        return Inertia::render('Admin/Edit', ['lesson' => null, 'modules' => Module::orderBy('position')->get(['id', 'title'])]);
    }

    public function edit(Lesson $lesson, LessonEditor $editor): Response
    {
        Gate::authorize('update', $lesson);
        $lesson->load('sections', 'codeExamples', 'exercises', 'tags', 'quiz.questions.options');
        $data = $lesson->toArray();
        $data['tags'] = $lesson->tags->pluck('name');
        $data['quiz'] = $editor->quizData($lesson);
        $data['quiz_locked'] = $lesson->quiz?->attempts()->exists() ?? false;

        return Inertia::render('Admin/Edit', ['lesson' => $data, 'modules' => Module::orderBy('position')->get(['id', 'title'])]);
    }

    public function store(SaveLessonRequest $request, LessonEditor $editor): RedirectResponse
    {
        Gate::authorize('create', Lesson::class);
        $lesson = $editor->save(new Lesson, $request->validated());

        return redirect()->route('admin.lessons.edit', $lesson)->with('success', 'Lesson created. Use Preview to review it.');
    }

    public function update(SaveLessonRequest $request, Lesson $lesson, LessonEditor $editor): RedirectResponse
    {
        Gate::authorize('update', $lesson);
        $saved = $editor->save($lesson, $request->validated());

        return redirect()->route('admin.lessons.edit', $saved)->with('success', 'Lesson saved.');
    }

    public function module(Request $request, ?Module $module = null): RedirectResponse
    {
        $data = $request->validate([
            'learning_path_id' => ['required', 'exists:learning_paths,id'],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('modules')->ignore($module)],
            'description' => ['required', 'string', 'max:2000'],
            'icon' => ['required', 'in:code,box,layers,route,zap,pattern,shield,check,gauge,cloud,rocket'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'position' => ['required', 'integer', 'between:0,10000'],
            'is_published' => ['required', 'boolean'],
        ]);
        ($module ?? new Module)->fill($data)->save();

        return back()->with('success', 'Module saved.');
    }

    public function reorder(Request $request, Module $module): RedirectResponse
    {
        $data = $request->validate(['lesson_ids' => ['required', 'array', 'min:1', 'max:1000'], 'lesson_ids.*' => ['required', 'integer', 'distinct']]);
        $expected = $module->lessons()->pluck('id')->sort()->values()->all();
        $actual = collect($data['lesson_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
        abort_unless($expected === $actual, 422, 'Supply every lesson in this module exactly once.');
        DB::transaction(function () use ($data, $module) {
            Module::whereKey($module->id)->lockForUpdate()->firstOrFail();
            foreach ($data['lesson_ids'] as $position => $id) {
                $module->lessons()->whereKey($id)->update(['position' => $position]);
            }
        });

        return back()->with('success', 'Lesson order updated.');
    }

    public function order(Module $module): Response
    {
        return Inertia::render('Admin/Order', ['module' => $module->load('lessons:id,module_id,title,slug,position,is_published')]);
    }
}
