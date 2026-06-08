<?php

namespace App\Modules\Audit\Repositories\Contracts;

interface AuditRepositoryInterface
{
    public function record(array $payload): void;
}
