<?php
namespace App\Repositories;

use App\Contracts\TaskContract;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskRepository implements TaskContract
{
    private const CACHE_TAG = 'tasks';
    private const CACHE_TTL = 300; // 5 minutos

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $cacheKey = 'tasks.list.' . md5($perPage . serialize($filters));

        return Cache::tags([self::CACHE_TAG])->remember(
            $cacheKey,
            self::CACHE_TTL,
            fn () => $this->buildQuery($filters)
                ->with('user:id,name')
                ->orderBy('id', 'desc')
                ->paginate($perPage)
        );
    }

    public function find($id)
    {
        return Task::with('user:id,name')->find($id);
    }

    public function create(array $data): Task
    {
        $task = Task::create($data);
        Cache::tags([self::CACHE_TAG])->flush();

        return $task;
    }

    public function update($id, array $data): ?Task
    {
        $task = Task::find($id);
        if (! $task) return null;

        $task->fill($data)->save();
        Cache::tags([self::CACHE_TAG])->flush();

        return $task;
    }

    public function delete($id): bool
    {
        $task = Task::find($id);
        if (! $task) return false;

        Cache::tags([self::CACHE_TAG])->flush();

        return (bool) $task->delete();
    }

    private function buildQuery(array $filters)
    {
        $query = Task::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(fn ($qb) =>
                $qb->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
            );
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }
}
