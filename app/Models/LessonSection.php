<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonSection extends Model
{
    protected $fillable = ['title', 'kind', 'body', 'position'];
}
