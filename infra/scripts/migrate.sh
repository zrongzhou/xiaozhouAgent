#!/usr/bin/env bash
# =====================================================================
# xiaozhouAgent 数据库迁移脚本（方案第十七章数据模型落地）
#
# 功能：
#   - 等待数据库就绪
#   - 生成 Laravel APP_KEY
#   - 执行数据库迁移
#   - 初始化 MinIO 桶
#
# 用法：
#   bash infra/scripts/migrate.sh
# =====================================================================

set -euo pipefail

# 配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$PROJECT_ROOT"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# 检查 .env 文件
check_env() {
    if [ ! -f .env ]; then
        log_error "缺少 .env 文件"
        log_info "请先从 .env.example 生成 .env 并填入密钥"
        log_info "示例: cp .env.example .env"
        exit 1
    fi
    
    # 加载环境变量
    export $(grep -v '^#' .env | xargs)
}

# 等待 PostgreSQL 就绪
wait_for_postgres() {
    log_info "等待 PostgreSQL 就绪..."
    
    for i in $(seq 1 30); do
        if docker compose exec -T postgres pg_isready -U "${DB_USERNAME:-xiaozhou}" -d "${DB_DATABASE:-xiaozhou}" >/dev/null 2>&1; then
            log_info "PostgreSQL 已就绪"
            return 0
        fi
        
        if [ $i -eq 30 ]; then
            log_error "PostgreSQL 启动超时"
            exit 1
        fi
        
        sleep 2
    done
}

# 生成 Laravel APP_KEY
generate_key() {
    log_info "检查 Laravel APP_KEY..."
    
    if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
        log_info "生成新的 APP_KEY..."
        docker compose exec -T console php artisan key:generate --force || true
        log_info "APP_KEY 已生成"
    else
        log_info "APP_KEY 已存在"
    fi
}

# 执行数据库迁移
run_migrations() {
    log_info "执行数据库迁移..."
    
    docker compose exec -T console php artisan migrate --force
    
    log_info "数据库迁移完成"
}

# 初始化 MinIO 桶
init_minio() {
    log_info "检查 MinIO 桶..."
    
    if docker compose ps minio | grep -q "Up"; then
        log_info "创建 MinIO 桶: ${MINIO_BUCKET:-projects}"
        
        docker compose exec -T minio sh -c \
            "mc alias set local http://localhost:9000 \
             \${MINIO_ROOT_USER} \${MINIO_ROOT_PASSWORD} && \
             mc mb --ignore-existing local/\${MINIO_BUCKET:-projects}"
        
        log_info "MinIO 桶初始化完成"
    else
        log_warn "MinIO 未运行，跳过桶初始化"
    fi
}

# 初始化示例数据
seed_data() {
    log_info "检查是否需要初始化示例数据..."
    
    # 检查是否有数据
    PROJECT_COUNT=$(docker compose exec -T console php -r "echo \App\Models\Project::count();" 2>/dev/null || echo "0")
    
    if [ "$PROJECT_COUNT" -eq "0" ]; then
        log_info "数据库为空，初始化示例数据..."
        docker compose exec -T console php artisan db:seed || true
    else
        log_info "已有 $PROJECT_COUNT 个项目，跳过初始化"
    fi
}

# 主函数
main() {
    check_env
    wait_for_postgres
    generate_key
    run_migrations
    init_minio
    seed_data
    
    log_info "=========================================="
    log_info "迁移完成！"
    log_info "=========================================="
}

main "$@"
