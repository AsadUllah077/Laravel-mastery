<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('lessons')->ignore($this->route('lesson'))],
            'summary' => ['required', 'string', 'max:2000'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'estimated_minutes' => ['required', 'integer', 'between:1,600'],
            'position' => ['required', 'integer', 'between:0,10000'],
            'objectives' => ['required', 'array', 'min:1', 'max:15'],
            'objectives.*' => ['required', 'string', 'max:500'],
            'prerequisites' => ['nullable', 'string', 'max:2000'],
            'is_published' => ['required', 'boolean'],
            'sections' => ['required', 'array', 'min:1', 'max:30'],
            'sections.*.title' => ['required', 'string', 'max:200'],
            'sections.*.kind' => ['required', 'in:explanation,analogy,walkthrough,mistakes,interview,takeaways,security,testing'],
            'sections.*.body' => ['required', 'string', 'max:30000'],
            'code_examples' => ['present', 'array', 'max:15'],
            'code_examples.*.title' => ['required', 'string', 'max:200'],
            'code_examples.*.language' => ['required', 'in:php,sql,bash,javascript,xml,css,blade'],
            'code_examples.*.source_code' => ['required', 'string', 'max:30000'],
            'code_examples.*.expected_output' => ['nullable', 'string', 'max:10000'],
            'code_examples.*.explanation' => ['required', 'string', 'max:10000'],
            'exercises' => ['present', 'array', 'max:15'],
            'exercises.*.title' => ['required', 'string', 'max:200'],
            'exercises.*.instructions' => ['required', 'string', 'max:10000'],
            'exercises.*.solution' => ['required', 'string', 'max:10000'],
            'tags' => ['present', 'array', 'max:15'],
            'tags.*' => ['required', 'string', 'max:50', 'distinct:ignore_case'],
            'quiz' => ['nullable', 'array'],
            'quiz.title' => ['required_with:quiz', 'string', 'max:200'],
            'quiz.questions' => ['required_with:quiz', 'array', 'min:1', 'max:30'],
            'quiz.questions.*.prompt' => ['required', 'string', 'max:2000'],
            'quiz.questions.*.explanation' => ['required', 'string', 'max:5000'],
            'quiz.questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'quiz.questions.*.options.*' => ['required', 'string', 'max:1000'],
            'quiz.questions.*.correct_index' => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            foreach ($this->input('quiz.questions', []) as $index => $question) {
                if (is_array($question) && isset($question['correct_index'], $question['options']) &&
                    is_array($question['options']) && ! array_key_exists($question['correct_index'], $question['options'])) {
                    $validator->errors()->add("quiz.questions.$index.correct_index", 'Choose an existing correct option.');
                }
            }
        }];
    }
}
