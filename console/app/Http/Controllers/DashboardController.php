<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\ModelProfile;
use App\Models\AcceptanceReport;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * 显示仪表盘
     */
    public function index(): View
    {
        // 统计数据
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tasks' => Task::count(),
            'running_tasks' => Task::where('status', 'running')->count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'failed_tasks' => Task::where('status', 'failed')->count(),
            'model_profiles' => ModelProfile::where('is_active', true)->count(),
            'passed_acceptances' => AcceptanceReport::where('status', 'passed')->count(),
        ];

        // 最近项目
        $recentProjects = Project::with(['tasks', 'teams'])->orderBy('created_at', 'desc')->limit(5)->get();

        // 最近任务
        $recentTasks = Task::with(['project', 'artifacts'])->orderBy('created_at', 'desc')->limit(10)->get();

        // 成本统计
        $costStats = [
            'total_tokens' => Task::sum('cost_tokens'),
            'total_duration_ms' => Task::sum('cost_ms'),
        ];

        return view('dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentTasks' => $recentTasks,
            'costStats' => $costStats,
        ]);
    }
}
