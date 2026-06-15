<?php

namespace App\Modules\Role\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(fn ($permission) => [
                    'id' => $permission->id,
                    'resource' => $permission->resource,
                    'action' => $permission->action,
                ])->values();
            }, $this->permissions->map(fn ($permission) => [
                'id' => $permission->id,
                'resource' => $permission->resource,
                'action' => $permission->action,
            ])->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
