<?php

namespace App\Modules\Tenant\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateTenantDTO extends BaseDTO
{
    public string $name;
    public string $slug;
    public ?string $plan = null;
    public ?bool $is_active = true;
}
