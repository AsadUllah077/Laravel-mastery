<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = ['user_id', 'lesson_id', 'last_viewed_at', 'completed_at'];

    protected function casts(): array
    {
        return ['last_viewed_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<Lesson, $this> */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
