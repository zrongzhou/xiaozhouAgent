"""Agent Loop 骨架（方案第一/十三章：Plan → Act → Observe → Iterate）。

骨架阶段不调用真实 LLM，仅串起流程并把交付占位写入 /data/projects，
证明“描述 → 自主开发 → 交付”闭环可跑；真实实现将在此挂载模型调用与验收。
"""
import os
import json
import asyncio
import logging
from datetime import datetime, timezone

logger = logging.getLogger("agent_loop")


class AgentLoop:
    def __init__(self, style, router, evolver, data_dir):
        self.style = style
        self.router = router
        self.evolver = evolver
        self.data_dir = data_dir
        self._status: dict = {}

    def status(self, task_id: str) -> dict:
        return self._status.get(task_id, {"task_id": task_id, "state": "unknown"})

    async def run(self, task_id: str, goal: str, files, images) -> None:
        log: list = []
        self._status[task_id] = {"state": "planning", "steps": log}

        # 1) Plan
        style_name = self.style.name if self.style and self.style.name else "none"
        log.append(self._step("plan", f"理解需求：{goal[:80]!r}；加载风格={style_name}；素材={len(files)}文件/{len(images)}图"))
        await asyncio.sleep(0.05)

        # 2) Act（经模型路由器选模型；骨架仅记录）
        if self.router:
            model = self.router.select("coding")
            log.append(self._step("act", f"派发开发任务，选用模型：{model}"))

        # 3) Observe / 生成（骨架占位）
        log.append(self._step("observe", "生成前端(Vite+Tailwind) + 后端(Laravel+Livewire) 骨架（占位）"))
        log.append(self._step("observe", "对照 acceptance.yaml 自检（待接入视觉比对/性能门禁）"))

        # 4) 交付占位（真实阶段写完整站点 + 触发验收）
        proj_dir = os.path.join(self.data_dir, "projects", task_id)
        os.makedirs(proj_dir, exist_ok=True)
        manifest = {
            "task_id": task_id,
            "goal": goal,
            "style": style_name,
            "created_at": _now(),
        }
        with open(os.path.join(proj_dir, "manifest.json"), "w", encoding="utf-8") as fh:
            json.dump(manifest, fh, ensure_ascii=False, indent=2)

        # 5) 进化钩子（流程/知识进化，纯 API 即可）
        if self.evolver:
            self.evolver.ingest({"task_id": task_id, "ok": True})

        self._status[task_id] = {"state": "done", "steps": log}
        logger.info("task %s done -> %s", task_id, proj_dir)

    @staticmethod
    def _step(phase: str, msg: str) -> dict:
        s = {"phase": phase, "msg": msg, "at": _now()}
        logger.info("[%s] %s", phase, msg)
        return s


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()
