<?php

namespace App\Modules\Answer\Repositories;

use App\Modules\Answer\Models\Answer;
use App\Modules\Answer\Repositories\Contracts\AnswerRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class AnswerRepository extends BaseRepository implements AnswerRepositoryInterface
{
    public function __construct(Answer $model)
    {
        parent::__construct($model);
    }

    public function upsertForAttempt(string $attemptId, string $testSectionQuestionId, array $attributes)
    {
        return $this->model->updateOrCreate(
            [
                'attempt_id' => $attemptId,
                'test_section_question_id' => $testSectionQuestionId,
            ],
            $attributes
        )->load('testSectionQuestion');
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['attempt.assignment.test', 'testSectionQuestion'])
            ->where('id', $id)
            ->first();
    }

    public function update(string $id, array $attributes)
    {
        $answer = $this->model->where('id', $id)->first();
        if ($answer) {
            $answer->update($attributes);
        }

        return $answer?->load(['attempt.assignment.test', 'testSectionQuestion']);
    }
}
