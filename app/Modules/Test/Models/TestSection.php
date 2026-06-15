<?php

namespace App\Modules\Test\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSection extends Model
{
    use HasUuid;

    protected $table = 'test_sections';
    public $timestamps = false;

    protected $fillable = [
        'test_id',
        'title',
        'instructions',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Section belongs to a test
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id', 'id');
    }

    /**
     * Section has many questions
     */
    public function questions(): HasMany
    {
        return $this->hasMany(\App\Modules\Test\Models\TestSectionQuestion::class, 'section_id', 'id');
    }
}
