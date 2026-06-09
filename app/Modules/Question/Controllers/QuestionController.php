<?php

namespace App\Modules\Question\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Question\DTOs\CreateQuestionDTO;
use App\Modules\Question\DTOs\UpdateQuestionDTO;
use App\Modules\Question\Requests\CreateQuestionRequest;
use App\Modules\Question\Requests\UpdateQuestionRequest;
use App\Modules\Question\Resources\QuestionResource;
use App\Modules\Question\Services\QuestionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ApiResponse;

    public function __construct(private QuestionService $questionService) {}

    public function store(CreateQuestionRequest $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

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
        $result = $this->questionService->getById($id);

        return response()->json(
            $this->successResponse('Question retrieved', new QuestionResource($result['question']))
        );
    }

    public function update(string $id, UpdateQuestionRequest $request): JsonResponse
    {
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

        $result = $this->questionService->update($id, $dto);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->questionService->delete($id);

        return response()->json(
            $this->successResponse('Question deleted successfully')
        );
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        $questions = $this->questionService->list($tenantId, $page, $perPage);

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
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        $questions = $this->questionService->listByStatus($tenantId, $status, $page, $perPage);

        return response()->json(
            $this->successResponse(
                "Questions with status '{$status}' retrieved",
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

    public function byTags(Request $request): JsonResponse
    {
        $tags = $request->input('tags', []);

        if (!is_array($tags) || empty($tags)) {
            return response()->json(
                $this->errorResponse('Tags parameter must be a non-empty array'),
                400
            );
        }

        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        $questions = $this->questionService->listByTags($tenantId, $tags, $page, $perPage);

        return response()->json(
            $this->successResponse(
                'Questions by tags retrieved',
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

    public function publish(string $id): JsonResponse
    {
        $result = $this->questionService->publish($id);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }

    public function archive(string $id): JsonResponse
    {
        $result = $this->questionService->archive($id);

        return response()->json(
            $this->successResponse(
                $result['message'],
                new QuestionResource($result['question'])
            )
        );
    }
}
