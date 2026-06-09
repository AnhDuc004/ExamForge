<?php

namespace App\Modules\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\DTOs\CreateTenantDTO;
use App\Modules\Tenant\DTOs\UpdateTenantDTO;
use App\Modules\Tenant\Requests\CreateTenantRequest;
use App\Modules\Tenant\Requests\UpdateTenantRequest;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Tenant\Services\TenantService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use ApiResponse;

    public function __construct(private TenantService $tenantService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        $list = $this->tenantService->list($perPage);

        return response()->json(
            $this->successResponse('Tenants list', $list->through(fn($t) => new TenantResource($t)))
        );
    }

    public function show(string $tenant): JsonResponse
    {
        $t = $this->tenantService->find($tenant);

        return response()->json(
            $this->successResponse('Tenant retrieved', new TenantResource($t))
        );
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $dto = new CreateTenantDTO([
            'name' => $request->name,
            'slug' => $request->slug,
            'plan' => $request->plan,
            'is_active' => $request->is_active ?? true,
        ]);

        $tenant = $this->tenantService->create((array) $dto);

        return response()->json(
            $this->successResponse('Tenant created', new TenantResource($tenant)),
            201
        );
    }

    public function update(UpdateTenantRequest $request, string $tenant): JsonResponse
    {
        $dto = new UpdateTenantDTO($request->all());

        $updated = $this->tenantService->update($tenant, (array) $dto);

        return response()->json(
            $this->successResponse('Tenant updated', new TenantResource($updated))
        );
    }

    public function destroy(string $tenant): JsonResponse
    {
        $this->tenantService->delete($tenant);

        return response()->json(
            $this->successResponse('Tenant deleted')
        );
    }
}
