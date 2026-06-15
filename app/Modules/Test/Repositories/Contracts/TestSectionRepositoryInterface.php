<?php

namespace App\Modules\Test\Repositories\Contracts;

interface TestSectionRepositoryInterface
{
    public function findById(string $id);

    public function findByTestAndPosition(string $testId, int $position);

    public function listByTest(string $testId);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
