@extends('layouts.app')

@section('title', '新建项目')

@section('content')
    <div class="space-y-8 max-w-2xl">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-white">新建项目</h1>
            <p class="text-gray-400 mt-1">描述您想要开发的产品，AI 将自动完成开发</p>
        </div>
        
        <!-- Form -->
        <div class="glass rounded-xl p-6">
            <form method="POST" id="projectForm">
                @csrf
                
                <!-- Name -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">项目名称 *</label>
                    <input type="text" 
                           name="name" 
                           required
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium"
                           placeholder="我的第一个 AI 应用">
                </div>
                
                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">项目描述</label>
                    <textarea name="description" 
                              rows="2"
                              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium"
                              placeholder="可选，简要描述项目目标"></textarea>
                </div>
                
                <!-- Prompt -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">详细需求描述 *</label>
                    <textarea name="prompt" 
                              rows="6"
                              required
                              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium"
                              placeholder="详细描述您想要开发的应用，包括功能、界面、交互等要求..."></textarea>
                    <p class="text-sm text-gray-500 mt-2">提示：描述越详细，AI 生成的结果越准确</p>
                </div>
                
                <!-- Style Preset -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">风格预设</label>
                    <select name="style_preset" 
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium">
                        <option value="">默认风格</option>
                        <option value="modern">现代简约</option>
                        <option value="luxury">高端奢华</option>
                        <option value="tech">科技感</option>
                        <option value="minimal">极简主义</option>
                    </select>
                </div>
                
                <!-- Reference Files -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">参考文件（JSON 格式）</label>
                    <textarea name="reference_files" 
                              rows="3"
                              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium font-mono text-sm"
                              placeholder='["path/to/file1.pdf", "path/to/file2.docx"]'></textarea>
                </div>
                
                <!-- Reference Images -->
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-medium mb-2">参考图片（JSON 格式）</label>
                    <textarea name="reference_images" 
                              rows="3"
                              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-premium font-mono text-sm"
                              placeholder='["path/to/image1.png", "path/to/image2.jpg"]'></textarea>
                </div>
                
                <!-- Submit -->
                <div class="flex items-center space-x-4">
                    <button type="submit" 
                            id="submitBtn"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-premium font-medium">
                        创建项目
                    </button>
                    <a href="{{ route('projects.index') }}" class="px-6 py-3 text-gray-400 hover:text-white transition-premium">
                        取消
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('projectForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = '创建中...';
            
            // Convert to JSON
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if (key === 'reference_files' || key === 'reference_images') {
                    try {
                        data[key] = value ? JSON.parse(value) : [];
                    } catch {
                        data[key] = [];
                    }
                } else {
                    data[key] = value;
                }
            });
            
            fetch('/api/v1/projects', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/projects/' + data.data.data.slug;
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = '创建项目';
            });
            
            e.preventDefault();
        });
    </script>
@endsection
