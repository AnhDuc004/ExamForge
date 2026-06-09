<?php

namespace App\Modules\Tenant\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateTenantDTO extends BaseDTO
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $plan = null;
    public ?bool $is_active = null;
}
