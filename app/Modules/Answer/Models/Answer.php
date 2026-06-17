<?php

namespace App\Modules\Answer\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasUuid;

    protected $table = 'answers';

    protected $fillable = [
        'attempt_id',
        'test_section_question_id',
        'response',
        'auto_score',
        'manual_score',
        'review_status',
        'reviewer_feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'response' => 'array',
        'auto_score' => 'integer',
        'manual_score' => 'integer',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Attempt\Models\Attempt::class, 'attempt_id', 'id');
    }

    public function testSectionQuestion(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Test\Models\TestSectionQuestion::class, 'test_section_question_id', 'id');
    }
}
