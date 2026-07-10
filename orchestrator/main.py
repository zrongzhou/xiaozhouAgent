"""xiaozhouAgent 编排器入口（方案第十五章：编排器与控制台解耦）。

功能：
- FastAPI REST API
- 健康检查与指标
- 任务提交与查询
- Redis 队列监听
"""
import os
import uuid
import asyncio
import logging
from contextlib import asynccontextmanager

from fastapi import FastAPI, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from agent_loop import AgentLoop
from model_router import ModelRouter
from style_loader import StyleLoader
from capability_evolver import CapabilityEvolver

# 日志配置
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(name)s %(levelname)s %(message)s",
)
logger = logging.getLogger("orchestrator")

# 配置
CONFIG_DIR = os.getenv("CONFIG_DIR", "/config")
DATA_DIR = os.getenv("DATA_DIR", "/data")
REDIS_URL = os.getenv("REDIS_URL", "redis://redis:6379")

# 全局实例
agent_loop: AgentLoop = None
model_router: ModelRouter = None
style_loader: StyleLoader = None
capability_evolver: CapabilityEvolver = None
redis_client = None


@asynccontextmanager
async def lifespan(app: FastAPI):
    """应用生命周期管理"""
    global agent_loop, model_router, style_loader, capability_evolver, redis_client
    
    logger.info("Initializing orchestrator...")
    
    try:
        # 初始化组件
        style_loader = StyleLoader(CONFIG_DIR)
        model_router = ModelRouter(CONFIG_DIR)
        capability_evolver = CapabilityEvolver(DATA_DIR)
        
        # 尝试连接 Redis
        try:
            import redis
            redis_client = redis.from_url(REDIS_URL)
            redis_client.ping()
            logger.info("Redis connected")
        except Exception as e:
            logger.warning(f"Redis not available: {e}")
            redis_client = None
            
        # 初始化 Agent Loop
        agent_loop = AgentLoop(
            style=style_loader,
            router=model_router,
            evolver=capability_evolver,
            data_dir=DATA_DIR,
            redis_client=redis_client,
        )
        agent_loop.start()
        
        logger.info("Orchestrator ready")
        
    except Exception as e:
        logger.error(f"Failed to initialize orchestrator: {e}")
        raise
        
    yield
    
    # 清理
    if agent_loop:
        agent_loop.stop()
    logger.info("Orchestrator shutdown")


# FastAPI 应用
app = FastAPI(
    title="xiaozhouAgent Orchestrator",
    description="AI Agent 编排器 - 自然语言驱动的智能开发平台",
    version="0.1.0",
    lifespan=lifespan,
)

# CORS 配置
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ============ 数据模型 ============

class BuildRequest(BaseModel):
    """构建请求"""
    description: str = Field(..., description="项目描述", min_length=10)
    files: List[str] = Field(default=[], description="参考文件路径列表")
    images: List[str] = Field(default=[], description="参考图片路径列表")
    style_preset: Optional[str] = Field(default=None, description="风格预设")
    

class TaskRequest(BaseModel):
    """任务请求"""
    project_id: str = Field(..., description="项目 ID")
    task_type: str = Field(..., description="任务类型", pattern="^(prd|design|code|test|deploy)$")
    input: str = Field(..., description="任务输入")
    model_profile: Optional[str] = Field(default=None, description="模型画像")
    

class ProjectStartRequest(BaseModel):
    """项目启动请求"""
    project_id: str = Field(..., description="项目 ID")
    slug: str = Field(..., description="项目标识")
    prompt: str = Field(..., description="项目描述", min_length=10)
    style_preset: Optional[str] = Field(default=None, description="风格预设")


# ============ API 路由 ============

@app.get("/health")
async def health():
    """健康检查"""
    return {
        "status": "ok",
        "service": "xiaozhouAgent Orchestrator",
        "version": "0.1.0",
        "components": {
            "agent_loop": agent_loop is not None,
            "model_router": model_router is not None,
            "style_loader": style_loader is not None,
            "redis": redis_client is not None if redis_client else False,
        },
    }


@app.get("/metrics")
async def metrics():
    """Prometheus 指标"""
    ready = 1 if agent_loop is not None else 0
    tasks_count = len(agent_loop.get_all_tasks()) if agent_loop else 0
    
    return (
        "# HELP xiaozhou_orchestrator_ready 编排器是否就绪\n"
        "# TYPE xiaozhou_orchestrator_ready gauge\n"
        f"xiaozhou_orchestrator_ready {ready}\n"
        "\n"
        "# HELP xiaozhou_tasks_total 当前任务数\n"
        "# TYPE xiaozhou_tasks_total gauge\n"
        f"xiaozhou_tasks_total {tasks_count}\n"
    )


@app.post("/api/build")
async def build(req: BuildRequest, background_tasks: BackgroundTasks):
    """提交自主开发任务"""
    if agent_loop is None:
        raise HTTPException(status_code=503, detail="Orchestrator not ready")
    
    task_id = f"task-{uuid.uuid4().hex[:8]}"
    
    # 异步执行
    background_tasks.add_task(
        agent_loop.run_task,
        task_id=task_id,
        project_id=f"manual-{task_id}",
        task_type="code",
        input_data=req.description,
        model_profile=None,
    )
    
    logger.info(f"Queued build task: {task_id}")
    
    return {
        "task_id": task_id,
        "status": "queued",
        "message": "任务已加入队列",
    }


@app.post("/api/project/start")
async def start_project(req: ProjectStartRequest, background_tasks: BackgroundTasks):
    """启动项目"""
    if agent_loop is None:
        raise HTTPException(status_code=503, detail="Orchestrator not ready")
    
    background_tasks.add_task(
        agent_loop.run_project,
        project_id=req.project_id,
        slug=req.slug,
        prompt=req.prompt,
        style_preset=req.style_preset,
    )
    
    logger.info(f"Starting project: {req.project_id}")
    
    return {
        "project_id": req.project_id,
        "status": "starting",
        "message": "项目启动中",
    }


@app.get("/api/build/{task_id}")
async def build_status(task_id: str):
    """查询任务状态"""
    if agent_loop is None:
        raise HTTPException(status_code=503, detail="Orchestrator not ready")
    
    status = agent_loop.status(task_id)
    if status.get("state") == "unknown":
        raise HTTPException(status_code=404, detail="Task not found")
    
    return status


@app.get("/api/tasks")
async def list_tasks():
    """获取所有任务状态"""
    if agent_loop is None:
        raise HTTPException(status_code=503, detail="Orchestrator not ready")
    
    return agent_loop.get_all_tasks()


@app.get("/api/models/stats")
async def model_stats():
    """获取模型使用统计"""
    if model_router is None:
        raise HTTPException(status_code=503, detail="Model router not ready")
    
    return model_router.get_stats()


@app.get("/api/evolution/suggest")
async def evolution_suggest():
    """获取进化建议"""
    if capability_evolver is None:
        raise HTTPException(status_code=503, detail="Evolver not ready")
    
    return capability_evolver.suggest()


@app.post("/api/evolution/ingest")
async def evolution_ingest(report: dict):
    """摄入运行报告"""
    if capability_evolver is None:
        raise HTTPException(status_code=503, detail="Evolver not ready")
    
    capability_evolver.ingest(report)
    
    return {"success": True, "message": "Report ingested"}


@app.get("/api/styles/presets")
async def list_styles():
    """列出所有可用风格预设"""
    if style_loader is None:
        raise HTTPException(status_code=503, detail="Style loader not ready")
    
    return style_loader.list_presets()


@app.get("/api/styles/context/{preset_slug}")
async def get_style_context(preset_slug: str):
    """获取风格上下文"""
    if style_loader is None:
        raise HTTPException(status_code=503, detail="Style loader not ready")
    
    context = style_loader.build_prompt_context(preset_slug)
    return {"preset": preset_slug, "context": context}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=False)
