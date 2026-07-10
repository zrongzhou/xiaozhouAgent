<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Container\InteractsWithContainer;
use IlluminateQueue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class StartProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithContainer, Queueable, InteractsWithQueue, SerializesModels;

    public $project;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the middleware the job should run with.
     */
    public function middleware()
    {
        return [
            // 同一项目同时只允许一个启动任务
            (new WithoutOverlapping('project:' . $this->project->id))->dontRelease(),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $project = $this->project;
        
        // 1. 创建初始任务链
        $this->createInitialTasks($project);
        
        // 2. 发送到编排器
        $this->dispatchToOrchestrator($project);
    }

    private function createInitialTasks(Project $project): void
    {
        $team = $project->teams->first();
        $roles = $team->roles ?? [];

        // 按团队拓扑创建任务链
        $previousTask = null;
        foreach ($roles as $role) {
            $task = $project->tasks()->create([
                'name' => "{$role['name']}任务",
                'type' => $role['slug'],
                'status' => 'pending',
                'input' => $project->prompt,
                'assigned_role' => $role['slug'],
                'model_profile' => $role['model_profile'] ?? 'brain',
                'parent_id' => $previousTask?->id,
            ]);

            $previousTask = $task;
        }
    }

    private function dispatchToOrchestrator(Project $project): void
    {
        // 通过 Redis 队列发送给编排器
        Redis::connection('orchestrator')->lpush('project:start', json_encode([
            'project_id' => $project->id,
            'slug' => $project->slug,
            'prompt' => $project->prompt,
            'style_preset' => $project->style_preset,
            'created_at' => now()->toISOString(),
        ]));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Project start job failed for {$this->project->id}: {$exception->getMessage()}");
        
        // 更新项目状态
        $this->project->update(['status' => 'failed']);
    }
}
