<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\QuizGrader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function store(Request $request, Lesson $lesson, QuizGrader $grader): JsonResponse
    {
        abort_unless(Lesson::published()->whereKey($lesson->id)->exists(), 404);
        $quiz = $lesson->quiz()->firstOrFail();
        $data = $request->validate(['answers' => ['required', 'array', 'max:30'], 'answers.*' => ['required', 'integer']]);
        $attempt = $grader->grade($request->user(), $quiz, $data['answers']);
        $quiz->load('questions.options');

        return response()->json([
            'attempt' => $attempt,
            'feedback' => $quiz->questions->map(fn ($q) => [
                'question_id' => $q->id, 'explanation' => $q->explanation,
                'correct_option_id' => $q->options->firstWhere('is_correct', true)?->id,
            ]),
        ]);
    }
}
