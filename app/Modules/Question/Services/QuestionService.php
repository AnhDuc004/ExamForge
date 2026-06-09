<?php

namespace App\Modules\Question\Services;

use App\Shared\Services\BaseService;
use App\Modules\Question\DTOs\CreateQuestionDTO;
use App\Modules\Question\DTOs\UpdateQuestionDTO;
use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Events\QuestionCreated;
use Illuminate\Pagination\Paginator;

class QuestionService extends BaseService
{
    public function __construct(
        private QuestionRepositoryInterface $questionRepository,
    ) {
    }

    public function create(CreateQuestionDTO $dto, string $tenantId, string $userId): array
    {
        $question = $this->questionRepository->create([
            'tenant_id' => $tenantId,
            'created_by' => $userId,
            'type' => $dto->type,
            'content' => $dto->content,
            'options' => $dto->options,
            'correct_answer' => $dto->correct_answer,
            'max_score' => $dto->max_score ?? 1,
            'difficulty' => $dto->difficulty,
            'tags' => $dto->tags ?? [],
            'status' => 'draft',
        ]);

        QuestionCreated::dispatch($question->id);

        return [
            'question' => $question,
            'message' => 'Question created successfully',
        ];
    }

    public function update(string $questionId, UpdateQuestionDTO $dto): array
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found', 404);
        }

        $attributes = [];

        if ($dto->type !== null) {
            $attributes['type'] = $dto->type;
        }
        if ($dto->content !== null) {
            $attributes['content'] = $dto->content;
        }
        if ($dto->options !== null) {
            $attributes['options'] = $dto->options;
        }
        if ($dto->correct_answer !== null) {
            $attributes['correct_answer'] = $dto->correct_answer;
        }
        if ($dto->max_score !== null) {
            $attributes['max_score'] = $dto->max_score;
        }
        if ($dto->difficulty !== null) {
            $attributes['difficulty'] = $dto->difficulty;
        }
        if ($dto->tags !== null) {
            $attributes['tags'] = $dto->tags;
        }
        if ($dto->status !== null) {
            $attributes['status'] = $dto->status;
        }

        $updated = $this->questionRepository->update($questionId, $attributes);

        return [
            'question' => $updated,
            'message' => 'Question updated successfully',
        ];
    }

    public function getById(string $questionId): array
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found', 404);
        }

        return [
            'question' => $question,
        ];
    }

    public function list(string $tenantId, int $page = 1, int $perPage = 15): Paginator
    {
        return $this->questionRepository->listByTenant($tenantId, $page, $perPage);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): Paginator
    {
        return $this->questionRepository->listByStatus($tenantId, $status, $page, $perPage);
    }

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): Paginator
    {
        return $this->questionRepository->listByTags($tenantId, $tags, $page, $perPage);
    }

    public function delete(string $questionId): void
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found', 404);
        }

        $this->questionRepository->delete($questionId);
    }

    public function publish(string $questionId): array
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found', 404);
        }

        if ($question->status === 'published') {
            throw new \Exception('Question is already published', 400);
        }

        $updated = $this->questionRepository->update($questionId, ['status' => 'published']);

        return [
            'question' => $updated,
            'message' => 'Question published successfully',
        ];
    }

    public function archive(string $questionId): array
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found', 404);
        }

        $updated = $this->questionRepository->update($questionId, ['status' => 'archived']);

        return [
            'question' => $updated,
            'message' => 'Question archived successfully',
        ];
    }
}
