<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonEditor
{
    public function quizData(Lesson $lesson): ?array
    {
        $lesson->loadMissing('quiz.questions.options');
        $quiz = $lesson->quiz;
        if (! $quiz) {
            return null;
        }

        return ['title' => $quiz->title, 'questions' => $quiz->questions->map(fn ($question) => [
            'prompt' => $question->prompt, 'explanation' => $question->explanation,
            'options' => $question->options->pluck('label')->all(),
            'correct_index' => $question->options->search(fn ($option) => $option->is_correct),
        ])->all()];
    }

    public function save(Lesson $lesson, array $data): Lesson
    {
        return DB::transaction(function () use ($lesson, $data) {
            if ($lesson->exists) {
                $lesson = Lesson::whereKey($lesson->id)->lockForUpdate()->firstOrFail();
            }
            $quiz = $lesson->exists ? $lesson->quiz()->lockForUpdate()->first() : null;
            if ($quiz && $quiz->attempts()->exists() && $this->quizData($lesson) != ($data['quiz'] ?? null)) {
                throw ValidationException::withMessages(['quiz' => 'This quiz has attempts and is locked to preserve results. Create a new lesson for a revised quiz.']);
            }
            $lesson->fill(Arr::only($data, $lesson->getFillable()))->save();
            foreach (['sections' => 'sections', 'code_examples' => 'codeExamples', 'exercises' => 'exercises'] as $key => $relation) {
                $lesson->{$relation}()->delete();
                $lesson->{$relation}()->createMany(array_map(fn ($row, $position) => $row + ['position' => $position], $data[$key], array_keys($data[$key])));
            }
            $tagIds = array_map(fn ($name) => Tag::firstOrCreate(['name' => mb_strtolower(trim($name))])->id, $data['tags']);
            $lesson->tags()->sync($tagIds);
            if (! $quiz || ! $quiz->attempts()->exists()) {
                $quiz?->delete();
                if ($data['quiz'] ?? null) {
                    $quiz = $lesson->quiz()->create(['title' => $data['quiz']['title'], 'difficulty' => $lesson->difficulty]);
                    foreach ($data['quiz']['questions'] as $position => $row) {
                        $question = $quiz->questions()->create(Arr::only($row, ['prompt', 'explanation']) + ['position' => $position]);
                        foreach ($row['options'] as $index => $label) {
                            $question->options()->create(['label' => $label, 'is_correct' => $index === (int) $row['correct_index']]);
                        }
                    }
                }
            }

            return $lesson;
        });
    }
}
