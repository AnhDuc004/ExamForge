<?php

namespace App\Modules\Test\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use HasUuid;

    protected $table = 'tests';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'title',
        'description',
        'duration_seconds',
        'passing_score',
        'status',
        'shuffle_questions',
        'show_correct_answers',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'passing_score' => 'float',
        'shuffle_questions' => 'boolean',
        'show_correct_answers' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Test belongs to a tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenant\Models\Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Test belongs to a creator (user)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by', 'id');
    }

    /**
     * Get test sections
     */
    public function sections(): HasMany
    {
        return $this->hasMany(\App\Modules\Test\Models\TestSection::class, 'test_id', 'id');
    }
}
