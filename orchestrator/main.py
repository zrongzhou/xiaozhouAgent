"""xiaozhouAgent 编排器入口（方案第十五章：编排器与控制台解耦）。

控制台（Laravel）只负责发任务；Agent Loop 在此 Python Worker 中运行。
端点：
  GET  /health          健康检查
  GET  /metrics         Prometheus 文本指标（observability profile）
  POST /api/build       提交一次“描述+文件+图片”的自主开发任务
  GET  /api/build/{id}  查询任务进度
"""
import os
import uuid
import asyncio
import logging

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

from agent_loop import AgentLoop
from model_router import ModelRouter
from style_loader import StyleLoader
from capability_evolver import CapabilityEvolver

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
logger = logging.getLogger("orchestrator")

app = FastAPI(title="xiaozhouAgent Orchestrator", version="0.1.0")

CONFIG_DIR = os.getenv("CONFIG_DIR", "/config")
DATA_DIR = os.getenv("DATA_DIR", "/data")

# 启动即加载配置资产（缺失不致命，骨架阶段允许）
try:
    style = StyleLoader(CONFIG_DIR)
    router = ModelRouter(CONFIG_DIR)
    evolver = CapabilityEvolver()
    loop = AgentLoop(style, router, evolver, DATA_DIR)
    logger.info("orchestrator ready: style=%s models=%d", style.name, len(router.pool))
except Exception as exc:  # pragma: no cover
    logger.warning("orchestrator init partial: %s", exc)
    loop = None


class BuildRequest(BaseModel):
    description: str
    files: list[str] = []
    images: list[str] = []


@app.get("/health")
def health():
    return {"status": "ok", "loop_ready": loop is not None}


@app.get("/metrics")
def metrics():
    ready = 1 if loop is not None else 0
    return (
        "# HELP xiaozhou_orchestrator_ready 编排器是否就绪\n"
        "# TYPE xiaozhou_orchestrator_ready gauge\n"
        f"xiaozhou_orchestrator_ready {ready}\n"
    )


@app.post("/api/build")
async def build(req: BuildRequest):
    if loop is None:
        raise HTTPException(status_code=503, detail="orchestrator not ready")
    task_id = f"task-{uuid.uuid4().hex[:8]}"
    asyncio.create_task(loop.run(task_id, req.description, req.files, req.images))
    logger.info("queued %s", task_id)
    return {"task_id": task_id, "status": "queued"}


@app.get("/api/build/{task_id}")
def build_status(task_id: str):
    if loop is None:
        raise HTTPException(status_code=503, detail="orchestrator not ready")
    return loop.status(task_id)
