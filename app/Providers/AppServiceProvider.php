<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\TaskContract;
use App\Contracts\UserRepositoryContract;
use App\Models\Task;
use App\Observers\ActivityLogObserver;
use App\Observers\TaskObserver;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(TaskContract::class, TaskRepository::class);
    }

    public function boot(): void
    {
        Task::observe(ActivityLogObserver::class);
        Task::observe(TaskObserver::class);
    }
}
