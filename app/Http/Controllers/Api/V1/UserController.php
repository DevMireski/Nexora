<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function __construct(UserService $service)
    {
        $this->service = $service;
        $this->dtoClass = UserDTO::class;
    }

    public function store(Request $request)
    {
        $formRequest = app(StoreUserRequest::class);

        $created = $this->service->create(
            UserDTO::fromRequest($formRequest),
            $formRequest->input('role')
        );

        return $this->successResponse($created, 'Usuário criado com sucesso.', 201);
    }

    public function update(Request $request, $id)
    {
        $formRequest = app(UpdateUserRequest::class);

        $updated = $this->service->update(
            $id,
            UserDTO::fromRequest($formRequest),
            $formRequest->input('role')
        );

        if (! $updated) {
            return $this->errorResponse('Usuário não encontrado.', 404);
        }

        return $this->successResponse($updated, 'Usuário atualizado com sucesso.');
    }
}
