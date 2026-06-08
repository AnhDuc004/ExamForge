<?php

namespace App\Modules\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Tenant extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'slug', 'plan', 'is_active'];
}
