<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['learning_path_id', 'title', 'slug', 'description', 'icon', 'difficulty', 'position', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    /** @return BelongsTo<LearningPath, $this> */
    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    /** @return HasMany<Lesson, $this> */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position')->orderBy('id');
    }

    /** @param Builder<Module> $query
     * @return Builder<Module>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
