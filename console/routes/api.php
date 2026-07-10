<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function () {
    
    // 健康检查
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    })->name('api.health');

    // 项目 API
    Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class);
    
    // 任务 API
    Route::apiResource('tasks', \App\Http\Controllers\Api\TaskController::class);
    
    // 模型画像 API
    Route::apiResource('models', \App\Http\Controllers\Api\ModelProfileController::class);
    
    // 接受报告 API
    Route::apiResource('reports', \App\Http\Controllers\Api\AcceptanceReportController::class);
    
    // 团队 API
    Route::apiResource('teams', \App\Http\Controllers\Api\TeamController::class);
    
    // 统计 API
    Route::get('stats/overview', [\App\Http\Controllers\Api\StatsController::class, 'overview'])->name('api.stats.overview');
    Route::get('stats/costs', [\App\Http\Controllers\Api\StatsController::class, 'costs'])->name('api.stats.costs');
});
