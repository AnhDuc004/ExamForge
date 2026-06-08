<?php

namespace App\Modules\Question\Repositories\Contracts;

interface QuestionRepositoryInterface
{
    public function findById(string $id);
}
