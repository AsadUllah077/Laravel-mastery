<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'quiz_id', 'correct_answers', 'total_questions', 'score'];

    /** @return BelongsTo<Quiz, $this> */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /** @return HasMany<QuizAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
