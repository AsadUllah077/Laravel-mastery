<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['module_id', 'title', 'slug', 'summary', 'difficulty', 'estimated_minutes', 'objectives', 'prerequisites', 'position', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'objectives' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @param Builder<Lesson> $query
     * @return Builder<Lesson>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('lessons.is_published', true)->whereHas('module', fn (Builder $q) => $q->where('is_published', true));
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return HasMany<LessonSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(LessonSection::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<CodeExample, $this> */
    public function codeExamples(): HasMany
    {
        return $this->hasMany(CodeExample::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<Exercise, $this> */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasOne<Quiz, $this> */
    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    /** @return HasMany<LessonProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /** @return HasMany<Bookmark, $this> */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasOne<ProjectGuide, $this> */
    public function projectGuide(): HasOne
    {
        return $this->hasOne(ProjectGuide::class);
    }
}
