<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLearningGoal extends Model
{
    protected $fillable = ['user_id', 'daily_lessons', 'weekly_lessons'];
}
