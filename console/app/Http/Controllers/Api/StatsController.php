<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Task;
use App\Models\ModelProfile;
use App\Models\AcceptanceReport;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * 获取总览统计
     */
    public function overview(): JsonResponse
    {
        $stats = [
            'projects' => [
                'total' => Project::count(),
                'active' => Project::where('status', 'active')->count(),
                'completed' => Project::where('status', 'completed')->count(),
            ],
            'tasks' => [
                'total' => Task::count(),
                'running' => Task::where('status', 'running')->count(),
                'completed' => Task::where('status', 'done')->count(),
                'failed' => Task::where('status', 'failed')->count(),
            ],
            'models' => [
                'total' => ModelProfile::count(),
                'active' => ModelProfile::where('is_active', true)->count(),
            ],
            'acceptances' => [
                'total' => AcceptanceReport::count(),
                'passed' => AcceptanceReport::where('status', 'passed')->count(),
                'failed' => AcceptanceReport::where('status', 'failed')->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * 获取成本统计
     */
    public function costs()
    {
        $profiles = ModelProfile::with('records')->get();
        
        $costs = [];
        foreach ($profiles as $profile) {
            $records = $profile->records()->where('success', true)->get();
            $costs[] = [
                'profile_id' => $profile->id,
                'profile_name' => $profile->name,
                'provider' => $profile->provider,
                'model' => $profile->model,
                'total_tokens_input' => $records->sum('tokens_input'),
                'total_tokens_output' => $records->sum('tokens_output'),
                'total_cost' => $records->sum('cost_usd'),
                'request_count' => $records->count(),
            ];
        }

        return response()->json([
            'profiles' => $costs,
            'total' => [
                'tokens_input' => array_sum(array_column($costs, 'total_tokens_input')),
                'tokens_output' => array_sum(array_column($costs, 'total_tokens_output')),
                'cost_usd' => array_sum(array_column($costs, 'total_cost')),
                'requests' => array_sum(array_column($costs, 'request_count')),
            ],
        ]);
    }
}
