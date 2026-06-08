<?php

namespace App\Modules\Question\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Question extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'tags' => 'array',
    ];

    protected $fillable = ['tenant_id', 'created_by', 'type', 'content', 'options', 'correct_answer', 'max_score', 'difficulty', 'tags', 'status'];
}
