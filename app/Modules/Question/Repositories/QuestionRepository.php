<?php

namespace App\Modules\Question\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Modules\Question\Models\Question;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }
}
