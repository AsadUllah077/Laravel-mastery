<?php

namespace Database\Factories;

use App\Models\LearningPath;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ModuleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return ['learning_path_id' => fn () => LearningPath::firstOrCreate(['slug' => 'mastery'], ['title' => 'Laravel Mastery', 'description' => 'From language fundamentals to production.'])->id,
            'title' => $title, 'slug' => Str::slug($title), 'description' => fake()->paragraph(), 'is_published' => true];
    }
}
