<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CalendarDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\UpdateCalendarRequest;
use App\Services\CalendarService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CalendarService $service) {}

    public function update(Request $request)
    {
        $formRequest = app(UpdateCalendarRequest::class);
        $dto = CalendarDTO::fromRequest($formRequest);

        $user = $this->service->connect($dto->user_id, $dto);

        if (! $user) {
            return $this->errorResponse('Usuário não encontrado.', 404);
        }

        return $this->successResponse($user, 'Google Agenda conectada com sucesso.');
    }
}
