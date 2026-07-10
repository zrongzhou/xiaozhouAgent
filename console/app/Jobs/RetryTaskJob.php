<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Container\InteractsWithContainer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class RetryTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithContainer, Queueable, InteractsWithQueue, SerializesModels;

    public $task;
    public $maxAttempts = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the middleware the job should run with.
     */
    public function middleware()
    {
        return [
            (new WithoutOverlapping('task:' . $this->task->id))->dontRelease(),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = $this->task;
        
        // 更新任务状态
        $task->update([
            'status' => 'pending',
            'output' => null,
            'cost_tokens' => 0,
            'cost_ms' => 0,
        ]);

        // 发送到编排器重试队列
        Redis::connection('orchestrator')->lpush('task:retry', json_encode([
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'type' => $task->type,
            'input' => $task->input,
            'model_profile' => $task->model_profile,
            'created_at' => now()->toISOString(),
        ]));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Task retry job failed for {$this->task->id}: {$exception->getMessage()}");
        
        // 更新任务状态
        $this->task->update(['status' => 'failed']);
    }
}
