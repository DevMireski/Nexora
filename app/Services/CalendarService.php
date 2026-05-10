<?php

namespace App\Services;

use App\Contracts\UserRepositoryContract;
use App\DTOs\CalendarDTO;
use App\Models\Task;
use Illuminate\Support\Facades\Http;

class CalendarService
{
    public function __construct(protected UserRepositoryContract $repo) {}

    public function connect(int $userId, CalendarDTO $dto): mixed
    {
        return $this->repo->update($userId, $dto->toArray());
    }

    public function syncTaskToCalendar(Task $task): void
    {
        if (! $task->due_date) {
            return;
        }

        $task->loadMissing('user');

        $calendarId = $task->user?->google_calendar_id;
        if (! $calendarId) {
            return;
        }

        $token = config('services.google.calendar_token');
        if (! $token) {
            return;
        }

        $start = $task->due_date;
        $end   = $task->due_date->copy()->addHour();

        Http::withToken($token)->post(
            'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarId) . '/events',
            [
                'summary'     => '[Nexora] ' . $task->title,
                'description' => $task->description ?? '',
                'start'       => ['dateTime' => $start->toIso8601String(), 'timeZone' => 'UTC'],
                'end'         => ['dateTime' => $end->toIso8601String(), 'timeZone' => 'UTC'],
            ]
        );
    }
}
