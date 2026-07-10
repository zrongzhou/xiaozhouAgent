<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'xiaozhouAgent') - 智能开发平台</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Livewire -->
    @livewireStyles
    
    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Premium Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-dark {
            background: rgba(30, 30, 30, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        /* Smooth Transitions */
        .transition-premium {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        /* Magnetic Effect */
        .magnetic {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .magnetic:hover {
            transform: translateY(-2px);
        }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Status Badges */
        .status-draft { @apply bg-gray-100 text-gray-800; }
        .status-active { @apply bg-blue-100 text-blue-800; }
        .status-paused { @apply bg-yellow-100 text-yellow-800; }
        .status-completed { @apply bg-green-100 text-green-800; }
        .status-failed { @apply bg-red-100 text-red-800; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen">
    <!-- Navigation -->
    <nav class="glass border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <span class="gradient-text text-2xl">🤖</span>
                    <span class="text-xl font-bold text-white">xiaozhouAgent</span>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-premium">
                        仪表盘
                    </a>
                    <a href="{{ route('projects.index') }}" class="text-gray-300 hover:text-white transition-premium">
                        项目
                    </a>
                    <a href="{{ route('models.index') }}" class="text-gray-300 hover:text-white transition-premium">
                        模型
                    </a>
                </div>
                
                <!-- User -->
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 text-sm">智能开发平台</span>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="glass border-t border-white/10 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-gray-400 text-sm">
                © {{ date('Y') }} xiaozhouAgent. 自然语言驱动的智能开发平台。
            </div>
        </div>
    </footer>
    
    @livewireScripts
    
    @stack('scripts')
</body>
</html>
