<?php

namespace App\Traits;

trait HasPermission
{
    // Placeholder implementations; services will call into repositories for real checks
    public function hasRole(string $role): bool
    {
        return false;
    }

    public function hasPermission(string $resource, string $action): bool
    {
        return false;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $r) {
            if ($this->hasRole($r)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission(array $pairs): bool
    {
        foreach ($pairs as $pair) {
            [$resource, $action] = $pair;
            if ($this->hasPermission($resource, $action)) {
                return true;
            }
        }

        return false;
    }
}
