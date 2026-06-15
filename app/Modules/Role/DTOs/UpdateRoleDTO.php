<?php

namespace App\Modules\Role\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateRoleDTO extends BaseDTO
{
    public ?string $tenant_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?array $permission_ids = null;
}
