<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizGrader
{
    /** @param array<int|string, int|string> $answers */
    public function grade(User $user, Quiz $quiz, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($user, $quiz, $answers) {
            // Lock the parent used by the editor too: an edit cannot race a submission.
            Quiz::whereKey($quiz->id)->lockForUpdate()->firstOrFail();
            $questions = $quiz->questions()->with('options')->get();
            if ($questions->isEmpty() || count($answers) !== $questions->count()) {
                throw ValidationException::withMessages(['answers' => 'Answer every question once before submitting.']);
            }
            $rows = [];
            foreach ($questions as $question) {
                $selected = $question->options->firstWhere('id', (int) ($answers[$question->id] ?? 0));
                if (! $selected) {
                    throw ValidationException::withMessages(['answers' => 'Each answer must belong to its question.']);
                }
                $rows[] = ['question_id' => $question->id, 'question_option_id' => $selected->id, 'is_correct' => $selected->is_correct];
            }
            $correct = collect($rows)->where('is_correct', true)->count();
            $attempt = $user->quizAttempts()->create([
                'quiz_id' => $quiz->id, 'correct_answers' => $correct,
                'total_questions' => $questions->count(), 'score' => (int) round($correct / $questions->count() * 100),
            ]);
            $attempt->answers()->createMany($rows);

            return $attempt;
        });
    }
}
