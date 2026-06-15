<?php

namespace App\Modules\Test\Repositories\Contracts;

interface TestSectionQuestionRepositoryInterface
{
    public function findById(string $id);

    public function findBySectionAndQuestion(string $sectionId, string $questionId);

    public function listBySection(string $sectionId);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
