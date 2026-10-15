<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $publishedProgress = $user->progress()->whereHas('lesson', fn ($q) => $q->published());
        $completed = (clone $publishedProgress)->whereNotNull('completed_at')->count();
        $total = Lesson::published()->count();
        $recent = (clone $publishedProgress)->with('lesson.module')->orderByDesc('last_viewed_at')->limit(5)->get();
        $resume = (clone $publishedProgress)->whereNull('completed_at')->with('lesson.module')->orderByDesc('last_viewed_at')->first();
        $next = Lesson::published()->with('module')->whereDoesntHave('progress', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('completed_at'))
            ->orderBy('module_id')->orderBy('position')->orderBy('id')->first();

        return Inertia::render('Dashboard', [
            'stats' => [
                'modules' => Module::published()->count(), 'lessons' => $total, 'completed' => $completed,
                'in_progress' => (clone $publishedProgress)->whereNull('completed_at')->count(),
                'percentage' => $total ? (int) round($completed / $total * 100) : 0,
                'average_score' => (int) round($user->quizAttempts()->avg('score') ?? 0),
                'today' => (clone $publishedProgress)->whereDate('completed_at', today())->count(),
                'this_week' => (clone $publishedProgress)->where('completed_at', '>=', now()->startOfWeek())->count(),
            ],
            'modules' => Module::published()->with('learningPath:id,title')->withCount([
                'lessons' => fn ($q) => $q->published(),
                'lessons as completed_count' => fn ($q) => $q->published()->whereHas('progress', fn ($p) => $p->where('user_id', $user->id)->whereNotNull('completed_at')),
            ])->orderBy('position')->get(),
            'resume' => $resume ? $resume->lesson : $next,
            'recent' => $recent,
            'bookmarks' => $user->bookmarks()->whereHas('lesson', fn ($q) => $q->published())->with('lesson.module')->latest()->limit(4)->get(),
            'attempts' => $user->quizAttempts()->with('quiz.lesson')->latest()->limit(5)->get(),
            'goal' => $user->learningGoal()->first() ?? ['daily_lessons' => 1, 'weekly_lessons' => 5],
        ]);
    }
}
