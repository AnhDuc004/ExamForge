<?php

namespace App\Modules\Test\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Test\DTOs\AttachTestSectionQuestionDTO;
use App\Modules\Test\DTOs\CreateTestDTO;
use App\Modules\Test\DTOs\CreateTestSectionDTO;
use App\Modules\Test\DTOs\UpdateTestDTO;
use App\Modules\Test\DTOs\UpdateTestSectionDTO;
use App\Modules\Test\DTOs\UpdateTestSectionQuestionDTO;
use App\Modules\Test\Requests\AttachTestSectionQuestionRequest;
use App\Modules\Test\Requests\CreateTestRequest;
use App\Modules\Test\Requests\CreateTestSectionRequest;
use App\Modules\Test\Requests\UpdateTestRequest;
use App\Modules\Test\Requests\UpdateTestSectionQuestionRequest;
use App\Modules\Test\Requests\UpdateTestSectionRequest;
use App\Modules\Test\Resources\TestResource;
use App\Modules\Test\Resources\TestSectionQuestionResource;
use App\Modules\Test\Resources\TestSectionResource;
use App\Modules\Test\Services\TestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    use ApiResponse;

    public function __construct(private TestService $testService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);
        $status = $request->query('status');

        $tests = $status
            ? $this->testService->listByStatus($tenantId, $status, $page, $perPage)
            : $this->testService->listByTenant($tenantId, $page, $perPage);

        return response()->json(
            $this->successResponse('Tests retrieved', [
                'data' => TestResource::collection($tests->items()),
                'pagination' => [
                    'total' => $tests->total(),
                    'count' => $tests->count(),
                    'per_page' => $tests->perPage(),
                    'current_page' => $tests->currentPage(),
                    'last_page' => $tests->lastPage(),
                    'from' => $tests->firstItem(),
                    'to' => $tests->lastItem(),
                ],
            ])
        );
    }

    public function show(string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $test = $this->testService->find($id, $tenantId);

        if (!$test) {
            return response()->json($this->errorResponse('Test not found'), 404);
        }

        return response()->json(
            $this->successResponse('Test retrieved', new TestResource($test))
        );
    }

    public function store(CreateTestRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new CreateTestDTO([
            'title' => $request->title,
            'description' => $request->description,
            'duration_seconds' => $request->duration_seconds,
            'passing_score' => $request->passing_score,
            'sections' => $request->sections,
        ]);

        $result = $this->testService->create($dto, $tenantId, $request->user()->id);

        return response()->json(
            $this->successResponse($result['message'], new TestResource($result['test'])),
            201
        );
    }

    public function update(string $id, UpdateTestRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new UpdateTestDTO([
            'title' => $request->title,
            'description' => $request->description,
            'duration_seconds' => $request->duration_seconds,
            'passing_score' => $request->passing_score,
        ]);

        $result = $this->testService->update($id, $tenantId, $dto);

        return response()->json(
            $this->successResponse($result['message'], new TestResource($result['test']))
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $this->testService->delete($id, $tenantId);

        return response()->json($this->successResponse('Test deleted successfully'));
    }

    public function publish(string $id): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $result = $this->testService->publish($id, $tenantId);

        return response()->json(
            $this->successResponse($result['message'], new TestResource($result['test']))
        );
    }

    public function addSection(string $testId, CreateTestSectionRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new CreateTestSectionDTO([
            'title' => $request->title,
            'instructions' => $request->instructions,
            'position' => $request->position,
            'questions' => $request->questions,
        ]);

        $result = $this->testService->addSection($testId, $tenantId, $dto);

        return response()->json(
            $this->successResponse($result['message'], new TestSectionResource($result['section'])),
            201
        );
    }

    public function updateSection(string $testId, string $sectionId, UpdateTestSectionRequest $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new UpdateTestSectionDTO([
            'title' => $request->title,
            'instructions' => $request->instructions,
            'position' => $request->position,
        ]);

        $result = $this->testService->updateSection($testId, $sectionId, $tenantId, $dto);

        return response()->json(
            $this->successResponse($result['message'], new TestSectionResource($result['section']))
        );
    }

    public function deleteSection(string $testId, string $sectionId): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $this->testService->deleteSection($testId, $sectionId, $tenantId);

        return response()->json($this->successResponse('Section deleted successfully'));
    }

    public function attachQuestion(
        string $testId,
        string $sectionId,
        AttachTestSectionQuestionRequest $request
    ): JsonResponse {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new AttachTestSectionQuestionDTO([
            'question_id' => $request->question_id,
            'position' => $request->position,
            'score_override' => $request->score_override,
        ]);

        $result = $this->testService->attachQuestion($testId, $sectionId, $tenantId, $dto);

        return response()->json(
            $this->successResponse($result['message'], new TestSectionQuestionResource($result['test_section_question'])),
            201
        );
    }

    public function updateSectionQuestion(
        string $testId,
        string $sectionId,
        string $sectionQuestionId,
        UpdateTestSectionQuestionRequest $request
    ): JsonResponse {
        $tenantId = $this->resolveTenantId($request);

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new UpdateTestSectionQuestionDTO([
            'position' => $request->position,
            'score_override' => $request->score_override,
        ]);

        $result = $this->testService->updateSectionQuestion($testId, $sectionId, $sectionQuestionId, $tenantId, $dto);

        return response()->json(
            $this->successResponse($result['message'], new TestSectionQuestionResource($result['test_section_question']))
        );
    }

    public function deleteSectionQuestion(string $testId, string $sectionId, string $sectionQuestionId): JsonResponse
    {
        $tenantId = $this->resolveTenantId(request());

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $this->testService->deleteSectionQuestion($testId, $sectionId, $sectionQuestionId, $tenantId);

        return response()->json($this->successResponse('Question removed from section successfully'));
    }

    private function resolveTenantId(Request $request): ?string
    {
        return $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
    }
}
