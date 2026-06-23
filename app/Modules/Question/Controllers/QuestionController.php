<?php

namespace App\Modules\Question\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Question\DTOs\CreateQuestionDTO;
use App\Modules\Question\DTOs\UpdateQuestionDTO;
use App\Modules\Question\Requests\BulkImportQuestionsRequest;
use App\Modules\Question\Requests\BulkUpdateQuestionsRequest;
use App\Modules\Question\Requests\CreateQuestionRequest;
use App\Modules\Question\Requests\UpdateQuestionRequest;
use App\Modules\Question\Resources\QuestionResource;
use App\Modules\Question\Services\QuestionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class QuestionController extends Controller
{
    use ApiResponse;

    public function __construct(private QuestionService $questionService)
    {
    }

    public function store(CreateQuestionRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $dto = new CreateQuestionDTO([
            'type' => $request->type,
            'content' => $request->content,
            'options' => $request->options,
            'correct_answer' => $request->correct_answer,
            'max_score' => $request->max_score,
            'difficulty' => $request->difficulty,
            'tags' => $request->tags,
        ]);

        $result = $this->questionService->create($dto, $tenantId, $request->user()->id);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            ),
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $result = $this->questionService->getById($id, $tenantId);

        return response()->json(
            $this->successResponse('Question retrieved', new QuestionResource($result['question']))
        );
    }

    public function update(string $id, UpdateQuestionRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $dto = new UpdateQuestionDTO($request->only([
            'type',
            'content',
            'options',
            'correct_answer',
            'max_score',
            'difficulty',
            'tags',
            'status',
        ]));

        $result = $this->questionService->update($id, $tenantId, $dto);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $this->questionService->delete($id, $tenantId);

        return response()->json(
            $this->successResponse('Question deleted successfully')
        );
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);
        $filters = [
            'type' => $request->query('type'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'tags' => $this->normalizeTags($request->query('tags', [])),
        ];

        $questions = $this->questionService->list($tenantId, $page, $perPage, $filters);

        return response()->json(
            $this->successResponse(
                'Questions retrieved',
                [
                    'data' => QuestionResource::collection($questions->items()),
                    'pagination' => [
                        'total' => $questions->total(),
                        'count' => $questions->count(),
                        'per_page' => $questions->perPage(),
                        'current_page' => $questions->currentPage(),
                        'last_page' => $questions->lastPage(),
                        'from' => $questions->firstItem(),
                        'to' => $questions->lastItem(),
                    ],
                ]
            )
        );
    }

    public function byStatus(Request $request, string $status): JsonResponse
    {
        $request->query->set('status', $status);

        return $this->index($request);
    }

    public function byTags(Request $request): JsonResponse
    {
        $tags = $this->normalizeTags($request->input('tags', []));

        if (empty($tags)) {
            return response()->json(
                $this->errorResponse('Tags parameter must be a non-empty array'),
                400
            );
        }

        $request->query->set('tags', $tags);

        return $this->index($request);
    }

    public function bulkImport(BulkImportQuestionsRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $questions = $request->input('questions', []);

        if ($request->hasFile('file')) {
            $questions = $this->parseCsvQuestions($request->file('file'));
        }

        $result = $this->questionService->bulkImport($tenantId, $request->user()->id, $questions);

        return response()->json(
            $this->successResponse(
                $result['message'],
                [
                    'created_count' => $result['created_count'],
                    'questions' => QuestionResource::collection($result['questions']),
                ]
            ),
            201
        );
    }

    public function bulkUpdate(BulkUpdateQuestionsRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $attributes = [];
        if ($request->has('tags')) {
            $attributes['tags'] = $request->input('tags');
        }
        if ($request->has('difficulty')) {
            $attributes['difficulty'] = $request->input('difficulty');
        }

        $result = $this->questionService->bulkUpdate(
            $tenantId,
            $request->input('question_ids', []),
            $attributes
        );

        return response()->json(
            $this->successResponse(
                $result['message'],
                $result
            )
        );
    }

    public function publish(string $id): JsonResponse
    {
        $request = request();
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $result = $this->questionService->publish($id, $tenantId);

        AuditLog::create([
            'tenant_id' => $tenantId,
            'actor_id' => $request->user()?->id,
            'action' => 'question.published',
            'resource_type' => 'question',
            'resource_id' => $id,
            'metadata' => array_filter([
                'ip' => $request->ip(),
            ], fn ($value) => $value !== null),
        ]);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }

    public function archive(string $id): JsonResponse
    {
        $request = request();
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $result = $this->questionService->archive($id, $tenantId);

        AuditLog::create([
            'tenant_id' => $tenantId,
            'actor_id' => $request->user()?->id,
            'action' => 'question.archived',
            'resource_type' => 'question',
            'resource_id' => $id,
            'metadata' => array_filter([
                'ip' => $request->ip(),
            ], fn ($value) => $value !== null),
        ]);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }

    private function resolveTenantId(Request $request): ?string
    {
        return $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
    }

    private function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            return array_values(array_filter(array_map('trim', explode(',', $tags))));
        }

        if (!is_array($tags)) {
            return [];
        }

        return array_values(array_filter($tags, fn ($tag) => is_string($tag) && trim($tag) !== ''));
    }

    private function parseCsvQuestions(UploadedFile $file): array
    {
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new FileException('Unable to read uploaded file.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $payload = array_combine($headers, $row);
            if ($payload === false) {
                continue;
            }

            $rows[] = [
                'type' => $payload['type'] ?? null,
                'content' => $payload['content'] ?? null,
                'options' => $this->decodeJsonOrDelimitedArray($payload['options'] ?? null),
                'correct_answer' => $this->decodeJsonOrDelimitedArray($payload['correct_answer'] ?? null),
                'max_score' => isset($payload['max_score']) ? (int) $payload['max_score'] : null,
                'difficulty' => $payload['difficulty'] ?? null,
                'tags' => $this->decodeJsonOrDelimitedArray($payload['tags'] ?? null),
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function decodeJsonOrDelimitedArray(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $items = array_values(array_filter(array_map('trim', explode('|', (string) $value))));

        return empty($items) ? null : $items;
    }
}
