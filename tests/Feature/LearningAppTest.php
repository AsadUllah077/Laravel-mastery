<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\QuizAttempt;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\CurriculumSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LearningAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function learner(): User
    {
        return User::factory()->create();
    }

    private function admin(): User
    {
        $user = $this->learner();
        $user->is_admin = true;
        $user->save();

        return $user;
    }

    private function lessonWithQuiz(): Lesson
    {
        $lesson = Lesson::factory()->create();
        $quiz = $lesson->quiz()->create(['title' => 'Check your knowledge']);
        foreach (['First question', 'Second question'] as $prompt) {
            $question = $quiz->questions()->create(['prompt' => $prompt, 'explanation' => 'This is the explanation.']);
            $question->options()->createMany([['label' => 'Correct', 'is_correct' => true], ['label' => 'Incorrect', 'is_correct' => false]]);
        }

        return $lesson->load('quiz.questions.options');
    }

    private function payload(Lesson $lesson): array
    {
        return ['module_id' => $lesson->module_id, 'title' => 'Edited lesson', 'slug' => $lesson->slug,
            'summary' => 'A useful explanation.', 'difficulty' => 'beginner', 'estimated_minutes' => 20,
            'objectives' => ['Understand invariants'], 'prerequisites' => '', 'position' => 0, 'is_published' => true,
            'sections' => [['title' => 'Definition', 'kind' => 'explanation', 'body' => 'A concrete definition.']],
            'code_examples' => [], 'exercises' => [], 'tags' => ['php'], 'quiz' => null];
    }

    public function test_learning_routes_require_login_and_verified_email(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->unverified()->create())->get('/lessons')->assertRedirect('/verify-email');
    }

    public function test_dashboard_counts_only_current_users_published_progress(): void
    {
        $lesson = Lesson::factory()->create();
        $other = $this->learner();
        $other->progress()->create(['lesson_id' => $lesson->id, 'completed_at' => now()]);
        $user = $this->learner();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')->where('stats.completed', 0)->where('stats.lessons', 1)->where('stats.percentage', 0));
        $user->progress()->create(['lesson_id' => $lesson->id, 'completed_at' => now()]);
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page->where('stats.completed', 1)->where('stats.percentage', 100));
    }

    public function test_drafts_and_unpublished_modules_are_excluded_from_search(): void
    {
        $public = Lesson::factory()->create(['title' => 'Visible interface']);
        Lesson::factory()->create(['title' => 'Secret interface', 'is_published' => false]);
        Lesson::factory()->create(['title' => 'Hidden interface', 'module_id' => Module::factory()->create(['is_published' => false])->id]);
        $this->actingAs($this->learner())->get('/lessons?q=interface')->assertInertia(fn (Assert $page) => $page
            ->has('lessons.data', 1)->where('lessons.data.0.id', $public->id));
    }

    public function test_search_finds_code_tags_and_treats_wildcards_literally(): void
    {
        $lesson = Lesson::factory()->create(['title' => 'Gateway']);
        $lesson->codeExamples()->create(['title' => 'Contract', 'source_code' => 'interface Chargeable {}', 'explanation' => 'An interface.']);
        $this->actingAs($this->learner())->get('/lessons?q=Chargeable')->assertInertia(fn (Assert $page) => $page->has('lessons.data', 1));
        $this->get('/lessons?q=%25')->assertInertia(fn (Assert $page) => $page->has('lessons.data', 0));
    }

    public function test_viewing_a_lesson_does_not_reset_completion(): void
    {
        $lesson = $this->lessonWithQuiz();
        $user = $this->learner();
        $user->progress()->create(['lesson_id' => $lesson->id, 'completed_at' => now()->subDay()]);
        $this->actingAs($user)->get(route('lessons.show', $lesson))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Lessons/Show')->missing('lesson.quiz.questions.0.explanation')->missing('lesson.quiz.questions.0.options.0.is_correct'));
        $this->assertNotNull($user->progress()->first()->completed_at);
        $this->assertNotNull($user->progress()->first()->last_viewed_at);
        $this->assertDatabaseCount('lesson_progress', 1);
    }

    public function test_progress_and_bookmark_updates_are_idempotent_and_user_scoped(): void
    {
        $lesson = Lesson::factory()->create();
        $user = $this->learner();
        $this->actingAs($user);
        for ($i = 0; $i < 2; $i++) {
            $this->put(route('lessons.progress', $lesson), ['completed' => true, 'user_id' => 999])->assertRedirect();
            $this->put(route('lessons.bookmark', $lesson), ['bookmarked' => true])->assertRedirect();
        }
        $this->assertDatabaseCount('lesson_progress', 1);
        $this->assertDatabaseCount('bookmarks', 1);
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'lesson_id' => $lesson->id]);
        $this->put(route('lessons.progress', $lesson), ['completed' => false]);
        $this->assertNull($user->progress()->first()->completed_at);
        $this->put(route('lessons.bookmark', $lesson), ['bookmarked' => false]);
        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_draft_is_protected_and_admin_preview_does_not_record_progress(): void
    {
        $lesson = Lesson::factory()->create(['is_published' => false]);
        $this->actingAs($this->learner())->get(route('lessons.show', $lesson))->assertForbidden();
        $this->put(route('lessons.progress', $lesson), ['completed' => true])->assertNotFound();
        $this->actingAs($this->admin())->get(route('lessons.show', $lesson))->assertOk()->assertInertia(fn (Assert $page) => $page->where('preview', true));
        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_quiz_scores_feedback_and_retake_are_persisted(): void
    {
        $lesson = $this->lessonWithQuiz();
        $questions = $lesson->quiz->questions;
        $answers = [$questions[0]->id => $questions[0]->options[0]->id, $questions[1]->id => $questions[1]->options[1]->id];
        $this->actingAs($this->learner())->postJson(route('lessons.quiz', $lesson), ['answers' => $answers])
            ->assertOk()->assertJsonPath('attempt.score', 50)->assertJsonPath('feedback.0.correct_option_id', $questions[0]->options[0]->id);
        $this->postJson(route('lessons.quiz', $lesson), ['answers' => $answers])->assertOk();
        $this->assertDatabaseCount('quiz_attempts', 2);
        $this->assertDatabaseCount('quiz_answers', 4);
    }

    public function test_quiz_rejects_partial_and_cross_question_options_without_writes(): void
    {
        $lesson = $this->lessonWithQuiz();
        $questions = $lesson->quiz->questions;
        $this->actingAs($this->learner())->postJson(route('lessons.quiz', $lesson), ['answers' => [$questions[0]->id => $questions[0]->options[0]->id]])->assertUnprocessable();
        $this->postJson(route('lessons.quiz', $lesson), ['answers' => [$questions[0]->id => $questions[1]->options[0]->id, $questions[1]->id => $questions[1]->options[0]->id]])->assertUnprocessable();
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_regular_users_cannot_access_any_admin_content_mutation(): void
    {
        $lesson = Lesson::factory()->create();
        $this->actingAs($this->learner())->get('/admin')->assertForbidden();
        $this->get(route('admin.lessons.edit', $lesson))->assertForbidden();
        $this->post('/admin/lessons', $this->payload($lesson))->assertForbidden();
        $this->put(route('admin.lessons.update', $lesson), $this->payload($lesson))->assertForbidden();
        $this->post('/admin/modules', [])->assertForbidden();
        $this->put(route('admin.modules.reorder', $lesson->module_id), ['lesson_ids' => [$lesson->id]])->assertForbidden();
    }

    public function test_admin_can_create_edit_publish_and_preview_normalized_content(): void
    {
        $moduleLesson = Lesson::factory()->create();
        $payload = $this->payload($moduleLesson);
        $payload['slug'] = 'new-lesson';
        $payload['is_published'] = false;
        $this->actingAs($this->admin())->post('/admin/lessons', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $lesson = Lesson::where('slug', 'new-lesson')->firstOrFail();
        $this->assertCount(1, $lesson->sections);
        $this->assertCount(1, $lesson->tags);
        $payload['is_published'] = true;
        $this->put(route('admin.lessons.update', $lesson), $payload)->assertSessionHasNoErrors();
        $this->assertTrue($lesson->fresh()->is_published);
        $this->get(route('lessons.show', $lesson))->assertOk();
    }

    public function test_invalid_nested_admin_payload_rolls_back_entire_write(): void
    {
        $lesson = Lesson::factory()->create();
        $payload = $this->payload($lesson);
        $payload['quiz'] = ['title' => 'Quiz', 'questions' => [['prompt' => 'Q', 'explanation' => 'A', 'options' => ['A', 'B'], 'correct_index' => 5]]];
        $this->actingAs($this->admin())->put(route('admin.lessons.update', $lesson), $payload)->assertSessionHasErrors('quiz.questions.0.correct_index');
        $this->assertNotSame('Edited lesson', $lesson->fresh()->title);
    }

    public function test_attempted_quiz_cannot_be_destroyed_by_content_edit(): void
    {
        $lesson = $this->lessonWithQuiz();
        QuizAttempt::create(['user_id' => $this->learner()->id, 'quiz_id' => $lesson->quiz->id, 'correct_answers' => 1, 'total_questions' => 2, 'score' => 50]);
        $this->actingAs($this->admin())->put(route('admin.lessons.update', $lesson), $this->payload($lesson))->assertSessionHasErrors('quiz');
        $this->assertDatabaseCount('questions', 2);
        $this->assertNotSame('Edited lesson', $lesson->fresh()->title);
    }

    public function test_goal_validation_and_persistence(): void
    {
        $user = $this->learner();
        $this->actingAs($user)->put('/learning-goal', ['daily_lessons' => 0, 'weekly_lessons' => 500])->assertSessionHasErrors(['daily_lessons', 'weekly_lessons']);
        $this->put('/learning-goal', ['daily_lessons' => 2, 'weekly_lessons' => 8])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('user_learning_goals', ['user_id' => $user->id, 'daily_lessons' => 2, 'weekly_lessons' => 8]);
    }

    public function test_lesson_reordering_checks_membership(): void
    {
        $a = Lesson::factory()->create();
        $b = Lesson::factory()->create(['module_id' => $a->module_id]);
        $foreign = Lesson::factory()->create();
        $this->actingAs($this->admin())->put(route('admin.modules.reorder', $a->module_id), ['lesson_ids' => [$a->id, $foreign->id]])->assertUnprocessable();
        $this->put(route('admin.modules.reorder', $a->module_id), ['lesson_ids' => [$b->id, $a->id]])->assertSessionHasNoErrors();
        $this->assertSame(0, $b->fresh()->position);
        $this->assertSame(1, $a->fresh()->position);
    }

    public function test_public_registration_cannot_escalate_privileges(): void
    {
        $this->post('/register', ['name' => 'Learner', 'email' => 'learner@example.com', 'password' => 'password', 'password_confirmation' => 'password', 'is_admin' => true]);
        $this->assertFalse(User::where('email', 'learner@example.com')->firstOrFail()->is_admin);
    }

    public function test_curriculum_seeding_is_idempotent_and_preserves_edits(): void
    {
        $this->seed(CurriculumSeeder::class);
        $count = Lesson::count();
        $lesson = Lesson::firstOrFail();
        $lesson->update(['title' => 'Editorial revision']);
        $this->seed(CurriculumSeeder::class);
        $this->assertSame($count, Lesson::count());
        $this->assertSame('Editorial revision', $lesson->fresh()->title);
        $this->assertSame(11, Module::count());
        $this->assertGreaterThanOrEqual(20, $count);
    }

    public function test_library_query_count_stays_bounded_as_lessons_increase(): void
    {
        $lesson = Lesson::factory()->create();
        $this->actingAs($this->learner());
        DB::enableQueryLog();
        $this->get('/lessons')->assertOk();
        $small = count(DB::getQueryLog());
        DB::disableQueryLog();
        Lesson::factory()->count(11)->create(['module_id' => $lesson->module_id]);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/lessons')->assertOk();
        $large = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual($small + 1, $large, 'Query count should not grow per lesson.');
    }

    public function test_editing_a_slug_redirects_to_the_new_editor_url(): void
    {
        $lesson = Lesson::factory()->create();
        $payload = $this->payload($lesson);
        $payload['slug'] = 'renamed-lesson';
        $this->actingAs($this->admin())->put(route('admin.lessons.update', $lesson), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lessons.edit', 'renamed-lesson'));
        $this->get(route('admin.lessons.edit', 'renamed-lesson'))->assertOk();
    }

    public function test_tag_management_is_protected_and_preserves_lesson_content(): void
    {
        $this->actingAs($this->learner())->post('/admin/tags', ['name' => 'Unsafe'])->assertForbidden();
        $this->actingAs($this->admin())->post('/admin/tags', ['name' => ' New Tag '])->assertSessionHasNoErrors();
        $tag = Tag::where('name', 'new tag')->firstOrFail();
        $lesson = Lesson::factory()->create();
        $lesson->tags()->attach($tag);
        $this->put(route('admin.tags.update', $tag), ['name' => 'renamed'])->assertSessionHasNoErrors();
        $this->assertSame('renamed', $tag->fresh()->name);
        $this->delete(route('admin.tags.destroy', $tag))->assertRedirect();
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseCount('lesson_tag', 0);
    }

    public function test_malformed_nested_fields_return_validation_errors_instead_of_server_errors(): void
    {
        $lesson = Lesson::factory()->create();
        $payload = $this->payload($lesson);
        $payload['quiz'] = ['title' => 'Quiz', 'questions' => 'not-an-array'];
        $this->actingAs($this->admin())->putJson(route('admin.lessons.update', $lesson), $payload)->assertUnprocessable();
        $this->postJson('/admin/tags', ['name' => ['invalid']])->assertUnprocessable();
    }
}
