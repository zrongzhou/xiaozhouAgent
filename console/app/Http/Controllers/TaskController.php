<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**
     * 显示项目任务列表
     */
    public function index(Project $project, Request $request): View
    {
        $tasks = $project->tasks()->orderBy('created_at', 'desc')->get();
        return view('tasks.index', compact('project', 'tasks'));
    }

    /**
     * 显示创建任务表单
     */
    public function create(Project $project): View
    {
        return view('tasks.create', compact('project'));
    }

    /**
     * 创建新任务
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'type' => 'required|in:prd,design,code,test,deploy',
            'input' => 'required|string',
            'assigned_role' => 'nullable|string',
            'model_profile' => 'nullable|string',
        ]);

        $task = $project->tasks()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'status' => 'pending',
            'input' => $validated['input'],
            'assigned_role' => $validated['assigned_role'],
            'model_profile' => $validated['model_profile'],
            'cost_tokens' => 0,
            'cost_ms' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '任务创建成功',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * 显示任务详情
     */
    public function show(Project $project, Task $task): View
    {
        $task->load('artifacts', 'acceptanceReport');
        return view('tasks.show', compact('project', 'task'));
    }

    /**
     * 更新任务状态
     */
    public function updateStatus(Request $request, Project $project, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,running,review,done,failed,retry',
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => '状态已更新',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * 删除任务
     */
    public function destroy(Project $project, Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => '任务已删除',
        ]);
    }

    /**
     * 重试失败的任务
     */
    public function retry(Project $project, Task $task): JsonResponse
    {
        $task->update([
            'status' => 'pending',
            'output' => null,
        ]);

        // 发送到编排器重试
        dispatch(new \App\Jobs\RetryTaskJob($task));

        return response()->json([
            'success' => true,
            'message' => '任务已加入重试队列',
            'data' => new TaskResource($task),
        ]);
    }
}
