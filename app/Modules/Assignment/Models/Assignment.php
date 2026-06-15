<?php

namespace App\Modules\Assignment\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'assignments';

    protected $fillable = [
        'tenant_id',
        'test_id',
        'assignee_id',
        'assigned_by',
        'due_at',
        'access_type',
        'access_token',
        'max_attempts',
        'status',
    ];

    protected $casts = [
        'max_attempts' => 'integer',
        'due_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['access_token'];

    /**
     * Assignment belongs to the assignee
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'assignee_id', 'id');
    }

    /**
     * Assignment belongs to the user who assigned it
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'assigned_by', 'id');
    }

    /**
     * Assignment belongs to a test
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Test\Models\Test::class, 'test_id', 'id');
    }

    /**
     * Assignment belongs to a tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Models\Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get attempts for this assignment
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(\App\Modules\Attempt\Models\Attempt::class, 'assignment_id', 'id');
    }
}
