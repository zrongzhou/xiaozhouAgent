"""模型路由器（方案第十/十一章：混合 + 手工指定 + 进化池）。

- 默认按 router.rules 按任务类型派模型；
- 支持 task 级 / 项目级手工指定（override 优先）；
- 主模型失败时回退到 fallback（由调用方在 AgentLoop 中实现）。
"""
import os
import logging

try:
    import yaml
except ImportError:  # pragma: no cover
    yaml = None

logger = logging.getLogger("model_router")


class ModelRouter:
    def __init__(self, config_dir: str):
        self.config_dir = config_dir
        self.pool: list = []
        self.rules: list = []
        self._load()

    def _load(self) -> None:
        path = os.path.join(self.config_dir, "model-profiles.yaml")
        if not os.path.exists(path) or yaml is None:
            logger.warning("model-profiles.yaml 缺失或 pyyaml 未装，使用默认回退")
            return
        with open(path, "r", encoding="utf-8") as fh:
            cfg = yaml.safe_load(fh) or {}
        self.pool = cfg.get("pool", [])
        self.rules = cfg.get("router", {}).get("rules", [])

    def select(self, task: str, override: str | None = None) -> str:
        """返回模型 id。手工指定优先，其次路由规则，最后回退 main 档。"""
        if override:
            return override
        for rule in self.rules:
            if task in rule.get("task", []):
                return rule.get("model")
        for m in self.pool:
            if m.get("tier") == "main":
                return m.get("model") or m.get("id")
        return "default"
