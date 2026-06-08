<?php

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Model;
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
}
