<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return ['module_id' => Module::factory(), 'title' => $title, 'slug' => Str::slug($title), 'summary' => fake()->paragraph(),
            'objectives' => ['Understand the concept', 'Apply it in Laravel'], 'is_published' => true];
    }
}
