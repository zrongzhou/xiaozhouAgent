<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * 获取项目列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['tasks', 'teams']);
        
        // 筛选
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $projects = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));
        
        return ProjectResource::collection($projects);
    }

    /**
     * 获取项目详情
     */
    public function show(Project $project): JsonResponse
    {
        $project->load('tasks', 'artifacts', 'acceptanceReports', 'teams');
        return new ProjectResource($project);
    }

    /**
     * 创建项目
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:128',
            'slug' => 'required|string|max:64|unique:projects,slug',
            'description' => 'nullable|string',
            'prompt' => 'required|string|min:10',
            'reference_files' => 'nullable|array',
            'reference_images' => 'nullable|array',
            'style_preset' => 'nullable|string',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project),
        ], 201);
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
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * 删除项目
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        return response()->json(['success' => true], 204);
    }
}
