#!/usr/bin/env bash
# =====================================================================
# xiaozhouAgent 一键部署脚本
#
# 用法：
#   bash scripts/deploy.sh [setup|start|stop|restart|status|logs|migrate|backup]
#
# 示例：
#   bash scripts/deploy.sh setup    # 首次部署
#   bash scripts/deploy.sh start    # 启动服务
#   bash scripts/deploy.sh logs     # 查看日志
# =====================================================================

set -euo pipefail

# 配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info()  { echo -e "${BLUE}[INFO]${NC} $1"; }
log_ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# 检查 Docker
check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker 未安装"
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        log_error "Docker 未运行"
        exit 1
    fi
    
    if ! command -v docker compose &> /dev/null; then
        log_error "Docker Compose 未安装"
        exit 1
    fi
    
    log_ok "Docker 环境正常"
}

# 检查 .env 文件
check_env() {
    if [ ! -f .env ]; then
        log_error "缺少 .env 文件"
        log_info "请先执行: cp .env.example .env"
        log_info "然后编辑 .env 填入必要配置"
        exit 1
    fi
    
    # 加载环境变量
    export $(grep -v '^#' .env | grep -v '^$' | xargs)
}

# 首次部署
setup() {
    log_info "开始首次部署..."
    
    # 检查 Docker
    check_docker
    
    # 检查 .env
    if [ ! -f .env ]; then
        log_info "创建 .env 文件..."
        cp .env.example .env
        
        # 生成随机密码
        DB_PASSWORD=$(openssl rand -base64 32)
        MINIO_ROOT_PASSWORD=$(openssl rand -base64 32)
        
        # 更新 .env
        sed -i.bak "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
        sed -i.bak "s/^MINIO_ROOT_PASSWORD=.*/MINIO_ROOT_PASSWORD=$MINIO_ROOT_PASSWORD/" .env
        rm -f .env.bak
        
        log_ok "已生成随机密码"
        log_info "请编辑 .env 配置以下必要项："
        log_info "  - APP_URL_HOST"
        log_info "  - DEFAULT_MODEL_API_KEY"
    fi
    
    # 构建并启动
    log_info "构建镜像..."
    docker compose build
    
    log_info "启动服务..."
    docker compose up -d
    
    # 等待数据库就绪
    log_info "等待数据库就绪..."
    sleep 15
    
    # 执行迁移
    log_info "执行数据库迁移..."
    bash infra/scripts/migrate.sh
    
    log_ok "部署完成！"
    log_info ""
    log_info "访问地址:"
    log_info "  控制台: http://${APP_URL_HOST:-localhost}"
    log_info "  API:    http://${APP_URL_HOST:-localhost}/api/v1/health"
    log_info ""
    log_info "查看日志: bash scripts/deploy.sh logs"
}

# 启动服务
start() {
    check_docker
    check_env
    
    log_info "启动服务..."
    docker compose up -d
    
    log_ok "服务已启动"
    docker compose ps
}

# 停止服务
stop() {
    check_docker
    
    log_info "停止服务..."
    docker compose stop
    
    log_ok "服务已停止"
}

# 重启服务
restart() {
    check_docker
    check_env
    
    log_info "重启服务..."
    docker compose down
    docker compose up -d
    
    log_ok "服务已重启"
}

# 查看状态
status() {
    check_docker
    
    echo ""
    log_info "服务状态:"
    docker compose ps
    echo ""
    
    # 健康检查
    log_info "健康检查:"
    if curl -sf http://localhost:8001/health > /dev/null 2>&1; then
        log_ok "Orchestrator: OK"
    else
        log_warn "Orchestrator: 不可达"
    fi
    
    if curl -sf http://localhost:8000/health > /dev/null 2>&1; then
        log_ok "Console: OK"
    else
        log_warn "Console: 不可达"
    fi
}

# 查看日志
logs() {
    check_docker
    
    log_info "查看实时日志 (Ctrl+C 退出)..."
    docker compose logs -f
}

# 执行迁移
migrate() {
    check_docker
    check_env
    
    log_info "执行数据库迁移..."
    bash infra/scripts/migrate.sh
    
    log_ok "迁移完成"
}

# 备份
backup() {
    check_docker
    check_env
    
    log_info "执行备份..."
    bash infra/scripts/backup.sh full
    
    log_ok "备份完成"
}

# 主函数
main() {
    local command="${1:-help}"
    shift 2>/dev/null || true
    
    case "$command" in
        setup)
            setup
            ;;
        start)
            start
            ;;
        stop)
            stop
            ;;
        restart)
            restart
            ;;
        status)
            status
            ;;
        logs)
            logs
            ;;
        migrate)
            migrate
            ;;
        backup)
            backup
            ;;
        help|--help|-h|"")
            echo ""
            echo "xiaozhouAgent 部署脚本"
            echo ""
            echo "用法: bash scripts/deploy.sh <command>"
            echo ""
            echo "命令:"
            echo "  setup    - 首次部署（构建、启动、迁移）"
            echo "  start    - 启动服务"
            echo "  stop     - 停止服务"
            echo "  restart  - 重启服务"
            echo "  status   - 查看服务状态"
            echo "  logs     - 查看实时日志"
            echo "  migrate  - 执行数据库迁移"
            echo "  backup   - 执行完整备份"
            echo "  help     - 显示帮助"
            echo ""
            ;;
        *)
            log_error "未知命令: $command"
            echo "使用 'bash scripts/deploy.sh help' 查看帮助"
            exit 1
            ;;
    esac
}

main "$@"
