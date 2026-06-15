<?php

namespace App\Modules\Test\Repositories;

use App\Modules\Test\Models\TestSection;
use App\Modules\Test\Repositories\Contracts\TestSectionRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class TestSectionRepository extends BaseRepository implements TestSectionRepositoryInterface
{
    public function __construct(TestSection $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->with('questions.question')->find($id);
    }

    public function findByTestAndPosition(string $testId, int $position)
    {
        return $this->model
            ->with('questions.question')
            ->where('test_id', $testId)
            ->where('position', $position)
            ->first();
    }

    public function listByTest(string $testId)
    {
        return $this->model
            ->with('questions.question')
            ->where('test_id', $testId)
            ->orderBy('position')
            ->get();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $section = $this->findById($id);

        if ($section) {
            $section->update($attributes);
        }

        return $section;
    }

    public function delete(string $id): void
    {
        $section = $this->findById($id);

        if ($section) {
            $section->delete();
        }
    }
}
