@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="space-y-8">
        <!-- Project Header -->
        <div class="glass rounded-xl p-6">
            <div class="flex justify-between items-center">
                <div>
                    <nav class="text-gray-400 text-sm mb-2">
                        <a href="{{ route('projects.index') }}" class="hover:text-white">项目</a>
                        / {{ $project->name }}
                    </nav>
                    <h1 class="text-3xl font-bold text-white">{{ $project->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ $project->description ?? '无描述' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    @if($project->status === 'active')
                        <form method="POST" action="{{ route('projects.pause', $project) }}">
                            @csrf
                            @method('POST')
                            <button type="submit" 
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-premium">
                                暂停
                            </button>
                        </form>
                    @elseif($project->status === 'paused' || $project->status === 'draft')
                        <form method="POST" action="{{ route('projects.start', $project) }}">
                            @csrf
                            @method('POST')
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-premium">
                                启动
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            
            <!-- Project Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                <div class="p-4 bg-white/5 rounded-lg">
                    <p class="text-gray-400 text-sm">状态</p>
                    <p class="text-white font-medium mt-1">{{ ucfirst($project->status) }}</p>
                </div>
                <div class="p-4 bg-white/5 rounded-lg">
                    <p class="text-gray-400 text-sm">任务数</p>
                    <p class="text-white font-medium mt-1">{{ $project->tasks()->count() }}</p>
                </div>
                <div class="p-4 bg-white/5 rounded-lg">
                    <p class="text-gray-400 text-sm">产物数</p>
                    <p class="text-white font-medium mt-1">{{ $project->artifacts()->count() }}</p>
                </div>
                <div class="p-4 bg-white/5 rounded-lg">
                    <p class="text-gray-400 text-sm">创建时间</p>
                    <p class="text-white font-medium mt-1">{{ $project->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Project Prompt -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-4">项目描述</h2>
            <div class="p-4 bg-white/5 rounded-lg">
                <p class="text-gray-300">{{ $project->prompt }}</p>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="glass rounded-xl">
            <div class="flex border-b border-white/10">
                <button class="px-6 py-4 text-gray-400 border-b-2 border-transparent hover:text-white transition-premium">
                    任务列表
                </button>
                <button class="px-6 py-4 text-gray-400 border-b-2 border-transparent hover:text-white transition-premium">
                    产物
                </button>
                <button class="px-6 py-4 text-gray-400 border-b-2 border-transparent hover:text-white transition-premium">
                    验收报告
                </button>
                <button class="px-6 py-4 text-gray-400 border-b-2 border-transparent hover:text-white transition-premium">
                    团队
                </button>
            </div>
            
            <!-- Tasks -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-white mb-4">任务列表 ({{ $project->tasks()->count() }})</h3>
                <div class="space-y-3">
                    @forelse($project->tasks as $task)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg transition-premium hover:bg-white/10">
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-{{ $task->type === 'code' ? 'blue' : $task->type === 'design' ? 'purple' : 'gray' }}-600 rounded-lg flex items-center justify-center">
                                    <span>{{ strtoupper($task->type[0]) }}</span>
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $task->name }}</p>
                                    <p class="text-sm text-gray-400">{{ ucfirst($task->type) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @switch($task->status)
                                        @case('running') status-active
                                        @case('done') status-completed
                                        @case('failed') status-failed
                                        @default status-draft
                                    @endswitch">
                                    {{ ucfirst($task->status) }}
                                </span>
                                <span class="text-sm text-gray-500">{{ number_format($task->cost_tokens) }} tokens</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p>暂无任务</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
