<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name'];

    /** @return BelongsToMany<Lesson, $this> */
    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class);
    }
}
