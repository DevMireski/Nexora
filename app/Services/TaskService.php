<?php
namespace App\Services;

use App\Contracts\TaskContract;
use App\DTOs\TaskDTO;

class TaskService
{
    public function __construct(protected TaskContract $repo)
    {
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function find($id)
    {
        return $this->repo->find($id);
    }

    public function create(TaskDTO $dto)
    {
        return $this->repo->create($dto->toArray());
    }

    public function update($id, TaskDTO $dto)
    {
        return $this->repo->update($id, $dto->toArray());
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
