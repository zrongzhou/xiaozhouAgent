<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * 获取项目任务列表
     */
    public function index(Project $project, Request $request): JsonResponse
    {
        $query = $project->tasks();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->with('artifacts', 'acceptanceReport')->orderBy('created_at', 'desc')->get();
        
        return TaskResource::collection($tasks);
    }

    /**
     * 获取任务详情
     */
    public function show(Task $task): JsonResponse
    {
        $task->load('project', 'artifacts', 'acceptanceReport');
        return new TaskResource($task);
    }

    /**
     * 创建任务
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

        $task = $project->tasks()->create($validated);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * 更新任务
     */
    public function update(Request $request, Project $project, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,running,review,done,failed,retry',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * 删除任务
     */
    public function destroy(Project $project, Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['success' => true], 204);
    }
}
