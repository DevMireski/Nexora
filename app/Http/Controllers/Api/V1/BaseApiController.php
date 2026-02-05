<?php
namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    use ApiResponse;

    protected $service;
    protected $dtoClass;

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $data = $this->service->paginate($perPage, $request->all());
        return $this->successResponse($data);
    }

    public function show($id)
    {
        $model = $this->service->find($id);
        if (!$model) return $this->errorResponse('Not found', 404);
        return $this->successResponse($model);
    }

    public function store(Request $request)
    {
        $dto = $this->dtoClass::fromRequest($request);
        $created = $this->service->create($dto);
        return $this->successResponse($created, 'Created', 201);
    }

    public function update(Request $request, $id)
    {
        $dto = $this->dtoClass::fromRequest($request);
        $updated = $this->service->update($id, $dto);
        if (!$updated) return $this->errorResponse('Not found', 404);
        return $this->successResponse($updated, 'Updated');
    }

    public function destroy($id)
    {
        $deleted = $this->service->delete($id);
        if (!$deleted) return $this->errorResponse('Not found', 404);
        return $this->successResponse(null, 'Deleted', 204);
    }
}
