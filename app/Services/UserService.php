<?php

namespace App\Services;

use App\Contracts\UserRepositoryContract;
use App\DTOs\UserDTO;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected UserRepositoryContract $repo) {}

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function find($id)
    {
        return $this->repo->find($id);
    }

    public function create(UserDTO $dto, ?string $role = null)
    {
        $data = $dto->toArray();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = $this->repo->create($data);

        if ($role) {
            $roleModel = Role::where('name', $role)->first();
            if ($roleModel) {
                $user->roles()->attach($roleModel->id);
            }
        }

        return $user->load('roles:id,name');
    }

    public function update($id, UserDTO $dto, ?string $role = null)
    {
        $data = $dto->toArray();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = $this->repo->update($id, $data);
        if (! $user) {
            return null;
        }

        if ($role !== null) {
            $roleModel = Role::where('name', $role)->first();
            $user->roles()->sync($roleModel ? [$roleModel->id] : []);
        }

        return $user->load('roles:id,name');
    }

    public function delete($id)
    {
        return $this->repo->delete($id);
    }
}
