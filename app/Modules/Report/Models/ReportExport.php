<?php

namespace App\Modules\Report\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use HasUuid;

    protected $table = 'report_exports';

    protected $fillable = [
        'tenant_id',
        'test_id',
        'requested_by',
        'status',
        'file_path',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
