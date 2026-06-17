<?php

namespace App\Modules\Answer\Repositories\Contracts;

interface AnswerRepositoryInterface
{
    public function upsertForAttempt(string $attemptId, string $testSectionQuestionId, array $attributes);

    public function findById(string $id);

    public function update(string $id, array $attributes);
}
