<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ModelProfileController;
use Illuminate\Support\Facades\Route;

// 首页重定向到仪表盘
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 仪表盘
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// 项目管理
Route::resource('projects', ProjectController::class)->except(['show']);
Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('projects/{project}/start', [ProjectController::class, 'start'])->name('projects.start');
Route::post('projects/{project}/pause', [ProjectController::class, 'pause'])->name('projects.pause');

// 任务管理
Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('projects/{project}/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
Route::post('projects/{project}/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
Route::post('projects/{project}/tasks/{task}/retry', [TaskController::class, 'retry'])->name('tasks.retry');
Route::delete('projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

// 模型管理
Route::resource('models', ModelProfileController::class)->except(['show']);
Route::get('models/{profile}', [ModelProfileController::class, 'show'])->name('models.show');
Route::post('models/{profile}/test', [ModelProfileController::class, 'test'])->name('models.test');
