# xiaozhouAgent 部署检查清单

## 服务器信息

- **IP**: 43.152.232.197
- **用户**: ubuntu
- **项目路径**: /opt/xiaozhou

## 部署前检查

### 1. 服务器环境 [ ]
- [ ] Ubuntu 22.04 已安装
- [ ] Docker Engine 24+ 已安装
- [ ] Docker Compose v2 已安装
- [ ] Git 已安装
- [ ] 磁盘空间 ≥ 60GB
- [ ] 内存 ≥ 4GB

### 2. 克隆代码 [ ]
```bash
# SSH 方式
git clone git@github.com:zrongzhou/xiaozhouAgent.git /opt/xiaozhou

# 或 HTTPS 方式
git clone https://github.com/zrongzhou/xiaozhouAgent.git /opt/xiaozhou
```
- [ ] 代码已克隆
- [ ] 权限已设置 (chmod +x infra/scripts/*.sh)

### 3. 配置环境变量 [ ]
```bash
cd /opt/xiaozhou
cp .env.example .env
```

需要配置的必要项：
- [ ] APP_URL_HOST=43.152.232.197
- [ ] DB_PASSWORD=<随机生成>
- [ ] DEFAULT_MODEL_API_KEY=<智谱API Key>
- [ ] MINIO_ROOT_USER=minioadmin
- [ ] MINIO_ROOT_PASSWORD=<随机生成>

### 4. 数据库密码生成 [ ]
```bash
# 生成随机密码
openssl rand -base64 32
```

## 部署步骤

### 1. 首次部署 [ ]
```bash
cd /opt/xiaozhou
bash scripts/deploy.sh setup
```
- [ ] 镜像构建成功
- [ ] 服务启动成功
- [ ] 数据库迁移成功

### 2. 验证服务 [ ]
```bash
# 检查服务状态
docker compose ps

# 健康检查
curl http://localhost:8001/health
curl http://localhost:8000/health
```
- [ ] 所有容器运行正常
- [ ] PostgreSQL 就绪
- [ ] Redis 就绪
- [ ] Console 响应正常
- [ ] Orchestrator 响应正常

### 3. 配置网络 [ ]

#### 无域名（使用 IP）
- [ ] 防火墙开放 80 端口
- [ ] 防火墙开放 443 端口（如使用 HTTPS）

#### 有域名
- [ ] 配置域名解析
- [ ] 修改 infra/caddy/Caddyfile
- [ ] 重启 Caddy

### 4. 可选：启用可观测栈 [ ]
```bash
docker compose --profile observability up -d
```
- [ ] Loki 运行正常
- [ ] Prometheus 运行正常
- [ ] Grafana 可访问 (端口 3000)

### 5. 可选：启用对象存储 [ ]
```bash
docker compose --profile storage up -d
```
- [ ] MinIO 运行正常
- [ ] MinIO Console 可访问 (端口 9001)
- [ ] 桶已创建

## 部署后验证

### 1. 功能测试 [ ]
- [ ] 访问 http://43.152.232.197
- [ ] 能看到仪表盘
- [ ] 能创建新项目
- [ ] 能查看项目详情
- [ ] 能管理模型画像

### 2. API 测试 [ ]
```bash
# 健康检查
curl http://localhost/api/v1/health

# 获取项目列表
curl http://localhost/api/v1/projects

# 获取模型统计
curl http://localhost/api/v1/models/stats
```

### 3. 数据持久化测试 [ ]
```bash
# 检查数据目录
df -h /opt/xiaozhou/data

# 测试备份
bash infra/scripts/backup.sh full
```

## 定期维护

### 每日 [ ]
- [ ] 检查服务状态
- [ ] 检查磁盘空间

### 每周 [ ]
- [ ] 执行完整备份
  ```bash
  bash infra/scripts/backup.sh full
  ```
- [ ] 检查日志大小

### 每月 [ ]
- [ ] 更新依赖
- [ ] 清理旧日志
- [ ] 验证备份可恢复

## 故障恢复

### 服务崩溃 [ ]
```bash
# 重启服务
docker compose restart

# 查看日志
docker compose logs -f
```

### 数据库损坏 [ ]
```bash
# 从备份恢复
gunzip < data/backups/db-*.sql.gz | docker compose exec -T postgres psql -U xiaozhou -d xiaozhou
```

### 磁盘空间不足 [ ]
```bash
# 清理旧备份
find data/backups -mtime +7 -delete

# 清理 Docker 镜像
docker system prune -a
```

## 联系方式

- **GitHub**: https://github.com/zrongzhou/xiaozhouAgent
- **Issues**: https://github.com/zrongzhou/xiaozhouAgent/issues

## 检查清单完成情况

| 类别 | 完成 | 备注 |
|------|------|------|
| 服务器环境 | [ ] | |
| 代码部署 | [ ] | |
| 环境配置 | [ ] | |
| 服务启动 | [ ] | |
| 功能验证 | [ ] | |
| 备份测试 | [ ] | |
| 定期维护计划 | [ ] | |

**部署人签字**: _______________ **日期**: _______________
