<?php

namespace App\Modules\Tenant\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Modules\Tenant\Models\Tenant;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->where('id', $id)->where('is_active', true)->first();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->where('is_active', true)->first();
    }

    public function findDefaultTenant()
    {
        return $this->model->where('slug', 'default')->where('is_active', true)->first();
    }
}
