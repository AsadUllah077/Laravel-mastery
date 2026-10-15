<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['label', 'is_correct'];

    protected $hidden = ['is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }
}
