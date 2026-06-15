<?php

namespace App\Modules\Role\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateRoleDTO extends BaseDTO
{
    public ?string $tenant_id = null;
    public string $name;
    public ?string $description = null;
    public ?array $permission_ids = null;
}
