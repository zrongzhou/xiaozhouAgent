#!/usr/bin/env bash
# =====================================================================
# xiaozhouAgent 本地测试脚本
#
# 用法：bash scripts/test-local.sh
#
# 功能：验证各组件是否正常工作
# =====================================================================

set -euo pipefail

# 配置
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

# 颜色
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

passed=0
failed=0

check() {
    local name="$1"
    local command="$2"
    
    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} $name"
        ((passed++))
    else
        echo -e "${RED}✗${NC} $name"
        ((failed++))
    fi
}

echo ""
echo "=========================================="
echo "xiaozhouAgent 本地测试"
echo "=========================================="
echo ""

# 1. 基础文件检查
echo "【1. 基础文件检查】"
check "docker-compose.yml 存在" "test -f docker-compose.yml"
check ".env.example 存在" "test -f .env.example"
check "console/ 目录存在" "test -d console"
check "orchestrator/ 目录存在" "test -d orchestrator"
check "infra/ 目录存在" "test -d infra"
check "config/ 目录存在" "test -d config"
echo ""

# 2. Python 依赖检查
echo "【2. Python 依赖检查】"
if command -v python3 &> /dev/null; then
    check "Python3 已安装" "python3 --version"
    
    # 检查关键包
    python3 -c "import yaml" 2>/dev/null && check "PyYAML 可用" "true" || check "PyYAML 可用" "false"
else
    check "Python3 已安装" "false"
fi
echo ""

# 3. Docker 检查
echo "【3. Docker 环境检查】"
if command -v docker &> /dev/null; then
    check "Docker 已安装" "docker --version"
    
    if docker info &> /dev/null; then
        check "Docker 正在运行" "true"
    else
        check "Docker 正在运行" "false"
    fi
    
    if command -v docker compose &> /dev/null; then
        check "Docker Compose v2 可用" "true"
    else
        check "Docker Compose v2 可用" "false"
    fi
else
    check "Docker 已安装" "false"
fi
echo ""

# 4. 配置文件检查
echo "【4. 配置文件检查】"
check "model-profiles.yaml 存在" "test -f config/model-profiles.yaml"
check "style-guide.yaml 存在" "test -f config/style-guide.yaml"
check "team.yaml 存在" "test -f config/team.yaml"
check "acceptance.yaml 存在" "test -f config/acceptance.yaml"
echo ""

# 5. Laravel 配置检查
echo "【5. Laravel 配置检查】"
check "composer.json 存在" "test -f console/composer.json"
check "app/Models 目录存在" "test -d console/app/Models"
check "database/migrations 目录存在" "test -d console/database/migrations"
check "routes/web.php 存在" "test -f console/routes/web.php"
echo ""

# 6. 编排器检查
echo "【6. 编排器检查】"
check "main.py 存在" "test -f orchestrator/main.py"
check "agent_loop.py 存在" "test -f orchestrator/agent_loop.py"
check "model_router.py 存在" "test -f orchestrator/model_router.py"
check "requirements.txt 存在" "test -f orchestrator/requirements.txt"
echo ""

# 7. 基础设施检查
echo "【7. 基础设施检查】"
check "Caddyfile 存在" "test -f infra/caddy/Caddyfile"
check "prometheus.yml 存在" "test -f infra/observability/prometheus.yml"
check "backup.sh 存在" "test -f infra/scripts/backup.sh"
check "migrate.sh 存在" "test -f infra/scripts/migrate.sh"
echo ""

# 8. 代码语法检查（可选）
echo "【8. Python 语法检查】"
if command -v python3 &> /dev/null; then
    check "agent_loop.py 语法正确" "python3 -m py_compile orchestrator/agent_loop.py"
    check "model_router.py 语法正确" "python3 -m py_compile orchestrator/model_router.py"
    check "capability_evolver.py 语法正确" "python3 -m py_compile orchestrator/capability_evolver.py"
    check "style_loader.py 语法正确" "python3 -m py_compile orchestrator/style_loader.py"
else
    echo "跳过 Python 语法检查（Python3 未安装）"
fi
echo ""

# 结果汇总
echo "=========================================="
echo "测试结果汇总"
echo "=========================================="
echo -e "通过: ${GREEN}$passed${NC}"
echo -e "失败: ${RED}$failed${NC}"
echo ""

if [ $failed -eq 0 ]; then
    echo -e "${GREEN}所有检查通过！${NC}"
    echo ""
    echo "下一步："
    echo "  1. cp .env.example .env"
    echo "  2. 编辑 .env 配置"
    echo "  3. docker compose up -d"
    echo "  4. bash infra/scripts/migrate.sh"
else
    echo -e "${YELLOW}部分检查失败，请检查上述项目${NC}"
fi
