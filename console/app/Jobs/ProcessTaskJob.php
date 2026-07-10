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

class ProcessTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithContainer, Queueable, InteractsWithQueue, SerializesModels;

    public $task;
    public $maxAttempts = 5;

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
        
        // 更新状态为运行中
        $task->update(['status' => 'running']);

        try {
            // 根据任务类型处理
            switch ($task->type) {
                case 'prd':
                    $this->processPrdTask($task);
                    break;
                case 'design':
                    $this->processDesignTask($task);
                    break;
                case 'code':
                    $this->processCodeTask($task);
                    break;
                case 'test':
                    $this->processTestTask($task);
                    break;
                case 'deploy':
                    $this->processDeployTask($task);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown task type: {$task->type}");
            }

            // 标记完成
            $task->update([
                'status' => 'done',
                'cost_ms' => (microtime(true) - LARAVEL_START) * 1000,
            ]);

        } catch (\Exception $e) {
            \Log::error("Task processing failed for {$task->id}: {$e->getMessage()}");
            
            $task->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processPrdTask(Task $task): void
    {
        // TODO: 调用模型生成 PRD
        $task->update([
            'output' => [
                'title' => $task->input,
                'sections' => [],
            ],
        ]);
    }

    private function processDesignTask(Task $task): void
    {
        // TODO: 调用模型生成设计规格
        $task->update([
            'output' => [
                'components' => [],
                'style_tokens' => [],
            ],
        ]);
    }

    private function processCodeTask(Task $task): void
    {
        // TODO: 调用模型生成代码
        $task->update([
            'output' => [
                'files' => [],
                'dependencies' => [],
            ],
        ]);
    }

    private function processTestTask(Task $task): void
    {
        // TODO: 调用模型生成测试用例
        $task->update([
            'output' => [
                'test_cases' => [],
                'coverage' => 0,
            ],
        ]);
    }

    private function processDeployTask(Task $task): void
    {
        // TODO: 调用模型生成部署配置
        $task->update([
            'output' => [
                'dockerfile' => '',
                'compose' => [],
            ],
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Task processing job failed for {$this->task->id}: {$exception->getMessage()}");
    }
}
