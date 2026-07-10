<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ModelProfileResource;
use App\Models\ModelProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelProfileController extends Controller
{
    /**
     * 获取模型画像列表
     */
    public function index(Request $request): JsonResponse
    {
        $profiles = ModelProfile::with('records')->get();
        return ModelProfileResource::collection($profiles);
    }

    /**
     * 获取模型画像详情
     */
    public function show(ModelProfile $profile): JsonResponse
    {
        $profile->load('records');
        return new ModelProfileResource($profile);
    }

    /**
     * 创建模型画像
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'slug' => 'required|string|max:32|unique:model_profiles',
            'provider' => 'required|in:openai,anthropic,zhipu,gemini,local',
            'model' => 'required|string|max:64',
            'base_url' => 'nullable|string|max:256',
            'tier' => 'required|in:brain,light,backup',
            'capabilities' => 'nullable|array',
            'cost_per_1k_input' => 'nullable|numeric',
            'cost_per_1k_output' => 'nullable|numeric',
            'max_tokens' => 'nullable|integer',
        ]);

        $profile = ModelProfile::create($validated);

        return response()->json([
            'success' => true,
            'data' => new ModelProfileResource($profile),
        ], 201);
    }

    /**
     * 更新模型画像
     */
    public function update(Request $request, ModelProfile $profile): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'tier' => 'required|in:brain,light,backup',
            'capabilities' => 'nullable|array',
            'cost_per_1k_input' => 'nullable|numeric',
            'cost_per_1k_output' => 'nullable|numeric',
            'max_tokens' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $profile->update($validated);

        return response()->json([
            'success' => true,
            'data' => new ModelProfileResource($profile),
        ]);
    }

    /**
     * 删除模型画像
     */
    public function destroy(ModelProfile $profile): JsonResponse
    {
        $profile->delete();
        return response()->json(['success' => true], 204);
    }
}
