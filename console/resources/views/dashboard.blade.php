@extends('layouts.app')

@section('title', '仪表盘')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">欢迎回来</h1>
                <p class="text-gray-400 mt-1">这是您项目的概览</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('projects.create') }}" 
                   class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-premium font-medium">
                    新建项目
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">项目总数</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_projects'] }}</p>
                        <p class="text-sm text-gray-500 mt-1">活跃项目: {{ $stats['active_projects'] }}</p>
                    </div>
                    <div class="text-3xl">📁</div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">任务总数</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_tasks'] }}</p>
                        <p class="text-sm text-gray-500 mt-1">运行中: {{ $stats['running_tasks'] }}</p>
                    </div>
                    <div class="text-3xl">⚡</div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">模型画像</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['model_profiles'] }}</p>
                        <p class="text-sm text-gray-500 mt-1">已激活模型</p>
                    </div>
                    <div class="text-3xl">🤖</div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-6 transition-premium hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">验收通过</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['passed_acceptances'] }}</p>
                        <p class="text-sm text-gray-500 mt-1">质量保证</p>
                    </div>
                    <div class="text-3xl">✅</div>
                </div>
            </div>
        </div>
        
        <!-- Recent Projects -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">最近项目</h2>
            <div class="space-y-4">
                @forelse($recentProjects as $project)
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg transition-premium hover:bg-white/10">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                                <span class="text-lg">🚀</span>
                            </div>
                            <div>
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="text-lg text-white font-medium hover:text-purple-400 transition-premium">
                                    {{ $project->name }}
                                </a>
                                <p class="text-sm text-gray-400">{{ $project->description ?? '无描述' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @switch($project->status)
                                    @case('active') status-active
                                    @case('completed') status-completed
                                    @case('paused') status-paused
                                    @default status-draft
                                @endswitch">
                                {{ ucfirst($project->status) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ $project->tasks()->count() }} 个任务
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <p class="text-2xl mb-2">📭</p>
                        <p>还没有项目，创建第一个吧！</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Recent Tasks -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">最近任务</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-sm">
                            <th>任务名称</th>
                            <th>类型</th>
                            <th>状态</th>
                            <th>Token 消耗</th>
                            <th>创建时间</th>
                        </tr>
                    </thead>
                    <tbody class="text-white">
                        @forelse($recentTasks as $task)
                            <tr class="border-t border-white/10 hover:bg-white/5 transition-premium">
                                <td class="py-3">
                                    <a href="{{ route('tasks.show', [$task->project, $task]) }}" 
                                       class="font-medium hover:text-purple-400 transition-premium">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td class="py-3 text-gray-300">{{ ucfirst($task->type) }}</td>
                                <td class="py-3">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        @switch($task->status)
                                            @case('running') status-active
                                            @case('done') status-completed
                                            @case('failed') status-failed
                                            @default status-draft
                                        @endswitch">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-300">{{ number_format($task->cost_tokens) }}</td>
                                <td class="py-3 text-gray-400">{{ $task->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400">暂无任务</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
