<?php
namespace App\Contracts;

interface UserRepositoryContract
{
    public function paginate(int $perPage = 15, array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
