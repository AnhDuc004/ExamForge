<?php

namespace App\Modules\Tenant\Repositories\Contracts;

interface TenantRepositoryInterface
{
    public function findBySlug(string $slug);

    public function findDefaultTenant();
}
