<?php

namespace App\Modules\User\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(string $id);

    public function findByEmail(string $email);

    public function create(array $attributes);
}
