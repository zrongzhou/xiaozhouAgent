<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'xiaozhouAgent · 自主开发平台')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    (function () {
      var t = localStorage.getItem('theme') || 'system';
      var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    })();
  </script>
  <style>
    :root, [data-theme="light"] {
      --bg:#F7F8FA; --surface:#FFFFFF; --text:#0F172A; --muted:#475569;
      --primary:#4F46E5; --accent:#06B6D4; --border:#E2E8F0; --glass:rgba(255,255,255,0.6);
    }
    [data-theme="dark"] {
      --bg:#0B1020; --surface:#121A2E; --text:#E2E8F0; --muted:#94A3B8;
      --primary:#818CF8; --accent:#22D3EE; --border:#1E293B; --glass:rgba(255,255,255,0.06);
    }
    * { box-sizing: border-box; }
    body {
      margin:0; background:var(--bg); color:var(--text);
      transition: background-color .3s ease, color .3s ease;
      font-family:'Inter','PingFang SC','Microsoft YaHei',system-ui,sans-serif;
      -webkit-font-smoothing:antialiased;
    }
    .glass { background:var(--glass); backdrop-filter:blur(20px) saturate(160%); -webkit-backdrop-filter:blur(20px) saturate(160%); border:1px solid var(--border); border-radius:20px; }
    .magnetic { transition: transform .3s cubic-bezier(0.16,1,0.3,1), box-shadow .3s ease; }
    .magnetic:hover { transform: translateY(-3px) scale(1.03); }
    .gradient-text { background:linear-gradient(135deg,var(--primary),var(--accent)); -webkit-background-clip:text; background-clip:text; color:transparent; }
    .btn-primary { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border:none; border-radius:999px; padding:14px 28px; font-weight:600; cursor:pointer; box-shadow:0 20px 50px -20px rgba(79,70,229,.55); }
    .theme-btn { width:42px;height:42px;border-radius:12px;display:grid;place-items:center;cursor:pointer;background:var(--surface);border:1px solid var(--border); }
    a { color:var(--primary); text-decoration:none; }
    .container { max-width:1120px; margin:0 auto; padding:0 20px; }
  </style>
</head>
<body>
  <header class="glass" style="position:sticky;top:0;z-index:50;backdrop-filter:blur(16px);">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;height:68px;">
      <div style="font-weight:700;font-size:18px;" class="gradient-text">◆ xiaozhouAgent</div>
      <nav style="display:flex;gap:26px;align-items:center;color:var(--muted);font-size:14px;">
        <a href="#">项目</a><a href="#">团队</a><a href="#">模型</a><a href="#">验收</a>
        <button class="theme-btn" id="themeToggle" title="切换主题">🌓</button>
      </nav>
    </div>
  </header>

  <main class="container" style="padding:48px 20px 80px;">
    @yield('content')
  </main>

  <footer style="border-top:1px solid var(--border);color:var(--muted);font-size:13px;">
    <div class="container" style="padding:24px 20px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <span>xiaozhouAgent · 自然语言 → 可上线产品</span>
      <span>单机可迁移 · 混合模型 · 专家团队 · 自主验收</span>
    </div>
  </footer>

  <script>
    var order = ['light','dark','system'];
    var labels = {light:'☀️',dark:'🌙',system:'🌓'};
    document.getElementById('themeToggle').addEventListener('click', function () {
      var cur = localStorage.getItem('theme') || 'system';
      var next = order[(order.indexOf(cur)+1)%order.length];
      localStorage.setItem('theme', next);
      var dark = next === 'dark' || (next === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
      this.textContent = labels[next];
    });
    document.getElementById('themeToggle').textContent = labels[localStorage.getItem('theme') || 'system'];
  </script>
  @yield('scripts')
</body>
</html>
