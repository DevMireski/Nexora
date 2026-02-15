<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware(['jwt'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);

        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('logs', [ActivityLogController::class, 'index'])
            ->middleware('role:admin');

        // Must be registered before apiResource to prevent 'calendar' matching {user}
        Route::put('users/calendar', [CalendarController::class, 'update'])
            ->middleware('role:admin');
        Route::apiResource('users', UserController::class)->middleware('role:admin');

        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::apiResource('tasks', TaskController::class);
    });
});
