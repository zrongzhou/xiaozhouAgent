"""模型路由器（方案第十/十一章：混合 + 手工指定 + 进化池）。

功能：
- 按任务类型自动选择模型
- 支持任务级/项目级手工指定
- 主模型失败时自动回退
- 模型能力统计与成本追踪
"""
import os
import logging
import asyncio
import httpx
from typing import Optional, Dict, Any, List
from dataclasses import dataclass, field
from datetime import datetime, timezone

try:
    import yaml
except ImportError:
    yaml = None

logger = logging.getLogger("model_router")


@dataclass
class ModelStats:
    """模型统计数据"""
    model_id: str
    total_requests: int = 0
    successful_requests: int = 0
    failed_requests: int = 0
    total_tokens_input: int = 0
    total_tokens_output: int = 0
    total_cost: float = 0.0
    avg_response_time_ms: float = 0.0


class ModelRouter:
    """模型路由器"""
    
    def __init__(self, config_dir: str):
        self.config_dir = config_dir
        self.pool: List[Dict[str, Any]] = []
        self.rules: List[Dict[str, Any]] = []
        self.fallback: str = "brain"
        self._stats: Dict[str, ModelStats] = {}
        self._clients: Dict[str, httpx.AsyncClient] = {}
        self._load()
        
    def _load(self) -> None:
        """加载模型配置"""
        path = os.path.join(self.config_dir, "model-profiles.yaml")
        if not os.path.exists(path) or yaml is None:
            logger.warning("model-profiles.yaml 缺失或 pyyaml 未装，使用默认配置")
            self._load_defaults()
            return
            
        with open(path, "r", encoding="utf-8") as fh:
            cfg = yaml.safe_load(fh) or {}
            
        self.pool = cfg.get("pool", [])
        self.rules = cfg.get("router", {}).get("rules", [])
        self.fallback = cfg.get("router", {}).get("fallback", "brain")
        
        # 初始化模型统计
        for model in self.pool:
            model_id = model.get("id") or model.get("model", "unknown")
            self._stats[model_id] = ModelStats(model_id=model_id)
            
        logger.info(f"Loaded {len(self.pool)} model profiles, {len(self.rules)} routing rules")
        
    def _load_defaults(self):
        """加载默认模型配置"""
        self.pool = [
            {
                "id": "brain",
                "name": "主脑模型",
                "provider": "openai",
                "model": "gpt-4",
                "tier": "brain",
                "capabilities": ["code", "vision", "long_context"],
            },
            {
                "id": "light",
                "name": "轻量模型",
                "provider": "openai", 
                "model": "gpt-3.5-turbo",
                "tier": "light",
                "capabilities": ["code"],
            },
        ]
        self.rules = [
            {"task": ["prd", "design", "code"], "model": "brain"},
            {"task": ["test", "deploy"], "model": "light"},
        ]
        
    def select(self, task: str, override: Optional[str] = None) -> str:
        """选择模型。
        
        优先级：
        1. 手工指定 (override)
        2. 路由规则
        3. 默认回退
        """
        if override:
            return override
            
        # 按规则匹配
        for rule in self.rules:
            if task in rule.get("task", []):
                model = rule.get("model")
                if model:
                    return model
                    
        # 回退到默认
        for model in self.pool:
            if model.get("tier") == self.fallback:
                return model.get("model") or model.get("id")
                
        return "default"
    
    def get_model_config(self, model_id: str) -> Optional[Dict[str, Any]]:
        """获取模型配置"""
        for model in self.pool:
            if model.get("id") == model_id or model.get("model") == model_id:
                return model
        return None
    
    def record_usage(self, model_id: str, tokens_in: int, tokens_out: int, 
                     cost: float, duration_ms: float, success: bool):
        """记录模型使用统计"""
        if model_id not in self._stats:
            self._stats[model_id] = ModelStats(model_id=model_id)
            
        stats = self._stats[model_id]
        stats.total_requests += 1
        stats.total_tokens_input += tokens_in
        stats.total_tokens_output += tokens_out
        stats.total_cost += cost
        
        if success:
            stats.successful_requests += 1
        else:
            stats.failed_requests += 1
            
        # 更新平均响应时间
        total_requests = stats.total_requests
        stats.avg_response_time_ms = (
            (stats.avg_response_time_ms * (total_requests - 1)) + duration_ms
        ) / total_requests
        
    def get_stats(self) -> Dict[str, Dict[str, Any]]:
        """获取所有模型统计"""
        return {
            model_id: {
                "total_requests": stats.total_requests,
                "successful_requests": stats.successful_requests,
                "failed_requests": stats.failed_requests,
                "success_rate": (
                    stats.successful_requests / stats.total_requests * 100
                    if stats.total_requests > 0 else 0
                ),
                "total_tokens_input": stats.total_tokens_input,
                "total_tokens_output": stats.total_tokens_output,
                "total_cost": round(stats.total_cost, 6),
                "avg_response_time_ms": round(stats.avg_response_time_ms, 2),
            }
            for model_id, stats in self._stats.items()
        }
    
    def get_best_model(self, capability: str) -> Optional[str]:
        """根据能力需求选择最佳模型"""
        capable_models = []
        for model in self.pool:
            capabilities = model.get("capabilities", [])
            if capability in capabilities:
                model_id = model.get("id") or model.get("model")
                stats = self._stats.get(model_id)
                if stats and stats.total_requests > 0:
                    capable_models.append((
                        model_id,
                        stats.successful_requests / stats.total_requests,
                        stats.avg_response_time_ms,
                    ))
                    
        if not capable_models:
            return None
            
        # 按成功率排序，成功率相同时按响应时间
        capable_models.sort(key=lambda x: (-x[1], x[2]))
        return capable_models[0][0]
