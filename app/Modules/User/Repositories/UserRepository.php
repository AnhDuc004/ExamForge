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
        return $this->model->with('roles')->find($id);
    }

    public function findByEmail(string $email)
    {
        return $this->model->with('roles')->where('email', $email)->first();
    }

    public function findByTenantAndEmail(string $tenantId, string $email)
    {
        return $this->model->with('roles')
            ->where('tenant_id', $tenantId)
            ->where('email', $email)
            ->first();
    }

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15, ?string $search = null)
    {
        return $this->model->with('roles')
            ->where('tenant_id', $tenantId)
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('email', 'like', '%' . $search . '%')
                        ->orWhere('display_name', 'like', '%' . $search . '%');
                });
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function list(int $page = 1, int $perPage = 15, ?string $search = null)
    {
        return $this->model->with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('email', 'like', '%' . $search . '%')
                        ->orWhere('display_name', 'like', '%' . $search . '%');
                });
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $user = $this->findById($id);

        if ($user) {
            $user->update($attributes);
        }

        return $user;
    }

    public function delete(string $id): void
    {
        $user = $this->findById($id);

        if ($user) {
            $user->delete();
        }
    }
}
