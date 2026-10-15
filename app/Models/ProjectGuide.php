<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectGuide extends Model
{
    protected $fillable = ['brief', 'milestones', 'acceptance_criteria'];

    protected function casts(): array
    {
        return ['milestones' => 'array', 'acceptance_criteria' => 'array'];
    }
}
