<?php

namespace App\Modules\Question\Services;

use App\Modules\Question\DTOs\CreateQuestionDTO;
use App\Modules\Question\DTOs\UpdateQuestionDTO;
use App\Shared\Services\BaseService;
use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Events\QuestionCreated;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    public function bulkImport(string $tenantId, string $userId, array $questions): array
    {
        $createdQuestions = [];

        DB::transaction(function () use ($questions, $tenantId, $userId, &$createdQuestions) {
            foreach ($questions as $index => $payload) {
                $validator = Validator::make($payload, [
                    'type' => ['required', 'string', 'in:multiple_choice,short_answer,essay,true_false'],
                    'content' => ['required', 'string'],
                    'options' => ['nullable', 'array', 'required_if:type,multiple_choice'],
                    'options.*' => ['string'],
                    'correct_answer' => ['nullable', 'array'],
                    'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
                    'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
                    'tags' => ['nullable', 'array'],
                    'tags.*' => ['string', 'max:50'],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        "questions.$index" => $validator->errors()->all(),
                    ]);
                }

                $result = $this->create(
                    new CreateQuestionDTO($payload),
                    $tenantId,
                    $userId
                );

                $createdQuestions[] = $result['question'];
            }
        });

        return [
            'questions' => $createdQuestions,
            'created_count' => count($createdQuestions),
            'message' => 'Questions imported successfully',
        ];
    }

    public function bulkUpdate(string $tenantId, array $questionIds, array $attributes): array
    {
        $allowedAttributes = array_intersect_key($attributes, array_flip(['tags', 'difficulty']));

        if (empty($allowedAttributes)) {
            throw ValidationException::withMessages([
                'tags' => ['No updateable attributes were provided.'],
            ]);
        }

        $missingIds = [];
        foreach ($questionIds as $questionId) {
            if (!$this->questionRepository->findByIdAndTenant($questionId, $tenantId)) {
                $missingIds[] = $questionId;
            }
        }

        if (!empty($missingIds)) {
            throw ValidationException::withMessages([
                'question_ids' => ['One or more questions were not found in the current tenant.'],
            ]);
        }

        $updated = $this->questionRepository->bulkUpdateByTenantAndIds($tenantId, $questionIds, $allowedAttributes);

        return [
            'updated_count' => $updated,
            'message' => 'Questions updated successfully',
        ];
    }

    public function update(string $questionId, string $tenantId, UpdateQuestionDTO $dto): array
    {
        $question = $this->questionRepository->findByIdAndTenant($questionId, $tenantId);

        if (!$question) {
            throw ValidationException::withMessages([
                'question' => ['Question not found.'],
            ]);
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

        $updated = $this->questionRepository->updateByTenant($questionId, $tenantId, $attributes);

        return [
            'question' => $updated,
            'message' => 'Question updated successfully',
        ];
    }

    public function getById(string $questionId, string $tenantId): array
    {
        $question = $this->questionRepository->findByIdAndTenant($questionId, $tenantId);

        if (!$question) {
            throw ValidationException::withMessages([
                'question' => ['Question not found.'],
            ]);
        }

        return [
            'question' => $question,
        ];
    }

    public function list(string $tenantId, int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->questionRepository->listByTenant($tenantId, $page, $perPage, $filters);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->questionRepository->listByStatus($tenantId, $status, $page, $perPage);
    }

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->questionRepository->listByTags($tenantId, $tags, $page, $perPage);
    }

    public function delete(string $questionId, string $tenantId): void
    {
        $question = $this->questionRepository->findByIdAndTenant($questionId, $tenantId);

        if (!$question) {
            throw ValidationException::withMessages([
                'question' => ['Question not found.'],
            ]);
        }

        $this->questionRepository->deleteByTenant($questionId, $tenantId);
    }

    public function publish(string $questionId, string $tenantId): array
    {
        $question = $this->questionRepository->findByIdAndTenant($questionId, $tenantId);

        if (!$question) {
            throw ValidationException::withMessages([
                'question' => ['Question not found.'],
            ]);
        }

        if ($question->status === 'published') {
            throw ValidationException::withMessages([
                'question' => ['Question is already published.'],
            ]);
        }

        $updated = $this->questionRepository->updateByTenant($questionId, $tenantId, ['status' => 'published']);

        return [
            'question' => $updated,
            'message' => 'Question published successfully',
        ];
    }

    public function archive(string $questionId, string $tenantId): array
    {
        $question = $this->questionRepository->findByIdAndTenant($questionId, $tenantId);

        if (!$question) {
            throw ValidationException::withMessages([
                'question' => ['Question not found.'],
            ]);
        }

        $updated = $this->questionRepository->updateByTenant($questionId, $tenantId, ['status' => 'archived']);

        return [
            'question' => $updated,
            'message' => 'Question archived successfully',
        ];
    }
}
