<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryContract;
use App\Models\User;

class UserRepository implements UserRepositoryContract
{
    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = User::with('roles:id,name');
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(fn ($b) => $b->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function find($id)
    {
        return User::with('roles:id,name')->find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $model = User::find($id);
        if (! $model) {
            return null;
        }
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function delete($id)
    {
        $model = User::find($id);
        if (! $model) {
            return false;
        }

        return $model->delete();
    }
}
