<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $logs = ActivityLog::with('user:id,name')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse($logs);
    }
}
