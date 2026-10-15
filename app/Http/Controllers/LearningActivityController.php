<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningActivityController extends Controller
{
    public function progress(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless(Lesson::published()->whereKey($lesson->id)->exists(), 404);
        $data = $request->validate(['completed' => ['required', 'boolean']]);
        $progress = $request->user()->progress()->firstOrCreate(['lesson_id' => $lesson->id], ['last_viewed_at' => now()]);
        $progress->update(['completed_at' => $data['completed'] ? ($progress->completed_at ?? now()) : null]);

        return back()->with('success', $data['completed'] ? 'Lesson completed. Keep building!' : 'Lesson marked in progress.');
    }

    public function bookmark(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless(Lesson::published()->whereKey($lesson->id)->exists(), 404);
        $data = $request->validate(['bookmarked' => ['required', 'boolean']]);
        if ($data['bookmarked']) {
            $request->user()->bookmarks()->firstOrCreate(['lesson_id' => $lesson->id]);
        } else {
            $request->user()->bookmarks()->where('lesson_id', $lesson->id)->delete();
        }

        return back()->with('success', $data['bookmarked'] ? 'Saved to your bookmarks.' : 'Bookmark removed.');
    }

    public function goal(Request $request): RedirectResponse
    {
        $data = $request->validate(['daily_lessons' => ['required', 'integer', 'min:1', 'max:20'], 'weekly_lessons' => ['required', 'integer', 'min:1', 'max:100']]);
        $request->user()->learningGoal()->updateOrCreate([], $data);

        return back()->with('success', 'Your learning goals have been updated.');
    }

    public function history(Request $request): Response
    {
        return Inertia::render('History', [
            'history' => $request->user()->progress()->whereHas('lesson', fn ($q) => $q->published())->with('lesson.module')->orderByDesc('last_viewed_at')->paginate(15),
            'attempts' => $request->user()->quizAttempts()->with('quiz.lesson')->latest()->limit(20)->get(),
        ]);
    }
}
