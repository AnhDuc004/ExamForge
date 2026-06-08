<?php

namespace App\Modules\Auth\DTOs;

use App\Shared\DTOs\BaseDTO;

class LoginDTO extends BaseDTO
{
    public string $email;
    public string $password;
    public ?string $device_name = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
