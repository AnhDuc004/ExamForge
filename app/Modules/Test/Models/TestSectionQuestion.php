<?php

namespace App\Modules\Test\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestSectionQuestion extends Model
{
    use HasUuid;

    protected $table = 'test_section_questions';

    protected $fillable = [
        'section_id',
        'question_id',
        'position',
        'score_override',
        'question_snapshot',
    ];

    protected $casts = [
        'position' => 'integer',
        'score_override' => 'integer',
        'question_snapshot' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(TestSection::class, 'section_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Question\Models\Question::class, 'question_id', 'id');
    }
}
