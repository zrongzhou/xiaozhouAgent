#!/usr/bin/env bash
# 自动备份（方案第十七章）：pg_dump → ./data/backups + WAL(PITR 由 postgres 自身保证)
# 建议 crontab： 0 4 * * *  bash /opt/xiaozhou/infra/scripts/backup.sh
set -euo pipefail
cd "$(dirname "$0")/../.."

BACKUP_DIR="./data/backups"
mkdir -p "$BACKUP_DIR"
STAMP="$(date +%Y%m%d-%H%M%S)"
KEEP_DAYS=7

echo "==> 备份 PostgreSQL -> $BACKUP_DIR/db-$STAMP.sql.gz"
docker compose exec -T postgres pg_dump -U "${DB_USERNAME:-xiaozhou}" "${DB_DATABASE:-xiaozhou}" \
  | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz"

# 工件目录一并打包（生成的项目）
echo "==> 打包 ./data/projects -> $BACKUP_DIR/projects-$STAMP.tar.gz"
tar -czf "$BACKUP_DIR/projects-$STAMP.tar.gz" -C ./data projects 2>/dev/null || true

# 清理旧备份
find "$BACKUP_DIR" -mtime +"$KEEP_DAYS" -type f -delete

echo "✓ 备份完成：$BACKUP_DIR"
