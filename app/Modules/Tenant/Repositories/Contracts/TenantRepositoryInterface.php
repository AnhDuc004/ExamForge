<?php

namespace App\Modules\Tenant\Repositories\Contracts;

interface TenantRepositoryInterface
{
    public function findById(string $id);

    public function findBySlug(string $slug);

    public function findDefaultTenant();
}
