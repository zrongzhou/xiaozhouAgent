<?php

namespace App\Http\Controllers\Api;

use App\Models\AcceptanceReport;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcceptanceReportController extends Controller
{
    /**
     * 获取项目验收报告列表
     */
    public function index(Project $project, Request $request): JsonResponse
    {
        $query = $project->acceptanceReports();
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $reports = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($reports);
    }

    /**
     * 获取验收报告详情
     */
    public function show(AcceptanceReport $report): JsonResponse
    {
        return response()->json($report);
    }

    /**
     * 更新验收报告
     */
    public function update(Request $request, Project $project, AcceptanceReport $report): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,passed,failed,retry',
            'human_notes' => 'nullable|string',
        ]);

        $report->update($validated);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
