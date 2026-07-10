<?php

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * 获取项目团队列表
     */
    public function index(Project $project): JsonResponse
    {
        $teams = $project->teams()->get();
        return response()->json($teams);
    }

    /**
     * 获取团队详情
     */
    public function show(Team $team): JsonResponse
    {
        return response()->json($team);
    }

    /**
     * 创建团队
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'topology' => 'required|in:hierarchical,pipeline,parallel,debate',
            'roles' => 'required|array',
        ]);

        $team = $project->teams()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $team,
        ], 201);
    }

    /**
     * 更新团队
     */
    public function update(Request $request, Project $project, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'topology' => 'required|in:hierarchical,pipeline,parallel,debate',
            'roles' => 'required|array',
        ]);

        $team->update($validated);

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }

    /**
     * 删除团队
     */
    public function destroy(Project $project, Team $team): JsonResponse
    {
        $team->delete();
        return response()->json(['success' => true], 204);
    }
}
