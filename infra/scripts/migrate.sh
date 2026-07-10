#!/usr/bin/env bash
# 数据库迁移 + 初始化（方案第十七章数据模型落地）
# 在服务器上于仓库根目录执行： bash infra/scripts/migrate.sh
set -euo pipefail
cd "$(dirname "$0")/../.."

if [ ! -f .env ]; then
  echo "✗ 缺少 .env，请先由 .env.example 生成并填入密钥"
  exit 1
fi

echo "==> 等待 postgres 就绪"
for i in $(seq 1 30); do
  if docker compose exec -T postgres pg_isready -U "${DB_USERNAME:-xiaozhou}" -d "${DB_DATABASE:-xiaozhou}" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo "==> 生成 Laravel APP_KEY（如缺失）"
if ! grep -q "^APP_KEY=base64" .env; then
  docker compose exec -T console php artisan key:generate --force || true
fi

echo "==> 执行迁移"
docker compose exec -T console php artisan migrate --force

echo "==> 若启用 storage，创建 MinIO 桶"
if docker compose ps minio | grep -q "Up"; then
  docker compose exec -T minio sh -c \
    "mc alias set local http://localhost:9000 \${MINIO_ROOT_USER} \${MINIO_ROOT_PASSWORD} && mc mb -p local/\${MINIO_BUCKET}"
fi

echo "✓ 迁移完成"
