<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\TaskDTO;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends BaseApiController
{
    public function __construct(TaskService $service)
    {
        $this->service = $service;
        $this->dtoClass = TaskDTO::class;
    }

    public function store(Request $request)
    {
        $formRequest = app(StoreTaskRequest::class);
        $created = $this->service->create(TaskDTO::fromRequest($formRequest));
        return $this->successResponse($created, 'Tarefa criada com sucesso.', 201);
    }

    public function update(Request $request, $id)
    {
        $formRequest = app(UpdateTaskRequest::class);
        $updated = $this->service->update($id, TaskDTO::fromRequest($formRequest));
        if (! $updated) {
            return $this->errorResponse('Tarefa não encontrada.', 404);
        }
        return $this->successResponse($updated, 'Tarefa atualizada com sucesso.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => ['required', 'in:pending,progress,done']]);
        $updated = $this->service->update($id, new TaskDTO(status: $request->input('status')));
        if (! $updated) {
            return $this->errorResponse('Tarefa não encontrada.', 404);
        }
        return $this->successResponse($updated, 'Status atualizado.');
    }
}
