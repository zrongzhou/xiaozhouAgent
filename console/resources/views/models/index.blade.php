@extends('layouts.app')

@section('title', '模型管理')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-white">模型管理</h1>
            <a href="{{ route('models.create') }}" 
               class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-premium font-medium">
                添加模型
            </a>
        </div>
        
        <!-- Models Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($profiles as $profile)
                <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-{{ $profile->tier === 'brain' ? 'purple' : $profile->tier === 'light' ? 'blue' : 'gray' }}-600 rounded-lg flex items-center justify-center">
                                <span>🤖</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $profile->name }}</h3>
                                <span class="text-sm text-gray-400">{{ $profile->provider }} / {{ $profile->model }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @switch($profile->tier)
                                @case('brain') bg-purple-100 text-purple-800
                                @case('light') bg-blue-100 text-blue-800
                                @default bg-gray-100 text-gray-800
                            @endswitch">
                            {{ ucfirst($profile->tier) }}
                        </span>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">最大 Tokens</span>
                            <span class="text-white">{{ $profile->max_tokens }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">输入成本</span>
                            <span class="text-white">${{ $profile->cost_per_1k_input }}/1K</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">输出成本</span>
                            <span class="text-white">${{ $profile->cost_per_1k_output }}/1K</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <span class="text-sm text-gray-500">
                            @if($profile->is_active) 已启用 @else 已禁用 @endif
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('models.show', $profile) }}" 
                               class="text-purple-400 hover:text-purple-300 transition-premium">
                                详情
                            </a>
                            <a href="{{ route('models.test', $profile) }}" 
                               class="text-blue-400 hover:text-blue-300 transition-premium">
                                测试
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
