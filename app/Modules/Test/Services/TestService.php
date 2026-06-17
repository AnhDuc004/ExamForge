<?php

namespace App\Modules\Test\Services;

use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Modules\Test\DTOs\AttachTestSectionQuestionDTO;
use App\Modules\Test\DTOs\CreateTestDTO;
use App\Modules\Test\DTOs\CreateTestSectionDTO;
use App\Modules\Test\DTOs\UpdateTestDTO;
use App\Modules\Test\DTOs\UpdateTestSectionDTO;
use App\Modules\Test\DTOs\UpdateTestSectionQuestionDTO;
use App\Modules\Test\Models\Test;
use App\Modules\Test\Repositories\Contracts\TestRepositoryInterface;
use App\Modules\Test\Repositories\Contracts\TestSectionQuestionRepositoryInterface;
use App\Modules\Test\Repositories\Contracts\TestSectionRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TestService extends BaseService
{
    public function __construct(
        private TestRepositoryInterface $testRepository,
        private TestSectionRepositoryInterface $sectionRepository,
        private TestSectionQuestionRepositoryInterface $sectionQuestionRepository,
        private QuestionRepositoryInterface $questionRepository,
    ) {
    }

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->testRepository->listByTenant($tenantId, $page, $perPage);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->testRepository->listByStatus($tenantId, $status, $page, $perPage);
    }

    public function find(string $id, string $tenantId): ?Test
    {
        return $this->testRepository->findByIdAndTenant($id, $tenantId);
    }

    public function create(CreateTestDTO $dto, string $tenantId, string $userId): array
    {
        $test = DB::transaction(function () use ($dto, $tenantId, $userId) {
            $test = $this->testRepository->create([
                'tenant_id' => $tenantId,
                'created_by' => $userId,
                'title' => $dto->title,
                'description' => $dto->description,
                'duration_seconds' => $dto->duration_seconds,
                'passing_score' => $dto->passing_score,
                'status' => 'draft',
            ]);

            if (!empty($dto->sections)) {
                $this->createSectionsFromPayload($test->id, $dto->sections);
            }

            return $test;
        });

        return [
            'test' => $this->find($test->id, $tenantId),
            'message' => 'Test created successfully',
        ];
    }

    public function update(string $id, string $tenantId, UpdateTestDTO $dto): array
    {
        $test = $this->testRepository->findByIdAndTenant($id, $tenantId);

        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        $this->ensureDraftTest($test);

        $attributes = [];

        if ($dto->title !== null) {
            $attributes['title'] = $dto->title;
        }
        if ($dto->description !== null) {
            $attributes['description'] = $dto->description;
        }
        if ($dto->duration_seconds !== null) {
            $attributes['duration_seconds'] = $dto->duration_seconds;
        }
        if ($dto->passing_score !== null) {
            $attributes['passing_score'] = $dto->passing_score;
        }

        if (!empty($attributes)) {
            $this->testRepository->updateByTenant($id, $tenantId, $attributes);
        }

        return [
            'test' => $this->find($id, $tenantId),
            'message' => 'Test updated successfully',
        ];
    }

    public function delete(string $id, string $tenantId): void
    {
        $test = $this->testRepository->findByIdAndTenant($id, $tenantId);

        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        $this->ensureDraftTest($test);

        $this->testRepository->deleteByTenant($id, $tenantId);
    }

    public function publish(string $id, string $tenantId): array
    {
        $test = $this->testRepository->findByIdAndTenant($id, $tenantId);

        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        if ($test->status === 'published') {
            throw ValidationException::withMessages([
                'test' => ['Test is already published.'],
            ]);
        }

        if ($test->sections->isEmpty()) {
            throw ValidationException::withMessages([
                'sections' => ['Test must have at least one section before publishing.'],
            ]);
        }

        $hasQuestions = $test->sections->contains(fn ($section) => $section->questions->isNotEmpty());
        if (!$hasQuestions) {
            throw ValidationException::withMessages([
                'questions' => ['Test must have at least one question before publishing.'],
            ]);
        }

        $updated = $this->testRepository->updateByTenant($id, $tenantId, [
            'status' => 'published',
            'published_at' => now(),
        ]);

        return [
            'test' => $this->find($updated->id, $tenantId),
            'message' => 'Test published successfully',
        ];
    }

    public function addSection(string $testId, string $tenantId, CreateTestSectionDTO $dto): array
    {
        $section = DB::transaction(function () use ($testId, $tenantId, $dto) {
            $test = $this->resolveTestOrFail($testId, $tenantId);
            $this->ensureDraftTest($test);

            $section = $this->sectionRepository->create([
                'test_id' => $testId,
                'title' => $dto->title,
                'instructions' => $dto->instructions,
                'position' => $dto->position,
            ]);

            if (!empty($dto->questions)) {
                $this->createQuestionsFromPayload($section->id, $dto->questions);
            }

            return $section;
        });

        return [
            'section' => $this->sectionRepository->findById($section->id),
            'message' => 'Section created successfully',
        ];
    }

    public function updateSection(string $testId, string $sectionId, string $tenantId, UpdateTestSectionDTO $dto): array
    {
        $section = $this->sectionRepository->findById($sectionId);

        if (!$section || $section->test_id !== $testId) {
            throw ValidationException::withMessages([
                'section' => ['Section not found.'],
            ]);
        }

        $test = $this->resolveTestOrFail($testId, $tenantId);
        $this->ensureDraftTest($test);

        $attributes = [];

        if ($dto->title !== null) {
            $attributes['title'] = $dto->title;
        }
        if ($dto->instructions !== null) {
            $attributes['instructions'] = $dto->instructions;
        }
        if ($dto->position !== null) {
            $attributes['position'] = $dto->position;
        }

        if (!empty($attributes)) {
            $this->sectionRepository->update($sectionId, $attributes);
        }

        return [
            'section' => $this->sectionRepository->findById($sectionId),
            'message' => 'Section updated successfully',
        ];
    }

    public function deleteSection(string $testId, string $sectionId, string $tenantId): void
    {
        $section = $this->sectionRepository->findById($sectionId);

        if (!$section || $section->test_id !== $testId) {
            throw ValidationException::withMessages([
                'section' => ['Section not found.'],
            ]);
        }

        $test = $this->resolveTestOrFail($testId, $tenantId);
        $this->ensureDraftTest($test);

        $this->sectionRepository->delete($sectionId);
    }

    public function attachQuestion(string $testId, string $sectionId, string $tenantId, AttachTestSectionQuestionDTO $dto): array
    {
        $record = DB::transaction(function () use ($testId, $sectionId, $tenantId, $dto) {
            $section = $this->sectionRepository->findById($sectionId);

            if (!$section || $section->test_id !== $testId) {
                throw ValidationException::withMessages([
                    'section' => ['Section not found.'],
                ]);
            }

            $test = $this->resolveTestOrFail($testId, $tenantId);
            $this->ensureDraftTest($test);

            $question = $this->questionRepository->findById($dto->question_id);
            if (!$question) {
                throw ValidationException::withMessages([
                    'question_id' => ['Question not found.'],
                ]);
            }

            if ($question->status !== 'published') {
                throw ValidationException::withMessages([
                    'question_id' => ['Question must be published before adding to a test.'],
                ]);
            }

            $existing = $this->sectionQuestionRepository->findBySectionAndQuestion($sectionId, $dto->question_id);
            $payload = [
                'section_id' => $sectionId,
                'question_id' => $dto->question_id,
                'position' => $dto->position,
                'score_override' => $dto->score_override,
                'question_snapshot' => $this->snapshotQuestion($question),
            ];

            if ($existing) {
                $this->sectionQuestionRepository->update($existing->id, $payload);
                return $this->sectionQuestionRepository->findById($existing->id);
            }

            $created = $this->sectionQuestionRepository->create($payload);

            return $this->sectionQuestionRepository->findById($created->id);
        });

        return [
            'test_section_question' => $record,
            'message' => 'Question attached to section successfully',
        ];
    }

    public function updateSectionQuestion(
        string $testId,
        string $sectionId,
        string $sectionQuestionId,
        string $tenantId,
        UpdateTestSectionQuestionDTO $dto
    ): array {
        $section = $this->sectionRepository->findById($sectionId);

        if (!$section || $section->test_id !== $testId) {
            throw ValidationException::withMessages([
                'section' => ['Section not found.'],
            ]);
        }

        $test = $this->resolveTestOrFail($testId, $tenantId);
        $this->ensureDraftTest($test);

        $record = $this->sectionQuestionRepository->findById($sectionQuestionId);
        if (!$record || $record->section_id !== $sectionId) {
            throw ValidationException::withMessages([
                'question' => ['Question mapping not found.'],
            ]);
        }

        $attributes = [];

        if ($dto->position !== null) {
            $attributes['position'] = $dto->position;
        }
        if ($dto->score_override !== null) {
            $attributes['score_override'] = $dto->score_override;
        }

        if (!empty($attributes)) {
            $this->sectionQuestionRepository->update($sectionQuestionId, $attributes);
        }

        return [
            'test_section_question' => $this->sectionQuestionRepository->findById($sectionQuestionId),
            'message' => 'Section question updated successfully',
        ];
    }

    public function deleteSectionQuestion(string $testId, string $sectionId, string $sectionQuestionId, string $tenantId): void
    {
        $section = $this->sectionRepository->findById($sectionId);

        if (!$section || $section->test_id !== $testId) {
            throw ValidationException::withMessages([
                'section' => ['Section not found.'],
            ]);
        }

        $test = $this->resolveTestOrFail($testId, $tenantId);
        $this->ensureDraftTest($test);

        $record = $this->sectionQuestionRepository->findById($sectionQuestionId);
        if (!$record || $record->section_id !== $sectionId) {
            throw ValidationException::withMessages([
                'question' => ['Question mapping not found.'],
            ]);
        }

        $this->sectionQuestionRepository->delete($sectionQuestionId);
    }

    private function createSectionsFromPayload(string $testId, array $sections): void
    {
        foreach ($sections as $sectionPayload) {
            $section = $this->sectionRepository->create([
                'test_id' => $testId,
                'title' => $sectionPayload['title'],
                'instructions' => $sectionPayload['instructions'] ?? null,
                'position' => $sectionPayload['position'],
            ]);

            if (!empty($sectionPayload['questions'])) {
                $this->createQuestionsFromPayload($section->id, $sectionPayload['questions']);
            }
        }
    }

    private function createQuestionsFromPayload(string $sectionId, array $questions): void
    {
        foreach ($questions as $questionPayload) {
            $question = $this->questionRepository->findById($questionPayload['question_id']);

            if (!$question) {
                throw ValidationException::withMessages([
                    'question_id' => ['Question not found.'],
                ]);
            }

            if ($question->status !== 'published') {
                throw ValidationException::withMessages([
                    'question_id' => ['Question must be published before adding to a test.'],
                ]);
            }

            $existing = $this->sectionQuestionRepository->findBySectionAndQuestion($sectionId, $question->id);
            $payload = [
                'section_id' => $sectionId,
                'question_id' => $question->id,
                'position' => $questionPayload['position'],
                'score_override' => $questionPayload['score_override'] ?? null,
                'question_snapshot' => $this->snapshotQuestion($question),
            ];

            if ($existing) {
                $this->sectionQuestionRepository->update($existing->id, $payload);
            } else {
                $this->sectionQuestionRepository->create($payload);
            }
        }
    }

    private function snapshotQuestion($question): array
    {
        return [
            'id' => $question->id,
            'type' => $question->type,
            'content' => $question->content,
            'options' => $question->options,
            'correct_answer' => $question->correct_answer,
            'max_score' => $question->max_score,
            'difficulty' => $question->difficulty,
            'tags' => $question->tags,
            'status' => $question->status,
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
        ];
    }

    private function ensureDraftTest(?Test $test): void
    {
        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        if ($test->status === 'published') {
            throw ValidationException::withMessages([
                'test' => ['Published tests cannot be modified.'],
            ]);
        }
    }

    private function resolveTestOrFail(string $testId, string $tenantId): Test
    {
        $test = $this->testRepository->findByIdAndTenant($testId, $tenantId);

        if (!$test) {
            throw ValidationException::withMessages([
                'test' => ['Test not found.'],
            ]);
        }

        return $test;
    }
}
