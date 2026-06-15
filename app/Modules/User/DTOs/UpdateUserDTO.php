<?php

namespace App\Modules\User\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateUserDTO extends BaseDTO
{
    public ?string $email = null;
    public ?string $display_name = null;
    public ?string $password = null;
    public ?string $tenant_id = null;
    public ?bool $is_active = null;
    public ?array $role_ids = null;
}
