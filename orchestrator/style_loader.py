"""风格加载器（方案第十九章：风格引导）。

加载 /config/style-guide.yaml，作为每次构建的“风格上下文”。
纯 API 阶段不微调权重，靠本文件让生成结果对齐用户的视觉偏好。
"""
import os
import logging

try:
    import yaml
except ImportError:  # pragma: no cover
    yaml = None

logger = logging.getLogger("style_loader")


class StyleLoader:
    def __init__(self, config_dir: str):
        self.config_dir = config_dir
        self.name: str | None = None
        self.data: dict = {}
        self._load()

    def _load(self) -> None:
        path = os.path.join(self.config_dir, "style-guide.yaml")
        if not os.path.exists(path) or yaml is None:
            logger.warning("style-guide.yaml 缺失或 pyyaml 未装")
            return
        with open(path, "r", encoding="utf-8") as fh:
            self.data = yaml.safe_load(fh) or {}
        self.name = self.data.get("name")
