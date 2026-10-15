<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = ['question_id', 'question_option_id', 'is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }
}
