<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\CalendarService;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    public function __construct(private CalendarService $calendar) {}

    public function created(Task $task): void
    {
        $this->sync($task);
    }

    public function updated(Task $task): void
    {
        if ($task->wasChanged(['due_date', 'title', 'description'])) {
            $this->sync($task);
        }
    }

    private function sync(Task $task): void
    {
        try {
            $this->calendar->syncTaskToCalendar($task);
        } catch (\Throwable $e) {
            Log::warning('Google Calendar sync failed', [
                'task_id' => $task->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
