@extends('layouts.app')

@section('title', '项目列表')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-white">项目管理</h1>
            <a href="{{ route('projects.create') }}" 
               class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-premium font-medium">
                新建项目
            </a>
        </div>
        
        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projects as $project)
                <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg cursor-pointer">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">🚀</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @switch($project->status)
                                @case('active') status-active
                                @case('completed') status-completed
                                @case('paused') status-paused
                                @default status-draft
                            @endswitch">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    
                    <h2 class="text-xl font-bold text-white mb-2">{{ $project->name }}</h2>
                    <p class="text-gray-400 text-sm mb-4">{{ $project->description ?? '无描述' }}</p>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span>{{ $project->tasks()->count() }} 个任务</span>
                        <span>{{ $project->artifacts()->count() }} 个产物</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">{{ $project->created_at->format('Y-m-d') }}</span>
                        <a href="{{ route('projects.show', $project) }}" 
                           class="text-purple-400 hover:text-purple-300 transition-premium">
                            查看详情 →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full glass rounded-xl p-12 text-center">
                    <p class="text-6xl mb-4">📭</p>
                    <p class="text-gray-400 text-lg mb-6">还没有项目</p>
                    <a href="{{ route('projects.create') }}" 
                       class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-premium">
                        创建第一个项目
                    </a>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        {{ $projects->links() }}
    </div>
@endsection
