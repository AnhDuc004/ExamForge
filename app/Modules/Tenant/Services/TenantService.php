<?php

namespace App\Modules\Tenant\Services;

use App\Shared\Services\BaseService;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Validation\ValidationException;

class TenantService extends BaseService
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private Tenant $model
    ) {
    }

    public function list(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function find(string $id): ?Tenant
    {
        return $this->model->find($id);
    }

    public function create(array $attributes): Tenant
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes): Tenant
    {
        $tenant = $this->find($id);

        if (!$tenant) {
            throw ValidationException::withMessages(['tenant' => ['Tenant not found.']]);
        }

        $tenant->update($attributes);

        return $tenant;
    }

    public function delete(string $id): void
    {
        $tenant = $this->find($id);

        if (!$tenant) {
            throw ValidationException::withMessages(['tenant' => ['Tenant not found.']]);
        }

        $tenant->delete();
    }
}
