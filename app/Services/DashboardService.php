<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getMetrics(): array
    {
        return Cache::tags(['dashboard'])->remember('dashboard.metrics', 60, function () {
            return [
                'total_users'     => User::count(),
                'active_tasks'    => Task::whereIn('status', ['pending', 'progress'])->count(),
                'recent_activity' => ActivityLog::with('user:id,name')
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        });
    }
}
