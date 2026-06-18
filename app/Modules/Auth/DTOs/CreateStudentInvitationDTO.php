<?php

namespace App\Modules\Auth\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateStudentInvitationDTO extends BaseDTO
{
    public string $email;
    public ?int $expires_in_days = 7;
}
