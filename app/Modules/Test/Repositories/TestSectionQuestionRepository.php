<?php

namespace App\Modules\Test\Repositories;

use App\Modules\Test\Models\TestSectionQuestion;
use App\Modules\Test\Repositories\Contracts\TestSectionQuestionRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class TestSectionQuestionRepository extends BaseRepository implements TestSectionQuestionRepositoryInterface
{
    public function __construct(TestSectionQuestion $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->with('question')->find($id);
    }

    public function findBySectionAndQuestion(string $sectionId, string $questionId)
    {
        return $this->model
            ->with('question')
            ->where('section_id', $sectionId)
            ->where('question_id', $questionId)
            ->first();
    }

    public function listBySection(string $sectionId)
    {
        return $this->model
            ->with('question')
            ->where('section_id', $sectionId)
            ->orderBy('position')
            ->get();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $record = $this->findById($id);

        if ($record) {
            $record->update($attributes);
        }

        return $record;
    }

    public function delete(string $id): void
    {
        $record = $this->findById($id);

        if ($record) {
            $record->delete();
        }
    }
}
