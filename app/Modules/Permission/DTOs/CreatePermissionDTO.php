<?php

namespace App\Modules\Permission\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreatePermissionDTO extends BaseDTO
{
    public string $resource;
    public string $action;
}
