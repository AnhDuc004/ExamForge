<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasUuid;

class Role extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'name', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\User\Models\User::class,
            'user_roles',
            'role_id',
            'user_id'
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Permission\Models\Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }
}
