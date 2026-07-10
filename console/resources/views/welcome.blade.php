@extends('layouts.app')

@section('content')
  <section style="text-align:center;padding:56px 0 40px;">
    <div style="display:inline-block;padding:6px 14px;border-radius:999px;font-size:13px;color:var(--muted);border:1px solid var(--border);margin-bottom:22px;">
      🚀 P1 工程骨架已上线 · 描述即出站点
    </div>
    <h1 style="font-size:clamp(34px,6vw,56px);line-height:1.1;margin:0 0 18px;font-weight:800;">
      你只描述，<span class="gradient-text">AI 自主完成</span><br>前端 + 后端 + 验收
    </h1>
    <p style="color:var(--muted);font-size:18px;max-width:640px;margin:0 auto 32px;line-height:1.7;">
      混合模型大脑 · 专家团队开发 · 风格引导复用 · 单机可迁移部署。
      给它一句话，它按你的风格把站点做出来，并自己跑验收。
    </p>
    <button class="btn-primary magnetic" id="genBtn">✨ 一句话生成站点</button>
    <p id="genStatus" style="color:var(--muted);font-size:14px;margin-top:16px;min-height:20px;"></p>
  </section>

  <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-top:24px;">
    @foreach([
      ['🧠','混合模型大脑','商业 API 主脑 + 开源轻量路由 + 手工指定，按 GPU 决策树按需升级'],
      ['👥','专家团队','产品/设计/开发/测试/运维多角色协作，共享黑板 + 产物契约'],
      ['🎨','风格引导','前期共建设计系统，后期只描述+验收，越用越懂你的口味'],
      ['✅','自主验收','构建/E2E/视觉比对/性能/无障碍 全过才交付，不达标自动返工'],
      ['📦','单机可迁移','/data + /config 即迁移闭包，换机器 = 拷两目录 + 一条命令'],
      ['🔒','安全护栏','执行沙箱 + 密钥不入库 + 并发配额 + 成本熔断'],
    ] as $f)
      <div class="glass magnetic" style="padding:24px;">
        <div style="font-size:26px;">{{ $f[0] }}</div>
        <h3 style="margin:12px 0 8px;font-size:17px;">{{ $f[1] }}</h3>
        <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:0;">{{ $f[2] }}</p>
      </div>
    @endforeach
  </section>

  <section class="glass" style="margin-top:28px;padding:24px 28px;">
    <h3 style="margin:0 0 10px;font-size:16px;">🛠 当前链路</h3>
    <p style="color:var(--muted);font-size:14px;line-height:1.7;margin:0;">
      caddy → console(Laravel) / orchestrator(Python) → postgres · redis。
      点上方按钮会经 Caddy 反向代理调用 orchestrator 的 <code>/api/build</code>，
      在 <code>/data/projects</code> 落一个交付占位，证明“描述→开发→交付”闭环可跑。
    </p>
  </section>
@endsection

@section('scripts')
  <script>
    document.getElementById('genBtn').addEventListener('click', async function () {
      var el = document.getElementById('genStatus');
      el.textContent = '提交中…';
      try {
        var r = await fetch('/api/build', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ description: '一个现代 SaaS 产品落地页，含 hero、定价、FAQ' })
        });
        var d = await r.json();
        el.textContent = '已派发任务：' + d.task_id + '（状态：' + d.status + '）';
      } catch (e) {
        el.textContent = '调用失败：' + e.message + '（请确认 orchestrator 已启动）';
      }
    });
  </script>
@endsection
