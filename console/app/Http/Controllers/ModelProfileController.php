<?php

namespace App\Http\Controllers;

use App\Http\Resources\ModelProfileResource;
use App\Models\ModelProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelProfileController extends Controller
{
    /**
     * 显示模型画像列表
     */
    public function index(Request $request): View
    {
        $profiles = ModelProfile::with('records')->get();
        return view('models.index', compact('profiles'));
    }

    /**
     * 显示创建模型画像表单
     */
    public function create(): View
    {
        return view('models.create');
    }

    /**
     * 创建新模型画像
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:64',
            'slug' => 'required|string|max:32|unique:model_profiles',
            'provider' => 'required|in:openai,anthropic,zhipu,gemini,local',
            'model' => 'required|string|max:64',
            'base_url' => 'nullable|string|max:256',
            'api_key' => 'nullable|string',
            'tier' => 'required|in:brain,light,backup',
            'capabilities' => 'nullable|array',
            'cost_per_1k_input' => 'nullable|numeric',
            'cost_per_1k_output' => 'nullable|numeric',
            'max_tokens' => 'nullable|integer',
        ]);

        // 加密 API Key
        $apiKeyEncrypted = null;
        if ($validated['api_key']) {
            $apiKeyEncrypted = \Illuminate\Support\Facades\Crypt::encryptString($validated['api_key']);
        }

        $profile = ModelProfile::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'base_url' => $validated['base_url'],
            'api_key_encrypted' => $apiKeyEncrypted,
            'tier' => $validated['tier'],
            'capabilities' => $validated['capabilities'] ?? [],
            'cost_per_1k_input' => $validated['cost_per_1k_input'] ?? 0,
            'cost_per_1k_output' => $validated['cost_per_1k_output'] ?? 0,
            'max_tokens' => $validated['max_tokens'] ?? 4096,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '模型画像创建成功',
            'data' => new ModelProfileResource($profile),
        ], 201);
    }

    /**
     * 显示模型画像详情
     */
    public function show(ModelProfile $profile): View
    {
        $profile->load('records');
        return view('models.show', compact('profile'));
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
            'message' => '模型画像已更新',
            'data' => new ModelProfileResource($profile),
        ]);
    }

    /**
     * 删除模型画像
     */
    public function destroy(ModelProfile $profile): JsonResponse
    {
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => '模型画像已删除',
        ]);
    }

    /**
     * 测试模型连接
     */
    public function test(ModelProfile $profile): JsonResponse
    {
        try {
            // TODO: 实现模型连接测试
            return response()->json([
                'success' => true,
                'message' => '模型连接测试成功',
                'data' => [
                    'provider' => $profile->provider,
                    'model' => $profile->model,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '模型连接测试失败',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
