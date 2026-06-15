<?php

namespace App\Modules\User\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateUserDTO extends BaseDTO
{
    public string $email;
    public string $display_name;
    public string $password;
    public string $tenant_id;
    public ?bool $is_active = true;
    public ?array $role_ids = null;
}
