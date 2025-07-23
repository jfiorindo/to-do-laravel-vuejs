<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AdminController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/tasks', [AdminController::class, 'tasks']);
    Route::get('/export-tasks', [\App\Http\Controllers\ExportController::class, 'export']);
    Route::get('/admin/export-tasks', [AdminController::class, 'exportTasks']);

});


