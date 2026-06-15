<?php

namespace App\Traits;

use App\Modules\Role\Models\Role;

trait HasPermission
{
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->exists();
    }

    public function hasPermission(string $resource, string $action): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($resource, $action) {
                $query->where('resource', $resource)
                      ->where('action', $action);
            })
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()
            ->whereIn('name', $roles)
            ->exists();
    }

    public function hasAnyPermission(array $pairs): bool
    {
        foreach ($pairs as $pair) {
            if ($this->hasPermission($pair[0], $pair[1])) {
                return true;
            }
        }

        return false;
    }
}