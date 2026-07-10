#!/usr/bin/env bash
# =====================================================================
# xiaozhouAgent 自动备份脚本（方案第十七章）
# 
# 功能：
#   - PostgreSQL 数据库备份（pg_dump + gzip）
#   - 项目数据打包
#   - 配置文件备份
#   - 自动清理旧备份
#
# 用法：
#   bash infra/scripts/backup.sh [full|incremental]
#
# 建议 crontab：
#   0 4 * * *  bash /opt/xiaozhou/infra/scripts/backup.sh full
#   0 12 * * * bash /opt/xiaozhou/infra/scripts/backup.sh incremental
# =====================================================================

set -euo pipefail

# 配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BACKUP_DIR="$PROJECT_ROOT/data/backups"
LOG_DIR="$PROJECT_ROOT/logs"

# 环境变量
BACKUP_TYPE="${1:-full}"
KEEP_FULL_DAYS=7
KEEP_INC_DAYS=3

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# 检查是否在 Docker 环境中
check_docker() {
    if ! command -v docker &> /dev/null; then
        log_error "Docker 未安装"
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        log_error "Docker 未运行"
        exit 1
    fi
}

# 创建备份目录
setup_dirs() {
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$LOG_DIR"
}

# 备份 PostgreSQL
backup_postgres() {
    log_info "备份 PostgreSQL..."
    
    cd "$PROJECT_ROOT"
    
    # 获取数据库配置
    source "$PROJECT_ROOT/.env" 2>/dev/null || true
    
    DB_USER="${DB_USERNAME:-xiaozhou}"
    DB_NAME="${DB_DATABASE:-xiaozhou}"
    DB_PASSWORD="${DB_PASSWORD:-}"
    
    TIMESTAMP=$(date +%Y%m%d-%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/db-${TIMESTAMP}.sql.gz"
    
    # 执行备份
    docker compose exec -T \
        -e PGPASSWORD="$DB_PASSWORD" \
        postgres \
        pg_dump -U "$DB_USER" -d "$DB_NAME" \
        | gzip > "$BACKUP_FILE"
    
    log_info "PostgreSQL 备份完成: $BACKUP_FILE"
    
    # 记录大小
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_info "备份大小: $SIZE"
}

# 备份项目数据
backup_projects() {
    log_info "备份项目数据..."
    
    cd "$PROJECT_ROOT"
    
    TIMESTAMP=$(date +%Y%m%d-%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/projects-${TIMESTAMP}.tar.gz"
    
    # 打包项目目录（排除大文件）
    if [ -d "$PROJECT_ROOT/data/projects" ]; then
        tar -czf "$BACKUP_FILE" \
            --exclude='*.log' \
            --exclude='node_modules' \
            --exclude='__pycache__' \
            -C "$PROJECT_ROOT/data" projects 2>/dev/null || true
    fi
    
    log_info "项目数据备份完成: $BACKUP_FILE"
}

# 备份配置文件
backup_config() {
    log_info "备份配置文件..."
    
    TIMESTAMP=$(date +%Y%m%d-%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/config-${TIMESTAMP}.tar.gz"
    
    # 打包配置目录
    if [ -d "$PROJECT_ROOT/config" ]; then
        tar -czf "$BACKUP_FILE" \
            -C "$PROJECT_ROOT" config 2>/dev/null || true
    fi
    
    log_info "配置备份完成: $BACKUP_FILE"
}

# 清理旧备份
cleanup_old_backups() {
    log_info "清理旧备份..."
    
    # 清理超过保留期限的备份
    find "$BACKUP_DIR" -name "db-*.sql.gz" -mtime +"$KEEP_FULL_DAYS" -delete 2>/dev/null || true
    find "$BACKUP_DIR" -name "projects-*.tar.gz" -mtime +"$KEEP_FULL_DAYS" -delete 2>/dev/null || true
    find "$BACKUP_DIR" -name "config-*.tar.gz" -mtime +"$KEEP_INC_DAYS" -delete 2>/dev/null || true
    
    log_info "清理完成"
}

# 主函数
main() {
    check_docker
    setup_dirs
    
    log_info "开始备份 (类型: $BACKUP_TYPE)"
    
    case "$BACKUP_TYPE" in
        full)
            backup_postgres
            backup_projects
            backup_config
            ;;
        incremental)
            backup_postgres
            ;;
        *)
            log_error "未知备份类型: $BACKUP_TYPE"
            log_info "用法: bash $0 [full|incremental]"
            exit 1
            ;;
    esac
    
    cleanup_old_backups
    
    log_info "备份完成！"
}

main "$@"
