<?php

namespace App\Modules\Audit\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class AuditLog extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['tenant_id', 'actor_id', 'action', 'resource_type', 'resource_id', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
