<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    protected $fillable = ['title', 'slug', 'description'];

    /** @return HasMany<Module, $this> */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position')->orderBy('id');
    }
}
