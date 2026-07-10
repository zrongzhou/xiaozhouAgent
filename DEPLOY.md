# xiaozhouAgent 部署指南

## 系统要求

### 最低配置（POC）
- CPU: 2 核
- 内存: 4GB
- 磁盘: 60GB SSD
- 网络: 公网 IP

### 推荐配置（生产）
- CPU: 4 核
- 内存: 8-16GB
- 磁盘: 200GB NVMe SSD
- 网络: 公网 IP + 域名

## 软件要求

- Ubuntu 22.04 / Debian 12
- Docker Engine 24+
- Docker Compose v2
- Git

## 快速部署

### 1. 克隆仓库

```bash
# 克隆到服务器
git clone https://github.com/zrongzhou/xiaozhouAgent.git /opt/xiaozhou

# 切换到项目目录
cd /opt/xiaozhou
```

### 2. 配置环境变量

```bash
# 从示例文件创建 .env
cp .env.example .env

# 编辑 .env 文件
nano .env
```

需要配置的必要项：

```env
# 基础配置
TZ=Asia/Shanghai
APP_URL_HOST=43.152.232.197

# 数据库密码（随机生成）
DB_PASSWORD=$(openssl rand -base64 32)

# MinIO（如需对象存储）
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=$(openssl rand -base64 32)
```

### 3. 启动基础服务

```bash
# 启动核心服务
docker compose up -d

# 查看服务状态
docker compose ps
```

### 4. 执行数据库迁移

```bash
# 执行迁移脚本
bash infra/scripts/migrate.sh
```

### 5. 启动可观测性服务（可选）

```bash
# 启动可观测栈
docker compose --profile observability up -d

# 启动对象存储
docker compose --profile storage up -d
```

### 6. 配置域名和 HTTPS（可选）

编辑 `infra/caddy/Caddyfile`，设置域名：

```caddyfile
your-domain.com {
    encode gzip zstd
    
    handle /api/* {
        reverse_proxy orchestrator:8001
    }
    
    handle /* {
        reverse_proxy console:8000
    }
    
    tls {
        protocols TLSv1.2 TLSv1.3
    }
}
```

重启 Caddy：

```bash
docker compose restart caddy
```

## 服务说明

### 核心服务

| 服务 | 端口 | 说明 |
|------|------|------|
| caddy | 80/443 | 反向代理 |
| console | 8000 | Laravel Web 控制台 |
| orchestrator | 8001 | Python 编排器 |
| postgres | 5432 | 数据库 |
| redis | 6379 | 缓存和队列 |

### 可选服务（--profile observability）

| 服务 | 端口 | 说明 |
|------|------|------|
| loki | - | 日志聚合 |
| prometheus | 9090 | 指标采集 |
| grafana | 3000 | 监控看板 |
| tempo | - | 分布式追踪 |

### 可选服务（--profile storage）

| 服务 | 端口 | 说明 |
|------|------|------|
| minio | 9000 | 对象存储 API |
| minio-console | 9001 | MinIO 控制台 |

## 常用命令

### Docker Compose

```bash
# 启动所有服务
docker compose up -d

# 启动核心服务 + 可观测栈
docker compose --profile observability up -d

# 停止所有服务
docker compose down

# 查看服务状态
docker compose ps

# 查看日志
docker compose logs -f

# 重启单个服务
docker compose restart console
```

### 数据库

```bash
# 进入数据库容器
docker compose exec -it postgres psql -U xiaozhou -d xiaozhou

# 备份数据库
bash infra/scripts/backup.sh full

# 恢复数据库
gunzip < backup.sql.gz | docker compose exec -T postgres psql -U xiaozhou -d xiaozhou
```

### 迁移

```bash
# 执行迁移
bash infra/scripts/migrate.sh

# 回滚迁移
docker compose exec -T console php artisan migrate:rollback

# 重置数据库
docker compose exec -T console php artisan migrate:reset
```

## 目录结构

```
xiaozhouAgent/
├── console/           # Laravel Web 控制台
├── orchestrator/      # Python 编排器
├── infra/             # 基础设施配置
│   ├── caddy/         # Caddy 配置
│   ├── observability/ # 可观测栈配置
│   └── scripts/       # 运维脚本
├── config/           # 系统配置（不入库）
├── data/              # 数据持久化
│   ├── postgres/      # 数据库数据
│   ├── redis/         # Redis 数据
│   ├── projects/      # 生成的项目
│   └── backups/       # 备份文件
└── certs/             # SSL 证书（不入库）
```

## 安全建议

1. **定期更新密码**
   ```bash
   # 生成新密码
   DB_PASSWORD=$(openssl rand -base64 32)
   
   # 更新 .env
   echo "DB_PASSWORD=$DB_PASSWORD" >> .env
   
   # 重启数据库
   docker compose restart postgres
   ```

2. **启用防火墙**
   ```bash
   # 只开放必要端口
   ufw allow 22/tcp
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw enable
   ```

3. **定期备份**
   ```bash
   # 添加到 crontab
   0 4 * * * /opt/xiaozhou/infra/scripts/backup.sh full
   ```

## 故障排查

### 服务无法启动

```bash
# 查看具体服务日志
docker compose logs console
docker compose logs postgres
docker compose logs orchestrator
```

### 数据库连接失败

```bash
# 检查数据库状态
docker compose exec postgres pg_isready -U xiaozhou -d xiaozhou

# 重启数据库
docker compose restart postgres

# 等待数据库就绪
sleep 10
docker compose exec postgres pg_isready -U xiaozhou -d xiaozhou
```

### Laravel 迁移失败

```bash
# 重新生成 APP_KEY
docker compose exec -T console php artisan key:generate --force

# 重新执行迁移
docker compose exec -T console php artisan migrate:fresh
```

## 性能优化

### 内存优化

编辑 `docker-compose.yml` 调整容器内存限制：

```yaml
services:
  console:
    mem_limit: 1024m  # 根据实际调整
  postgres:
    mem_limit: 1024m
  redis:
    mem_limit: 512m
```

### 数据库优化

```sql
-- 创建常用查询索引
CREATE INDEX idx_projects_status_created ON projects(status, created_at DESC);
CREATE INDEX idx_tasks_project_status ON tasks(project_id, status);
```

## 监控

### Grafana 访问

访问 `https://your-domain.com/grafana` 或 `http://localhost:3000`

默认用户名：`admin`
默认密码：见 `.env` 中 `GRAFANA_PASSWORD`

### 常用监控面板

- **系统资源**：CPU、内存、磁盘使用率
- **应用指标**：请求数、响应时间、错误率
- **业务指标**：项目数、任务数、模型调用成本

## 升级

```bash
# 拉取最新代码
git pull origin main

# 重新构建镜像
docker compose build

# 更新服务
docker compose up -d

# 执行数据库迁移（如有）
bash infra/scripts/migrate.sh
```

## 卸载

```bash
# 停止所有服务
docker compose down -v

# 删除数据目录（谨慎操作）
rm -rf data/

# 删除配置文件
rm -rf config/
```

## 获取帮助

如有问题，请检查：
1. Docker 和 Docker Compose 版本
2. 磁盘空间是否充足
3. 网络连接是否正常
4. 环境变量配置是否正确
