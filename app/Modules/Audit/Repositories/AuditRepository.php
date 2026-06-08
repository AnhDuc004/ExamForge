<?php

namespace App\Modules\Audit\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Audit\Repositories\Contracts\AuditRepositoryInterface;
use App\Modules\Audit\Models\AuditLog;

class AuditRepository extends BaseRepository implements AuditRepositoryInterface
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    public function record(array $payload): void
    {
        $this->model->create($payload);
    }
}
