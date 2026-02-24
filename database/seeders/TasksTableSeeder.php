<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            Task::factory(20)->create();
            return;
        }

        $users->each(fn ($user) => Task::factory(rand(3, 8))->create(['user_id' => $user->id]));
    }
}
