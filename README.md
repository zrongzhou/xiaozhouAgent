# xiaozhouAgent

> 自然语言 → 可上线产品的自主开发 Agent。用户给一句话描述（+文件/图片），
> Agent 自主完成前端 + 后端 + 验收；支持混合模型、专家团队、风格引导、单机可迁移。

本仓库是 **P1 工程骨架**（对应设计文档《AI-Agent-快速开发方案》第十九章前的全部章节），
已落地：容器编排、密钥安全、Web 控制台（Laravel 骨架）、编排器（Python Agent Loop）、
可版本化配置资产（`/config`）。后续阶段在此基础上接入真实模型调用、视觉比对验收、团队协同与进化池。

## 架构（单机 Docker Compose）

```
            ┌─────────────────────────────────────────────┐
  浏览器 ──► │  caddy (:80) 反代                            │
            │   ├─ /api/* ─► orchestrator (Python, :8001)   │
            │   └─ /*     ─► console (Laravel, :8000)        │
            └─────────────────────────────────────────────┘
                   │                    │
              postgres ────────────── redis
              (数据库)                (队列/黑板)

可选 profile：
  --profile storage       → minio（对象存储，存生成工件）
  --profile observability  → loki / prometheus / grafana / tempo
```

默认核心链路按 **2 vCPU / 4 GB / 60 GB** 调校（方案第十八/十八点九章）；
加 `--profile observability` 或上 7B 进化池时建议升到 4C8G。

## 目录

```
.
├── docker-compose.yml        # 编排（core 默认启；storage/observability 走 profile）
├── .env.example              # 环境变量样例（复制为 .env 并填密钥，.env 不入库）
├── console/                  # Web 控制台（Laravel 12 骨架 + 高级感落地页）
├── orchestrator/             # 编排器（Python：Agent Loop + 模型路由 + 风格加载 + 进化钩子）
├── config/                   # 可版本化配置资产：style-guide / acceptance / model-profiles / team
├── infra/
│   ├── caddy/Caddyfile       # 反代 + HTTPS（使用你提供的证书，非 ACME）
│   ├── observability/        # prometheus 配置
│   └── scripts/              # migrate.sh（迁移+初始化）/ backup.sh（自动备份）
└── data/                     # 运行时状态（git 忽略；迁移闭包之一）
```

## 快速开始（服务器）

```bash
# 1) 安装 Docker（若未装）
curl -fsSL https://get.docker.com | sh
# 2) 拉取代码
git clone https://github.com/zrongzhou/xiaozhouAgent.git && cd xiaozhouAgent
# 3) 生成并填写密钥（绝不提交）
cp .env.example .env
#   编辑 .env：DB_PASSWORD / DEFAULT_MODEL_API_KEY / MINIO_ROOT_* 必填
# 4) 初始化（生成 APP_KEY + 建表）
bash infra/scripts/migrate.sh
# 5) 启动核心链路
docker compose up -d --build
#   打开 http://<服务器IP> 查看控制台
# 可选：开启存储 / 可观测
docker compose --profile storage up -d
docker compose --profile observability up -d
```

> 注：Caddyfile 默认监听 `:80` 反代；若未配置证书，Caddy 仅提供 HTTP。
> 下方第 6 步为可选的 HTTPS 域名接入（需要你自己的证书）。

## 6)（可选）启用 HTTPS 域名

本仓库已为 `xiaozhou.qtechvending.com` 写好 Caddy 域名配置（`infra/caddy/Caddyfile`），
并使用你提供的证书（**不**走 ACME 自动签发），证书由 `certs/` 目录挂载进容器。

```bash
# 1) 把证书链与私钥放到仓库 certs/（已在 .gitignore，绝不入库）：
#      certs/xiaozhou.qtechvending.com_bundle.pem   # 完整链：叶子 + 中间 CA
#      certs/xiaozhou.qtechvending.com.key          # 私钥
mkdir -p certs
#   （把证书文件复制进来；不要提交到 git）

# 2) 在 DNS 把域名 A 记录指向本机公网 IP
#      xiaozhou.qtechvending.com  →  <服务器公网IP>

# 3) 重新拉起（caddy 会加载证书并监听 443）
docker compose up -d

# 4) 验证
curl -I https://xiaozhou.qtechvending.com
#   → HTTP/2 200 即成功；浏览器访问该域名即走 HTTPS
```

安全要点：私钥只存在于服务器本地 `certs/`（git 忽略），绝不进入镜像或仓库；
迁移时 `certs/` 随 `/data` 一并备份，属于“迁移闭包”之外需单独保全的机密资产。

点控制台首页「✨ 一句话生成站点」会经 Caddy 调 `orchestrator /api/build`，
在 `data/projects/<taskId>/manifest.json` 落一个交付占位，验证“描述→开发→交付”闭环。

## 密钥与安全（方案第十五章）

- 所有密文（API Key / DB 密码 / 私钥）只进 `.env` 或 docker secrets，**绝不进 git**。
- 本仓库只保留 `.env.example`（占位符）与可读的配置资产（`config/`）。
- 私钥用于部署机 SSH，不写入任何文件。
- 生产建议改用 deploy key / Vault；本骨架用 `.env` 注入以满足 P1。

## 分阶段路线

- **P1（本仓库）**：骨架 + 部署闭环 + 控制台 + 编排器 + 配置资产。
- **P2**：真实模型调用（model-router 接 LLM）、浏览器 E2E 验收、视觉比对（CLIP）、专家团队协同 UI。
- **P3**：执行沙箱（gVisor/Firecracker）、成本熔断、进化池（7B 权重进化）、生成项目版本回滚。

> 注：控制台落地页用 Tailwind CDN + 原生 CSS 实现玻璃拟态/磁吸/主题切换，便于骨架阶段零构建预览；
> 生产将改为构建期 Tailwind + 接入 FluxUI 组件库（方案高级 CSS 标准）。
