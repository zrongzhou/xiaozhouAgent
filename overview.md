# xiaozhouAgent 项目开发概览

## 项目概述

**xiaozhouAgent** 是一个 AI 驱动的智能开发平台，用户只需提供自然语言描述，AI Agent 就能自动完成从需求分析到代码生成、测试、部署的全流程开发工作。

## 技术栈

| 层级 | 技术 |
|------|------|
| 前端 | Laravel + Livewire + Tailwind CSS |
| 后端 | Python FastAPI |
| 数据库 | PostgreSQL 16 |
| 缓存/队列 | Redis 7 |
| 反向代理 | Caddy 2 |
| 对象存储 | MinIO (可选) |
| 可观测性 | Loki + Prometheus + Grafana + Tempo (可选) |

## 已完成功能

### 1. Laravel Web 控制台
- ✅ 项目管理（CRUD）
- ✅ 任务管理（按类型/状态）
- ✅ 模型画像管理
- ✅ 仪表盘统计
- ✅ API 接口
- ✅ 数据库迁移

**核心模型：**
- Project（项目）
- Task（任务）
- Artifact（产物）
- ModelProfile（模型画像）
- ModelRecord（模型调用记录）
- AcceptanceReport（验收报告）
- Team（团队）
- User（用户）

### 2. Python 编排器
- ✅ Agent Loop 状态机
- ✅ 任务调度与队列监听
- ✅ 模型路由器
- ✅ 风格加载器
- ✅ 能力进化引擎
- ✅ FastAPI REST API

**核心功能：**
- Plan → Act → Observe → Iterate 循环
- 多角色协作（产品/设计/开发/测试/运维）
- 模型自动选择与回退
- 运行统计与成本追踪

### 3. 基础设施
- ✅ Docker Compose 编排
- ✅ Caddy 反向代理配置
- ✅ Prometheus 指标采集配置
- ✅ 自动备份脚本
- ✅ 数据库迁移脚本
- ✅ 一键部署脚本

### 4. 配置文件
- ✅ model-profiles.yaml（模型画像与路由）
- ✅ style-guide.yaml（风格引导）
- ✅ team.yaml（专家团队定义）
- ✅ acceptance.yaml（验收基准）

### 5. 文档
- ✅ DEPLOY.md（完整部署指南）
- ✅ scripts/deploy.sh（部署脚本）
- ✅ scripts/test-local.sh（本地测试）

## 项目结构

```
xiaozhouAgent/
├── console/                 # Laravel Web 控制台
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/   # 控制器
│   │   │   ├── Resources/     # API 资源
│   │   │   └── Controllers/Api/  # API 控制器
│   │   └── Models/          # Eloquent 模型
│   ├── database/
│   │   ├── migrations/      # 数据库迁移
│   │   └── seeders/         # 数据填充
│   ├── routes/              # 路由定义
│   └── resources/views/     # Blade 视图
├── orchestrator/            # Python 编排器
│   ├── agent_loop.py        # Agent 循环
│   ├── model_router.py      # 模型路由器
│   ├── style_loader.py      # 风格加载器
│   ├── capability_evolver.py # 能力进化
│   ├── main.py              # FastAPI 入口
│   └── requirements.txt     # Python 依赖
├── infra/                   # 基础设施
│   ├── caddy/               # Caddy 配置
│   ├── observability/      # 可观测栈配置
│   └── scripts/            # 运维脚本
├── config/                 # 系统配置
│   ├── model-profiles.yaml
│   ├── style-guide.yaml
│   ├── team.yaml
│   └── acceptance.yaml
├── scripts/                # 工具脚本
│   ├── deploy.sh           # 部署脚本
│   └── test-local.sh       # 本地测试
├── docker-compose.yml      # Docker 编排
├── .env.example            # 环境变量示例
└── DEPLOY.md               # 部署指南
```

## 快速开始

### 1. 克隆仓库

```bash
git clone https://github.com/zrongzhou/xiaozhouAgent.git
cd xiaozhouAgent
```

### 2. 配置环境

```bash
cp .env.example .env
# 编辑 .env 填入必要配置
```

### 3. 启动服务

```bash
# 启动核心服务
docker compose up -d

# 启动可观测栈（可选）
docker compose --profile observability up -d
```

### 4. 执行迁移

```bash
bash infra/scripts/migrate.sh
```

### 5. 访问应用

- 控制台: http://localhost
- API: http://localhost/api/v1/health

## 部署信息

- **服务器 IP**: 43.152.232.197
- **用户**: ubuntu
- **项目路径**: /opt/xiaozhou

## 下一步

1. **集成真实 LLM API**
   - 配置 API Key
   - 实现模型调用逻辑

2. **完善前端 UI**
   - 添加 Livewire 组件
   - 实现实时更新

3. **实现验收流程**
   - 视觉比对
   - 性能测试
   - E2E 测试

4. **优化可观测性**
   - 完善 Grafana 看板
   - 配置告警规则
