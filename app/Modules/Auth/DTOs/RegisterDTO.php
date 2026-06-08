<?php

namespace App\Modules\Auth\DTOs;

use App\Shared\DTOs\BaseDTO;

class RegisterDTO extends BaseDTO
{
    public string $email;
    public string $display_name;
    public string $password;
    public ?string $device_name = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
