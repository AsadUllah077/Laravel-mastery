<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        return $user->is_admin || ($lesson->is_published && $lesson->module->is_published);
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->is_admin;
    }
}
