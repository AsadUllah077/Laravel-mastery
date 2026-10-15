<?php

namespace Database\Seeders;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public const MODULES = [
        ['PHP Fundamentals', 'code', 'beginner', 'Write clear, typed PHP and understand the language beneath the framework.'],
        ['Object-Oriented PHP', 'box', 'beginner', 'Turn classes, contracts, and composition into tools you can reason about.'],
        ['SOLID Principles', 'layers', 'intermediate', 'Design around change with five practical principles and their trade-offs.'],
        ['Laravel Fundamentals', 'route', 'beginner', 'Follow a request through routing, validation, models, and responses.'],
        ['Advanced Laravel', 'zap', 'advanced', 'Use the container, queues, caching, and application boundaries deliberately.'],
        ['Design Patterns', 'pattern', 'intermediate', 'Recognize recurring problems and choose patterns with a purpose.'],
        ['Laravel Security', 'shield', 'intermediate', 'Protect data with validation, authorization, and defensive defaults.'],
        ['Testing & Code Quality', 'check', 'intermediate', 'Test behavior, isolate dependencies, and refactor with evidence.'],
        ['Performance & Scalability', 'gauge', 'advanced', 'Measure bottlenecks and reduce query, memory, and latency costs.'],
        ['Deployment & DevOps', 'cloud', 'advanced', 'Build reproducible releases and operate Laravel in production.'],
        ['Real-World Projects', 'rocket', 'advanced', 'Connect the concepts through progressive project blueprints.'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $path = LearningPath::firstOrCreate(['slug' => 'laravel-mastery'], ['title' => 'The Laravel Mastery Path', 'description' => 'From PHP fundamentals to thoughtful, production-ready Laravel development.']);
            $modules = [];
            foreach (self::MODULES as $position => [$title, $icon, $difficulty, $description]) {
                $modules[$position] = Module::firstOrCreate(['slug' => Str::slug($title)], [
                    'learning_path_id' => $path->id, 'title' => $title, 'description' => $description,
                    'icon' => $icon, 'difficulty' => $difficulty, 'position' => $position, 'is_published' => true,
                ]);
            }
            $positions = array_fill(0, count($modules), 0);
            foreach (glob(database_path('content/*.json')) as $file) {
                foreach (json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) as $row) {
                    $slug = Str::slug($row['title']);
                    $position = $positions[$row['module']]++;
                    // Re-seeding adds missing lessons and preserves editorial changes and quiz history.
                    if (Lesson::where('slug', $slug)->exists()) {
                        continue;
                    }
                    $lesson = $modules[$row['module']]->lessons()->create([
                        'title' => $row['title'], 'slug' => $slug, 'summary' => $row['definition'],
                        'difficulty' => $row['difficulty'] ?? $modules[$row['module']]->difficulty,
                        'estimated_minutes' => $row['minutes'] ?? 18,
                        'objectives' => $row['objectives'] ?? [
                            'Explain '.lcfirst($row['title']).' in your own words.',
                            'Trace the example and predict its documented output.',
                            'Apply the idea and identify the trade-offs in the practice exercise.',
                        ],
                        'prerequisites' => $row['prerequisites'] ?? ($row['module'] === 0 ? 'A PHP CLI installation and a text editor.' : 'Basic PHP variables, functions, and the preceding lessons in this learning path.'),
                        'position' => $position, 'is_published' => true,
                    ]);
                    foreach ([['The concept', 'explanation', 'definition'], ['How it works and why it matters', 'explanation', 'why'], ['A real-world analogy', 'analogy', 'analogy'], ['Common mistakes and a better approach', 'mistakes', 'bad'], ['Applying it in Laravel 12', 'explanation', 'laravel'], ['Interview question & answer', 'interview', 'interview'], ['Key takeaways', 'takeaways', 'takeaway']] as $index => [$title, $kind, $key]) {
                        $lesson->sections()->create(['title' => $title, 'kind' => $kind, 'body' => $row[$key], 'position' => $index]);
                    }
                    if (isset($row['security'])) {
                        $lesson->sections()->create(['title' => 'Security and operational considerations', 'kind' => 'security', 'body' => $row['security'], 'position' => 6]);
                    }
                    $lesson->codeExamples()->create([
                        'title' => $row['example_title'] ?? 'Syntax & practical example', 'language' => $row['language'] ?? 'php',
                        'source_code' => $row['code'], 'expected_output' => $row['output'], 'explanation' => $row['steps'],
                    ]);
                    $lesson->exercises()->create(['title' => 'Put the concept to work', 'instructions' => $row['exercise'], 'solution' => $row['solution']]);
                    $quiz = $lesson->quiz()->create(['title' => $row['title'].' — knowledge check', 'difficulty' => $lesson->difficulty]);
                    $question = $quiz->questions()->create(['prompt' => $row['question'], 'explanation' => $row['quiz_explanation'] ?? $row['takeaway']]);
                    foreach ($row['options'] as $index => $label) {
                        $question->options()->create(['label' => $label, 'is_correct' => $index === $row['answer']]);
                    }
                    $lesson->tags()->sync(array_map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id, $row['tags']));
                    if (isset($row['project'])) {
                        $lesson->projectGuide()->create($row['project']);
                    }
                }
            }
        });
    }
}
