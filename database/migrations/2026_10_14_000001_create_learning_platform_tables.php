<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->timestamps();
        });
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('code');
            $table->string('difficulty')->default('beginner');
            $table->unsignedInteger('position')->default(0)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->string('difficulty')->default('beginner');
            $table->unsignedSmallInteger('estimated_minutes')->default(15);
            $table->json('objectives');
            $table->text('prerequisites')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['module_id', 'is_published', 'position']);
        });
        Schema::create('lesson_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('kind')->default('explanation');
            $table->longText('body');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['lesson_id', 'position']);
        });
        Schema::create('code_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('language')->default('php');
            $table->text('source_code');
            $table->text('expected_output')->nullable();
            $table->text('explanation');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions');
            $table->text('solution');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('difficulty')->default('beginner');
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->text('explanation');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('correct_answers');
            $table->unsignedInteger('total_questions');
            $table->unsignedSmallInteger('score');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->foreignId('question_option_id')->constrained()->restrictOnDelete();
            $table->boolean('is_correct');
            $table->unique(['quiz_attempt_id', 'question_id']);
        });
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
            $table->index(['user_id', 'completed_at']);
        });
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });
        Schema::create('user_learning_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('daily_lessons')->default(1);
            $table->unsignedSmallInteger('weekly_lessons')->default(5);
            $table->timestamps();
        });
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('lesson_tag', function (Blueprint $table) {
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['lesson_id', 'tag_id']);
        });
        Schema::create('project_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('brief');
            $table->json('milestones');
            $table->json('acceptance_criteria');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['project_guides', 'lesson_tag', 'tags', 'user_learning_goals', 'bookmarks', 'lesson_progress', 'quiz_answers', 'quiz_attempts', 'question_options', 'questions', 'quizzes', 'exercises', 'code_examples', 'lesson_sections', 'lessons', 'modules', 'learning_paths'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
