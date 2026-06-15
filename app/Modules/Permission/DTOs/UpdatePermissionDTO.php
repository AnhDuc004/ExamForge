<?php

namespace App\Modules\Permission\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdatePermissionDTO extends BaseDTO
{
    public ?string $resource = null;
    public ?string $action = null;
}
