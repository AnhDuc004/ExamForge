<?php

namespace App\Modules\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasUuid;

class Permission extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['resource', 'action'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Role\Models\Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
        );
    }
}
