<?php

namespace App\Modules\Attempt\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    use HasUuid;

    protected $table = 'attempts';

    protected $fillable = [
        'assignment_id',
        'assignee_id',
        'started_at',
        'submitted_at',
        'expires_at',
        'status',
        'auto_score',
        'manual_score',
        'total_score',
        'is_passed',
        'is_finalized',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_score' => 'integer',
        'manual_score' => 'integer',
        'total_score' => 'integer',
        'is_passed' => 'boolean',
        'is_finalized' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Assignment\Models\Assignment::class, 'assignment_id', 'id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'assignee_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(\App\Modules\Answer\Models\Answer::class, 'attempt_id', 'id');
    }
}
