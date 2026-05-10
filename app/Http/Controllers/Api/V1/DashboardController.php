<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(protected DashboardService $service)
    {
    }

    public function index()
    {
        return $this->successResponse($this->service->getMetrics());
    }
}
