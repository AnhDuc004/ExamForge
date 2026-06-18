<?php

namespace App\Modules\Auth\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentInvitation extends Model
{
    use HasUuid;

    protected $table = 'student_invitations';

    protected $fillable = [
        'tenant_id',
        'invited_by',
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Models\Tenant::class, 'tenant_id', 'id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'invited_by', 'id');
    }
}
