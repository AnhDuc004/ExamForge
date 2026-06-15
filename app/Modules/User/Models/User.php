<?php

namespace App\Modules\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasUuid;
use App\Traits\HasPermission;

class User extends Authenticatable
{
    use HasUuid, HasPermission, HasApiTokens;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'email',
        'display_name',
        'password_hash',
        'tenant_id',
        'is_active'
    ];

    protected $hidden = [
        'password_hash'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Role\Models\Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        )->wherePivot(
                'model_type',
                self::class
            );
    }
}
