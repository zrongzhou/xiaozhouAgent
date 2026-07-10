<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * 显示项目列表
     */
    public function index(Request $request): View
    {
        $projects = Project::with(['tasks', 'teams'])->orderBy('created_at', 'desc')->paginate(20);
        return view('projects.index', compact('projects'));
    }

    /**
     * 显示创建项目表单
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * 创建新项目
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'description' => 'nullable|string',
            'prompt' => 'required|string|min:10',
            'reference_files' => 'nullable|array',
            'reference_images' => 'nullable|array',
            'style_preset' => 'nullable|string',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'description' => $validated['description'] ?? '',
            'status' => 'draft',
            'prompt' => $validated['prompt'],
            'reference_files' => $validated['reference_files'] ?? [],
            'reference_images' => $validated['reference_images'] ?? [],
            'style_preset' => $validated['style_preset'],
            'config' => [],
        ]);

        // 创建默认团队
        $project->teams()->create([
            'name' => '默认团队',
            'topology' => 'pipeline',
            'roles' => [
                ['slug' => 'product', 'name' => '产品经理', 'model_profile' => 'brain'],
                ['slug' => 'design', 'name' => '设计师', 'model_profile' => 'brain'],
                ['slug' => 'dev', 'name' '开发工程师', 'model_profile' => 'brain'],
                ['slug' => 'qa', 'name' => '测试工程师', 'model_profile' => 'light'],
                ['slug' => 'ops', 'name' => '运维工程师', 'model_profile' => 'light'],
            ],
            'blackboard' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => '项目创建成功',
            'data' => new ProjectResource($project),
        ], 201);
    }

    /**
     * 显示项目详情
     */
    public function show(Project $project): View
    {
        $project->load('tasks', 'artifacts', 'acceptanceReports', 'teams');
        return view('projects.show', compact('project'));
    }

    /**
     * 显示编辑项目表单
     */
    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * 更新项目
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'description' => 'nullable|string',
            'prompt' => 'required|string',
            'reference_files' => 'nullable|array',
            'reference_images' => 'nullable|array',
            'style_preset' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => '项目更新成功',
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * 删除项目
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => '项目已删除',
        ]);
    }

    /**
     * 启动项目
     */
    public function start(Project $project): JsonResponse
    {
        $project->update(['status' => 'active']);

        // 发送任务到编排器
        dispatch(new \App\Jobs\StartProjectJob($project));

        return response()->json([
            'success' => true,
            'message' => '项目已启动',
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * 暂停项目
     */
    public function pause(Project $project): JsonResponse
    {
        $project->update(['status' => 'paused']);
        return response()->json([
            'success' => true,
            'message' => '项目已暂停',
            'data' => new ProjectResource($project),
        ]);
    }
}
