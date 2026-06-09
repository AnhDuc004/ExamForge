<?php

namespace App\Modules\Question\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = ['tenant_id', 'created_by', 'type', 'content', 'options', 'correct_answer', 'max_score', 'difficulty', 'tags', 'status'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Models\Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }
}
