<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeExample extends Model
{
    protected $fillable = ['title', 'language', 'source_code', 'expected_output', 'explanation', 'position'];
}
