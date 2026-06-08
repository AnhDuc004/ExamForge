<?php

namespace App\Modules\User\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\User\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }
}
